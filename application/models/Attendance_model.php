<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_model extends CI_Model
{
    private function fail($type, $msg, $extra = array())
    {
        return array_merge(array('ok' => FALSE, 'type' => $type, 'message' => $msg), $extra);
    }

    public function stats($session_id, $class_id = NULL)
    {
        $ses = $this->db->get_where('attendance_sessions', array('id' => $session_id))->row();
        if (!$ses) return array('total' => 0, 'present' => 0, 'absent' => 0, 'pct' => 0);
        $total = $ses->total_students;
        if ($ses->status === 'open') {
            $total = (int)$this->db->where(array('class_id' => $ses->class_id, 'is_active' => 1, 'deleted_at' => NULL))->count_all_results('students');
        }
        $present = (int)$this->db->where('session_id', $session_id)->where_in('status', array('hadir', 'terlambat'))->count_all_results('attendance_records');
        $late = (int)$this->db->where(array('session_id' => $session_id, 'status' => 'terlambat'))->count_all_results('attendance_records');
        return array('total' => $total, 'present' => $present, 'late' => $late, 'absent' => max(0, $total - $present),
            'pct' => $total > 0 ? round($present * 100 / $total) : 0);
    }

    private function log($session, $uid, $code, $result, $msg)
    {
        $this->db->insert('scan_logs', array('session_id' => $session ? $session->id : NULL, 'user_id' => $uid,
            'code_prefix' => substr(preg_replace('/[^A-Za-z0-9:]/', '', (string)$code), 0, 12), 'result' => $result,
            'message' => substr($msg, 0, 255), 'created_at' => date('Y-m-d H:i:s')));
    }

    /**
     * Validasi & catat satu hasil scan. Waktu utama selalu waktu server.
     * $opts: client_uuid, offline (bool), client_ms (epoch ms dari perangkat, hanya dipakai utk scan offline yg disinkronkan)
     */
    public function scan($session, $code, $uid, $opts = array())
    {
        $code = trim((string)$code);
        $raw = $code;
        if (strpos($code, 'QRSA:') === 0) $code = substr($code, 5);
        $msgInvalid = 'QR CODE TIDAK VALID ATAU TIDAK TERDAFTAR.';

        if (!preg_match('/^[a-f0-9]{32}$/', $code)) {
            $this->log($session, $uid, $raw, 'invalid', $msgInvalid);
            return $this->fail('invalid', $msgInvalid);
        }
        $qr = $this->db->get_where('student_qr_codes', array('token' => $code))->row();
        if (!$qr) {
            $this->log($session, $uid, $raw, 'invalid', $msgInvalid);
            return $this->fail('invalid', $msgInvalid);
        }
        if (!$qr->is_active) {
            $this->log($session, $uid, $raw, 'invalid', 'QR lama sudah dinonaktifkan');
            return $this->fail('invalid', $msgInvalid, array('detail' => 'QR ini sudah diganti/dinonaktifkan. Gunakan kartu terbaru.'));
        }
        $st = $this->db->query('SELECT s.*, c.name AS class_name FROM students s LEFT JOIN classes c ON c.id=s.class_id WHERE s.id=?', array($qr->student_id))->row();
        if (!$st || $st->deleted_at || !$st->is_active) {
            $this->log($session, $uid, $raw, 'inactive', 'Siswa tidak aktif');
            return $this->fail('inactive', 'SISWA TIDAK AKTIF.');
        }
        $stu = array('name' => $st->name, 'class' => $st->class_name, 'photo' => student_photo($st->photo));
        if ((int)$st->class_id !== (int)$session->class_id) {
            $this->log($session, $uid, $raw, 'class', $st->name . ' bukan siswa kelas ini');
            return $this->fail('class', 'SISWA TIDAK TERDAFTAR DI KELAS INI.', array('student' => $stu));
        }
        if ($session->status !== 'open' || $session->session_date !== date('Y-m-d')) {
            $this->log($session, $uid, $raw, 'closed', 'Sesi tidak aktif');
            return $this->fail('closed', 'SESI ABSENSI TIDAK AKTIF.', array('student' => $stu));
        }

        $existing = $this->db->get_where('attendance_records', array('session_id' => $session->id, 'student_id' => $st->id))->row();
        if ($existing && in_array($existing->status, array('hadir', 'terlambat'), TRUE)) {
            $dupSame = (!empty($opts['client_uuid']) && $existing->client_uuid === $opts['client_uuid']);
            $this->log($session, $uid, $raw, 'duplicate', $st->name);
            return $this->fail('duplicate', 'SISWA SUDAH TERCATAT HADIR PADA SESI INI.', array('student' => $stu, 'repeat' => $dupSame,
                'time' => jam_id($existing->scanned_at, FALSE)));
        }

        $nowTs = time();
        $whenTs = $nowTs;
        $source = 'scan';
        $clientTime = NULL;
        if (!empty($opts['offline'])) {
            $source = 'offline_sync';
            if (!empty($opts['client_ms'])) {
                $c = (int)floor($opts['client_ms'] / 1000);
                $clientTime = date('Y-m-d H:i:s', $c);
                // waktu perangkat hanya diterima jika masuk akal (setelah sesi dimulai & tidak di masa depan)
                if ($c <= $nowTs && $c >= strtotime($session->started_at) - 60) $whenTs = $c;
            }
        }
        $limit = strtotime($session->session_date . ' ' . $session->late_limit);
        $status = ($whenTs > $limit) ? 'terlambat' : 'hadir';
        $whenStr = date('Y-m-d H:i:s', $whenTs);
        $nowStr = date('Y-m-d H:i:s', $nowTs);

        $this->db->db_debug = FALSE;
        if ($existing) {
            $this->db->update('attendance_records', array('status' => $status, 'qr_id' => $qr->id, 'scanned_at' => $whenStr, 'client_time' => $clientTime,
                'source' => $source, 'client_uuid' => !empty($opts['client_uuid']) ? $opts['client_uuid'] : NULL, 'updated_at' => $nowStr), array('id' => $existing->id));
            $rid = $existing->id;
        } else {
            $this->db->insert('attendance_records', array('session_id' => $session->id, 'student_id' => $st->id, 'qr_id' => $qr->id, 'status' => $status,
                'scanned_at' => $whenStr, 'client_time' => $clientTime, 'source' => $source,
                'client_uuid' => !empty($opts['client_uuid']) ? $opts['client_uuid'] : NULL, 'created_at' => $nowStr, 'updated_at' => $nowStr));
            if ($this->db->affected_rows() < 1) { // balapan: permintaan ganda
                $this->log($session, $uid, $raw, 'duplicate', $st->name);
                return $this->fail('duplicate', 'SISWA SUDAH TERCATAT HADIR PADA SESI INI.', array('student' => $stu, 'repeat' => TRUE));
            }
            $rid = $this->db->insert_id();
        }
        $this->log($session, $uid, $raw, $status, $st->name);

        $this->load->model('Wa_model');
        $this->Wa_model->queue_record($rid); // hanya menulis antrean (cepat), tidak memanggil API

        return array('ok' => TRUE, 'type' => $status, 'record_id' => $rid,
            'message' => 'BERHASIL! ' . $st->name . ' tercatat ' . ($status === 'hadir' ? 'hadir' : 'terlambat') . '.',
            'student' => $stu, 'time' => date('H.i', $whenTs), 'subject' => $session->subject_name);
    }

    /** Tutup sesi dan buat baris 'belum' untuk siswa yang tidak scan. */
    public function close_session($session_id)
    {
        $ses = $this->db->get_where('attendance_sessions', array('id' => $session_id))->row();
        if (!$ses) return FALSE;
        $now = date('Y-m-d H:i:s');
        $this->db->query("INSERT INTO attendance_records (session_id, student_id, status, source, created_at, updated_at)
            SELECT ?, s.id, COALESCE((SELECT lr.leave_type FROM leave_requests lr WHERE lr.student_id=s.id AND lr.status='disetujui' AND ? BETWEEN lr.date_from AND lr.date_to ORDER BY lr.id DESC LIMIT 1), 'belum'), 'system', ?, ? FROM students s
            WHERE s.class_id=? AND s.is_active=1 AND s.deleted_at IS NULL
              AND NOT EXISTS (SELECT 1 FROM attendance_records r WHERE r.session_id=? AND r.student_id=s.id)",
            array($ses->id, $ses->session_date, $now, $now, $ses->class_id, $ses->id));
        $total = (int)$this->db->where('session_id', $ses->id)->count_all_results('attendance_records');
        $this->db->update('attendance_sessions', array('status' => 'closed', 'ended_at' => $now, 'total_students' => $total, 'updated_at' => $now), array('id' => $ses->id));
        return TRUE;
    }

    /** Tutup sesi 'open' dari hari-hari sebelumnya. */
    public function close_stale()
    {
        $rows = $this->db->query("SELECT id FROM attendance_sessions WHERE status='open' AND session_date < ?", array(date('Y-m-d')))->result();
        foreach ($rows as $r) { $this->close_session($r->id); }
        return count($rows);
    }
}
