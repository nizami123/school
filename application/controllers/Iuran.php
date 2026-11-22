<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Tagihan & pembayaran SPP/iuran/infaq. Khusus Admin. Tidak ada WhatsApp; pembayaran manual (tunai / transfer + bukti). */
class Iuran extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->need('admin');
        $this->load->model('Fee_model');
    }

    private function pending_count()
    {
        return (int)$this->db->where('status', 'menunggu')->count_all_results('payments');
    }

    public function index()
    {
        $f = array('status' => (string)$this->input->get('status'), 'class_id' => (int)$this->input->get('class_id'),
            'fee_type_id' => (int)$this->input->get('fee_type_id'), 'period' => trim((string)$this->input->get('period')), 'q' => trim((string)$this->input->get('q')));
        if (!in_array($f['status'], array('tunggakan', 'belum', 'sebagian', 'lunas', 'semua'), TRUE)) $f['status'] = 'tunggakan';
        $w = " WHERE i.status<>'batal'"; $a = array();
        if ($f['status'] === 'tunggakan') $w .= " AND i.status IN ('belum','sebagian')";
        elseif ($f['status'] !== 'semua') { $w .= ' AND i.status=?'; $a[] = $f['status']; }
        if ($f['class_id']) { $w .= ' AND s.class_id=?'; $a[] = $f['class_id']; }
        if ($f['fee_type_id']) { $w .= ' AND i.fee_type_id=?'; $a[] = $f['fee_type_id']; }
        if ($f['period'] !== '') { $w .= ' AND i.period=?'; $a[] = $f['period']; }
        if ($f['q'] !== '') { $w .= ' AND (s.name LIKE ? OR s.nis LIKE ?)'; $a[] = '%' . $f['q'] . '%'; $a[] = '%' . $f['q'] . '%'; }
        $from = ' FROM invoices i JOIN students s ON s.id=i.student_id LEFT JOIN classes c ON c.id=s.class_id JOIN fee_types ft ON ft.id=i.fee_type_id';
        $sum = $this->db->query('SELECT COUNT(*) n, IFNULL(SUM(i.amount),0) total, IFNULL(SUM(i.paid_total),0) paid' . $from . $w, $a)->row();
        $rows = $this->db->query('SELECT i.*, s.name student_name, s.nis, c.name class_name, ft.category' . $from . $w . ' ORDER BY i.due_date IS NULL, i.due_date, c.name, s.name LIMIT 1000', $a)->result();
        $this->render('iuran/index', array('title' => 'Iuran, SPP & Infaq', 'rows' => $rows, 'sum' => $sum, 'f' => $f, 'pending' => $this->pending_count(),
            'classes' => $this->db->order_by('name')->get('classes')->result(), 'fees' => $this->db->order_by('name')->get('fee_types')->result()));
    }

    public function generate()
    {
        $fees = $this->db->where('is_active', 1)->where('frequency !=', 'bebas')->order_by('name')->get('fee_types')->result();
        if ($this->input->method() === 'post') {
            $ft = $this->db->get_where('fee_types', array('id' => (int)$this->input->post('fee_type_id')))->row();
            $period = trim((string)$this->input->post('period'));
            $cid = (int)$this->input->post('class_id');
            $due = trim((string)$this->input->post('due_date'));
            $amount = (int)$this->input->post('amount');
            $err = '';
            if (!$ft || !$ft->is_active || $ft->frequency === 'bebas') $err = 'Pilih jenis iuran yang valid.';
            elseif ($ft->frequency === 'bulanan' && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) $err = 'Periode bulanan harus berformat bulan-tahun.';
            elseif ($ft->frequency === 'sekali' && ($period === '' || strlen($period) > 30)) $err = 'Isi label periode (mis. Semester Ganjil 2026), maks 30 karakter.';
            elseif ($due !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $due)) $err = 'Format jatuh tempo tidak valid.';
            if ($err === '' && $amount < 1) $amount = (int)$ft->amount;
            if ($err === '' && $amount < 1) $err = 'Nominal wajib diisi.';
            if ($err !== '') { $this->flash('danger', $err); redirect('iuran/generate'); }
            $title = $ft->name . ($ft->frequency === 'bulanan' ? ' ' . bulan_id($period) : ' - ' . $period);
            $now = date('Y-m-d H:i:s');
            $total = (int)$this->db->query('SELECT COUNT(*) c FROM students s WHERE s.is_active=1 AND s.deleted_at IS NULL' . ($cid ? ' AND s.class_id=?' : ''), $cid ? array($cid) : array())->row()->c;
            $args = array($ft->id, $period, $title, $amount, $due !== '' ? $due : NULL, $this->uid, $now, $now);
            $sql = "INSERT IGNORE INTO invoices (student_id, fee_type_id, period, title, amount, due_date, status, created_by, created_at, updated_at)
                SELECT s.id, ?, ?, ?, ?, ?, 'belum', ?, ?, ? FROM students s WHERE s.is_active=1 AND s.deleted_at IS NULL";
            if ($cid) { $sql .= ' AND s.class_id=?'; $args[] = $cid; }
            $this->db->query($sql, $args);
            $made = $this->db->affected_rows();
            audit('buat_tagihan', $title . ' dibuat=' . $made . ' dilewati=' . ($total - $made));
            $this->flash('success', $made . ' tagihan dibuat' . ($total - $made > 0 ? ', ' . ($total - $made) . ' dilewati karena sudah ada.' : '.'));
            redirect('iuran');
        }
        $this->render('iuran/generate', array('title' => 'Buat Tagihan', 'fees' => $fees, 'classes' => $this->db->order_by('name')->get('classes')->result(), 'pending' => $this->pending_count()));
    }

    public function detail($id)
    {
        $inv = $this->db->query('SELECT i.*, s.name student_name, s.nis, c.name class_name FROM invoices i JOIN students s ON s.id=i.student_id LEFT JOIN classes c ON c.id=s.class_id WHERE i.id=?', array((int)$id))->row();
        if (!$inv) show_404();
        $pays = $this->db->order_by('id', 'DESC')->get_where('payments', array('invoice_id' => $inv->id))->result();
        $this->render('iuran/detail', array('title' => 'Detail Tagihan', 'inv' => $inv, 'pays' => $pays, 'remaining' => $this->Fee_model->remaining($inv)));
    }

    public function bayar($id)
    {
        $this->require_post();
        $inv = $this->db->get_where('invoices', array('id' => (int)$id))->row();
        if (!$inv) show_404();
        $method = $this->input->post('method');
        if (!in_array($method, array('tunai', 'transfer', 'lainnya'), TRUE)) $method = 'tunai';
        $err = $this->Fee_model->add_verified($inv, (int)$this->input->post('amount'), $method, substr(trim((string)$this->input->post('note')), 0, 250), $this->uid);
        if ($err) $this->flash('danger', $err);
        else { audit('catat_pembayaran', 'invoice=' . $inv->id); $this->flash('success', 'Pembayaran tercatat.'); }
        redirect('iuran/detail/' . $inv->id);
    }

    public function batal($id)
    {
        $this->require_post();
        $inv = $this->db->get_where('invoices', array('id' => (int)$id))->row();
        if (!$inv) show_404();
        $n = (int)$this->db->where(array('invoice_id' => $inv->id))->where_in('status', array('terverifikasi', 'menunggu'))->count_all_results('payments');
        if ($n > 0) { $this->flash('danger', 'Tagihan sudah memiliki pembayaran/pengajuan, tidak dapat dibatalkan.'); redirect('iuran/detail/' . $inv->id); }
        $this->db->update('invoices', array('status' => 'batal', 'updated_at' => date('Y-m-d H:i:s')), array('id' => $inv->id));
        audit('batal_tagihan', 'invoice=' . $inv->id);
        $this->flash('success', 'Tagihan dibatalkan.');
        redirect('iuran');
    }

    public function verifikasi()
    {
        $rows = $this->db->query("SELECT p.*, i.title, i.amount invoice_amount, i.paid_total, s.name student_name, c.name class_name
            FROM payments p JOIN invoices i ON i.id=p.invoice_id JOIN students s ON s.id=i.student_id LEFT JOIN classes c ON c.id=s.class_id
            WHERE p.status='menunggu' ORDER BY p.id")->result();
        $this->render('iuran/verifikasi', array('title' => 'Verifikasi Pembayaran', 'rows' => $rows, 'pending' => count($rows)));
    }

    public function proses($id)
    {
        $this->require_post();
        $act = $this->input->post('action');
        if (!in_array($act, array('terima', 'tolak'), TRUE)) show_404();
        $err = $this->Fee_model->review($id, $act === 'terima', $this->uid, substr(trim((string)$this->input->post('note')), 0, 250));
        if ($err) $this->flash('danger', $err);
        else { audit($act === 'terima' ? 'verifikasi_bayar' : 'tolak_bayar', 'payment=' . (int)$id); $this->flash('success', $act === 'terima' ? 'Pembayaran diverifikasi.' : 'Pembayaran ditolak.'); }
        redirect('iuran/verifikasi');
    }

    public function bukti($id)
    {
        $p = $this->db->get_where('payments', array('id' => (int)$id))->row();
        if (!$p || !$p->proof) show_404();
        send_private_file($p->proof);
    }

    public function kwitansi($id)
    {
        $p = $this->db->query("SELECT p.*, i.title, s.name student_name, s.nis, c.name class_name FROM payments p JOIN invoices i ON i.id=p.invoice_id JOIN students s ON s.id=i.student_id LEFT JOIN classes c ON c.id=s.class_id WHERE p.id=? AND p.status='terverifikasi'", array((int)$id))->row();
        if (!$p) show_404();
        $this->load->view('iuran/kwitansi', array('p' => $p));
    }
}
