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
        $o['classes'] = $this->db->where('school_id', $this->sid)->order_by('name')->get('classes')->result();
        $o['teachers'] = $this->db->where('school_id', $this->sid)->order_by('name')->get('teachers')->result();
        $o['subjects'] = $this->db->where('school_id', $this->sid)->order_by('name')->get('subjects')->result();
        $o['semesters'] = $this->db->query("SELECT s.id, CONCAT(y.name,' ',s.name) label, s.start_date, s.end_date FROM semesters s JOIN academic_years y ON y.id=s.academic_year_id WHERE s.school_id=? ORDER BY y.start_date DESC, s.start_date DESC", array($this->sid))->result();
        $o['years'] = $this->db->where('school_id', $this->sid)->order_by('start_date', 'DESC')->get('academic_years')->result();
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
        $st = $this->db->query('SELECT s.*, c.name class_name FROM students s LEFT JOIN classes c ON c.id=s.class_id WHERE s.id=? AND s.school_id=?', array((int)$sid, $this->sid))->row();
        if (!$st) show_404();
        $f = $this->Report_model->filters($this->input->get());
        $this->render('rekap/siswa', array('title' => 'Rekap ' . $st->name, 'st' => $st, 'f' => $f, 'recs' => $this->Report_model->student_matrix($st->id, $f)));
    }

    /** Rekap bulanan otomatis (dihitung langsung dari data absensi; tidak perlu proses manual). */
    public function bulanan()
    {
        $ym = (string)$this->input->get('bulan');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $ym)) $ym = date('Y-m');
        $cid = (int)$this->input->get('class_id');
        $rows = $this->Report_model->monthly($ym, $cid);
        $fmt = $this->input->get('format');
        if ($fmt === 'xlsx') {
            $out = array();
            foreach ($rows as $i => $r) {
                $out[] = array($i + 1, (string)$r->nis, $r->name, (string)$r->class_name, (int)$r->hadir, (int)$r->terlambat, (int)$r->sakit, (int)$r->izin, (int)$r->alpa, (int)$r->belum, (int)$r->total, $r->pct === NULL ? '' : $r->pct);
            }
            audit('ekspor_rekap_bulanan', $ym);
            export_table('rekap-bulanan-' . $ym, array('No', 'NIS', 'Nama', 'Kelas', 'Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpa', 'Belum Dikonfirmasi', 'Total Sesi', '% Hadir'), $out, $fmt, array('sheet' => 'Rekap ' . $ym, 'text_cols' => array(1)));
        }
        $this->render('rekap/bulanan', array('title' => 'Rekap Bulanan', 'ym' => $ym, 'class_id' => $cid, 'rows' => $rows,
            'classes' => $this->db->where('school_id', $this->sid)->order_by('name')->get('classes')->result()));
    }
}
