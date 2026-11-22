<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Keanggotaan ekstrakurikuler (data kegiatan ada di Master > Ekstrakurikuler). Khusus Admin. */
class Ekskul extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->need('admin');
    }

    private function load_ekskul($id)
    {
        $x = $this->db->get_where('extracurriculars', array('id' => (int)$id, 'school_id' => $this->sid))->row();
        if (!$x) show_404();
        return $x;
    }

    private function active_count($id)
    {
        return (int)$this->db->where(array('extracurricular_id' => $id, 'status' => 'aktif'))->count_all_results('extracurricular_members');
    }

    public function anggota($id)
    {
        $x = $this->load_ekskul($id);
        $members = $this->db->query("SELECT m.*, s.name, s.nis, c.name class_name FROM extracurricular_members m JOIN students s ON s.id=m.student_id LEFT JOIN classes c ON c.id=s.class_id
            WHERE m.extracurricular_id=? ORDER BY FIELD(m.status,'menunggu','aktif','ditolak','keluar'), c.name, s.name", array($x->id))->result();
        $students = $this->db->query("SELECT s.id, CONCAT(s.name, ' (', IFNULL(c.name,'-'), ')') label FROM students s LEFT JOIN classes c ON c.id=s.class_id
            WHERE s.school_id=? AND s.is_active=1 AND s.deleted_at IS NULL AND s.id NOT IN (SELECT student_id FROM extracurricular_members WHERE extracurricular_id=? AND status IN ('aktif','menunggu')) ORDER BY c.name, s.name", array($this->sid, $x->id))->result();
        $this->render('ekskul/anggota', array('title' => 'Anggota ' . $x->name, 'x' => $x, 'members' => $members, 'students' => $students, 'active' => $this->active_count($x->id)));
    }

    public function tambah($id)
    {
        $this->require_post();
        $x = $this->load_ekskul($id);
        $sid = (int)$this->input->post('student_id');
        $st = $this->db->get_where('students', array('id' => $sid, 'school_id' => $this->sid, 'deleted_at' => NULL))->row();
        if (!$st) { $this->flash('danger', 'Siswa tidak ditemukan.'); redirect('ekskul/anggota/' . $x->id); }
        if ($x->quota && $this->active_count($x->id) >= (int)$x->quota) { $this->flash('danger', 'Kuota penuh.'); redirect('ekskul/anggota/' . $x->id); }
        $now = date('Y-m-d H:i:s');
        $this->db->query("INSERT INTO extracurricular_members (extracurricular_id, student_id, status, requested_by_parent, created_at, updated_at) VALUES (?,?, 'aktif', 0, ?, ?)
            ON DUPLICATE KEY UPDATE status='aktif', updated_at=VALUES(updated_at)", array($x->id, $sid, $now, $now));
        audit('tambah_anggota_ekskul', 'ekskul=' . $x->id . ' siswa=' . $sid);
        $this->flash('success', 'Anggota ditambahkan.');
        redirect('ekskul/anggota/' . $x->id);
    }

    public function status($mid)
    {
        $this->require_post();
        $m = $this->db->query('SELECT m.* FROM extracurricular_members m JOIN extracurriculars x ON x.id=m.extracurricular_id WHERE m.id=? AND x.school_id=?', array((int)$mid, $this->sid))->row();
        if (!$m) show_404();
        $x = $this->load_ekskul($m->extracurricular_id);
        $act = $this->input->post('action');
        $map = array('setuju' => 'aktif', 'tolak' => 'ditolak', 'keluar' => 'keluar');
        if ($act === 'hapus') {
            $this->db->delete('extracurricular_members', array('id' => $m->id));
        } elseif (isset($map[$act])) {
            if ($act === 'setuju' && $m->status !== 'aktif' && $x->quota && $this->active_count($x->id) >= (int)$x->quota) {
                $this->flash('danger', 'Kuota penuh.'); redirect('ekskul/anggota/' . $x->id);
            }
            $this->db->update('extracurricular_members', array('status' => $map[$act], 'updated_at' => date('Y-m-d H:i:s')), array('id' => $m->id));
        } else { show_404(); }
        audit('ubah_anggota_ekskul', 'member=' . $m->id . ' ' . $act);
        $this->flash('success', 'Tersimpan.');
        redirect('ekskul/anggota/' . $x->id);
    }
}
