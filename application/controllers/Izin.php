<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Pengajuan izin/sakit dari orang tua: tinjau, setujui, tolak. Khusus Admin. Tidak mengirim WhatsApp. */
class Izin extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->need('admin');
    }

    public function index()
    {
        $st = $this->input->get('status');
        if (!in_array($st, array('menunggu', 'disetujui', 'ditolak', 'dibatalkan', 'semua'), TRUE)) $st = 'menunggu';
        $sql = "SELECT lr.*, s.name student_name, s.nis, c.name class_name, p.name parent_name
            FROM leave_requests lr JOIN students s ON s.id=lr.student_id LEFT JOIN classes c ON c.id=s.class_id JOIN parents p ON p.id=lr.parent_id WHERE lr.school_id=?";
        $args = array($this->sid);
        if ($st !== 'semua') { $sql .= ' AND lr.status=?'; $args[] = $st; }
        $sql .= ' ORDER BY (lr.status=\'menunggu\') DESC, lr.date_from DESC, lr.id DESC LIMIT 500';
        $counts = array();
        foreach ($this->db->query('SELECT status, COUNT(*) c FROM leave_requests WHERE school_id=? GROUP BY status', array($this->sid))->result() as $r) { $counts[$r->status] = (int)$r->c; }
        $this->render('izin/index', array('title' => 'Izin & Sakit Siswa', 'rows' => $this->db->query($sql, $args)->result(), 'st' => $st, 'counts' => $counts));
    }

    public function lampiran($id)
    {
        $r = $this->db->get_where('leave_requests', array('id' => (int)$id, 'school_id' => $this->sid))->row();
        if (!$r || !$r->attachment) show_404();
        send_private_file($r->attachment);
    }

    public function review($id)
    {
        $this->require_post();
        $lr = $this->db->get_where('leave_requests', array('id' => (int)$id, 'school_id' => $this->sid))->row();
        if (!$lr) show_404();
        if ($lr->status !== 'menunggu') { $this->flash('warning', 'Pengajuan ini sudah diproses.'); redirect('izin'); }
        $act = $this->input->post('action');
        $note = substr(trim((string)$this->input->post('note')), 0, 250);
        if (!in_array($act, array('setuju', 'tolak'), TRUE)) show_404();
        $now = date('Y-m-d H:i:s');
        $this->db->update('leave_requests', array('status' => $act === 'setuju' ? 'disetujui' : 'ditolak', 'review_note' => $note !== '' ? $note : NULL,
            'reviewed_by' => $this->uid, 'reviewed_at' => $now, 'updated_at' => $now), array('id' => $lr->id, 'school_id' => $this->sid));
        $n = 0;
        if ($act === 'setuju') { $lr->status = 'disetujui'; $n = apply_leave_to_sessions($lr); }
        audit($act === 'setuju' ? 'setujui_izin' : 'tolak_izin', 'id=' . $lr->id . ' sesi_terpengaruh=' . $n);
        $this->flash('success', $act === 'setuju' ? 'Pengajuan disetujui. Absensi pada tanggal tersebut otomatis tercatat ' . $lr->leave_type . '.' : 'Pengajuan ditolak.');
        redirect('izin');
    }
}
