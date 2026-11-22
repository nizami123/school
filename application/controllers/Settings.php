<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller
{
    private $plain = array('school_name', 'school_address', 'school_phone', 'school_email', 'start_time', 'break_start', 'break_end', 'late_minutes',
        'wa_policy', 'wa_delay', 'card_note', 'pay_bank', 'pay_account', 'pay_holder', 'pay_note',
        'tpl_hadir', 'tpl_terlambat', 'tpl_belum', 'tpl_izin', 'tpl_sakit', 'tpl_rekap');
    private $flags = array('wa_enabled', 'wa_notify_hadir', 'wa_notify_terlambat', 'wa_notify_absent', 'wa_notify_izin', 'wa_notify_sakit', 'wa_notify_daily');

    public function __construct()
    {
        parent::__construct();
        $this->need('admin');
        $this->load->model('Wa_model');
    }

    public function index()
    {
        $this->load->library('whatsapp');
        $this->render('settings/index', array('title' => 'Pengaturan Sekolah', 'has_token' => $this->whatsapp->token() !== '',
            'defaults' => $this->Wa_model->templates()));
    }

    public function save()
    {
        $this->require_post();
        $errors = array();
        foreach ($this->plain as $k) {
            $v = $this->input->post($k);
            if ($v === NULL) continue;
            $v = trim($v);
            if (in_array($k, array('start_time', 'break_start', 'break_end'), TRUE) && $v !== '' && !preg_match('/^\d{2}:\d{2}$/', $v)) { $errors[] = 'Format jam harus HH:MM.'; continue; }
            if ($k === 'late_minutes') $v = (string)max(0, min(120, (int)$v));
            if ($k === 'school_email' && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email sekolah tidak valid.'; continue; }
            if ($k === 'wa_delay' && $v !== '' && !preg_match('/^\d{1,3}(-\d{1,3})?$/', $v)) { $errors[] = 'Jeda kirim harus angka detik, mis. 2 atau 2-5.'; continue; }
            setting_set($k, $v);
        }
        foreach ($this->flags as $k) { setting_set($k, $this->input->post($k) ? '1' : '0'); }
        $days = $this->input->post('effective_days');
        setting_set('effective_days', is_array($days) ? implode(',', array_map('intval', $days)) : '1,2,3,4,5');

        $tok = trim((string)$this->input->post('wa_token'));
        if ($tok !== '') {
            $this->load->library('whatsapp');
            setting_set('wa_token', $this->whatsapp->encrypt_token($tok));
        }
        if (!empty($_FILES['logo']['name'])) {
            $this->load->library('upload', array('upload_path' => FCPATH . 'uploads/logo/', 'allowed_types' => 'jpg|jpeg|png', 'max_size' => 1024, 'encrypt_name' => TRUE));
            if ($this->upload->do_upload('logo')) setting_set('school_logo', $this->upload->data('file_name'));
            else $errors[] = strip_tags($this->upload->display_errors('', ''));
        }
        audit('ubah_pengaturan', 'pengaturan sekolah/WhatsApp diperbarui');
        if ($errors) $this->flash('warning', 'Sebagian tersimpan. ' . implode(' ', array_map('e', $errors)));
        else $this->flash('success', 'Pengaturan disimpan.');
        redirect('settings');
    }

    public function wa_test()
    {
        $this->require_post();
        $this->load->library('whatsapp');
        $to = normalize_phone($this->input->post('test_phone'));
        if (!valid_phone($to)) { $this->flash('danger', 'Nomor uji tidak valid.'); redirect('settings'); }
        if (!$this->whatsapp->configured()) { $this->flash('danger', 'Aktifkan integrasi dan isi Token Fonnte terlebih dahulu, lalu simpan.'); redirect('settings'); }
        $r = $this->whatsapp->send_text($to, 'Pesan uji dari ' . setting('school_name', 'sekolah') . '. Jika Anda menerima ini, integrasi WhatsApp (Fonnte) berfungsi.');
        audit('uji_wa', $to . ' => ' . ($r['ok'] ? 'ok' : 'gagal'));
        $this->flash($r['ok'] ? 'success' : 'danger', $r['ok'] ? 'Permintaan diterima Fonnte' . ($r['id'] ? ' (id ' . e($r['id']) . ')' : '') . '. Cek apakah pesan masuk di nomor tujuan. Jika tidak masuk, pastikan perangkat di dashboard Fonnte berstatus Connected.' : 'Gagal: ' . e($r['raw']));
        redirect('settings');
    }

    public function audit()
    {
        $rows = $this->db->query('SELECT a.*, u.name user_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id WHERE a.school_id=? ORDER BY a.id DESC LIMIT 500', array($this->sid))->result();
        $this->render('settings/audit', array('title' => 'Audit Log', 'rows' => $rows));
    }
}
