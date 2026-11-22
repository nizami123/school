<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{
    public function login()
    {
        if ($this->uid) redirect('dashboard');
        $error = '';
        if ($this->input->method() === 'post') {
            $u = trim((string)$this->input->post('username'));
            $p = (string)$this->input->post('password');
            $row = $this->db->query('SELECT u.*, r.name AS role_name, t.id AS teacher_id, pr.id AS parent_id FROM users u JOIN roles r ON r.id=u.role_id LEFT JOIN teachers t ON t.user_id=u.id LEFT JOIN parents pr ON pr.user_id=u.id WHERE u.username=? LIMIT 1', array($u))->row();
            if ($row && $row->locked_until && strtotime($row->locked_until) > time()) {
                $error = 'Akun terkunci sementara karena terlalu banyak percobaan. Coba lagi beberapa menit lagi.';
            } elseif ($row && $row->is_active && password_verify($p, $row->password)) {
                $this->db->update('users', array('failed_logins' => 0, 'locked_until' => NULL, 'last_login' => date('Y-m-d H:i:s')), array('id' => $row->id));
                $this->session->sess_regenerate(TRUE);
                $this->session->set_userdata(array('uid' => (int)$row->id, 'role' => $row->role_name, 'name' => $row->name, 'teacher_id' => (int)$row->teacher_id,
                    'parent_id' => (int)$row->parent_id, 'must_change' => (int)$row->must_change_password));
                audit('login', $row->username);
                redirect('dashboard');
            } else {
                if ($row) {
                    $f = $row->failed_logins + 1;
                    $this->db->update('users', array('failed_logins' => $f, 'locked_until' => $f >= 5 ? date('Y-m-d H:i:s', time() + 300) : NULL), array('id' => $row->id));
                }
                $error = ($row && !$row->is_active) ? 'Akun dinonaktifkan. Hubungi admin sekolah.' : 'Username atau password salah.';
            }
        }
        $this->load->view('auth/login', array('error' => $error, 'installed' => $this->input->get('installed')));
    }

    public function logout()
    {
        if ($this->uid) audit('logout', '');
        $this->session->sess_destroy();
        redirect('login');
    }
}
