<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct() { parent::__construct(); $this->need(); }

    public function index()
    {
        if ($this->role === 'ortu') redirect('portal');
        if ($this->role === 'superadmin') redirect('super');
        $today = date('Y-m-d');
        $sid = $this->sid;
        $this->load->model('Attendance_model');
        $this->Attendance_model->close_stale();
        if ($this->role === 'admin') {
            $d = array('title' => 'Dashboard');
            $d['total_students'] = (int)$this->db->where(array('school_id' => $sid, 'is_active' => 1, 'deleted_at' => NULL))->count_all_results('students');
            $d['total_teachers'] = (int)$this->db->where(array('school_id' => $sid, 'is_active' => 1))->count_all_results('teachers');
            $d['total_classes'] = (int)$this->db->where('school_id', $sid)->count_all_results('classes');
            $d['total_subjects'] = (int)$this->db->where(array('school_id' => $sid, 'code !=' => '_HARIAN'))->count_all_results('subjects');
            $d['sessions_today'] = (int)$this->db->where(array('school_id' => $sid, 'session_date' => $today))->count_all_results('attendance_sessions');
            $d['present_today'] = (int)$this->db->query("SELECT COUNT(DISTINCT r.student_id) c FROM attendance_records r JOIN attendance_sessions s ON s.id=r.session_id WHERE s.school_id=? AND s.session_date=? AND r.status IN ('hadir','terlambat')", array($sid, $today))->row()->c;
            $d['expected_today'] = (int)$this->db->query("SELECT COUNT(*) c FROM students WHERE school_id=? AND is_active=1 AND deleted_at IS NULL AND class_id IN (SELECT sc.class_id FROM schedules sc JOIN semesters sm ON sm.id=sc.semester_id WHERE sc.school_id=? AND sm.is_active=1 AND sc.day=?)", array($sid, $sid, date('N')))->row()->c;
            $d['absent_today'] = max(0, $d['expected_today'] - $d['present_today']);
            $d['pct_today'] = $d['expected_today'] > 0 ? round($d['present_today'] * 100 / $d['expected_today']) : 0;
            $d['wa_sent'] = (int)$this->db->where(array('school_id' => $sid, 'status' => 'terkirim'))->count_all_results('whatsapp_notifications');
            $d['wa_failed'] = (int)$this->db->where(array('school_id' => $sid, 'status' => 'gagal'))->count_all_results('whatsapp_notifications');
            $d['wa_pending'] = (int)$this->db->where('school_id', $sid)->where_in('status', array('menunggu', 'mengirim'))->count_all_results('whatsapp_notifications');
            $d['recent_scans'] = $this->db->query("SELECT r.scanned_at, r.status, st.name, c.name class_name, sub.name subject FROM attendance_records r JOIN attendance_sessions s ON s.id=r.session_id JOIN students st ON st.id=r.student_id LEFT JOIN classes c ON c.id=s.class_id JOIN subjects sub ON sub.id=s.subject_id WHERE s.school_id=? AND s.session_date=? AND r.scanned_at IS NOT NULL ORDER BY r.scanned_at DESC LIMIT 10", array($sid, $today))->result();
            $d['missing'] = $this->db->query("SELECT st.name, c.name class_name, sub.name subject, r.status FROM attendance_records r JOIN attendance_sessions s ON s.id=r.session_id JOIN students st ON st.id=r.student_id LEFT JOIN classes c ON c.id=s.class_id JOIN subjects sub ON sub.id=s.subject_id WHERE s.school_id=? AND s.session_date=? AND r.status IN ('belum','alpa') ORDER BY r.id DESC LIMIT 10", array($sid, $today))->result();
            $d['schedule_today'] = $this->db->query("SELECT sc.*, c.name class_name, sub.name subject, t.name teacher, (SELECT s.status FROM attendance_sessions s WHERE s.schedule_id=sc.id AND s.session_date=?) AS ses_status FROM schedules sc JOIN semesters sm ON sm.id=sc.semester_id AND sm.is_active=1 JOIN classes c ON c.id=sc.class_id JOIN subjects sub ON sub.id=sc.subject_id JOIN teachers t ON t.id=sc.teacher_id WHERE sc.school_id=? AND sc.day=? ORDER BY sc.start_time LIMIT 30", array($today, $sid, date('N')))->result();
            $d['teacher_activity'] = $this->db->query("SELECT t.name, COUNT(DISTINCT s.id) sessions, SUM(r.status IN ('hadir','terlambat')) present, MAX(r.scanned_at) last_scan FROM attendance_sessions s JOIN teachers t ON t.id=s.teacher_id LEFT JOIN attendance_records r ON r.session_id=s.id WHERE s.school_id=? AND s.session_date=? GROUP BY t.id, t.name ORDER BY last_scan DESC", array($sid, $today))->result();
            $d['wa_latest'] = $this->db->query("SELECT n.created_at, n.status, n.notif_type, st.name FROM whatsapp_notifications n JOIN students st ON st.id=n.student_id WHERE n.school_id=? ORDER BY n.id DESC LIMIT 8", array($sid))->result();
            if (attendance_mode() === 'harian') {
                $d['daily_classes'] = $this->daily_classes(0);
                $eff = explode(',', setting('effective_days', '1,2,3,4,5'));
                $d['expected_today'] = in_array((string)date('N'), $eff, TRUE) ? $d['total_students'] : 0;
                $d['absent_today'] = max(0, $d['expected_today'] - $d['present_today']);
                $d['pct_today'] = $d['expected_today'] > 0 ? round($d['present_today'] * 100 / $d['expected_today']) : 0;
            }
            $this->render('dashboard/admin', $d);
        } else {
            $d = array('title' => 'Beranda Guru');
            if (attendance_mode() === 'harian') {
                $d['classes'] = $this->daily_classes(0);   // mode harian: semua guru dapat mengabsen kelas mana pun
                $this->render('dashboard/guru_harian', $d);
                return;
            }
            $d['rows'] = $this->db->query("SELECT sc.*, c.name class_name, sub.name subject,
                (SELECT s.id FROM attendance_sessions s WHERE s.schedule_id=sc.id AND s.session_date=?) AS ses_id,
                (SELECT s.status FROM attendance_sessions s WHERE s.schedule_id=sc.id AND s.session_date=?) AS ses_status
                FROM schedules sc JOIN semesters sm ON sm.id=sc.semester_id AND sm.is_active=1
                JOIN classes c ON c.id=sc.class_id JOIN subjects sub ON sub.id=sc.subject_id
                WHERE sc.school_id=? AND sc.teacher_id=? AND sc.day=? ORDER BY sc.start_time", array($today, $today, $sid, $this->teacher_id, date('N')))->result();
            foreach ($d['rows'] as $r) {
                $r->stats = $r->ses_id ? $this->Attendance_model->stats($r->ses_id) : NULL;
                if (!$r->stats) {
                    $r->stats = array('total' => (int)$this->db->where(array('school_id' => $sid, 'class_id' => $r->class_id, 'is_active' => 1, 'deleted_at' => NULL))->count_all_results('students'), 'present' => 0, 'absent' => 0, 'pct' => 0);
                    $r->stats['absent'] = $r->stats['total'];
                }
            }
            $this->render('dashboard/guru', $d);
        }
    }

    /** Daftar kelas + status sesi absensi harian hari ini ($homeroom > 0: hanya kelas wali tersebut). */
    private function daily_classes($homeroom)
    {
        $sql = "SELECT c.id, c.name, t.name homeroom,
            (SELECT s.id FROM attendance_sessions s WHERE s.school_id=c.school_id AND s.class_id=c.id AND s.session_date=? AND s.session_type='harian' LIMIT 1) ses_id,
            (SELECT s.status FROM attendance_sessions s WHERE s.school_id=c.school_id AND s.class_id=c.id AND s.session_date=? AND s.session_type='harian' LIMIT 1) ses_status
            FROM classes c LEFT JOIN teachers t ON t.id=c.homeroom_teacher_id WHERE c.school_id=?";
        $a = array(date('Y-m-d'), date('Y-m-d'), $this->sid);
        if ($homeroom) { $sql .= ' AND c.homeroom_teacher_id=?'; $a[] = (int)$homeroom; }
        $rows = $this->db->query($sql . ' ORDER BY c.name', $a)->result();
        foreach ($rows as $r) {
            $r->stats = $r->ses_id ? $this->Attendance_model->stats($r->ses_id) : array('total' => (int)$this->db->where(array('school_id' => $this->sid, 'class_id' => $r->id, 'is_active' => 1, 'deleted_at' => NULL))->count_all_results('students'), 'present' => 0, 'absent' => 0, 'pct' => 0);
            if (!$r->ses_id) $r->stats['absent'] = $r->stats['total'];
            $r->present = $r->stats['present']; $r->total = $r->stats['total'];
        }
        return $rows;
    }

    public function charts()
    {
        if ($this->role !== 'admin') { $this->json(array(), 403); return; }
        $q = function ($sql, $a = array()) { return $this->db->query($sql, $a)->result_array(); };
        $sid = $this->sid;
        $from14 = date('Y-m-d', strtotime('-13 days'));
        $from30 = date('Y-m-d', strtotime('-29 days'));
        $from6m = date('Y-m-01', strtotime('-5 months'));
        $this->json(array(
            'daily' => $q("SELECT s.session_date d, SUM(r.status='hadir') h, SUM(r.status='terlambat') t, SUM(r.status IN ('sakit','izin','alpa','belum')) a FROM attendance_records r JOIN attendance_sessions s ON s.id=r.session_id WHERE s.school_id=? AND s.session_date>=? GROUP BY s.session_date ORDER BY s.session_date", array($sid, $from14)),
            'monthly' => $q("SELECT DATE_FORMAT(s.session_date,'%Y-%m') ym, ROUND(SUM(r.status IN ('hadir','terlambat'))*100/COUNT(*),1) pct FROM attendance_records r JOIN attendance_sessions s ON s.id=r.session_id WHERE s.school_id=? AND s.session_date>=? GROUP BY ym ORDER BY ym", array($sid, $from6m)),
            'by_class' => $q("SELECT c.name label, ROUND(SUM(r.status IN ('hadir','terlambat'))*100/COUNT(*),1) pct FROM attendance_records r JOIN attendance_sessions s ON s.id=r.session_id JOIN classes c ON c.id=s.class_id WHERE s.school_id=? AND s.session_date>=? GROUP BY c.id, c.name ORDER BY c.name", array($sid, $from30)),
            'by_subject' => $q("SELECT sub.name label, ROUND(SUM(r.status IN ('hadir','terlambat'))*100/COUNT(*),1) pct FROM attendance_records r JOIN attendance_sessions s ON s.id=r.session_id JOIN subjects sub ON sub.id=s.subject_id WHERE s.school_id=? AND s.session_date>=? GROUP BY sub.id, sub.name ORDER BY sub.name", array($sid, $from30)),
            'late' => $q("SELECT s.session_date d, SUM(r.status='terlambat') t FROM attendance_records r JOIN attendance_sessions s ON s.id=r.session_id WHERE s.school_id=? AND s.session_date>=? GROUP BY s.session_date ORDER BY s.session_date", array($sid, $from30)),
        ));
    }
}
