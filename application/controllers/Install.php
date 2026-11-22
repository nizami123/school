<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Installer sekali jalan: impor skema, buat Super Admin awal, tulis konfigurasi, kunci installer. */
class Install extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form', 'app'));
        if (file_exists(APPPATH . 'config/installed.lock')) {
            show_error('Aplikasi sudah terpasang. Hapus file application/config/installed.lock HANYA jika Anda sengaja ingin memasang ulang (data lama tidak dihapus otomatis).', 403, 'Installer Terkunci');
        }
    }

    public function index()
    {
        $checks = array(
            'PHP >= 7.3' => version_compare(PHP_VERSION, '7.3.0', '>='),
            'Ekstensi mysqli' => extension_loaded('mysqli'),
            'Ekstensi curl' => extension_loaded('curl'),
            'Ekstensi openssl' => extension_loaded('openssl'),
            'Ekstensi mbstring' => extension_loaded('mbstring'),
            'Folder application/config dapat ditulis' => is_writable(APPPATH . 'config'),
            'Folder application/cache/sessions dapat ditulis' => is_writable(APPPATH . 'cache/sessions'),
            'Folder uploads dapat ditulis' => is_writable(FCPATH . 'uploads'),
            'File database/schema.sql ada' => file_exists(FCPATH . 'database/schema.sql'),
        );
        $this->load->view('install/index', array('checks' => $checks, 'error' => $this->session_error()));
    }

    private function session_error() { return isset($_GET['err']) ? $_GET['err'] : ''; }

    private function back($msg)
    {
        $this->load->view('install/index', array('checks' => array(), 'error' => $msg, 'old' => $_POST));
    }

    public function run()
    {
        if ($this->input->method() !== 'post') redirect('install');
        $p = $this->input->post();
        foreach (array('db_host', 'db_name', 'db_user', 'admin_name', 'admin_user', 'admin_pass') as $k) {
            if (!isset($p[$k]) || trim($p[$k]) === '') return $this->back('Semua kolom bertanda * wajib diisi.');
        }
        if (strlen($p['admin_pass']) < 8) return $this->back('Password Super Admin minimal 8 karakter.');
        if ($p['admin_pass'] !== $p['admin_pass2']) return $this->back('Konfirmasi password tidak sama.');
        if (!preg_match('/^[A-Za-z0-9_.\-]{3,60}$/', $p['admin_user'])) return $this->back('Username hanya boleh huruf, angka, titik, strip, underscore (3-60 karakter).');
        if (!is_writable(APPPATH . 'config')) return $this->back('Folder application/config tidak dapat ditulis. Atur izin folder (chmod 755/775).');

        $cfg = array('dsn' => '', 'hostname' => $p['db_host'], 'username' => $p['db_user'], 'password' => isset($p['db_pass']) ? $p['db_pass'] : '',
            'database' => $p['db_name'], 'dbdriver' => 'mysqli', 'dbprefix' => '', 'pconnect' => FALSE, 'db_debug' => FALSE,
            'cache_on' => FALSE, 'cachedir' => '', 'char_set' => 'utf8mb4', 'dbcollat' => 'utf8mb4_unicode_ci', 'swap_pre' => '',
            'encrypt' => FALSE, 'compress' => FALSE, 'stricton' => FALSE, 'failover' => array(), 'save_queries' => FALSE);
        try {
            $db = $this->load->database($cfg, TRUE);
            if (!$db->conn_id) return $this->back('Tidak dapat terhubung ke database. Periksa host, nama database, user, dan password.');
        } catch (Throwable $e) {
            return $this->back('Koneksi database gagal: ' . $e->getMessage());
        }

        // 1. Skema
        $sql = file_get_contents(FCPATH . 'database/schema.sql');
        $sql = preg_replace('/^--.*$/m', '', $sql);
        foreach (explode(";\n", $sql) as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '') continue;
            if (!$db->query($stmt)) {
                $e = $db->error();
                return $this->back('Gagal membuat tabel: ' . $e['message']);
            }
        }
        if ($db->count_all_results('users') > 0 || $db->count_all_results('super_admins') > 0) {
            return $this->back('Database ini sudah berisi pengguna. Gunakan database kosong baru untuk instalasi.');
        }

        // 2. Data awal
        $now = date('Y-m-d H:i:s');
        $db->query("INSERT IGNORE INTO roles (id, name, label) VALUES (1,'admin','Admin Sekolah'),(2,'guru','Guru'),(3,'ortu','Orang Tua')");
        $db->insert('super_admins', array('name' => trim($p['admin_name']), 'username' => trim($p['admin_user']),
            'password' => password_hash($p['admin_pass'], PASSWORD_DEFAULT), 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now));
        $db->query('INSERT INTO app_settings (skey, svalue, updated_at) VALUES (?,?,?) ON DUPLICATE KEY UPDATE svalue=VALUES(svalue)', array('cron_key', bin2hex(random_bytes(16)), $now));

        // 3. Konfigurasi lokal & kunci
        $export = "<?php defined('BASEPATH') OR exit('No direct script access allowed');\nreturn " . var_export(array(
            'hostname' => $cfg['hostname'], 'username' => $cfg['username'], 'password' => $cfg['password'], 'database' => $cfg['database']), TRUE) . ";\n";
        if (file_put_contents(APPPATH . 'config/database_local.php', $export) === FALSE) return $this->back('Gagal menulis database_local.php.');
        $secret = "<?php defined('BASEPATH') OR exit('No direct script access allowed');\nreturn " . var_export(array('encryption_key' => bin2hex(random_bytes(16))), TRUE) . ";\n";
        file_put_contents(APPPATH . 'config/secret.php', $secret);
        @chmod(APPPATH . 'config/database_local.php', 0640);
        @chmod(APPPATH . 'config/secret.php', 0640);
        file_put_contents(APPPATH . 'config/installed.lock', 'Dipasang ' . $now . "\n");

        redirect('login?installed=1');
    }
}
