<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tugas terjadwal (cPanel Cron) untuk SEMUA sekolah aktif. Dijalankan oleh pengelola platform, bukan admin sekolah.
 *  CLI : php /home/USER/public_html/index.php cron wa
 *  URL : https://domain/cron/wa/KUNCI   (KUNCI ada di panel Super Admin)
 */
class Cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!file_exists(APPPATH . 'config/installed.lock')) exit('Belum terpasang');
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(array('url', 'app'));
        $this->load->model(array('Wa_model', 'Attendance_model'));
    }

    private function guard($key)
    {
        if (is_cli()) return;
        if ($key === NULL || !hash_equals((string)app_setting('cron_key', ''), (string)$key)) { show_error('Forbidden', 403); }
    }

    private function schools()
    {
        return $this->db->query('SELECT id FROM schools WHERE is_active=1 ORDER BY id')->result();
    }

    /** Kirim antrean WhatsApp + coba ulang yang tertunda. Jalankan tiap 1 menit. */
    public function wa($key = NULL)
    {
        $this->guard($key);
        $this->db->query("SET time_zone = '+07:00'");
        $sent = 0; $failed = 0;
        foreach ($this->schools() as $sc) {
            school_id((int)$sc->id);
            school_row(TRUE);
            setting('', '', TRUE);
            $r = $this->Wa_model->process(50);
            $sent += $r['sent']; $failed += $r['failed'];
        }
        school_id(0);
        echo json_encode(array('sent' => $sent, 'failed' => $failed)) . "\n";
    }

    /** Tutup sesi lama + (opsional) rekap harian. Jalankan sekali sehari (mis. 16:00). */
    public function daily($key = NULL)
    {
        $this->guard($key);
        $this->db->query("SET time_zone = '+07:00'");
        $closed = 0; $n = 0;
        foreach ($this->schools() as $sc) {
            school_id((int)$sc->id);
            school_row(TRUE);
            setting('', '', TRUE);
            $closed += $this->Attendance_model->close_stale();
            if (setting('wa_notify_daily', '0') === '1' && setting('wa_enabled', '0') === '1') {
                $date = date('Y-m-d');
                $rows = $this->db->query("SELECT st.id, st.name, c.name class_name, p.id parent_id, p.whatsapp, sub.name subject, r.status, r.scanned_at
                    FROM attendance_records r JOIN attendance_sessions s ON s.id=r.session_id JOIN students st ON st.id=r.student_id
                    LEFT JOIN classes c ON c.id=s.class_id JOIN subjects sub ON sub.id=s.subject_id
                    LEFT JOIN student_parents sp ON sp.student_id=st.id AND sp.is_primary=1 LEFT JOIN parents p ON p.id=sp.parent_id
                    WHERE s.school_id=? AND s.session_date=? ORDER BY st.id, s.start_time", array((int)$sc->id, $date))->result();
                $by = array();
                foreach ($rows as $r) {
                    if (!isset($by[$r->id])) $by[$r->id] = array('st' => $r, 'lines' => array());
                    $by[$r->id]['lines'][] = '- ' . $r->subject . ': ' . status_label($r->status) . ($r->scanned_at ? ' (' . jam_id($r->scanned_at, FALSE) . ')' : '');
                }
                foreach ($by as $b) { if ($this->Wa_model->queue_daily($b['st'], $date, $b['lines'])) $n++; }
            }
        }
        school_id(0);
        echo json_encode(array('closed_sessions' => $closed, 'daily_recaps_queued' => $n)) . "\n";
    }
}
