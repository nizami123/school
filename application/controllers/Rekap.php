<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Rekap extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->need('admin');
        $this->load->model('Report_model');
    }

    private function options()
    {
        $o = array();
        $o['classes'] = $this->db->order_by('name')->get('classes')->result();
        $o['teachers'] = $this->db->order_by('name')->get('teachers')->result();
        $o['subjects'] = $this->db->order_by('name')->get('subjects')->result();
        $o['semesters'] = $this->db->query("SELECT s.id, CONCAT(y.name,' ',s.name) label, s.start_date, s.end_date FROM semesters s JOIN academic_years y ON y.id=s.academic_year_id ORDER BY y.start_date DESC, s.start_date DESC")->result();
        $o['years'] = $this->db->order_by('start_date', 'DESC')->get('academic_years')->result();
        return $o;
    }

    public function index()
    {
        $type = $this->input->get('type');
        if (!isset(Report_model::$types[$type]) || $type === 'whatsapp') $type = 'siswa';
        $f = $this->Report_model->filters($this->input->get());
        list($title, $headers, $rows) = $this->Report_model->build($type, $f);
        $this->render('rekap/index', array_merge($this->options(), array('title' => 'Rekap Kehadiran', 'type' => $type, 'f' => $f, 'rtitle' => $title, 'headers' => $headers, 'rows' => $rows)));
    }

    /** Rekap siswa per mata pelajaran (matriks tanggal x mapel). */
    public function siswa($sid = 0)
    {
        $st = $this->db->query('SELECT s.*, c.name class_name FROM students s LEFT JOIN classes c ON c.id=s.class_id WHERE s.id=?', array((int)$sid))->row();
        if (!$st) show_404();
        $f = $this->Report_model->filters($this->input->get());
        $this->render('rekap/siswa', array('title' => 'Rekap ' . $st->name, 'st' => $st, 'f' => $f, 'recs' => $this->Report_model->student_matrix($st->id, $f)));
    }
}
