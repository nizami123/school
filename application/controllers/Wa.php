<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Wa extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->need('admin');
        $this->load->model('Wa_model');
    }

    public function index()
    {
        $st = $this->input->get('status');
        $this->db->select('n.*, s.name student_name')->from('whatsapp_notifications n')->join('students s', 's.id=n.student_id');
        if (in_array($st, array('menunggu', 'mengirim', 'terkirim', 'gagal'), TRUE)) $this->db->where('n.status', $st);
        $rows = $this->db->order_by('n.id', 'DESC')->limit(500)->get()->result();
        $counts = array();
        foreach ($this->db->query('SELECT status, COUNT(*) c FROM whatsapp_notifications GROUP BY status')->result() as $r) { $counts[$r->status] = (int)$r->c; }
        $this->load->library('whatsapp');
        $this->render('wa/index', array('title' => 'Notifikasi WhatsApp', 'rows' => $rows, 'counts' => $counts, 'st' => $st, 'configured' => $this->whatsapp->configured()));
    }

    public function resend()
    {
        $this->require_post();
        $id = (int)$this->input->post('id');
        $this->db->update('whatsapp_notifications', array('status' => 'menunggu', 'attempts' => 0, 'next_retry_at' => NULL, 'updated_at' => date('Y-m-d H:i:s')),
            array('id' => $id, 'status' => 'gagal'));
        audit('kirim_ulang_wa', 'id=' . $id);
        $this->flash('success', 'Pesan dimasukkan kembali ke antrean.');
        redirect('wa');
    }

    public function resend_all()
    {
        $this->require_post();
        $this->db->query("UPDATE whatsapp_notifications SET status='menunggu', attempts=0, next_retry_at=NULL, updated_at=? WHERE status='gagal' AND phone IS NOT NULL", array(date('Y-m-d H:i:s')));
        $n = $this->db->affected_rows();
        audit('kirim_ulang_semua_wa', $n . ' pesan');
        $this->flash('success', $n . ' pesan gagal dimasukkan kembali ke antrean.');
        redirect('wa');
    }

    public function process_now()
    {
        $this->require_post();
        $r = $this->Wa_model->process(20);
        if (!$r['configured']) $this->flash('warning', 'Integrasi WhatsApp belum dikonfigurasi/aktif. Isi kredensial di Pengaturan > WhatsApp.');
        else $this->flash('success', 'Diproses: ' . $r['sent'] . ' terkirim, ' . $r['failed'] . ' gagal/diulang.');
        redirect('wa');
    }
}
