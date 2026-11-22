<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Portal orang tua/wali: memantau kehadiran, jadwal, pengumuman, izin, iuran, ekskul.
 * Semua data dibatasi ke anak yang tertaut pada akun (tabel student_parents).
 * Portal TIDAK mengirim WhatsApp apa pun; WhatsApp hanya dipakai untuk notifikasi absensi.
 */
class Portal extends MY_Controller
{
    private $children = array();
    private $child = NULL;
    private $child_ids = array();

    public function __construct()
    {
        parent::__construct();
        $this->need('ortu');
        if (!$this->parent_id) show_error('Akun ini belum tertaut ke data orang tua. Hubungi admin sekolah.', 403, 'Akses Ditolak');
        $m = $this->router->fetch_method();
        if ($this->session->userdata('must_change') && !in_array($m, array('profil', 'profil_simpan'), TRUE)) {
            $this->flash('warning', 'Demi keamanan, silakan ganti password awal Anda terlebih dahulu.');
            redirect('portal/profil');
        }
        $this->children = $this->db->query("SELECT s.id, s.nis, s.name, s.photo, s.class_id, c.name class_name FROM student_parents sp
            JOIN students s ON s.id=sp.student_id AND s.deleted_at IS NULL AND s.is_active=1 AND s.school_id=? LEFT JOIN classes c ON c.id=s.class_id
            WHERE sp.parent_id=? ORDER BY s.name", array($this->sid, $this->parent_id))->result();
        foreach ($this->children as $c) { $this->child_ids[] = (int)$c->id; }
        $want = (int)$this->input->get('anak');
        if (!$want) $want = (int)$this->session->userdata('child_id');
        foreach ($this->children as $c) { if ((int)$c->id === $want) $this->child = $c; }
        if (!$this->child && $this->children) $this->child = $this->children[0];
        if ($this->child) $this->session->set_userdata('child_id', (int)$this->child->id);
        $this->load->model(array('Report_model', 'Fee_model'));
    }

    private function show($view, $data)
    {
        $data['children'] = $this->children;
        $data['child'] = $this->child;
        $this->render($view, $data);
    }

    private function need_child()
    {
        if (!$this->child) { $this->show('portal/kosong', array('title' => 'Portal Orang Tua')); $this->output->_display(); exit; }
    }

    private function class_ids()
    {
        $o = array();
        foreach ($this->children as $c) { if ($c->class_id) $o[] = (int)$c->class_id; }
        return array_values(array_unique($o));
    }

    /** SQL + args untuk pengumuman yang boleh dilihat orang tua ini. */
    private function ann_where(&$args)
    {
        $cls = $this->class_ids();
        $w = "a.school_id=? AND a.is_active=1 AND a.publish_date<=?";
        $args = array($this->sid, date('Y-m-d'));
        if ($cls) { $w .= ' AND (a.class_id IS NULL OR a.class_id IN (' . implode(',', $cls) . '))'; } else { $w .= ' AND a.class_id IS NULL'; }
        return $w;
    }

    private function own_student($sid)
    {
        return in_array((int)$sid, $this->child_ids, TRUE);
    }

    /* ---------------------------------------------------------------- Beranda */

    public function index()
    {
        $this->need_child();
        $ch = $this->child; $today = date('Y-m-d');
        $d = array('title' => 'Beranda');
        $d['today_status'] = $this->db->query("SELECT r.status, r.scanned_at, sub.name subject, ses.start_time, ses.end_time FROM attendance_records r
            JOIN attendance_sessions ses ON ses.id=r.session_id JOIN subjects sub ON sub.id=ses.subject_id WHERE r.student_id=? AND ses.session_date=? ORDER BY ses.start_time", array($ch->id, $today))->result();
        $d['schedule'] = $ch->class_id ? $this->db->query("SELECT sc.start_time, sc.end_time, sc.room, sub.name subject, t.name teacher FROM schedules sc JOIN semesters sm ON sm.id=sc.semester_id AND sm.is_active=1
            JOIN subjects sub ON sub.id=sc.subject_id JOIN teachers t ON t.id=sc.teacher_id WHERE sc.school_id=? AND sc.class_id=? AND sc.day=? ORDER BY sc.start_time", array($this->sid, $ch->class_id, date('N')))->result() : array();
        $m = $this->Report_model->monthly(date('Y-m'), 0, $ch->id);
        $d['month'] = $m ? $m[0] : NULL;
        $aw = $this->ann_where($a);
        $d['ann'] = $this->db->query("SELECT a.* FROM announcements a WHERE $aw ORDER BY a.is_pinned DESC, a.publish_date DESC, a.id DESC LIMIT 3", $a)->result();
        $d['events'] = $this->db->query("SELECT * FROM academic_events WHERE school_id=? AND end_date>=? AND start_date<=? ORDER BY start_date LIMIT 4", array($this->sid, $today, date('Y-m-d', strtotime('+30 days'))))->result();
        $in = $this->db->query("SELECT COUNT(*) n, IFNULL(SUM(amount-paid_total),0) sisa FROM invoices WHERE student_id=? AND status IN ('belum','sebagian')", array($ch->id))->row();
        $d['unpaid'] = $in;
        $d['leave_pending'] = (int)$this->db->where(array('student_id' => $ch->id, 'status' => 'menunggu'))->count_all_results('leave_requests');
        $this->show('portal/index', $d);
    }

    /* ------------------------------------------------------------- Kehadiran */

    public function kehadiran()
    {
        $this->need_child();
        $ym = (string)$this->input->get('bulan');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $ym)) $ym = date('Y-m');
        $from = $ym . '-01'; $to = date('Y-m-t', strtotime($from));
        $m = $this->Report_model->monthly($ym, 0, $this->child->id);
        $days = $this->db->query("SELECT ses.session_date, ses.start_time, ses.end_time, sub.name subject, r.status, r.scanned_at, r.note FROM attendance_records r
            JOIN attendance_sessions ses ON ses.id=r.session_id JOIN subjects sub ON sub.id=ses.subject_id
            WHERE r.student_id=? AND ses.session_date BETWEEN ? AND ? ORDER BY ses.session_date DESC, ses.start_time", array($this->child->id, $from, $to))->result();
        $trend = $this->db->query("SELECT DATE_FORMAT(ses.session_date,'%Y-%m') ym, COUNT(*) total, SUM(r.status IN ('hadir','terlambat')) present, SUM(r.status='terlambat') late,
            SUM(r.status IN ('sakit','izin')) excused, SUM(r.status IN ('alpa','belum')) absent FROM attendance_records r JOIN attendance_sessions ses ON ses.id=r.session_id
            WHERE r.student_id=? AND ses.session_date>=? GROUP BY ym ORDER BY ym DESC", array($this->child->id, date('Y-m-01', strtotime('-5 months'))))->result();
        $this->show('portal/kehadiran', array('title' => 'Kehadiran', 'ym' => $ym, 'month' => $m ? $m[0] : NULL, 'days' => $days, 'trend' => $trend,
            'prev' => date('Y-m', strtotime($from . ' -1 month')), 'next' => date('Y-m', strtotime($from . ' +1 month'))));
    }

    /* ------------------------------------------------------ Jadwal & Kalender */

    public function jadwal()
    {
        $this->need_child();
        $ym = (string)$this->input->get('bulan');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $ym)) $ym = date('Y-m');
        $from = $ym . '-01'; $to = date('Y-m-t', strtotime($from));
        $rows = $this->child->class_id ? $this->db->query("SELECT sc.day, sc.start_time, sc.end_time, sc.room, sub.name subject, t.name teacher FROM schedules sc JOIN semesters sm ON sm.id=sc.semester_id AND sm.is_active=1
            JOIN subjects sub ON sub.id=sc.subject_id JOIN teachers t ON t.id=sc.teacher_id WHERE sc.school_id=? AND sc.class_id=? ORDER BY sc.day, sc.start_time", array($this->sid, $this->child->class_id))->result() : array();
        $events = $this->db->query('SELECT * FROM academic_events WHERE school_id=? AND end_date>=? AND start_date<=? ORDER BY start_date', array($this->sid, $from, $to))->result();
        $this->show('portal/jadwal', array('title' => 'Jadwal & Kalender', 'rows' => $rows, 'events' => $events, 'ym' => $ym,
            'prev' => date('Y-m', strtotime($from . ' -1 month')), 'next' => date('Y-m', strtotime($from . ' +1 month')), 'tab' => $this->input->get('tab') === 'kalender' ? 'kalender' : 'jadwal'));
    }

    /* ------------------------------------------------------------ Pengumuman */

    public function pengumuman($id = 0)
    {
        $aw = $this->ann_where($a);
        if ((int)$id > 0) {
            $a[] = (int)$id;
            $row = $this->db->query("SELECT a.* FROM announcements a WHERE $aw AND a.id=?", $a)->row();
            if (!$row) show_404();
            $rsvp = $this->db->get_where('announcement_rsvps', array('announcement_id' => $row->id, 'parent_id' => $this->parent_id))->row();
            $this->show('portal/pengumuman_detail', array('title' => 'Pengumuman', 'a' => $row, 'rsvp' => $rsvp));
            return;
        }
        $cat = (string)$this->input->get('kategori');
        if (!in_array($cat, array('umum', 'libur', 'jadwal', 'kegiatan', 'ujian', 'rapat'), TRUE)) $cat = '';
        if ($cat !== '') { $aw .= ' AND a.category=?'; $a[] = $cat; }
        $rows = $this->db->query("SELECT a.* FROM announcements a WHERE $aw ORDER BY a.is_pinned DESC, a.publish_date DESC, a.id DESC LIMIT 100", $a)->result();
        $this->show('portal/pengumuman', array('title' => 'Pengumuman', 'rows' => $rows, 'cat' => $cat));
    }

    public function rsvp($id)
    {
        $this->require_post();
        $aw = $this->ann_where($a);
        $a[] = (int)$id;
        $row = $this->db->query("SELECT a.* FROM announcements a WHERE $aw AND a.id=? AND a.need_rsvp=1", $a)->row();
        if (!$row) show_404();
        $resp = $this->input->post('response');
        if (!in_array($resp, array('hadir', 'tidak'), TRUE)) show_404();
        $this->db->query('INSERT INTO announcement_rsvps (announcement_id, parent_id, response, note, updated_at) VALUES (?,?,?,?,?)
            ON DUPLICATE KEY UPDATE response=VALUES(response), note=VALUES(note), updated_at=VALUES(updated_at)',
            array($row->id, $this->parent_id, $resp, substr(trim((string)$this->input->post('note')), 0, 250) ?: NULL, date('Y-m-d H:i:s')));
        $this->flash('success', 'Konfirmasi kehadiran Anda tersimpan.');
        redirect('portal/pengumuman/' . $row->id);
    }

    /* ------------------------------------------------------------------ Izin */

    public function izin()
    {
        $this->need_child();
        $rows = $this->db->order_by('id', 'DESC')->limit(50)->get_where('leave_requests', array('student_id' => $this->child->id))->result();
        $this->show('portal/izin', array('title' => 'Izin & Sakit', 'rows' => $rows));
    }

    public function izin_simpan()
    {
        $this->require_post();
        $this->need_child();
        $type = $this->input->post('leave_type');
        $from = (string)$this->input->post('date_from');
        $to = (string)$this->input->post('date_to');
        if ($to === '') $to = $from;
        $reason = trim((string)$this->input->post('reason'));
        $err = '';
        if (!in_array($type, array('izin', 'sakit'), TRUE)) $err = 'Pilih jenis izin.';
        elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) $err = 'Tanggal tidak valid.';
        elseif ($to < $from) $err = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        elseif ($from < date('Y-m-d', strtotime('-14 days'))) $err = 'Pengajuan maksimal 14 hari ke belakang. Hubungi wali kelas.';
        elseif ($to > date('Y-m-d', strtotime('+60 days'))) $err = 'Tanggal terlalu jauh ke depan.';
        elseif ((strtotime($to) - strtotime($from)) / 86400 > 13) $err = 'Satu pengajuan maksimal 14 hari. Ajukan terpisah untuk periode lebih panjang.';
        elseif (mb_strlen($reason) < 5) $err = 'Tuliskan alasan (minimal 5 karakter).';
        elseif (mb_strlen($reason) > 500) $err = 'Alasan maksimal 500 karakter.';
        if ($err === '') {
            $dup = (int)$this->db->query("SELECT COUNT(*) c FROM leave_requests WHERE student_id=? AND status IN ('menunggu','disetujui') AND date_from<=? AND date_to>=?", array($this->child->id, $to, $from))->row()->c;
            if ($dup) $err = 'Sudah ada pengajuan yang menunggu/disetujui pada tanggal tersebut.';
        }
        $file = NULL;
        if ($err === '') { list($file, $uerr) = upload_private('attachment'); if ($uerr !== '') $err = $uerr; }
        if ($err !== '') { $this->flash('danger', $err); redirect('portal/izin'); }
        $now = date('Y-m-d H:i:s');
        $this->db->insert('leave_requests', array('school_id' => $this->sid, 'student_id' => $this->child->id, 'parent_id' => $this->parent_id, 'leave_type' => $type, 'date_from' => $from, 'date_to' => $to,
            'reason' => $reason, 'attachment' => $file, 'status' => 'menunggu', 'created_at' => $now, 'updated_at' => $now));
        audit('ajukan_izin', 'siswa=' . $this->child->id);
        $this->flash('success', 'Pengajuan terkirim. Menunggu persetujuan sekolah.');
        redirect('portal/izin');
    }

    public function izin_batal($id)
    {
        $this->require_post();
        $lr = $this->db->get_where('leave_requests', array('id' => (int)$id, 'school_id' => $this->sid, 'parent_id' => $this->parent_id, 'status' => 'menunggu'))->row();
        if (!$lr) { $this->flash('warning', 'Pengajuan tidak dapat dibatalkan.'); redirect('portal/izin'); }
        $this->db->update('leave_requests', array('status' => 'dibatalkan', 'updated_at' => date('Y-m-d H:i:s')), array('id' => $lr->id));
        $this->flash('success', 'Pengajuan dibatalkan.');
        redirect('portal/izin');
    }

    public function lampiran($id)
    {
        $lr = $this->db->get_where('leave_requests', array('id' => (int)$id, 'school_id' => $this->sid))->row();
        if (!$lr || !$lr->attachment || (int)$lr->parent_id !== $this->parent_id) show_404();
        send_private_file($lr->attachment);
    }

    /* ------------------------------------------------------------ Pembayaran */

    public function bayar()
    {
        $this->need_child();
        $inv = $this->db->query("SELECT i.*, (SELECT IFNULL(SUM(p.amount),0) FROM payments p WHERE p.invoice_id=i.id AND p.status='menunggu') pending_amt
            FROM invoices i WHERE i.student_id=? AND i.status<>'batal' ORDER BY (i.status='lunas'), i.due_date IS NULL, i.due_date, i.id DESC LIMIT 100", array($this->child->id))->result();
        $pays = $this->db->query("SELECT p.*, i.title FROM payments p JOIN invoices i ON i.id=p.invoice_id WHERE i.student_id=? ORDER BY p.id DESC LIMIT 30", array($this->child->id))->result();
        $infaq = $this->db->where(array('school_id' => $this->sid, 'is_active' => 1, 'frequency' => 'bebas'))->order_by('name')->get('fee_types')->result();
        $this->show('portal/bayar', array('title' => 'Pembayaran', 'inv' => $inv, 'pays' => $pays, 'infaq' => $infaq));
    }

    private function own_invoice($id)
    {
        $inv = $this->db->get_where('invoices', array('id' => (int)$id, 'school_id' => $this->sid))->row();
        if (!$inv || !$this->own_student($inv->student_id)) show_404();
        return $inv;
    }

    public function bayar_form($id)
    {
        $inv = $this->own_invoice($id);
        if ($inv->status === 'batal') show_404();
        $pending = (int)$this->db->query("SELECT IFNULL(SUM(amount),0) s FROM payments WHERE invoice_id=? AND status='menunggu'", array($inv->id))->row()->s;
        $this->show('portal/bayar_form', array('title' => 'Konfirmasi Pembayaran', 'inv' => $inv, 'max' => max(0, $this->Fee_model->remaining($inv) - $pending), 'pending' => $pending));
    }

    public function bayar_kirim($id)
    {
        $this->require_post();
        $inv = $this->own_invoice($id);
        $pending = (int)$this->db->query("SELECT IFNULL(SUM(amount),0) s FROM payments WHERE invoice_id=? AND status='menunggu'", array($inv->id))->row()->s;
        $max = max(0, $this->Fee_model->remaining($inv) - $pending);
        $amount = (int)preg_replace('/\D/', '', (string)$this->input->post('amount'));
        $err = '';
        if ($inv->status === 'batal' || $inv->status === 'lunas') $err = 'Tagihan ini tidak dapat dibayar.';
        elseif ($amount < 1 || $amount > $max) $err = 'Nominal harus antara Rp 1 dan ' . rupiah($max) . '.';
        $paid_date = (string)$this->input->post('paid_date');
        if ($err === '' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $paid_date) || $paid_date > date('Y-m-d') || $paid_date < date('Y-m-d', strtotime('-60 days')))) $err = 'Tanggal transfer tidak valid.';
        $file = NULL;
        if ($err === '') {
            list($file, $uerr) = upload_private('proof');
            if ($uerr !== '') $err = $uerr; elseif (!$file) $err = 'Unggah bukti transfer (JPG/PNG/PDF, maks 2 MB).';
        }
        if ($err !== '') { $this->flash('danger', $err); redirect('portal/bayar_form/' . $inv->id); }
        $now = date('Y-m-d H:i:s');
        $this->db->insert('payments', array('invoice_id' => $inv->id, 'amount' => $amount, 'method' => 'transfer', 'paid_at' => $paid_date . ' ' . date('H:i:s'), 'proof' => $file,
            'note' => substr(trim((string)$this->input->post('note')), 0, 250) ?: NULL, 'status' => 'menunggu', 'submitted_by_parent' => 1, 'created_at' => $now, 'updated_at' => $now));
        audit('kirim_bukti_bayar', 'invoice=' . $inv->id);
        $this->flash('success', 'Bukti pembayaran terkirim. Menunggu verifikasi sekolah.');
        redirect('portal/bayar');
    }

    public function infaq_kirim()
    {
        $this->require_post();
        $this->need_child();
        $ft = $this->db->get_where('fee_types', array('id' => (int)$this->input->post('fee_type_id'), 'school_id' => $this->sid, 'is_active' => 1, 'frequency' => 'bebas'))->row();
        $amount = (int)preg_replace('/\D/', '', (string)$this->input->post('amount'));
        $err = '';
        if (!$ft) $err = 'Jenis infaq tidak valid.';
        elseif ($amount < 1000) $err = 'Nominal minimal Rp 1.000.';
        elseif ($amount > 100000000) $err = 'Nominal terlalu besar.';
        $file = NULL;
        if ($err === '') {
            list($file, $uerr) = upload_private('proof');
            if ($uerr !== '') $err = $uerr; elseif (!$file) $err = 'Unggah bukti transfer (JPG/PNG/PDF, maks 2 MB).';
        }
        if ($err !== '') { $this->flash('danger', $err); redirect('portal/bayar'); }
        $now = date('Y-m-d H:i:s');
        $this->db->trans_start();
        $this->db->insert('invoices', array('school_id' => $this->sid, 'student_id' => $this->child->id, 'fee_type_id' => $ft->id, 'period' => date('YmdHis') . random_int(100, 999), 'title' => $ft->name . ' (' . tanggal_id($now, FALSE) . ')',
            'amount' => $amount, 'status' => 'belum', 'created_at' => $now, 'updated_at' => $now));
        $iid = $this->db->insert_id();
        $this->db->insert('payments', array('invoice_id' => $iid, 'amount' => $amount, 'method' => 'transfer', 'paid_at' => $now, 'proof' => $file, 'note' => substr(trim((string)$this->input->post('note')), 0, 250) ?: NULL,
            'status' => 'menunggu', 'submitted_by_parent' => 1, 'created_at' => $now, 'updated_at' => $now));
        $this->db->trans_complete();
        audit('kirim_infaq', 'siswa=' . $this->child->id);
        $this->flash('success', 'Terima kasih. Bukti infaq terkirim dan menunggu verifikasi sekolah.');
        redirect('portal/bayar');
    }

    public function bukti($id)
    {
        $p = $this->db->query('SELECT p.proof, i.student_id FROM payments p JOIN invoices i ON i.id=p.invoice_id WHERE p.id=?', array((int)$id))->row();
        if (!$p || !$p->proof || !$this->own_student($p->student_id)) show_404();
        send_private_file($p->proof);
    }

    public function kwitansi($id)
    {
        $p = $this->db->query("SELECT p.*, i.title, i.student_id, s.name student_name, s.nis, c.name class_name FROM payments p JOIN invoices i ON i.id=p.invoice_id JOIN students s ON s.id=i.student_id LEFT JOIN classes c ON c.id=s.class_id WHERE p.id=? AND p.status='terverifikasi'", array((int)$id))->row();
        if (!$p || !$this->own_student($p->student_id)) show_404();
        $this->load->view('iuran/kwitansi', array('p' => $p));
    }

    /* ------------------------------------------------------------ Ekstrakurikuler */

    public function ekskul()
    {
        $this->need_child();
        $rows = $this->db->query("SELECT x.*, (SELECT COUNT(*) FROM extracurricular_members m WHERE m.extracurricular_id=x.id AND m.status='aktif') filled,
            (SELECT m2.status FROM extracurricular_members m2 WHERE m2.extracurricular_id=x.id AND m2.student_id=?) my_status
            FROM extracurriculars x WHERE x.school_id=? AND x.is_active=1 ORDER BY x.name", array($this->child->id, $this->sid))->result();
        $this->show('portal/ekskul', array('title' => 'Ekstrakurikuler', 'rows' => $rows));
    }

    public function ekskul_daftar($id)
    {
        $this->require_post();
        $this->need_child();
        $x = $this->db->get_where('extracurriculars', array('id' => (int)$id, 'school_id' => $this->sid, 'is_active' => 1))->row();
        if (!$x) show_404();
        $filled = (int)$this->db->where(array('extracurricular_id' => $x->id, 'status' => 'aktif'))->count_all_results('extracurricular_members');
        if ($x->quota && $filled >= (int)$x->quota) { $this->flash('danger', 'Kuota kegiatan ini sudah penuh.'); redirect('portal/ekskul'); }
        $now = date('Y-m-d H:i:s');
        $this->db->query("INSERT INTO extracurricular_members (extracurricular_id, student_id, status, requested_by_parent, created_at, updated_at) VALUES (?,?, 'menunggu', 1, ?, ?)
            ON DUPLICATE KEY UPDATE status=IF(status IN ('aktif','menunggu'), status, 'menunggu'), requested_by_parent=1, updated_at=VALUES(updated_at)", array($x->id, $this->child->id, $now, $now));
        $this->flash('success', 'Pendaftaran terkirim. Menunggu persetujuan pembina/sekolah.');
        redirect('portal/ekskul');
    }

    public function ekskul_keluar($id)
    {
        $this->require_post();
        $this->need_child();
        $this->db->query("UPDATE extracurricular_members SET status='keluar', updated_at=? WHERE extracurricular_id=? AND student_id=? AND status IN ('aktif','menunggu') AND extracurricular_id IN (SELECT id FROM extracurriculars WHERE school_id=?)",
            array(date('Y-m-d H:i:s'), (int)$id, $this->child->id, $this->sid));
        $this->flash('success', 'Keikutsertaan dibatalkan.');
        redirect('portal/ekskul');
    }

    /* ----------------------------------------------------------- Menu / Profil */

    public function menu()
    {
        $this->show('portal/menu', array('title' => 'Menu'));
    }

    public function profil()
    {
        $p = $this->db->get_where('parents', array('id' => $this->parent_id))->row();
        $u = $this->db->get_where('users', array('id' => $this->uid))->row();
        $this->show('portal/profil', array('title' => 'Profil & Password', 'p' => $p, 'u' => $u, 'must' => (bool)$this->session->userdata('must_change')));
    }

    public function profil_simpan()
    {
        $this->require_post();
        $u = $this->db->get_where('users', array('id' => $this->uid))->row();
        $old = (string)$this->input->post('old_password');
        $new = (string)$this->input->post('new_password');
        $err = '';
        if (!$u || !password_verify($old, $u->password)) $err = 'Password lama salah.';
        elseif (strlen($new) < 8) $err = 'Password baru minimal 8 karakter.';
        elseif ($new !== (string)$this->input->post('new_password2')) $err = 'Konfirmasi password baru tidak sama.';
        elseif ($new === $old) $err = 'Password baru harus berbeda dari yang lama.';
        if ($err !== '') { $this->flash('danger', $err); redirect('portal/profil'); }
        $this->db->update('users', array('password' => password_hash($new, PASSWORD_DEFAULT), 'must_change_password' => 0, 'updated_at' => date('Y-m-d H:i:s')), array('id' => $u->id));
        $this->session->set_userdata('must_change', 0);
        audit('ganti_password', 'portal');
        $this->flash('success', 'Password berhasil diganti.');
        redirect('portal');
    }
}
