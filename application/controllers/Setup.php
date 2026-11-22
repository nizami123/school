<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pembuatan Super Admin pertama untuk instalasi LAMA yang baru dimigrasi (database/migrate_multi_sekolah.sql).
 * Hanya aktif selama tabel super_admins masih kosong, dan wajib memasukkan password database sebagai bukti akses server.
 */
class Setup extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(array('url', 'form', 'app'));
        $this->load->library('session');
        if (!file_exists(APPPATH . 'config/installed.lock')) redirect('install');
        if ($this->db->count_all_results('super_admins') > 0) show_error('Super Admin sudah dibuat. Halaman ini dikunci.', 403, 'Terkunci');
    }

    public function index()
    {
        $err = '';
        if ($this->input->method() === 'post') {
            $dbpass = (string)$this->input->post('db_pass');
            $name = trim((string)$this->input->post('name'));
            $user = trim((string)$this->input->post('username'));
            $pw = (string)$this->input->post('password');
            if (!hash_equals((string)$this->db->password, $dbpass)) $err = 'Password database salah.';
            elseif ($name === '' || !preg_match('/^[A-Za-z0-9_.\-]{3,60}$/', $user)) $err = 'Nama wajib diisi dan username 3-60 karakter (huruf, angka, titik, strip, underscore).';
            elseif (strlen($pw) < 8) $err = 'Password minimal 8 karakter.';
            elseif ($pw !== (string)$this->input->post('password2')) $err = 'Konfirmasi password tidak sama.';
            elseif (username_taken($user)) $err = 'Username sudah digunakan oleh akun lain.';
            if ($err === '') {
                $now = date('Y-m-d H:i:s');
                $this->db->insert('super_admins', array('name' => $name, 'username' => $user, 'password' => password_hash($pw, PASSWORD_DEFAULT), 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now));
                redirect('login?installed=1');
            }
        }
        $this->load->view('setup/index', array('error' => $err));
    }
}
