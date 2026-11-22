<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Panel Super Admin: kelola sekolah, akun admin sekolah, kuota & harga langganan, aktif/nonaktif. Tidak membuka data internal sekolah (hanya jumlah). */
class Superadmin extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->need('superadmin');
    }

    private function stats_sql()
    {
        return "SELECT sc.*,
            (SELECT COUNT(*) FROM students s WHERE s.school_id=sc.id AND s.deleted_at IS NULL) students_total,
            (SELECT COUNT(*) FROM students s WHERE s.school_id=sc.id AND s.deleted_at IS NULL AND s.is_active=1) students_active,
            (SELECT COUNT(*) FROM teachers t WHERE t.school_id=sc.id) teachers_total,
            (SELECT COUNT(*) FROM classes c WHERE c.school_id=sc.id) classes_total,
            (SELECT COUNT(*) FROM parents p WHERE p.school_id=sc.id) parents_total
            FROM schools sc";
    }

    private function school($id)
    {
        $r = $this->db->query($this->stats_sql() . ' WHERE sc.id=?', array((int)$id))->row();
        if (!$r) show_404();
        return $r;
    }

    public function index()
    {
        $rows = $this->db->query($this->stats_sql() . ' ORDER BY sc.is_active DESC, sc.name')->result();
        $t = array('schools' => count($rows), 'active' => 0, 'students' => 0, 'bill' => 0);
        foreach ($rows as $r) {
            if ($r->is_active) $t['active']++;
            $t['students'] += (int)$r->students_total;
            if ($r->is_active) $t['bill'] += (int)$r->students_total * (int)$r->price_per_student;
        }
        $this->render('super/index', array('title' => 'Daftar Sekolah', 'rows' => $rows, 't' => $t, 'cron_key' => app_setting('cron_key', '')));
    }

    public function sekolah_form($id = 0)
    {
        $row = $id ? (array)$this->school($id) : array('student_quota' => 0, 'price_per_student' => 0);
        $old = $this->session->flashdata('old');
        if (is_array($old)) $row = array_merge($row, $old);
        $this->render('super/school_form', array('title' => $id ? 'Ubah Sekolah' : 'Tambah Sekolah', 'row' => $row, 'id' => (int)$id));
    }

    public function sekolah_simpan()
    {
        $this->require_post();
        $id = (int)$this->input->post('id');
        if ($id) $this->school($id);
        $name = trim((string)$this->input->post('name'));
        $d = array('name' => mb_substr($name, 0, 150), 'address' => mb_substr(trim((string)$this->input->post('address')), 0, 255) ?: NULL,
            'phone' => mb_substr(trim((string)$this->input->post('phone')), 0, 30) ?: NULL, 'email' => mb_substr(trim((string)$this->input->post('email')), 0, 120) ?: NULL,
            'student_quota' => max(0, (int)preg_replace('/\D/', '', (string)$this->input->post('student_quota'))),
            'price_per_student' => max(0, (int)preg_replace('/\D/', '', (string)$this->input->post('price_per_student'))),
            'notes' => mb_substr(trim((string)$this->input->post('notes')), 0, 500) ?: NULL, 'updated_at' => date('Y-m-d H:i:s'));
        $err = array();
        if ($name === '') $err[] = 'Nama sekolah wajib diisi.';
        if ($d['email'] && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $err[] = 'Email sekolah tidak valid.';
        $au = trim((string)$this->input->post('admin_username')); $ap = (string)$this->input->post('admin_password'); $an = trim((string)$this->input->post('admin_name'));
        if (!$id) {
            if ($an === '') $err[] = 'Nama admin sekolah wajib diisi.';
            if (!preg_match('/^[A-Za-z0-9_.\-]{3,60}$/', $au)) $err[] = 'Username admin 3-60 karakter (huruf, angka, titik, strip, underscore).';
            elseif (username_taken($au)) $err[] = 'Username admin "' . $au . '" sudah digunakan.';
            if (strlen($ap) < 8) $err[] = 'Password admin minimal 8 karakter.';
        } elseif ($d['student_quota'] > 0) {
            $cnt = (int)$this->db->where(array('school_id' => $id, 'deleted_at' => NULL))->count_all_results('students');
            if ($cnt > $d['student_quota']) $err[] = 'Kuota (' . $d['student_quota'] . ') lebih kecil dari jumlah siswa terdaftar saat ini (' . $cnt . ').';
        }
        if ($err) {
            $this->session->set_flashdata('old', $this->input->post());
            $this->flash('danger', implode('<br>', array_map('e', $err)));
            redirect('super/sekolah_form' . ($id ? '/' . $id : ''));
        }
        $now = date('Y-m-d H:i:s');
        if ($id) {
            $this->db->update('schools', $d, array('id' => $id));
            audit('ubah_sekolah', 'sekolah=' . $id);
            $this->flash('success', 'Data sekolah disimpan.');
            redirect('super/sekolah_detail/' . $id);
        }
        $d['created_at'] = $now; $d['is_active'] = 1;
        $this->db->trans_start();
        $this->db->insert('schools', $d);
        $sid = (int)$this->db->insert_id();
        $this->db->insert('users', array('school_id' => $sid, 'role_id' => 1, 'name' => mb_substr($an, 0, 120), 'username' => $au,
            'password' => password_hash($ap, PASSWORD_DEFAULT), 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now));
        school_seed_defaults($sid);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) { $this->flash('danger', 'Gagal menyimpan sekolah. Coba lagi.'); redirect('super'); }
        audit('tambah_sekolah', 'sekolah=' . $sid . ' admin=' . $au);
        $this->flash('success', 'Sekolah dan akun admin dibuat. Berikan username & password admin kepada pihak sekolah.');
        redirect('super/sekolah_detail/' . $sid);
    }

    public function sekolah_detail($id)
    {
        $s = $this->school($id);
        $admins = $this->db->query('SELECT id, name, username, is_active, last_login FROM users WHERE school_id=? AND role_id=1 ORDER BY id', array($s->id))->result();
        $this->render('super/school_detail', array('title' => $s->name, 's' => $s, 'admins' => $admins, 'new' => $this->session->flashdata('new_pw')));
    }

    public function sekolah_toggle($id)
    {
        $this->require_post();
        $s = $this->school($id);
        $new = $s->is_active ? 0 : 1;
        $this->db->update('schools', array('is_active' => $new, 'deactivated_at' => $new ? NULL : date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')), array('id' => $s->id));
        audit($new ? 'aktifkan_sekolah' : 'nonaktifkan_sekolah', 'sekolah=' . $s->id);
        $this->flash('success', $new ? 'Sekolah diaktifkan kembali.' : 'Sekolah dinonaktifkan. Semua akun sekolah ini otomatis tidak dapat masuk; data tetap tersimpan.');
        redirect('super/sekolah_detail/' . $s->id);
    }

    public function admin_tambah($sid)
    {
        $this->require_post();
        $s = $this->school($sid);
        $an = trim((string)$this->input->post('name')); $au = trim((string)$this->input->post('username')); $ap = (string)$this->input->post('password');
        if ($an === '' || !preg_match('/^[A-Za-z0-9_.\-]{3,60}$/', $au) || strlen($ap) < 8) { $this->flash('danger', 'Nama wajib diisi, username 3-60 karakter, password minimal 8 karakter.'); redirect('super/sekolah_detail/' . $s->id); }
        if (username_taken($au)) { $this->flash('danger', 'Username sudah digunakan.'); redirect('super/sekolah_detail/' . $s->id); }
        $now = date('Y-m-d H:i:s');
        $this->db->insert('users', array('school_id' => $s->id, 'role_id' => 1, 'name' => mb_substr($an, 0, 120), 'username' => $au, 'password' => password_hash($ap, PASSWORD_DEFAULT),
            'is_active' => 1, 'created_at' => $now, 'updated_at' => $now));
        audit('tambah_admin_sekolah', 'sekolah=' . $s->id . ' admin=' . $au);
        $this->flash('success', 'Admin sekolah ditambahkan.');
        redirect('super/sekolah_detail/' . $s->id);
    }

    private function school_admin($uid)
    {
        $u = $this->db->get_where('users', array('id' => (int)$uid, 'role_id' => 1))->row();
        if (!$u) show_404();
        return $u;
    }

    public function admin_reset($uid)
    {
        $this->require_post();
        $u = $this->school_admin($uid);
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'; $pw = '';
        for ($i = 0; $i < 10; $i++) { $pw .= $chars[random_int(0, strlen($chars) - 1)]; }
        $this->db->update('users', array('password' => password_hash($pw, PASSWORD_DEFAULT), 'must_change_password' => 0, 'failed_logins' => 0, 'locked_until' => NULL, 'updated_at' => date('Y-m-d H:i:s')), array('id' => $u->id));
        audit('reset_password_admin', 'user=' . $u->id);
        $this->session->set_flashdata('new_pw', array('username' => $u->username, 'password' => $pw));
        redirect('super/sekolah_detail/' . $u->school_id);
    }

    public function admin_toggle($uid)
    {
        $this->require_post();
        $u = $this->school_admin($uid);
        $this->db->update('users', array('is_active' => $u->is_active ? 0 : 1, 'updated_at' => date('Y-m-d H:i:s')), array('id' => $u->id));
        audit('toggle_admin_sekolah', 'user=' . $u->id);
        $this->flash('success', 'Status akun admin diubah.');
        redirect('super/sekolah_detail/' . $u->school_id);
    }

    public function profil()
    {
        $this->render('super/profil', array('title' => 'Profil Super Admin'));
    }

    public function profil_simpan()
    {
        $this->require_post();
        $a = $this->db->get_where('super_admins', array('id' => $this->uid))->row();
        $new = (string)$this->input->post('new_password');
        $err = '';
        if (!$a || !password_verify((string)$this->input->post('old_password'), $a->password)) $err = 'Password lama salah.';
        elseif (strlen($new) < 8) $err = 'Password baru minimal 8 karakter.';
        elseif ($new !== (string)$this->input->post('new_password2')) $err = 'Konfirmasi password baru tidak sama.';
        if ($err !== '') { $this->flash('danger', $err); redirect('super/profil'); }
        $this->db->update('super_admins', array('password' => password_hash($new, PASSWORD_DEFAULT), 'updated_at' => date('Y-m-d H:i:s')), array('id' => $a->id));
        audit('ganti_password', 'super admin');
        $this->flash('success', 'Password diganti.');
        redirect('super/profil');
    }
}
