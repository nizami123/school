<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Halaman promosi Velano School (publik). */
class Home extends CI_Controller
{
    public function index()
    {
        if (!file_exists(APPPATH . 'config/installed.lock')) redirect('install');
        $this->load->helper(array('url', 'app'));
        $this->load->library('session');
        $this->config->load('velano', TRUE);
        $this->load->view('home/index', array('v' => $this->config->item('velano', 'velano'), 'logged' => (bool)$this->session->userdata('uid'),
            'is_super' => $this->session->userdata('role') === 'superadmin'));
    }
}
