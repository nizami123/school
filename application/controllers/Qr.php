<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Qr extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->need('admin');
        $this->load->model('Qr_model');
    }

    public function index()
    {
        $this->Qr_model->ensure_all($this->uid);
        $cid = (int)$this->input->get('class_id');
        $sql = "SELECT s.id, s.nis, s.name, s.photo, c.name class_name, q.token, q.created_at qr_at,
                (SELECT COUNT(*) FROM student_qr_codes x WHERE x.student_id=s.id) qr_count
                FROM students s LEFT JOIN classes c ON c.id=s.class_id
                LEFT JOIN student_qr_codes q ON q.student_id=s.id AND q.is_active=1
                WHERE s.school_id=" . (int)$this->sid . " AND s.deleted_at IS NULL AND s.is_active=1" . ($cid ? ' AND s.class_id=' . $cid : '') . ' ORDER BY c.name, s.name';
        $this->render('qr/index', array('title' => 'Generate QR Code', 'rows' => $this->db->query($sql)->result(),
            'classes' => $this->db->where('school_id', $this->sid)->order_by('name')->get('classes')->result(), 'cid' => $cid));
    }

    public function regenerate()
    {
        $this->require_post();
        $sid = (int)$this->input->post('student_id');
        $reason = trim((string)$this->input->post('reason')) ?: 'Kartu hilang/rusak';
        $st = $this->db->get_where('students', array('id' => $sid, 'school_id' => $this->sid, 'deleted_at' => NULL))->row();
        if (!$st) show_404();
        $this->Qr_model->generate($sid, $reason, $this->uid);
        audit('regenerasi_qr', 'siswa=' . $st->nis . ' alasan=' . $reason);
        $this->flash('success', 'QR baru untuk ' . $st->name . ' dibuat. QR lama sudah tidak berlaku. Silakan cetak kartu pengganti.');
        redirect('qr/cards?scope=student&id=' . $sid);
    }

    public function history($sid = 0)
    {
        $st = $this->db->get_where('students', array('id' => (int)$sid, 'school_id' => $this->sid))->row();
        if (!$st) show_404();
        $rows = $this->db->query('SELECT q.*, u.name by_name FROM student_qr_codes q LEFT JOIN users u ON u.id=q.created_by WHERE q.student_id=? ORDER BY q.id DESC', array($st->id))->result();
        $this->render('qr/history', array('title' => 'Riwayat QR - ' . $st->name, 'st' => $st, 'rows' => $rows));
    }

    public function cards()
    {
        $scope = $this->input->get('scope');
        $id = (int)$this->input->get('id');
        $where = 's.school_id=' . (int)$this->sid . ' AND s.deleted_at IS NULL AND s.is_active=1';
        if ($scope === 'student') $where .= ' AND s.id=' . $id;
        elseif ($scope === 'class') $where .= ' AND s.class_id=' . $id;
        $this->Qr_model->ensure_all($this->uid);
        $rows = $this->db->query("SELECT s.id, s.nis, s.nisn, s.name, s.photo, c.name class_name, q.token FROM students s LEFT JOIN classes c ON c.id=s.class_id
            JOIN student_qr_codes q ON q.student_id=s.id AND q.is_active=1 WHERE $where ORDER BY c.name, s.name")->result();
        audit('cetak_kartu', 'scope=' . $scope . ' id=' . $id . ' jumlah=' . count($rows));
        $this->render('qr/cards', array('title' => 'Cetak Kartu Pelajar', 'rows' => $rows, 'classes' => $this->db->where('school_id', $this->sid)->order_by('name')->get('classes')->result(), 'scope' => $scope, 'id' => $id));
    }
}
