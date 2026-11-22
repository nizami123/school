<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Akun login orang tua/wali untuk portal. Khusus Admin. Password acak ditampilkan SEKALI (tidak disimpan polos). */
class Akunortu extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->need('admin');
    }

    public function index()
    {
        $rows = $this->db->query("SELECT p.id, p.name, p.whatsapp, u.username, u.is_active, u.last_login, u.must_change_password,
            (SELECT GROUP_CONCAT(CONCAT(s.name, ' (', IFNULL(c.name,'-'), ')') SEPARATOR '; ') FROM student_parents sp JOIN students s ON s.id=sp.student_id AND s.deleted_at IS NULL LEFT JOIN classes c ON c.id=s.class_id WHERE sp.parent_id=p.id) AS children
            FROM parents p LEFT JOIN users u ON u.id=p.user_id ORDER BY p.name")->result();
        $this->render('akunortu/index', array('title' => 'Akun Orang Tua', 'rows' => $rows, 'new' => $this->session->flashdata('new_accounts')));
    }

    private function role_id()
    {
        $r = $this->db->get_where('roles', array('name' => 'ortu'))->row();
        if (!$r) { $this->db->query("INSERT IGNORE INTO roles (id, name, label) VALUES (3,'ortu','Orang Tua')"); return 3; }
        return (int)$r->id;
    }

    private function random_password()
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $p = '';
        for ($i = 0; $i < 8; $i++) { $p .= $chars[random_int(0, strlen($chars) - 1)]; }
        return $p;
    }

    private function make_username($parent)
    {
        $nis = $this->db->query('SELECT s.nis FROM student_parents sp JOIN students s ON s.id=sp.student_id WHERE sp.parent_id=? AND s.deleted_at IS NULL ORDER BY sp.is_primary DESC, s.id LIMIT 1', array($parent->id))->row();
        $base = 'ortu' . ($nis ? preg_replace('/[^A-Za-z0-9]/', '', $nis->nis) : $parent->id);
        $u = $base; $i = 1;
        while ($this->db->where('username', $u)->count_all_results('users') > 0) { $i++; $u = $base . '-' . $i; }
        return $u;
    }

    /** Buat akun untuk satu orang tua (mode one) atau semua yang belum punya (mode all). */
    private function create_for($parent)
    {
        $pw = $this->random_password();
        $now = date('Y-m-d H:i:s');
        $username = $this->make_username($parent);
        $this->db->insert('users', array('role_id' => $this->role_id(), 'name' => $parent->name, 'username' => $username,
            'password' => password_hash($pw, PASSWORD_DEFAULT), 'is_active' => 1, 'must_change_password' => 1, 'created_at' => $now, 'updated_at' => $now));
        $uid = (int)$this->db->insert_id();
        $this->db->update('parents', array('user_id' => $uid), array('id' => $parent->id));
        return array('name' => $parent->name, 'username' => $username, 'password' => $pw);
    }

    public function create($id = 0)
    {
        $this->require_post();
        $out = array();
        if ((int)$id > 0) {
            $p = $this->db->get_where('parents', array('id' => (int)$id))->row();
            if (!$p) show_404();
            if ($p->user_id) { $this->flash('warning', 'Orang tua ini sudah punya akun.'); redirect('akunortu'); }
            $out[] = $this->create_for($p);
        } else {
            foreach ($this->db->query('SELECT * FROM parents WHERE user_id IS NULL AND EXISTS (SELECT 1 FROM student_parents sp WHERE sp.parent_id=parents.id) ORDER BY name')->result() as $p) {
                $out[] = $this->create_for($p);
            }
        }
        audit('buat_akun_ortu', count($out) . ' akun');
        if (!$out) { $this->flash('info', 'Tidak ada orang tua yang perlu dibuatkan akun.'); redirect('akunortu'); }
        $this->session->set_flashdata('new_accounts', $out);
        $this->flash('success', count($out) . ' akun dibuat. Catat/cetak daftar password di bawah — hanya tampil sekali.');
        redirect('akunortu');
    }

    public function reset($id)
    {
        $this->require_post();
        $p = $this->db->query('SELECT p.*, u.username FROM parents p JOIN users u ON u.id=p.user_id WHERE p.id=?', array((int)$id))->row();
        if (!$p) show_404();
        $pw = $this->random_password();
        $this->db->update('users', array('password' => password_hash($pw, PASSWORD_DEFAULT), 'must_change_password' => 1, 'failed_logins' => 0, 'locked_until' => NULL,
            'updated_at' => date('Y-m-d H:i:s')), array('id' => $p->user_id));
        audit('reset_password_ortu', 'parent=' . $p->id);
        $this->session->set_flashdata('new_accounts', array(array('name' => $p->name, 'username' => $p->username, 'password' => $pw)));
        $this->flash('success', 'Password direset. Catat password baru di bawah — hanya tampil sekali.');
        redirect('akunortu');
    }

    public function toggle($id)
    {
        $this->require_post();
        $p = $this->db->get_where('parents', array('id' => (int)$id))->row();
        if (!$p || !$p->user_id) show_404();
        $u = $this->db->get_where('users', array('id' => $p->user_id))->row();
        $this->db->update('users', array('is_active' => $u->is_active ? 0 : 1, 'updated_at' => date('Y-m-d H:i:s')), array('id' => $u->id));
        audit('toggle_akun_ortu', 'parent=' . $p->id);
        $this->flash('success', 'Status akun diubah.');
        redirect('akunortu');
    }
}
