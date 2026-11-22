<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Mesin rekap/laporan. build() mengembalikan array(title, headers, rows, note). */
class Report_model extends CI_Model
{
    public static $types = array(
        'siswa' => 'Rekap Kehadiran per Siswa', 'kelas' => 'Rekap Kehadiran per Kelas', 'mapel' => 'Rekap Kehadiran per Mata Pelajaran',
        'guru' => 'Rekap Kehadiran per Guru', 'terlambat' => 'Laporan Keterlambatan', 'tidak_hadir' => 'Laporan Ketidakhadiran',
        'persentase' => 'Laporan Persentase Kehadiran', 'detail' => 'Detail Kehadiran (per mapel)', 'semester' => 'Rekapitulasi Semester',
        'whatsapp' => 'Laporan Notifikasi WhatsApp');

    public function filters($in)
    {
        $f = array('from' => date('Y-m-01'), 'to' => date('Y-m-d'), 'class_id' => 0, 'teacher_id' => 0, 'subject_id' => 0, 'student_id' => 0,
            'status' => '', 'semester_id' => 0, 'year_id' => 0);
        foreach ($f as $k => $v) {
            if (isset($in[$k]) && $in[$k] !== '') $f[$k] = is_int($v) ? (int)$in[$k] : $in[$k];
        }
        foreach (array('from', 'to') as $k) { if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $f[$k])) $f[$k] = date('Y-m-d'); }
        if (!in_array($f['status'], array('', 'hadir', 'terlambat', 'sakit', 'izin', 'alpa', 'belum'), TRUE)) $f['status'] = '';
        return $f;
    }

    private function where($f, &$args, $alias = 'ses')
    {
        $w = " WHERE $alias.session_date BETWEEN ? AND ?";
        $args = array($f['from'], $f['to']);
        if ($f['semester_id']) { $w .= " AND $alias.semester_id=?"; $args[] = $f['semester_id']; }
        elseif ($f['year_id']) { $w .= " AND $alias.semester_id IN (SELECT id FROM semesters WHERE academic_year_id=?)"; $args[] = $f['year_id']; }
        if ($f['class_id']) { $w .= " AND $alias.class_id=?"; $args[] = $f['class_id']; }
        if ($f['teacher_id']) { $w .= " AND $alias.teacher_id=?"; $args[] = $f['teacher_id']; }
        if ($f['subject_id']) { $w .= " AND $alias.subject_id=?"; $args[] = $f['subject_id']; }
        if ($f['student_id']) { $w .= ' AND r.student_id=?'; $args[] = $f['student_id']; }
        if ($f['status'] !== '') { $w .= ' AND r.status=?'; $args[] = $f['status']; }
        return $w;
    }

    private function sums()
    {
        return "COUNT(*) total, SUM(r.status='hadir') hadir, SUM(r.status='terlambat') terlambat, SUM(r.status='izin') izin, SUM(r.status='sakit') sakit, SUM(r.status='alpa') alpa, SUM(r.status='belum') belum, ROUND(SUM(r.status IN ('hadir','terlambat'))*100/COUNT(*),1) pct";
    }

    private function grp_headers($first)
    {
        return array_merge($first, array('Total', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa', 'Belum Konfirmasi', '% Hadir'));
    }

    private function grp_rows($q, $keys)
    {
        $rows = array();
        foreach ($q->result_array() as $r) {
            $line = array();
            foreach ($keys as $k) { $line[] = $r[$k]; }
            foreach (array('total', 'hadir', 'terlambat', 'izin', 'sakit', 'alpa', 'belum') as $k) { $line[] = (int)$r[$k]; }
            $line[] = $r['pct'] . '%';
            $rows[] = $line;
        }
        return $rows;
    }

    public function build($type, $f)
    {
        $args = array();
        $base = ' FROM attendance_records r JOIN attendance_sessions ses ON ses.id=r.session_id JOIN students st ON st.id=r.student_id JOIN classes c ON c.id=ses.class_id JOIN subjects sub ON sub.id=ses.subject_id JOIN teachers t ON t.id=ses.teacher_id';
        $title = isset(self::$types[$type]) ? self::$types[$type] : 'Laporan';
        switch ($type) {
            case 'siswa': case 'semester': case 'persentase':
                $w = $this->where($f, $args);
                $order = $type === 'persentase' ? ' ORDER BY pct ASC, st.name' : ' ORDER BY c.name, st.name';
                $q = $this->db->query("SELECT st.nis, st.name, c.name class_name, " . $this->sums() . $base . $w . ' GROUP BY st.id, st.nis, st.name, ses.class_id, c.name' . $order, $args);
                return array($title, $this->grp_headers(array('NIS', 'Nama', 'Kelas')), $this->grp_rows($q, array('nis', 'name', 'class_name')));
            case 'kelas':
                $w = $this->where($f, $args);
                $q = $this->db->query("SELECT c.name class_name, " . $this->sums() . $base . $w . ' GROUP BY c.id, c.name ORDER BY c.name', $args);
                return array($title, $this->grp_headers(array('Kelas')), $this->grp_rows($q, array('class_name')));
            case 'mapel':
                $w = $this->where($f, $args);
                $q = $this->db->query("SELECT sub.code, sub.name subject, " . $this->sums() . $base . $w . ' GROUP BY sub.id, sub.code, sub.name ORDER BY sub.name', $args);
                return array($title, $this->grp_headers(array('Kode', 'Mata Pelajaran')), $this->grp_rows($q, array('code', 'subject')));
            case 'guru':
                $w = $this->where($f, $args);
                $q = $this->db->query("SELECT t.name teacher, COUNT(DISTINCT ses.id) sesi, " . $this->sums() . $base . $w . ' GROUP BY t.id, t.name ORDER BY t.name', $args);
                return array($title, $this->grp_headers(array('Guru', 'Jml Sesi')), $this->grp_rows($q, array('teacher', 'sesi')));
            case 'terlambat': case 'tidak_hadir': case 'detail':
                if ($type === 'terlambat') $f['status'] = 'terlambat';
                $w = $this->where($f, $args);
                if ($type === 'tidak_hadir') $w .= " AND r.status IN ('belum','sakit','izin','alpa')";
                $q = $this->db->query("SELECT ses.session_date, st.nis, st.name, c.name class_name, sub.name subject, t.name teacher, r.status, r.scanned_at, r.note" . $base . $w . ' ORDER BY ses.session_date, c.name, ses.start_time, st.name LIMIT 5000', $args);
                $rows = array();
                foreach ($q->result() as $r) {
                    $rows[] = array(tanggal_id($r->session_date, FALSE), $r->nis, $r->name, $r->class_name, $r->subject, $r->teacher, status_label($r->status), $r->scanned_at ? jam_id($r->scanned_at, FALSE) : '-', (string)$r->note);
                }
                return array($title, array('Tanggal', 'NIS', 'Nama', 'Kelas', 'Mata Pelajaran', 'Guru', 'Status', 'Jam', 'Catatan'), $rows);
            case 'whatsapp':
                $q = $this->db->query("SELECT n.created_at, st.name, n.phone, n.notif_type, n.status, n.attempts, n.sent_at, n.response FROM whatsapp_notifications n JOIN students st ON st.id=n.student_id WHERE n.notif_date BETWEEN ? AND ? ORDER BY n.id DESC LIMIT 5000", array($f['from'], $f['to']));
                $rows = array();
                foreach ($q->result() as $r) { $rows[] = array($r->created_at, $r->name, $r->phone, $r->notif_type, status_label($r->status), $r->attempts, $r->sent_at ?: '-', mb_substr((string)$r->response, 0, 120)); }
                return array($title, array('Dibuat', 'Siswa', 'Nomor', 'Jenis', 'Status', 'Percobaan', 'Terkirim', 'Respons API'), $rows);
        }
        show_404();
    }

    /** Rekap satu siswa: baris per tanggal, kolom per mata pelajaran. */
    public function student_matrix($student_id, $f)
    {
        $q = $this->db->query("SELECT ses.session_date, sub.name subject, ses.start_time, r.status, r.scanned_at FROM attendance_records r JOIN attendance_sessions ses ON ses.id=r.session_id JOIN subjects sub ON sub.id=ses.subject_id WHERE r.student_id=? AND ses.session_date BETWEEN ? AND ? ORDER BY ses.session_date, ses.start_time", array($student_id, $f['from'], $f['to']));
        return $q->result();
    }

    /** Rekap bulanan otomatis per siswa. $ym 'YYYY-MM'. Mengembalikan baris per siswa (urut kelas, nama). */
    public function monthly($ym, $class_id = 0, $student_id = 0)
    {
        $from = $ym . '-01';
        $to = date('Y-m-t', strtotime($from));
        $sql = "SELECT st.id, st.nis, st.name, c.name class_name,
                COUNT(r.id) total, SUM(r.status='hadir') hadir, SUM(r.status='terlambat') terlambat, SUM(r.status='sakit') sakit, SUM(r.status='izin') izin,
                SUM(r.status='alpa') alpa, SUM(r.status='belum') belum,
                ROUND(SUM(r.status IN ('hadir','terlambat'))*100/NULLIF(COUNT(r.id),0),1) pct
            FROM students st LEFT JOIN classes c ON c.id=st.class_id
            LEFT JOIN (SELECT r2.* FROM attendance_records r2 JOIN attendance_sessions s2 ON s2.id=r2.session_id WHERE s2.session_date BETWEEN ? AND ?) r ON r.student_id=st.id
            WHERE st.deleted_at IS NULL AND st.is_active=1";
        $a = array($from, $to);
        if ($class_id) { $sql .= ' AND st.class_id=?'; $a[] = (int)$class_id; }
        if ($student_id) { $sql .= ' AND st.id=?'; $a[] = (int)$student_id; }
        $sql .= ' GROUP BY st.id, st.nis, st.name, c.name ORDER BY c.name, st.name';
        return $this->db->query($sql, $a)->result();
    }
}
