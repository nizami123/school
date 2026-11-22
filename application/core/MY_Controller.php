<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected $uid = 0;
    protected $role = '';
    protected $teacher_id = 0;
    protected $parent_id = 0;
    protected $sid = 0;   // ID sekolah milik akun yang login (0 untuk Super Admin)

    public function __construct()
    {
        parent::__construct();
        if (!file_exists(APPPATH . 'config/installed.lock')) {
            redirect('install');
        }
        $this->load->database();
        $this->db->query("SET time_zone = '+07:00'");
        $this->load->library('session');
        $this->load->helper(array('url', 'form', 'app'));
        $this->uid = (int)$this->session->userdata('uid');
        $this->role = (string)$this->session->userdata('role');
        $this->teacher_id = (int)$this->session->userdata('teacher_id');
        $this->parent_id = (int)$this->session->userdata('parent_id');
        $this->sid = (int)$this->session->userdata('school_id');
        school_id($this->sid);
        if ($this->uid) $this->guard_account();
    }

    /** Setiap request: akun harus masih aktif dan (untuk akun sekolah) sekolahnya harus aktif & sama dengan yang tersimpan di sesi. */
    private function guard_account()
    {
        $ok = TRUE;
        if ($this->role === 'superadmin') {
            $a = $this->db->select('is_active')->get_where('super_admins', array('id' => $this->uid))->row();
            $ok = $a && (int)$a->is_active === 1;
        } else {
            $u = $this->db->select('is_active, school_id')->get_where('users', array('id' => $this->uid))->row();
            $s = school_row();
            $ok = $u && (int)$u->is_active === 1 && $this->sid > 0 && (int)$u->school_id === $this->sid && $s && (int)$s->is_active === 1;
        }
        if ($ok) return;
        $this->session->sess_destroy();
        if ($this->input->is_ajax_request()) {
            $this->json(array('ok' => FALSE, 'message' => 'Akun atau sekolah dinonaktifkan. Silakan login ulang.'), 401);
            $this->output->_display();
            exit;
        }
        redirect('login?off=1');
    }

    /** Pastikan ID dari input memang milik sekolah ini (cegah akses data sekolah lain lewat manipulasi ID). */
    protected function own($table, $id, $extra = array())
    {
        $row = $this->db->get_where($table, array_merge(array('id' => (int)$id, 'school_id' => $this->sid), $extra))->row();
        return $row ? $row : NULL;
    }

    protected function need($roles = NULL)
    {
        if (!$this->uid) {
            if ($this->input->is_ajax_request()) {
                $this->json(array('ok' => FALSE, 'message' => 'Sesi login berakhir. Silakan login ulang.'), 401);
                $this->output->_display();
                exit;
            }
            redirect('login');
        }
        if ($roles !== NULL && !in_array($this->role, (array)$roles, TRUE)) {
            show_error('Anda tidak memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
        }
    }

    protected function render($view, $data = array())
    {
        if (!isset($data['title'])) $data['title'] = 'Beranda';
        $data['content'] = $this->load->view($view, $data, TRUE);
        $this->load->view('layout/main', $data);
    }

    protected function json($arr, $status = 200)
    {
        $this->output->set_status_header($status)->set_content_type('application/json', 'UTF-8')
            ->set_header('Cache-Control: no-store')->set_output(json_encode($arr));
    }

    protected function flash($type, $msg)
    {
        $this->session->set_flashdata('flash', array($type, $msg));
    }

    protected function active_semester()
    {
        return $this->db->query('SELECT s.*, y.name AS year_name FROM semesters s JOIN academic_years y ON y.id=s.academic_year_id WHERE s.school_id=? AND s.is_active=1 ORDER BY s.start_date DESC LIMIT 1', array($this->sid))->row();
    }

    protected function require_post()
    {
        if ($this->input->method() !== 'post') {
            show_error('Metode tidak valid.', 405);
        }
    }
}
