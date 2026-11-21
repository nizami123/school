<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Antrean notifikasi WhatsApp. Scan hanya menulis baris antrean; pengiriman dilakukan terpisah. */
class Wa_model extends CI_Model
{
    private $map = array('hadir' => 'hadir', 'terlambat' => 'terlambat', 'belum' => 'belum', 'alpa' => 'belum', 'izin' => 'izin', 'sakit' => 'sakit');
    private $toggle = array('hadir' => 'wa_notify_hadir', 'terlambat' => 'wa_notify_terlambat', 'belum' => 'wa_notify_absent', 'izin' => 'wa_notify_izin', 'sakit' => 'wa_notify_sakit');

    public function templates()
    {
        $this->config->load('wa_defaults', TRUE);
        return $this->config->item('wa_templates', 'wa_defaults');
    }

    public function render($tpl, $vars)
    {
        $s = $tpl;
        foreach ($vars as $k => $v) { $s = str_replace('{' . $k . '}', $v, $s); }
        return $s;
    }

    /** Masukkan notifikasi untuk satu catatan kehadiran ke antrean. Mengembalikan TRUE jika ditambahkan. */
    public function queue_record($record_id)
    {
        if (setting('wa_enabled', '0') !== '1') return FALSE;
        $r = $this->db->query("SELECT r.id, r.status, r.scanned_at, st.id AS sid, st.name, c.name AS class_name, sub.name AS subject, t.name AS teacher,
                ses.session_date, ses.id AS session_id, p.id AS parent_id, p.whatsapp
            FROM attendance_records r
            JOIN attendance_sessions ses ON ses.id=r.session_id
            JOIN students st ON st.id=r.student_id
            LEFT JOIN classes c ON c.id=ses.class_id
            JOIN subjects sub ON sub.id=ses.subject_id
            JOIN teachers t ON t.id=ses.teacher_id
            LEFT JOIN student_parents sp ON sp.student_id=st.id AND sp.is_primary=1
            LEFT JOIN parents p ON p.id=sp.parent_id
            WHERE r.id=?", array($record_id))->row();
        if (!$r || !isset($this->map[$r->status])) return FALSE;
        $type = $this->map[$r->status];
        if (setting($this->toggle[$type], '1') !== '1') return FALSE;

        $policy = setting('wa_policy', 'each');
        if (in_array($type, array('hadir', 'terlambat'), TRUE)) {
            if ($policy === 'daily') return FALSE;
            if ($policy === 'first') {
                $n = $this->db->where('student_id', $r->sid)->where('notif_date', $r->session_date)
                    ->where_in('notif_type', array('hadir', 'terlambat'))->count_all_results('whatsapp_notifications');
                if ($n > 0) return FALSE;
            }
        }
        $dedupe = $type . ':' . $r->id;
        if ($this->db->where('dedupe_key', $dedupe)->count_all_results('whatsapp_notifications') > 0) return FALSE;

        $vars = array('nama' => $r->name, 'kelas' => $r->class_name, 'mapel' => $r->subject, 'guru' => $r->teacher,
            'hari_tanggal' => tanggal_id($r->session_date), 'waktu' => $r->scanned_at ? jam_id($r->scanned_at) : '-', 'sekolah' => setting('school_name', 'Sekolah'));
        $tpls = $this->templates();
        $tpl = setting('tpl_' . $type, $tpls['tpl_' . $type]);
        $params = array($vars['nama'], $vars['kelas'], $vars['mapel'], $vars['guru'], $vars['hari_tanggal'], $vars['waktu']);
        return $this->insert_row($dedupe, $r->id, $r->session_id, $r->sid, $r->parent_id, $type, $r->session_date, $r->whatsapp,
            $this->render($tpl, $vars), $params);
    }

    /** Rekap harian untuk satu siswa. */
    public function queue_daily($student, $date, $lines)
    {
        $dedupe = 'rekap:' . $student->id . ':' . $date;
        if ($this->db->where('dedupe_key', $dedupe)->count_all_results('whatsapp_notifications') > 0) return FALSE;
        $tpls = $this->templates();
        $daftar = implode("\n", $lines);
        $vars = array('nama' => $student->name, 'kelas' => $student->class_name, 'hari_tanggal' => tanggal_id($date), 'daftar' => $daftar, 'sekolah' => setting('school_name', 'Sekolah'));
        return $this->insert_row($dedupe, NULL, NULL, $student->id, $student->parent_id, 'rekap', $date, $student->whatsapp,
            $this->render(setting('tpl_rekap', $tpls['tpl_rekap']), $vars), array($vars['nama'], $vars['kelas'], $vars['hari_tanggal'], implode('; ', $lines)));
    }

    private function insert_row($dedupe, $record_id, $session_id, $student_id, $parent_id, $type, $date, $phone, $message, $params)
    {
        $phone = normalize_phone($phone);
        $now = date('Y-m-d H:i:s');
        $ok = valid_phone($phone);
        $this->db->db_debug = FALSE;
        $this->db->insert('whatsapp_notifications', array(
            'record_id' => $record_id, 'session_id' => $session_id, 'student_id' => $student_id, 'parent_id' => $parent_id,
            'notif_type' => $type, 'notif_date' => $date, 'phone' => $phone ?: NULL, 'message' => $message,
            'params' => json_encode($params), 'template_name' => NULL,
            'status' => $ok ? 'menunggu' : 'gagal', 'attempts' => $ok ? 0 : 3,
            'response' => $ok ? NULL : 'Nomor WhatsApp orang tua belum diisi / tidak valid.',
            'dedupe_key' => $dedupe, 'created_at' => $now, 'updated_at' => $now));
        return $this->db->affected_rows() > 0;
    }

    /** Proses antrean. Aman dipanggil bersamaan (baris dikunci per item). */
    public function process($limit = 5)
    {
        $this->load->library('whatsapp');
        $res = array('sent' => 0, 'failed' => 0, 'configured' => $this->whatsapp->configured());
        if (!$res['configured']) return $res;
        $now = date('Y-m-d H:i:s');
        $rows = $this->db->query("SELECT * FROM whatsapp_notifications WHERE status='menunggu' AND (next_retry_at IS NULL OR next_retry_at<=?) ORDER BY id LIMIT " . (int)$limit, array($now))->result();
        foreach ($rows as $n) {
            $this->db->query("UPDATE whatsapp_notifications SET status='mengirim', updated_at=? WHERE id=? AND status='menunggu'", array($now, $n->id));
            if ($this->db->affected_rows() < 1) continue;
            $out = $this->whatsapp->send($n);
            $attempts = $n->attempts + 1;
            if ($out['ok']) {
                $this->db->update('whatsapp_notifications', array('status' => 'terkirim', 'attempts' => $attempts, 'wa_message_id' => $out['id'],
                    'response' => substr($out['raw'], 0, 2000), 'sent_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')), array('id' => $n->id));
                $res['sent']++;
            } else {
                $retry = $attempts < 3;
                $this->db->update('whatsapp_notifications', array('status' => $retry ? 'menunggu' : 'gagal', 'attempts' => $attempts,
                    'next_retry_at' => $retry ? date('Y-m-d H:i:s', time() + $attempts * 120) : NULL,
                    'response' => substr($out['raw'], 0, 2000), 'updated_at' => date('Y-m-d H:i:s')), array('id' => $n->id));
                $res['failed']++;
            }
        }
        // pulihkan baris 'mengirim' yang menggantung > 10 menit
        $this->db->query("UPDATE whatsapp_notifications SET status='menunggu' WHERE status='mengirim' AND updated_at < ?", array(date('Y-m-d H:i:s', time() - 600)));
        return $res;
    }
}
