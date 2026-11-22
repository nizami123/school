<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Laporan: tampilan cetak, PDF (Dompdf jika terpasang), Excel (PhpSpreadsheet jika terpasang, selain itu CSV). */
class Report extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->need('admin');
        $this->load->model('Report_model');
    }

    public function index()
    {
        $type = $this->input->get('type');
        $f = $this->Report_model->filters($this->input->get());
        $o = array();
        $o['classes'] = $this->db->where('school_id', $this->sid)->order_by('name')->get('classes')->result();
        $o['teachers'] = $this->db->where('school_id', $this->sid)->order_by('name')->get('teachers')->result();
        $o['subjects'] = $this->db->where('school_id', $this->sid)->order_by('name')->get('subjects')->result();
        $o['semesters'] = $this->db->query("SELECT s.id, CONCAT(y.name,' ',s.name) label FROM semesters s JOIN academic_years y ON y.id=s.academic_year_id WHERE s.school_id=? ORDER BY y.start_date DESC, s.start_date DESC", array($this->sid))->result();
        $o['years'] = $this->db->where('school_id', $this->sid)->order_by('start_date', 'DESC')->get('academic_years')->result();
        $o['has_pdf'] = class_exists('Dompdf\Dompdf');
        $o['has_xlsx'] = class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet');
        $this->render('report/index', array_merge($o, array('title' => 'Laporan', 'type' => $type, 'f' => $f)));
    }

    public function output()
    {
        $type = $this->input->get('type');
        if (!isset(Report_model::$types[$type])) show_404();
        $f = $this->Report_model->filters($this->input->get());
        list($title, $headers, $rows) = $this->Report_model->build($type, $f);
        $fmt = $this->input->get('format');
        audit('laporan_' . $type, 'format=' . $fmt . ' ' . $f['from'] . '..' . $f['to']);
        if ($fmt === 'xlsx' || $fmt === 'csv') {
            export_table('laporan-' . $type . '-' . date('Ymd'), $headers, $rows, $fmt);
        }
        $logo_data = '';
        $logo = setting('school_logo', '');
        if ($fmt === 'pdf' && $logo !== '' && is_file(FCPATH . 'uploads/logo/' . $logo)) {
            $ext = strtolower(pathinfo($logo, PATHINFO_EXTENSION));
            $logo_data = 'data:image/' . ($ext === 'png' ? 'png' : 'jpeg') . ';base64,' . base64_encode(file_get_contents(FCPATH . 'uploads/logo/' . $logo));
        }
        $html = $this->load->view('report/print', array('title' => $title, 'headers' => $headers, 'rows' => $rows, 'f' => $f, 'pdf' => $fmt === 'pdf', 'logo_data' => $logo_data), TRUE);
        if ($fmt === 'pdf' && class_exists('Dompdf\Dompdf')) {
            try {
                @ini_set('memory_limit', '256M');
                @set_time_limit(120);
                $d = new \Dompdf\Dompdf(array('isRemoteEnabled' => FALSE, 'defaultFont' => 'Helvetica', 'isHtml5ParserEnabled' => TRUE, 'tempDir' => sys_get_temp_dir()));
                $d->loadHtml($html, 'UTF-8');
                $d->setPaper('A4', count($headers) > 7 ? 'landscape' : 'portrait');
                $d->render();
                $bin = $d->output();
                while (ob_get_level() > 0) { @ob_end_clean(); }
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="laporan-' . $type . '-' . date('Ymd') . '.pdf"');
                header('Content-Length: ' . strlen($bin));
                header('Cache-Control: max-age=0, no-cache, must-revalidate');
                echo $bin;
                exit;
            } catch (\Throwable $ex) {
                log_message('error', 'PDF gagal: ' . $ex->getMessage());
                // lanjut menampilkan versi cetak agar pengguna tetap mendapat laporan
            }
        }
        $this->output->set_output($html);
    }
}
