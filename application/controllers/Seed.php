<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pengisi data dummy untuk uji coba (khusus Admin). Semua ID yang dibuat dicatat sehingga bisa dihapus
 * bersih lewat tombol "Hapus Data Dummy" tanpa menyentuh data asli. Hapus file ini sebelum produksi.
 */
class Seed extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->need('admin');
    }

    private function tracked()
    {
        $j = setting('dummy_ids', '');
        $a = $j !== '' ? json_decode($j, TRUE) : NULL;
        return is_array($a) ? $a : NULL;
    }

    public function index()
    {
        $t = $this->tracked();
        $counts = array();
        if ($t) { foreach ($t as $k => $v) { $counts[$k] = count($v); } }
        $this->render('seed/index', array('title' => 'Data Dummy', 'has' => $t !== NULL, 'counts' => $counts, 'sem' => $this->active_semester()));
    }

    public function run()
    {
        $this->require_post();
        if ($this->tracked() !== NULL) { $this->flash('danger', 'Data dummy sudah ada. Hapus dulu sebelum membuat ulang.'); redirect('seed'); }
        $sem = $this->active_semester();
        if (!$sem) { $this->flash('danger', 'Belum ada semester aktif.'); redirect('seed'); }
        $wa = trim((string)$this->input->post('test_wa'));
        $waN = '';
        if ($wa !== '') {
            $waN = normalize_phone($wa);
            if (!valid_phone($waN)) { $this->flash('danger', 'Nomor WhatsApp uji tidak valid.'); redirect('seed'); }
        }
        $pw = (string)$this->input->post('password');
        if (strlen($pw) < 6) $pw = 'guru12345';
        $history = $this->input->post('history') ? TRUE : FALSE;

        @set_time_limit(300);
        $this->load->model('Qr_model');
        $ids = array('users' => array(), 'teachers' => array(), 'subjects' => array(), 'classes' => array(), 'students' => array(), 'parents' => array(), 'schedules' => array());
        $old = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        $this->db->trans_start();
        $sum = NULL; $msg = '';
        try {
            $sum = $this->build($sem, $waN, $pw, $history, $ids);
        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
            $this->db->trans_rollback();
        }
        $this->db->trans_complete();
        $ok = ($sum !== NULL && $this->db->trans_status() !== FALSE);
        $this->db->db_debug = $old;
        if (!$ok) {
            $e2 = $this->db->error();
            $this->flash('danger', 'Gagal membuat data dummy: ' . e($msg !== '' ? $msg : $e2['message']));
            redirect('seed');
        }
        setting_set('dummy_ids', json_encode($ids));
        audit('buat_data_dummy', json_encode($sum));
        $this->flash('success', 'Data dummy dibuat: ' . $sum['students'] . ' siswa, ' . $sum['teachers'] . ' guru, ' . $sum['schedules'] . ' jadwal, ' . $sum['sessions'] . ' sesi riwayat absensi. Login guru: dummy.andi (password sesuai isian).');
        redirect('seed');
    }

    private function build($sem, $waN, $pw, $history, &$ids)
    {
        $now = date('Y-m-d H:i:s');
        mt_srand(2026);

        // Mata pelajaran
        $subj = array(array('MTK', 'Matematika'), array('BIN', 'Bahasa Indonesia'), array('ING', 'Bahasa Inggris'), array('IPA', 'IPA'), array('IPS', 'IPS'), array('PJOK', 'PJOK'));
        $sid = array();
        foreach ($subj as $i => $s) {
            $r = $this->db->get_where('subjects', array('code' => $s[0]))->row();
            if ($r) { $sid[$i] = (int)$r->id; continue; }
            if (!$this->db->insert('subjects', array('code' => $s[0], 'name' => $s[1], 'created_at' => $now, 'updated_at' => $now))) throw new RuntimeException('Gagal membuat mapel ' . $s[0]);
            $sid[$i] = (int)$this->db->insert_id();
            $ids['subjects'][] = $sid[$i];
        }

        // Guru + akun login
        $tn = array(array('Andi Prasetyo, S.Pd', 'dummy.andi'), array('Siti Nurhaliza, S.Pd', 'dummy.siti'), array('Budi Hartono, S.Pd', 'dummy.budi'),
                    array('Rina Wulandari, S.Pd', 'dummy.rina'), array('Agus Salim, S.Pd', 'dummy.agus'), array('Dewi Kartika, S.Pd', 'dummy.dewi'));
        $tid = array();
        foreach ($tn as $i => $t) {
            if ($this->db->where('username', $t[1])->count_all_results('users') > 0) throw new RuntimeException('Username ' . $t[1] . ' sudah ada.');
            $this->db->insert('users', array('role_id' => 2, 'name' => $t[0], 'username' => $t[1], 'password' => password_hash($pw, PASSWORD_DEFAULT), 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now));
            $uid = (int)$this->db->insert_id();
            $ids['users'][] = $uid;
            $this->db->insert('teachers', array('user_id' => $uid, 'name' => $t[0], 'nip' => '19850101201001' . sprintf('%04d', $i + 1), 'email' => $t[1] . '@example.com', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now));
            $tid[$i] = (int)$this->db->insert_id();
            $ids['teachers'][] = $tid[$i];
            $this->db->insert('teacher_subjects', array('teacher_id' => $tid[$i], 'subject_id' => $sid[$i]));
        }

        // Kelas
        $cn = array(array('DM VII A', 'VII'), array('DM VII B', 'VII'), array('DM VIII A', 'VIII'));
        $cid = array();
        foreach ($cn as $i => $c) {
            if (!$this->db->insert('classes', array('name' => $c[0], 'level' => $c[1], 'homeroom_teacher_id' => $tid[$i], 'academic_year_id' => $sem->academic_year_id, 'created_at' => $now, 'updated_at' => $now))) throw new RuntimeException('Gagal membuat kelas ' . $c[0]);
            $cid[$i] = (int)$this->db->insert_id();
            $ids['classes'][] = $cid[$i];
        }

        // Siswa (10 per kelas) + orang tua + QR
        $names = array(
            array('Ahmad Fauzi', 'L'), array('Siti Aminah', 'P'), array('Rizky Ramadhan', 'L'), array('Nur Aisyah', 'P'), array('Muhammad Iqbal', 'L'),
            array('Dewi Sartika', 'P'), array('Bagus Setiawan', 'L'), array('Putri Amelia', 'P'), array('Fajar Nugroho', 'L'), array('Lestari Ayu', 'P'),
            array('Dimas Pratama', 'L'), array('Salsabila Putri', 'P'), array('Yusuf Hidayat', 'L'), array('Anisa Rahmawati', 'P'), array('Arif Budiman', 'L'),
            array('Fitri Handayani', 'P'), array('Rafi Maulana', 'L'), array('Indah Permata', 'P'), array('Zaki Mubarok', 'L'), array('Nabila Zahra', 'P'),
            array('Hendra Wijaya', 'L'), array('Mega Safitri', 'P'), array('Taufik Hidayatullah', 'L'), array('Citra Kirana', 'P'), array('Farhan Alfarizi', 'L'),
            array('Wulan Sari', 'P'), array('Galih Saputra', 'L'), array('Rina Marlina', 'P'), array('Eko Prabowo', 'L'), array('Tiara Andini', 'P'));
        $byClass = array(0 => array(), 1 => array(), 2 => array());
        foreach ($names as $i => $n) {
            $c = intdiv($i, 10);
            $wa = $waN !== '' ? $waN : '+62812' . sprintf('%07d', $i + 1);
            $this->db->insert('parents', array('name' => 'Orang Tua ' . explode(' ', $n[0])[0], 'whatsapp' => $wa, 'created_at' => $now, 'updated_at' => $now));
            $pid = (int)$this->db->insert_id();
            $ids['parents'][] = $pid;
            if (!$this->db->insert('students', array('nis' => 'DM' . sprintf('%04d', $i + 1), 'nisn' => '99' . sprintf('%08d', $i + 1), 'name' => $n[0], 'gender' => $n[1],
                'class_id' => $cid[$c], 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now))) throw new RuntimeException('Gagal membuat siswa ' . $n[0]);
            $stid = (int)$this->db->insert_id();
            $ids['students'][] = $stid;
            $this->db->insert('student_parents', array('student_id' => $stid, 'parent_id' => $pid, 'is_primary' => 1));
            $this->Qr_model->generate($stid, 'Data dummy', $this->uid);
            $qr = $this->db->get_where('student_qr_codes', array('student_id' => $stid, 'is_active' => 1))->row();
            $byClass[$c][] = array('id' => $stid, 'qr' => $qr ? (int)$qr->id : NULL);
        }

        // Jadwal Senin-Jumat, 3 jam pelajaran/hari/kelas, guru tidak bentrok
        $slots = array(array('07:00:00', '08:30:00'), array('08:30:00', '10:00:00'), array('10:30:00', '12:00:00'));
        $sched = array();
        for ($day = 1; $day <= 5; $day++) {
            foreach ($slots as $s => $sl) {
                for ($c = 0; $c < 3; $c++) {
                    $ti = ($day * 2 + $s * 3 + $c) % 6;
                    if (!$this->db->insert('schedules', array('semester_id' => $sem->id, 'day' => $day, 'class_id' => $cid[$c], 'subject_id' => $sid[$ti], 'teacher_id' => $tid[$ti],
                        'start_time' => $sl[0], 'end_time' => $sl[1], 'room' => 'R' . ($c + 1), 'created_at' => $now, 'updated_at' => $now))) throw new RuntimeException('Gagal membuat jadwal.');
                    $jid = (int)$this->db->insert_id();
                    $ids['schedules'][] = $jid;
                    $sched[] = array('id' => $jid, 'day' => $day, 'c' => $c, 'subj' => $sid[$ti], 'teacher' => $tid[$ti], 'start' => $sl[0], 'end' => $sl[1]);
                }
            }
        }

        // Riwayat absensi 14 hari terakhir (hari kerja)
        $nses = 0; $nrec = 0;
        if ($history) {
            $late_min = (int)setting('late_minutes', 15);
            for ($d = 1; $d <= 14; $d++) {
                $ts = strtotime('-' . $d . ' day');
                $dow = (int)date('N', $ts);
                if ($dow > 5) continue;
                $date = date('Y-m-d', $ts);
                $state = array();
                foreach ($byClass as $list) {
                    foreach ($list as $stu) {
                        $r = mt_rand(1, 100);
                        $state[$stu['id']] = $r <= 4 ? 'sakit' : ($r <= 7 ? 'izin' : ($r <= 9 ? 'alpa' : NULL));
                    }
                }
                foreach ($sched as $sc) {
                    if ($sc['day'] !== $dow) continue;
                    $startTs = strtotime($date . ' ' . $sc['start']);
                    $late = date('H:i:s', $startTs + $late_min * 60);
                    if (!$this->db->insert('attendance_sessions', array('schedule_id' => $sc['id'], 'semester_id' => $sem->id, 'class_id' => $cid[$sc['c']], 'subject_id' => $sc['subj'],
                        'teacher_id' => $sc['teacher'], 'session_date' => $date, 'start_time' => $sc['start'], 'end_time' => $sc['end'], 'late_limit' => $late,
                        'total_students' => count($byClass[$sc['c']]), 'status' => 'closed', 'started_by' => $this->uid, 'started_at' => $date . ' ' . $sc['start'],
                        'ended_at' => $date . ' ' . $sc['end'], 'created_at' => $now, 'updated_at' => $now))) throw new RuntimeException('Gagal membuat sesi riwayat.');
                    $sesid = (int)$this->db->insert_id();
                    $nses++;
                    $rows = array();
                    foreach ($byClass[$sc['c']] as $stu) {
                        $st = $state[$stu['id']];
                        if ($st !== NULL) {
                            $status = $st; $scan = NULL; $src = 'manual'; $note = ($st === 'sakit' ? 'Surat dokter' : ($st === 'izin' ? 'Izin keluarga' : 'Tanpa keterangan')); $by = $this->uid;
                        } elseif (mt_rand(1, 100) <= 10) {
                            $status = 'terlambat'; $scan = date('Y-m-d H:i:s', $startTs + $late_min * 60 + mt_rand(60, 900)); $src = 'scan'; $note = NULL; $by = NULL;
                        } else {
                            $status = 'hadir'; $scan = date('Y-m-d H:i:s', $startTs + mt_rand(-300, max(0, $late_min * 60 - 30))); $src = 'scan'; $note = NULL; $by = NULL;
                        }
                        $rows[] = array('session_id' => $sesid, 'student_id' => $stu['id'], 'qr_id' => $src === 'scan' ? $stu['qr'] : NULL, 'status' => $status, 'scanned_at' => $scan,
                            'source' => $src, 'note' => $note, 'corrected_by' => $by, 'created_at' => $now, 'updated_at' => $now);
                    }
                    $this->db->insert_batch('attendance_records', $rows);
                    $nrec += count($rows);
                }
            }
        }
        return array('subjects' => count($ids['subjects']), 'teachers' => count($ids['teachers']), 'classes' => count($ids['classes']),
            'students' => count($ids['students']), 'schedules' => count($ids['schedules']), 'sessions' => $nses, 'records' => $nrec);
    }

    public function clear()
    {
        $this->require_post();
        $t = $this->tracked();
        if ($t === NULL) { $this->flash('warning', 'Tidak ada data dummy yang tercatat.'); redirect('seed'); }
        $old = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        $this->db->trans_start();
        $stu = $t['students']; $cls = $t['classes']; $tch = $t['teachers']; $sub = $t['subjects'];

        if ($stu) {
            $this->db->where_in('student_id', $stu)->delete('attendance_records');
            $this->db->where_in('student_id', $stu)->delete('whatsapp_notifications');
        }
        $sq = array();
        if ($cls) $sq[] = 'class_id IN (' . implode(',', array_map('intval', $cls)) . ')';
        if ($tch) $sq[] = 'teacher_id IN (' . implode(',', array_map('intval', $tch)) . ')';
        if ($sub) $sq[] = 'subject_id IN (' . implode(',', array_map('intval', $sub)) . ')';
        if ($sq) {
            $w = implode(' OR ', $sq);
            $sessIds = array();
            foreach ($this->db->query("SELECT id FROM attendance_sessions WHERE $w")->result() as $r) { $sessIds[] = (int)$r->id; }
            if ($sessIds) {
                $this->db->where_in('session_id', $sessIds)->delete('scan_logs');
                $this->db->where_in('session_id', $sessIds)->delete('whatsapp_notifications');
                $this->db->where_in('id', $sessIds)->delete('attendance_sessions');   // baris kehadiran ikut terhapus (cascade)
            }
            $this->db->query("DELETE FROM schedules WHERE $w");
        }
        if ($stu) $this->db->where_in('id', $stu)->delete('students');   // QR & relasi orang tua ikut terhapus
        if ($t['parents']) $this->db->where_in('id', $t['parents'])->delete('parents');
        if ($cls) $this->db->where_in('id', $cls)->delete('classes');
        if ($tch) $this->db->where_in('id', $tch)->delete('teachers');
        if ($t['users']) $this->db->where_in('id', $t['users'])->delete('users');
        if ($sub) $this->db->where_in('id', $sub)->delete('subjects');
        $this->db->trans_complete();
        $ok = $this->db->trans_status() !== FALSE;
        $err = $this->db->error();
        $this->db->db_debug = $old;
        if (!$ok) {
            $this->flash('danger', 'Sebagian data dummy tidak bisa dihapus karena dipakai data lain: ' . e($err['message']));
            redirect('seed');
        }
        setting_set('dummy_ids', '');
        audit('hapus_data_dummy', '');
        $this->flash('success', 'Data dummy dihapus.');
        redirect('seed');
    }
}
