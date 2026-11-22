<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Sesi absensi, scan QR (AJAX), konfirmasi ketidakhadiran, koreksi. Guru & Admin. */
class Scan extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->need(array('guru', 'admin'));
        $this->load->model('Attendance_model');
    }

    private function load_session($id)
    {
        $s = $this->db->query('SELECT s.*, c.name class_name, sub.name subject_name, t.name teacher_name FROM attendance_sessions s JOIN classes c ON c.id=s.class_id JOIN subjects sub ON sub.id=s.subject_id LEFT JOIN teachers t ON t.id=s.teacher_id WHERE s.id=? AND s.school_id=?', array((int)$id, $this->sid))->row();
        if (!$s) show_404();
        if ($this->role === 'guru' && $s->session_type !== 'harian' && (int)$s->teacher_id !== $this->teacher_id) show_error('Sesi ini bukan milik Anda.', 403, 'Akses Ditolak');
        return $s;
    }

    public function index() { redirect('dashboard'); }

    /** Mode absensi harian: satu sesi per kelas per hari (dibuka oleh wali kelas atau admin). */
    public function start_harian()
    {
        $this->require_post();
        if (attendance_mode() !== 'harian') { $this->flash('danger', 'Sekolah ini memakai absensi per mata pelajaran.'); redirect('dashboard'); }
        $cid = (int)$this->input->post('class_id');
        $cl = $this->own('classes', $cid);
        if (!$cl) show_404();
        $date = date('Y-m-d');
        $eff = explode(',', setting('effective_days', '1,2,3,4,5'));
        if (!in_array((string)date('N'), $eff, TRUE)) { $this->flash('danger', 'Hari ini bukan hari efektif sekolah (Pengaturan > Hari Efektif).'); redirect('dashboard'); }
        $subj = daily_subject_id();
        $ses = $this->db->get_where('attendance_sessions', array('school_id' => $this->sid, 'class_id' => $cid, 'session_date' => $date, 'session_type' => 'harian'))->row();
        $now = date('Y-m-d H:i:s');
        if (!$ses) {
            $start = setting('start_time', '07:00') . ':00';
            $late = date('H:i:s', strtotime($date . ' ' . $start) + (int)setting('late_minutes', 15) * 60);
            $sem = $this->active_semester();
            $total = (int)$this->db->where(array('school_id' => $this->sid, 'class_id' => $cid, 'is_active' => 1, 'deleted_at' => NULL))->count_all_results('students');
            $this->db->insert('attendance_sessions', array('school_id' => $this->sid, 'session_type' => 'harian', 'schedule_id' => NULL, 'semester_id' => $sem ? $sem->id : NULL,
                'class_id' => $cid, 'subject_id' => $subj, 'teacher_id' => $this->role === 'guru' ? $this->teacher_id : ($cl->homeroom_teacher_id ?: NULL), 'session_date' => $date, 'start_time' => $start, 'end_time' => '12:00:00',
                'late_limit' => $late, 'total_students' => $total, 'status' => 'open', 'started_by' => $this->uid, 'started_at' => $now, 'created_at' => $now, 'updated_at' => $now));
            $id = $this->db->insert_id();
            audit('mulai_sesi_harian', 'sesi=' . $id . ' kelas=' . $cid);
        } else {
            $id = $ses->id;
            if ($ses->status === 'closed') {
                $this->db->update('attendance_sessions', array('status' => 'open', 'ended_at' => NULL, 'updated_at' => $now), array('id' => $id, 'school_id' => $this->sid));
                audit('buka_ulang_sesi', 'sesi=' . $id);
            }
        }
        redirect('scan/run/' . $id);
    }

    public function start()
    {
        $this->require_post();
        if (attendance_mode() === 'harian') { $this->flash('danger', 'Sekolah ini memakai absensi harian. Mulai absensi dari daftar kelas.'); redirect('dashboard'); }
        $sid = (int)$this->input->post('schedule_id');
        $sc = $this->db->get_where('schedules', array('id' => $sid, 'school_id' => $this->sid))->row();
        if (!$sc) show_404();
        if ($this->role === 'guru' && (int)$sc->teacher_id !== $this->teacher_id) show_error('Jadwal ini bukan jadwal Anda.', 403, 'Akses Ditolak');
        if ($this->role === 'guru' && (int)$sc->day !== (int)date('N')) { $this->flash('danger', 'Jadwal ini bukan untuk hari ini.'); redirect('dashboard'); }
        $date = date('Y-m-d');
        $ses = $this->db->get_where('attendance_sessions', array('school_id' => $this->sid, 'schedule_id' => $sid, 'session_date' => $date))->row();
        $now = date('Y-m-d H:i:s');
        if (!$ses) {
            $total = (int)$this->db->where(array('school_id' => $this->sid, 'class_id' => $sc->class_id, 'is_active' => 1, 'deleted_at' => NULL))->count_all_results('students');
            $late = date('H:i:s', strtotime($date . ' ' . $sc->start_time) + (int)setting('late_minutes', 15) * 60);
            $this->db->insert('attendance_sessions', array('school_id' => $this->sid, 'schedule_id' => $sc->id, 'semester_id' => $sc->semester_id, 'class_id' => $sc->class_id, 'subject_id' => $sc->subject_id,
                'teacher_id' => $sc->teacher_id, 'session_date' => $date, 'start_time' => $sc->start_time, 'end_time' => $sc->end_time, 'late_limit' => $late,
                'total_students' => $total, 'status' => 'open', 'started_by' => $this->uid, 'started_at' => $now, 'created_at' => $now, 'updated_at' => $now));
            $id = $this->db->insert_id();
            audit('mulai_sesi', 'sesi=' . $id);
        } else {
            $id = $ses->id;
            if ($ses->status === 'closed') {
                $this->db->update('attendance_sessions', array('status' => 'open', 'ended_at' => NULL, 'updated_at' => $now), array('id' => $id));
                audit('buka_ulang_sesi', 'sesi=' . $id);
            }
        }
        redirect('scan/run/' . $id);
    }

    public function run($id)
    {
        $s = $this->load_session($id);
        if ($s->status !== 'open') redirect('scan/detail/' . $s->id);
        if ($s->session_date !== date('Y-m-d')) { $this->Attendance_model->close_session($s->id); redirect('scan/confirm/' . $s->id); }
        $this->render('scan/index', array('title' => 'Scan Absensi', 's' => $s, 'stats' => $this->Attendance_model->stats($s->id)));
    }

    public function ajax_scan()
    {
        $this->require_post();
        $s = $this->load_session($this->input->post('session_id'));
        $res = $this->Attendance_model->scan($s, $this->input->post('code'), $this->uid, array(
            'client_uuid' => substr(preg_replace('/[^A-Za-z0-9\-]/', '', (string)$this->input->post('client_uuid')), 0, 64) ?: NULL,
            'offline' => $this->input->post('offline') === '1', 'client_ms' => (int)$this->input->post('client_ms')));
        $res['stats'] = $this->Attendance_model->stats($s->id);
        $this->json($res);
    }

    public function status($id)
    {
        $s = $this->load_session($id);
        $present = $this->db->query("SELECT st.name, r.status, r.scanned_at FROM attendance_records r JOIN students st ON st.id=r.student_id WHERE r.session_id=? AND r.status IN ('hadir','terlambat') ORDER BY r.scanned_at DESC LIMIT 200", array($s->id))->result();
        $missing = $this->db->query("SELECT st.name FROM students st WHERE st.school_id=? AND st.class_id=? AND st.is_active=1 AND st.deleted_at IS NULL AND NOT EXISTS (SELECT 1 FROM attendance_records r WHERE r.session_id=? AND r.student_id=st.id AND r.status IN ('hadir','terlambat')) ORDER BY st.name", array($this->sid, $s->class_id, $s->id))->result();
        $recent = $this->db->query("SELECT result, message, created_at FROM scan_logs WHERE school_id=? AND session_id=? ORDER BY id DESC LIMIT 8", array($this->sid, $s->id))->result();
        $pl = array();
        foreach ($present as $p) { $pl[] = array('name' => $p->name, 'status' => $p->status, 'time' => date('H.i', strtotime($p->scanned_at))); }
        $ml = array();
        foreach ($missing as $m) { $ml[] = $m->name; }
        $rl = array();
        foreach ($recent as $r) { $rl[] = array('result' => $r->result, 'message' => $r->message, 'time' => date('H.i.s', strtotime($r->created_at))); }
        $this->json(array('ok' => TRUE, 'status' => $s->status, 'stats' => $this->Attendance_model->stats($s->id), 'present' => $pl, 'missing' => $ml, 'recent' => $rl));
    }

    public function finish()
    {
        $this->require_post();
        $s = $this->load_session($this->input->post('session_id'));
        $this->Attendance_model->close_session($s->id);
        audit('akhiri_sesi', 'sesi=' . $s->id);
        redirect('scan/confirm/' . $s->id);
    }

    public function confirm($id)
    {
        $s = $this->load_session($id);
        if ($s->status === 'open') redirect('scan/run/' . $s->id);
        $recs = $this->db->query("SELECT r.*, st.name, st.nis FROM attendance_records r JOIN students st ON st.id=r.student_id WHERE r.session_id=? ORDER BY FIELD(r.status,'belum','alpa','sakit','izin','terlambat','hadir'), st.name", array($s->id))->result();
        $this->render('scan/confirm', array('title' => 'Konfirmasi Kehadiran', 's' => $s, 'recs' => $recs, 'stats' => $this->Attendance_model->stats($s->id)));
    }

    /** Simpan status sakit/izin/alpa/belum untuk siswa yang tidak scan. */
    public function save_confirm()
    {
        $this->require_post();
        $s = $this->load_session($this->input->post('session_id'));
        $st = $this->input->post('status');
        $note = $this->input->post('note');
        $allowed = array('belum', 'sakit', 'izin', 'alpa');
        $changed = 0;
        if (is_array($st)) {
            foreach ($st as $rid => $val) {
                if (!in_array($val, $allowed, TRUE)) continue;
                $r = $this->db->get_where('attendance_records', array('id' => (int)$rid, 'session_id' => $s->id))->row();
                if (!$r || in_array($r->status, array('hadir', 'terlambat'), TRUE)) continue;
                if ($r->status !== $val || (isset($note[$rid]) && $note[$rid] !== $r->note)) {
                    $this->db->update('attendance_records', array('status' => $val, 'note' => isset($note[$rid]) ? substr(trim($note[$rid]), 0, 255) : $r->note,
                        'source' => 'manual', 'corrected_by' => $this->uid, 'updated_at' => date('Y-m-d H:i:s')), array('id' => $r->id));
                    $changed++;
                }
            }
        }
        audit('konfirmasi_kehadiran', 'sesi=' . $s->id . ' diubah=' . $changed);
        $msg = $changed . ' status disimpan.';
        if ($this->input->post('send_wa')) {
            $this->load->model('Wa_model');
            $n = 0;
            foreach ($this->db->query("SELECT id FROM attendance_records WHERE session_id=? AND status IN ('belum','alpa','izin','sakit')", array($s->id))->result() as $r) {
                if ($this->Wa_model->queue_record($r->id)) $n++;
            }
            $msg .= ' ' . $n . ' notifikasi WhatsApp dimasukkan ke antrean' . (setting('wa_enabled', '0') !== '1' ? ' (catatan: integrasi WhatsApp belum diaktifkan di Pengaturan).' : '.');
        }
        $this->flash('success', $msg);
        redirect('scan/confirm/' . $s->id);
    }

    public function detail($id)
    {
        $s = $this->load_session($id);
        $recs = $this->db->query("SELECT r.*, st.name, st.nis FROM attendance_records r JOIN students st ON st.id=r.student_id WHERE r.session_id=? ORDER BY st.name", array($s->id))->result();
        $this->render('scan/detail', array('title' => 'Detail Absensi', 's' => $s, 'recs' => $recs, 'stats' => $this->Attendance_model->stats($s->id)));
    }

    /** Koreksi absensi dengan alasan wajib. */
    public function correct()
    {
        $this->require_post();
        $s = $this->load_session($this->input->post('session_id'));
        $r = $this->db->get_where('attendance_records', array('id' => (int)$this->input->post('record_id'), 'session_id' => $s->id))->row();
        $status = $this->input->post('status');
        $reason = trim((string)$this->input->post('reason'));
        if (!$r || !in_array($status, array('hadir', 'terlambat', 'sakit', 'izin', 'alpa', 'belum'), TRUE)) show_error('Data tidak valid.', 400);
        if ($reason === '') { $this->flash('danger', 'Alasan koreksi wajib diisi.'); redirect('scan/detail/' . $s->id); }
        $upd = array('status' => $status, 'note' => substr($reason, 0, 255), 'source' => 'manual', 'corrected_by' => $this->uid, 'updated_at' => date('Y-m-d H:i:s'));
        if (in_array($status, array('hadir', 'terlambat'), TRUE) && !$r->scanned_at) $upd['scanned_at'] = date('Y-m-d H:i:s');
        $this->db->update('attendance_records', $upd, array('id' => $r->id));
        audit('koreksi_absensi', 'record=' . $r->id . ' ' . $r->status . '->' . $status . ' alasan=' . $reason);
        $this->flash('success', 'Koreksi disimpan.');
        redirect('scan/detail/' . $s->id);
    }

    public function history()
    {
        $w = 's.school_id=' . (int)$this->sid . ($this->role === 'guru' ? " AND (s.teacher_id=" . (int)$this->teacher_id . " OR s.session_type='harian')" : '');
        $rows = $this->db->query("SELECT s.*, c.name class_name, sub.name subject_name, t.name teacher_name,
            (SELECT COUNT(*) FROM attendance_records r WHERE r.session_id=s.id AND r.status IN ('hadir','terlambat')) present,
            (SELECT COUNT(*) FROM attendance_records r WHERE r.session_id=s.id) total
            FROM attendance_sessions s JOIN classes c ON c.id=s.class_id JOIN subjects sub ON sub.id=s.subject_id LEFT JOIN teachers t ON t.id=s.teacher_id
            WHERE $w ORDER BY s.session_date DESC, s.start_time DESC LIMIT 200")->result();
        $this->render('scan/history', array('title' => 'Riwayat Absensi', 'rows' => $rows));
    }

    public function notifications()
    {
        $w = 'n.school_id=' . (int)$this->sid . ($this->role === 'guru' ? " AND (ses.teacher_id=" . (int)$this->teacher_id . " OR ses.session_type='harian')" : '');
        $rows = $this->db->query("SELECT n.*, st.name student_name FROM whatsapp_notifications n JOIN students st ON st.id=n.student_id LEFT JOIN attendance_sessions ses ON ses.id=n.session_id WHERE $w ORDER BY n.id DESC LIMIT 200")->result();
        $this->render('scan/notifications', array('title' => 'Riwayat Notifikasi WhatsApp', 'rows' => $rows));
    }

    /** Dipanggil di latar belakang oleh scanner agar proses WA tidak menunggu scan. */
    public function kick()
    {
        $this->require_post();
        $this->load->model('Wa_model');
        $this->json($this->Wa_model->process(5));
    }
}
