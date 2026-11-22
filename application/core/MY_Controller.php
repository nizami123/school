<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected $uid = 0;
    protected $role = '';
    protected $teacher_id = 0;
    protected $parent_id = 0;

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
        if (!isset($data['title'])) $data['title'] = 'QR School Attendance';
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
        return $this->db->query('SELECT s.*, y.name AS year_name FROM semesters s JOIN academic_years y ON y.id=s.academic_year_id WHERE s.is_active=1 ORDER BY s.start_date DESC LIMIT 1')->row();
    }

    protected function require_post()
    {
        if ($this->input->method() !== 'post') {
            show_error('Metode tidak valid.', 405);
        }
    }
}
