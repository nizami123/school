<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('school_id')) {
    /** ID sekolah aktif untuk request ini (0 = tidak ada / super admin). Dipakai semua query agar data antar-sekolah tidak tercampur. */
    function school_id($set = NULL)
    {
        static $id = 0;
        if ($set !== NULL) $id = (int)$set;
        return $id;
    }
}
if (!function_exists('school_row')) {
    /** Baris tabel schools untuk sekolah aktif (cache per request). */
    function school_row($reset = FALSE)
    {
        static $cache = array();
        $sid = school_id();
        if ($reset) unset($cache[$sid]);
        if (!$sid) return NULL;
        if (!array_key_exists($sid, $cache)) {
            $CI =& get_instance();
            $cache[$sid] = $CI->db->get_where('schools', array('id' => $sid))->row();
        }
        return $cache[$sid];
    }
}
if (!function_exists('school_setting_cols')) {
    /** Pengaturan identitas sekolah disimpan di tabel schools (satu sumber untuk Super Admin & Admin Sekolah). */
    function school_setting_cols()
    {
        return array('school_name' => 'name', 'school_address' => 'address', 'school_phone' => 'phone', 'school_email' => 'email', 'school_logo' => 'logo');
    }
}
if (!function_exists('setting')) {
    function setting($key, $default = '', $reset = FALSE)
    {
        static $cache = array();
        $sid = school_id();
        if ($reset) { unset($cache[$sid]); }
        $cols = school_setting_cols();
        if (isset($cols[$key])) {
            $r = school_row();
            $v = $r ? $r->{$cols[$key]} : NULL;
            return ($v !== NULL && $v !== '') ? $v : $default;
        }
        if (!$sid) return $default;
        if (!isset($cache[$sid])) {
            $CI =& get_instance();
            $cache[$sid] = array();
            foreach ($CI->db->get_where('school_settings', array('school_id' => $sid))->result() as $r) {
                $cache[$sid][$r->skey] = $r->svalue;
            }
        }
        return (array_key_exists($key, $cache[$sid]) && $cache[$sid][$key] !== NULL && $cache[$sid][$key] !== '') ? $cache[$sid][$key] : $default;
    }
}
if (!function_exists('setting_set')) {
    function setting_set($key, $value)
    {
        $CI =& get_instance();
        $sid = school_id();
        if (!$sid) return;
        $cols = school_setting_cols();
        if (isset($cols[$key])) {
            if ($key === 'school_name' && trim((string)$value) === '') return;   // nama sekolah wajib ada
            $CI->db->update('schools', array($cols[$key] => ($value === '' ? NULL : $value), 'updated_at' => date('Y-m-d H:i:s')), array('id' => $sid));
            school_row(TRUE);
            return;
        }
        $CI->db->query('INSERT INTO school_settings (school_id, skey, svalue, updated_at) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE svalue=VALUES(svalue), updated_at=VALUES(updated_at)',
            array($sid, $key, $value, date('Y-m-d H:i:s')));
        setting('', '', TRUE);
    }
}
if (!function_exists('app_setting')) {
    /** Pengaturan global platform (mis. kunci cron). */
    function app_setting($key, $default = '')
    {
        $CI =& get_instance();
        $r = $CI->db->get_where('app_settings', array('skey' => $key))->row();
        return ($r && $r->svalue !== NULL && $r->svalue !== '') ? $r->svalue : $default;
    }
}
if (!function_exists('username_taken')) {
    /** Username harus unik secara global (satu form login untuk semua sekolah & Super Admin). */
    function username_taken($username, $ignore_user_id = 0)
    {
        $CI =& get_instance();
        if ($CI->db->where('username', $username)->count_all_results('super_admins') > 0) return TRUE;
        $CI->db->where('username', $username);
        if ($ignore_user_id) $CI->db->where('id !=', (int)$ignore_user_id);
        return $CI->db->count_all_results('users') > 0;
    }
}
if (!function_exists('school_student_count')) {
    /** Jumlah siswa terdaftar (belum dihapus) milik sekolah aktif; dasar kuota langganan. */
    function school_student_count()
    {
        $CI =& get_instance();
        return (int)$CI->db->where(array('school_id' => school_id(), 'deleted_at' => NULL))->count_all_results('students');
    }
}
if (!function_exists('school_quota_left')) {
    /** Sisa kuota siswa; NULL bila tanpa batas (kuota 0). */
    function school_quota_left()
    {
        $r = school_row();
        if (!$r || !(int)$r->student_quota) return NULL;
        return max(0, (int)$r->student_quota - school_student_count());
    }
}
if (!function_exists('school_seed_defaults')) {
    /** Data awal sekolah baru: pengaturan standar, tahun ajaran & semester berjalan. */
    function school_seed_defaults($sid)
    {
        $CI =& get_instance();
        $now = date('Y-m-d H:i:s');
        $y = (int)date('Y');
        if ((int)date('n') >= 7) { $yn = $y . '/' . ($y + 1); $ys = $y . '-07-01'; $ye = ($y + 1) . '-06-30'; $sn = 'Ganjil'; $ss = $y . '-07-01'; $se = $y . '-12-31'; }
        else { $yn = ($y - 1) . '/' . $y; $ys = ($y - 1) . '-07-01'; $ye = $y . '-06-30'; $sn = 'Genap'; $ss = $y . '-01-01'; $se = $y . '-06-30'; }
        $CI->db->insert('academic_years', array('school_id' => $sid, 'name' => $yn, 'start_date' => $ys, 'end_date' => $ye, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now));
        $yid = $CI->db->insert_id();
        $CI->db->insert('semesters', array('school_id' => $sid, 'academic_year_id' => $yid, 'name' => $sn, 'start_date' => $ss, 'end_date' => $se, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now));
        $CI->config->load('wa_defaults', TRUE);
        $settings = array_merge(array(
            'effective_days' => '1,2,3,4,5', 'start_time' => '07:00', 'break_start' => '', 'break_end' => '', 'late_minutes' => '15',
            'wa_enabled' => '0', 'wa_policy' => 'each', 'wa_delay' => '2', 'wa_token' => '',
            'wa_notify_hadir' => '1', 'wa_notify_terlambat' => '1', 'wa_notify_absent' => '1', 'wa_notify_izin' => '1', 'wa_notify_sakit' => '1', 'wa_notify_daily' => '0',
            'card_note' => 'Kartu ini milik sekolah. Jika ditemukan harap dikembalikan.'), $CI->config->item('wa_templates', 'wa_defaults'));
        foreach ($settings as $k => $v) {
            $CI->db->query('INSERT INTO school_settings (school_id, skey, svalue, updated_at) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE svalue=VALUES(svalue)', array($sid, $k, $v, $now));
        }
    }
}
if (!function_exists('e')) {
    function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('hari_id')) {
    function hari_id($n)
    {
        $h = array(1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu');
        return isset($h[(int)$n]) ? $h[(int)$n] : '-';
    }
}
if (!function_exists('tanggal_id')) {
    function tanggal_id($date, $with_day = TRUE)
    {
        if (!$date) return '-';
        $ts = strtotime($date);
        $b = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
        $s = date('j', $ts) . ' ' . $b[(int)date('n', $ts)] . ' ' . date('Y', $ts);
        return $with_day ? hari_id(date('N', $ts)) . ', ' . $s : $s;
    }
}
if (!function_exists('jam_id')) {
    function jam_id($datetime, $wib = TRUE)
    {
        if (!$datetime) return '-';
        return date('H.i', strtotime($datetime)) . ($wib ? ' WIB' : '');
    }
}
if (!function_exists('normalize_phone')) {
    function normalize_phone($p)
    {
        $d = preg_replace('/\D+/', '', (string)$p);
        if ($d === '') return '';
        if (substr($d, 0, 2) === '62') $n = $d;
        elseif (substr($d, 0, 1) === '0') $n = '62' . substr($d, 1);
        elseif (substr($d, 0, 1) === '8') $n = '62' . $d;
        else $n = $d;
        return '+' . $n;
    }
}
if (!function_exists('valid_phone')) {
    function valid_phone($p) { return (bool)preg_match('/^\+62[0-9]{8,13}$/', (string)$p); }
}
if (!function_exists('audit')) {
    function audit($action, $detail = '')
    {
        $CI =& get_instance();
        $role = $CI->session ? (string)$CI->session->userdata('role') : '';
        $uid = $CI->session ? ((int)$CI->session->userdata('uid') ?: NULL) : NULL;
        if ($role === 'superadmin') {   // id super admin bukan id tabel users
            $detail = '[super admin ' . $CI->session->userdata('name') . '] ' . $detail;
            $uid = NULL;
        }
        $CI->db->insert('audit_logs', array(
            'school_id' => school_id() ?: NULL, 'user_id' => $uid,
            'action' => $action, 'detail' => $detail,
            'ip' => $CI->input->ip_address(), 'created_at' => date('Y-m-d H:i:s')));
    }
}
if (!function_exists('status_label')) {
    function status_label($s)
    {
        $m = array('hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpa' => 'Alpa', 'belum' => 'Belum Dikonfirmasi',
                   'menunggu' => 'Menunggu', 'mengirim' => 'Mengirim', 'terkirim' => 'Terkirim', 'gagal' => 'Gagal',
                   'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan', 'terverifikasi' => 'Terverifikasi',
                   'lunas' => 'Lunas', 'sebagian' => 'Sebagian', 'batal' => 'Dibatalkan', 'aktif' => 'Aktif', 'keluar' => 'Keluar');
        return isset($m[$s]) ? $m[$s] : $s;
    }
}
if (!function_exists('status_badge')) {
    function status_badge($s)
    {
        $c = array('hadir' => 'success', 'terkirim' => 'success', 'terlambat' => 'warning text-dark', 'menunggu' => 'secondary', 'mengirim' => 'info',
                   'sakit' => 'info', 'izin' => 'primary', 'alpa' => 'danger', 'gagal' => 'danger', 'belum' => 'secondary',
                   'disetujui' => 'success', 'ditolak' => 'danger', 'dibatalkan' => 'secondary', 'terverifikasi' => 'success', 'lunas' => 'success', 'sebagian' => 'warning text-dark', 'batal' => 'secondary', 'aktif' => 'success', 'keluar' => 'secondary');
        return '<span class="badge bg-' . (isset($c[$s]) ? $c[$s] : 'secondary') . '">' . e(status_label($s)) . '</span>';
    }
}
if (!function_exists('upload_url')) {
    function upload_url($path) { return base_url('uploads/' . ltrim($path, '/')); }
}
if (!function_exists('student_photo')) {
    function student_photo($photo)
    {
        return $photo ? upload_url('students/' . $photo) : base_url('assets/icons/avatar.svg');
    }
}
if (!function_exists('export_table')) {
    /**
     * Ekspor tabel sebagai XLSX (jika PhpSpreadsheet terpasang) atau CSV (titik koma, UTF-8 BOM; dibuka langsung oleh Excel).
     * $opts: 'sheet' => nama sheet, 'text_cols' => indeks kolom (0-based) yang dipaksa berformat Teks
     *        (NIS, NISN, nomor WhatsApp, dll. agar angka 0 di depan / tanda + tidak hilang).
     * Angka pendek (<= 9 digit, tanpa 0 di depan) disimpan sebagai angka; selain itu sebagai teks.
     */
    function export_table($filename, $headers, $rows, $format = 'csv', $opts = array())
    {
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
        $text_cols = isset($opts['text_cols']) ? (array)$opts['text_cols'] : array();
        $sheet = isset($opts['sheet']) ? $opts['sheet'] : 'Data';
        while (ob_get_level() > 0) { @ob_end_clean(); }

        if ($format === 'xlsx' && class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $tmp = tempnam(sys_get_temp_dir(), 'xls');
            try {
                $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sh = $ss->getActiveSheet();
                $sh->setTitle(substr(preg_replace('/[\[\]\*\/\\\\\?:]/', '', (string)$sheet) ?: 'Data', 0, 31));
                $str = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;
                $nc = max(1, count($headers));
                foreach ($headers as $ci => $h) {
                    $sh->setCellValueExplicit(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1) . '1', (string)$h, $str);
                }
                $r = 2;
                foreach ($rows as $row) {
                    foreach (array_values($row) as $ci => $v) {
                        if ($v === NULL || $v === '') continue;
                        $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1) . $r;
                        $is_num = !in_array($ci, $text_cols, TRUE)
                            && (is_int($v) || is_float($v) || (is_string($v) && preg_match('/^-?(0|[1-9][0-9]{0,8})(\.[0-9]{1,6})?$/', $v)));
                        if ($is_num) $sh->setCellValueExplicit($coord, $v + 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        else $sh->setCellValueExplicit($coord, (string)$v, $str);
                    }
                    $r++;
                }
                $last = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($nc);
                foreach ($text_cols as $ci) {
                    if ($ci >= $nc) continue;
                    $c = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
                    $sh->getStyle($c . '2:' . $c . max(500, $r))->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
                }
                $sh->getStyle('A1:' . $last . '1')->applyFromArray(array(
                    'font' => array('bold' => TRUE, 'color' => array('rgb' => 'FFFFFF')),
                    'fill' => array('fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => array('rgb' => '14532D'))));
                $sh->freezePane('A2');
                for ($ci = 1; $ci <= $nc; $ci++) {
                    $sh->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci))->setAutoSize(TRUE);
                }
                $w = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss);
                $w->save($tmp);
                $ss->disconnectWorksheets();
                unset($ss);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
                header('Content-Length: ' . filesize($tmp));
                header('Cache-Control: max-age=0, no-cache, must-revalidate');
                readfile($tmp);
                @unlink($tmp);
                exit;
            } catch (\Throwable $ex) {
                @unlink($tmp);
                log_message('error', 'Ekspor XLSX gagal: ' . $ex->getMessage());
                // lanjut ke CSV agar pengguna tetap mendapat data
            }
        }
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Cache-Control: max-age=0, no-cache, must-revalidate');
        echo "\xEF\xBB\xBF";
        $o = fopen('php://output', 'w');
        fputcsv($o, $headers, ';');
        foreach ($rows as $r) { fputcsv($o, array_values($r), ';'); }
        fclose($o);
        exit;
    }
}

if (!function_exists('rupiah')) {
    function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
}
if (!function_exists('bulan_id')) {
    /** 'YYYY-MM' -> 'Oktober 2026' */
    function bulan_id($ym)
    {
        $b = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
        if (!preg_match('/^(\d{4})-(\d{2})/', (string)$ym, $m)) return (string)$ym;
        return $b[(int)$m[2]] . ' ' . $m[1];
    }
}
if (!function_exists('kategori_badge')) {
    function kategori_badge($c)
    {
        $m = array('umum' => 'secondary', 'libur' => 'danger', 'jadwal' => 'warning text-dark', 'kegiatan' => 'success', 'ujian' => 'primary', 'rapat' => 'info text-dark', 'lainnya' => 'secondary');
        $l = array('umum' => 'Umum', 'libur' => 'Libur', 'jadwal' => 'Perubahan Jadwal', 'kegiatan' => 'Kegiatan', 'ujian' => 'Ujian', 'rapat' => 'Rapat Wali Murid', 'lainnya' => 'Lainnya');
        return '<span class="badge bg-' . (isset($m[$c]) ? $m[$c] : 'secondary') . '">' . e(isset($l[$c]) ? $l[$c] : $c) . '</span>';
    }
}

if (!function_exists('send_private_file')) {
    /** Kirim file dari uploads/private (lampiran izin / bukti bayar) setelah izin akses diperiksa pemanggil. */
    function send_private_file($name)
    {
        if (!preg_match('/^[a-f0-9]{16,40}\.(jpg|jpeg|png|pdf)$/i', (string)$name)) show_404();
        $path = FCPATH . 'uploads/private/' . $name;
        if (!is_file($path)) show_404();
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $mime = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf');
        while (ob_get_level() > 0) { @ob_end_clean(); }
        header('Content-Type: ' . $mime[$ext]);
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=0, no-store');
        readfile($path);
        exit;
    }
}
if (!function_exists('upload_private')) {
    /** Unggah lampiran (JPG/PNG/PDF maks 2 MB) ke uploads/private. Mengembalikan array(nama_file|NULL, pesan_error). */
    function upload_private($field)
    {
        $CI =& get_instance();
        if (empty($_FILES[$field]['name'])) return array(NULL, '');
        $CI->load->library('upload');
        $CI->upload->initialize(array('upload_path' => FCPATH . 'uploads/private/', 'allowed_types' => 'jpg|jpeg|png|pdf', 'max_size' => 2048, 'encrypt_name' => TRUE), TRUE);
        if (!$CI->upload->do_upload($field)) return array(NULL, strip_tags($CI->upload->display_errors('', '')));
        return array($CI->upload->data('file_name'), '');
    }
}
if (!function_exists('apply_leave_to_sessions')) {
    /** Terapkan izin/sakit yang disetujui ke sesi absensi yang SUDAH ada (tidak menimpa hadir/terlambat). */
    function apply_leave_to_sessions($leave)
    {
        $CI =& get_instance();
        $st = $CI->db->get_where('students', array('id' => $leave->student_id, 'school_id' => school_id()))->row();
        if (!$st || !$st->class_id) return 0;
        $now = date('Y-m-d H:i:s');
        $note = ($leave->leave_type === 'sakit' ? 'Sakit' : 'Izin') . ' (pengajuan orang tua #' . $leave->id . ')';
        $CI->db->query("INSERT INTO attendance_records (session_id, student_id, status, source, note, created_at, updated_at)
            SELECT ses.id, ?, ?, 'manual', ?, ?, ? FROM attendance_sessions ses WHERE ses.school_id=? AND ses.class_id=? AND ses.session_date BETWEEN ? AND ?
            ON DUPLICATE KEY UPDATE note=IF(attendance_records.status IN ('hadir','terlambat'), attendance_records.note, VALUES(note)),
              status=IF(attendance_records.status IN ('hadir','terlambat'), attendance_records.status, VALUES(status)), updated_at=VALUES(updated_at)",
            array($st->id, $leave->leave_type, $note, $now, $now, $st->school_id, $st->class_id, $leave->date_from, $leave->date_to));
        return $CI->db->affected_rows();
    }
}
