<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Monitor extends MY_Controller
{
    public function __construct() { parent::__construct(); $this->need('admin'); }

    public function index()
    {
        $this->render('rekap/monitor', array('title' => 'Monitoring Absensi'));
    }

    public function live()
    {
        $today = date('Y-m-d');
        $sessions = $this->db->query("SELECT s.id, s.status, s.start_time, s.end_time, c.name class_name, sub.name subject, t.name teacher,
            (SELECT COUNT(*) FROM attendance_records r WHERE r.session_id=s.id AND r.status IN ('hadir','terlambat')) present,
            (SELECT COUNT(*) FROM students st WHERE st.class_id=s.class_id AND st.is_active=1 AND st.deleted_at IS NULL) total,
            (SELECT MAX(r.scanned_at) FROM attendance_records r WHERE r.session_id=s.id) last_scan
            FROM attendance_sessions s JOIN classes c ON c.id=s.class_id JOIN subjects sub ON sub.id=s.subject_id JOIN teachers t ON t.id=s.teacher_id
            WHERE s.school_id=? AND s.session_date=? ORDER BY s.status='open' DESC, s.start_time", array($this->sid, $today))->result_array();
        $scans = $this->db->query("SELECT l.created_at, l.result, l.message, u.name user_name FROM scan_logs l LEFT JOIN users u ON u.id=l.user_id WHERE l.school_id=? ORDER BY l.id DESC LIMIT 15", array($this->sid))->result_array();
        $this->json(array('sessions' => $sessions, 'scans' => $scans, 'time' => date('H:i:s')));
    }
}
