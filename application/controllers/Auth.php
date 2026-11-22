<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Satu form login untuk Super Admin, Admin Sekolah, Guru, dan Orang Tua. */
class Auth extends MY_Controller
{
    private function max_fail() { return 5; }

    public function login()
    {
        if ($this->uid) redirect('dashboard');
        $error = '';
        if ($this->input->get('off')) $error = 'Sesi berakhir: akun atau sekolah Anda dinonaktifkan. Hubungi admin sekolah atau Velano School.';
        if ($this->input->method() === 'post') {
            $u = trim((string)$this->input->post('username'));
            $p = (string)$this->input->post('password');
            $error = $this->try_super($u, $p);
            if ($error === NULL) $error = $this->try_school_user($u, $p);
            if ($error === NULL) $error = 'Username atau password salah.';
        }
        $this->load->view('auth/login', array('error' => $error, 'installed' => $this->input->get('installed')));
    }

    /** Return NULL bila bukan Super Admin / password tidak cocok (lanjut cek akun sekolah); string = pesan galat. */
    private function try_super($u, $p)
    {
        $a = $this->db->get_where('super_admins', array('username' => $u))->row();
        if (!$a) return NULL;
        if ($a->locked_until && strtotime($a->locked_until) > time()) return 'Akun terkunci sementara karena terlalu banyak percobaan. Coba lagi beberapa menit lagi.';
        if ($a->is_active && password_verify($p, $a->password)) {
            $this->db->update('super_admins', array('failed_logins' => 0, 'locked_until' => NULL, 'last_login' => date('Y-m-d H:i:s')), array('id' => $a->id));
            $this->session->sess_regenerate(TRUE);
            $this->session->set_userdata(array('uid' => (int)$a->id, 'role' => 'superadmin', 'name' => $a->name, 'school_id' => 0,
                'teacher_id' => 0, 'parent_id' => 0, 'must_change' => 0));
            school_id(0);
            audit('login', 'super admin ' . $a->username);
            redirect('super');
        }
        $f = $a->failed_logins + 1;
        $this->db->update('super_admins', array('failed_logins' => $f, 'locked_until' => $f >= $this->max_fail() ? date('Y-m-d H:i:s', time() + 300) : NULL), array('id' => $a->id));
        return $a->is_active ? NULL : 'Akun dinonaktifkan.';
    }

    private function try_school_user($u, $p)
    {
        $row = $this->db->query('SELECT u.*, r.name AS role_name, t.id AS teacher_id, pr.id AS parent_id, sc.is_active AS school_active
            FROM users u JOIN roles r ON r.id=u.role_id JOIN schools sc ON sc.id=u.school_id
            LEFT JOIN teachers t ON t.user_id=u.id LEFT JOIN parents pr ON pr.user_id=u.id WHERE u.username=? LIMIT 1', array($u))->row();
        if (!$row) return NULL;
        if ($row->locked_until && strtotime($row->locked_until) > time()) return 'Akun terkunci sementara karena terlalu banyak percobaan. Coba lagi beberapa menit lagi.';
        if (password_verify($p, $row->password)) {
            if (!(int)$row->school_active) return 'Sekolah Anda sedang dinonaktifkan. Hubungi Velano School.';
            if (!(int)$row->is_active) return 'Akun dinonaktifkan. Hubungi admin sekolah.';
            $this->db->update('users', array('failed_logins' => 0, 'locked_until' => NULL, 'last_login' => date('Y-m-d H:i:s')), array('id' => $row->id));
            $this->session->sess_regenerate(TRUE);
            $this->session->set_userdata(array('uid' => (int)$row->id, 'role' => $row->role_name, 'name' => $row->name, 'school_id' => (int)$row->school_id,
                'teacher_id' => (int)$row->teacher_id, 'parent_id' => (int)$row->parent_id, 'must_change' => (int)$row->must_change_password));
            school_id((int)$row->school_id);
            audit('login', $row->username);
            redirect('dashboard');
        }
        $f = $row->failed_logins + 1;
        $this->db->update('users', array('failed_logins' => $f, 'locked_until' => $f >= $this->max_fail() ? date('Y-m-d H:i:s', time() + 300) : NULL), array('id' => $row->id));
        return NULL;
    }

    public function logout()
    {
        if ($this->uid) audit('logout', '');
        $this->session->sess_destroy();
        redirect('login');
    }
}
