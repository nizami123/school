<?php
/**
 * Pembuat data dummy QR School Attendance (hasil: database/dummy_data.sql).
 * Jalankan ulang agar tanggal riwayat absensi/tagihan mengikuti hari ini:
 *     php database/tools/generate_dummy.php > database/dummy_data.sql
 * Semua data dummy bisa dihapus dengan database/hapus_data_dummy.sql.
 */
date_default_timezone_set('Asia/Jakarta');
$TODAY = new DateTime('today');
$tag = '[DATA DUMMY]';

function q($v) { if ($v === NULL) return 'NULL'; if (is_int($v) || is_float($v)) return (string)$v; return "'" . str_replace(array('\\', "'"), array('\\\\', "''"), $v) . "'"; }
function h($a, $b = '', $c = '') { return crc32($a . '|' . $b . '|' . $c) % 100; }
$out = array();
function ins($table, $cols, $rows, $chunk = 150)
{
    global $out;
    for ($i = 0; $i < count($rows); $i += $chunk) {
        $part = array_slice($rows, $i, $chunk);
        $out[] = 'INSERT INTO ' . $table . ' (' . implode(', ', $cols) . ") VALUES\n" . implode(",\n", array_map(function ($r) { return '(' . implode(', ', array_map('q', $r)) . ')'; }, $part)) . ';';
    }
    $out[] = '';
}
$guruPw = password_hash('guru12345', PASSWORD_DEFAULT);
$ortuPw = password_hash('ortu12345', PASSWORD_DEFAULT);

$out[] = "-- =====================================================================================";
$out[] = "-- DATA DUMMY QR School Attendance (dibuat " . $TODAY->format('Y-m-d') . ")";
$out[] = "-- Cara pakai: impor SEKALI ke database yang BARU diinstal (belum ada data master) lewat phpMyAdmin > Impor.";
$out[] = "-- Login guru  : dummy.andi / dummy.siti / dummy.budi / dummy.rina / dummy.agus / dummy.dewi  (password: guru12345)";
$out[] = "-- Login ortu  : ortudummy01 ... ortudummy10  (password: ortu12345). Orang tua lain belum punya akun (untuk uji menu Akun Orang Tua).";
$out[] = "-- Nomor WhatsApp orang tua SENGAJA dikosongkan agar tidak ada pesan terkirim ke nomor acak. Isi nomor Anda sendiri pada 1 orang tua bila ingin menguji WhatsApp.";
$out[] = "-- Hapus semua data dummy: jalankan database/hapus_data_dummy.sql";
$out[] = "-- =====================================================================================";
$out[] = "SET NAMES utf8mb4;";
$out[] = "SET FOREIGN_KEY_CHECKS=0;";
$out[] = "START TRANSACTION;";
$out[] = "SET @sem := (SELECT id FROM semesters WHERE is_active=1 ORDER BY start_date DESC LIMIT 1);";
$out[] = "SET @yr := (SELECT id FROM academic_years WHERE is_active=1 ORDER BY start_date DESC LIMIT 1);";
$out[] = "INSERT IGNORE INTO roles (id, name, label) VALUES (1,'admin','Admin Sekolah'),(2,'guru','Guru'),(3,'ortu','Orang Tua');";
$out[] = '';
$now = $TODAY->format('Y-m-d') . ' 06:00:00';

// ---- Mata pelajaran
$subj = array(array('DM-MTK', 'Matematika'), array('DM-BIN', 'Bahasa Indonesia'), array('DM-ING', 'Bahasa Inggris'), array('DM-IPA', 'IPA'), array('DM-IPS', 'IPS'), array('DM-PJOK', 'PJOK'));
$rows = array(); foreach ($subj as $i => $s) { $rows[] = array($i + 1, $s[0], $s[1], 3, $now, $now); }
ins('subjects', array('id', 'code', 'name', 'hours', 'created_at', 'updated_at'), $rows);

// ---- Guru + akun
$tn = array(array('Andi Prasetyo, S.Pd', 'dummy.andi'), array('Siti Nurhaliza, S.Pd', 'dummy.siti'), array('Budi Hartono, S.Pd', 'dummy.budi'), array('Rina Wulandari, S.Pd', 'dummy.rina'), array('Agus Salim, S.Pd', 'dummy.agus'), array('Dewi Kartika, S.Pd', 'dummy.dewi'));
$ur = array(); $tr = array(); $ts = array();
foreach ($tn as $i => $t) {
    $uid = 101 + $i;
    $ur[] = array($uid, 2, $t[0], $t[1], $guruPw, 1, 0, $now, $now);
    $tr[] = array($i + 1, $uid, $t[0], '19850101201001' . sprintf('%04d', $i + 1), $t[1] . '@example.com', 1, $now, $now);
    $ts[] = array($i + 1, $i + 1);
}
// ---- Orang tua & siswa
$boys = array('Ahmad Fauzi', 'Rizky Ramadhan', 'Muhammad Iqbal', 'Bagus Setiawan', 'Fajar Nugroho', 'Dimas Pratama', 'Yusuf Hidayat', 'Arif Budiman', 'Rafi Maulana', 'Zaki Mubarok', 'Hendra Wijaya', 'Taufik Hidayatullah', 'Farhan Alfarizi', 'Galih Saputra', 'Eko Prabowo');
$girls = array('Siti Aminah', 'Nur Aisyah', 'Dewi Sartika', 'Putri Amelia', 'Lestari Ayu', 'Salsabila Putri', 'Anisa Rahmawati', 'Fitri Handayani', 'Indah Permata', 'Nabila Zahra', 'Mega Safitri', 'Citra Kirana', 'Wulan Sari', 'Rina Marlina', 'Tiara Andini');
$fathers = array('Slamet Riyadi', 'Joko Susilo', 'Hasan Basri', 'Wahyu Hidayat', 'Teguh Santoso', 'Rudi Hartono', 'Anwar Sadat', 'Heru Cahyono', 'Dedi Kurniawan', 'Bambang Irawan');
$mothers = array('Sri Wahyuni', 'Endang Lestari', 'Nurhayati', 'Suharti', 'Yuliani', 'Ratna Dewi', 'Fatimah', 'Maryati', 'Kartini', 'Sumiati');
$students = array(); // id => [nis,name,gender,class_id]
for ($i = 0; $i < 30; $i++) {
    $cls = intdiv($i, 10) + 1;
    $isGirl = ($i % 2 === 1);
    $nm = $isGirl ? $girls[intdiv($i, 2)] : $boys[intdiv($i, 2)];
    $students[$i + 1] = array('DM' . sprintf('%04d', 2601 + $i), $nm, $isGirl ? 'P' : 'L', $cls);
}
$sr = array(); $qr = array();
foreach ($students as $id => $s) {
    $sr[] = array($id, $s[0], $s[1], $s[2], $s[3], 1, $now, $now);
    $qr[] = array($id, $id, md5('dummy-qr-' . $id), 1, 'Data dummy', $now);
}
// 29 orang tua: siswa 1 & 21 bersaudara (orang tua 1)
$parentOf = array(); $pr = array(); $sp = array(); $pu = array(); $pid = 0;
foreach ($students as $id => $s) {
    if ($id === 21) { $parentOf[$id] = $parentOf[1]; $sp[] = array($id, $parentOf[1], 'Ayah', 1); continue; }
    $pid++; $parentOf[$id] = $pid;
    $f = $fathers[($pid - 1) % 10]; $m = $mothers[($pid - 1) % 10];
    $uid = NULL;
    if ($pid <= 10) { $uid = 201 + $pid - 1; $pu[] = array($uid, 3, 'Orang tua ' . $s[1], 'ortudummy' . sprintf('%02d', $pid), $ortuPw, 1, 0, $now, $now); }
    $pr[] = array($pid, $uid, 'Bpk/Ibu ' . explode(' ', $s[1])[count(explode(' ', $s[1])) - 1], $f, $m, 'Ayah', NULL, $now, $now);
    $sp[] = array($id, $pid, 'Ayah', 1);
}
ins('users', array('id', 'role_id', 'name', 'username', 'password', 'is_active', 'must_change_password', 'created_at', 'updated_at'), array_merge($ur, $pu));
ins('teachers', array('id', 'user_id', 'name', 'nip', 'email', 'is_active', 'created_at', 'updated_at'), $tr);
ins('teacher_subjects', array('teacher_id', 'subject_id'), $ts);
$cr = array(array(1, '@yr', 'DM VII A', 'VII', 1, $now, $now), array(2, '@yr', 'DM VII B', 'VII', 2, $now, $now), array(3, '@yr', 'DM VIII A', 'VIII', 3, $now, $now));
$out[] = "INSERT INTO classes (id, academic_year_id, name, level, homeroom_teacher_id, created_at, updated_at) VALUES\n" . implode(",\n", array_map(function ($r) { return '(' . $r[0] . ', @yr, ' . q($r[2]) . ', ' . q($r[3]) . ', ' . $r[4] . ', ' . q($r[5]) . ', ' . q($r[6]) . ')'; }, $cr)) . ';';
$out[] = '';
ins('parents', array('id', 'user_id', 'name', 'father_name', 'mother_name', 'relation', 'whatsapp', 'created_at', 'updated_at'), $pr);
ins('students', array('id', 'nis', 'name', 'gender', 'class_id', 'is_active', 'created_at', 'updated_at'), $sr);
ins('student_parents', array('student_id', 'parent_id', 'relation', 'is_primary'), $sp);
ins('student_qr_codes', array('id', 'student_id', 'token', 'is_active', 'reason', 'created_at'), $qr);

// ---- Jadwal
$slots = array(array('07:00:00', '08:20:00'), array('08:20:00', '09:40:00'), array('10:00:00', '11:20:00'), array('11:20:00', '12:40:00'));
$sched = array(); $schedRows = array(); $sid = 0; $byDay = array();
for ($c = 0; $c < 3; $c++) for ($d = 1; $d <= 5; $d++) for ($s = 0; $s < 4; $s++) {
    $sid++; $subjIdx = ($s + 2 * $c + ($d - 1)) % 6;
    $sched[$sid] = array('class' => $c + 1, 'day' => $d, 'subject' => $subjIdx + 1, 'teacher' => $subjIdx + 1, 'start' => $slots[$s][0], 'end' => $slots[$s][1]);
    $byDay[$d][] = $sid;
    $schedRows[] = '(' . implode(', ', array($sid, '@sem', $d, $c + 1, $subjIdx + 1, $subjIdx + 1, q($slots[$s][0]), q($slots[$s][1]), q('R-' . (101 + $c)), q($now), q($now))) . ')';
}
$out[] = "INSERT INTO schedules (id, semester_id, day, class_id, subject_id, teacher_id, start_time, end_time, room, created_at, updated_at) VALUES\n" . implode(",\n", $schedRows) . ';';
$out[] = '';

// ---- Riwayat absensi: 15 hari sekolah terakhir sebelum hari ini
$days = array(); $d = clone $TODAY;
while (count($days) < 15) { $d->modify('-1 day'); if ((int)$d->format('N') <= 5) $days[] = clone $d; }
$days = array_reverse($days);
// izin/sakit terencana: [siswa, indeks hari (0=terlama), jenis, panjang hari, id pengajuan]
$planned = array(array(3, 2, 'sakit', 2), array(8, 5, 'izin', 1), array(14, 9, 'sakit', 1), array(22, 11, 'izin', 2), array(27, 4, 'sakit', 3));
$leaveDay = array(); // "siswa|idx" => jenis
foreach ($planned as $p) for ($k = 0; $k < $p[3]; $k++) { if ($p[1] + $k < 15) $leaveDay[$p[0] . '|' . ($p[1] + $k)] = $p[2]; }
$sesRows = array(); $recRows = array(); $sesId = 0; $recId = 0;
foreach ($days as $di => $date) {
    $dow = (int)$date->format('N'); $ds = $date->format('Y-m-d');
    foreach ($byDay[$dow] as $scid) {
        $sc = $sched[$scid]; $sesId++;
        $startTs = strtotime($ds . ' ' . $sc['start']);
        $sesRows[] = '(' . implode(', ', array($sesId, $scid, '@sem', $sc['class'], $sc['subject'], $sc['teacher'], q($ds), q($sc['start']), q($sc['end']), q(date('H:i:s', $startTs + 900)), 10, q('closed'), 101 + $sc['teacher'] - 1,
            q(date('Y-m-d H:i:s', $startTs - 120)), q(date('Y-m-d H:i:s', strtotime($ds . ' ' . $sc['end']))), q($now), q($now))) . ')';
        foreach ($students as $stId => $st) {
            if ($st[3] !== $sc['class']) continue;
            $recId++; $key = $stId . '|' . $di;
            $status = 'hadir'; $scanned = NULL; $source = 'scan'; $note = NULL;
            if (isset($leaveDay[$key])) { $status = $leaveDay[$key]; $source = 'manual'; $note = ($status === 'sakit' ? 'Sakit' : 'Izin') . ' (disetujui)'; }
            else {
                $r = h($stId, $di, 'day');            // keputusan per siswa per hari
                $r2 = h($stId, $di, $scid);           // variasi per sesi
                if ($r < 3) { $status = 'alpa'; $source = 'manual'; $note = 'Tanpa keterangan'; }
                elseif ($r2 < 9) { $status = 'terlambat'; $scanned = date('Y-m-d H:i:s', $startTs + 960 + ($r2 * 60)); }
                else { $scanned = date('Y-m-d H:i:s', $startTs - 300 + ($r2 % 9) * 60); }
            }
            $recRows[] = '(' . implode(', ', array($recId, $sesId, $stId, $stId, q($status), q($scanned), q($source), q($note), q($now), q($now))) . ')';
        }
    }
}
// sesi
$chunks = array_chunk($sesRows, 100);
foreach ($chunks as $c) { $out[] = "INSERT INTO attendance_sessions (id, schedule_id, semester_id, class_id, subject_id, teacher_id, session_date, start_time, end_time, late_limit, total_students, status, started_by, started_at, ended_at, created_at, updated_at) VALUES\n" . implode(",\n", $c) . ';'; }
$out[] = '';
foreach (array_chunk($recRows, 200) as $c) { $out[] = "INSERT INTO attendance_records (id, session_id, student_id, qr_id, status, scanned_at, source, note, created_at, updated_at) VALUES\n" . implode(",\n", $c) . ';'; }
$out[] = '';

// ---- Pengajuan izin orang tua (yang disetujui sesuai riwayat di atas + contoh menunggu/ditolak)
$lr = array(); $lid = 0;
foreach ($planned as $p) {
    $lid++; $from = $days[$p[1]]->format('Y-m-d'); $to = $days[min(14, $p[1] + $p[3] - 1)]->format('Y-m-d');
    $lr[] = array($lid, $p[0], $parentOf[$p[0]], $p[2], $from, $to, $p[2] === 'sakit' ? 'Demam dan flu, dokter menyarankan istirahat.' : 'Ada keperluan keluarga.', 'disetujui', 'Disetujui. Semoga lekas sembuh.', $now, $now);
}
$t1 = (clone $TODAY)->modify('+1 day')->format('Y-m-d'); $t3 = (clone $TODAY)->modify('+3 day')->format('Y-m-d');
$lid++; $lr[] = array($lid, 1, $parentOf[1], 'izin', $t1, $t1, 'Menghadiri acara keluarga di luar kota.', 'menunggu', NULL, $now, $now);
$lid++; $lr[] = array($lid, 5, $parentOf[5], 'sakit', $TODAY->format('Y-m-d'), $t1, 'Sakit demam berdarah ringan, rawat jalan.', 'menunggu', NULL, $now, $now);
$lid++; $lr[] = array($lid, 12, $parentOf[12], 'izin', $t3, $t3, 'Liburan keluarga.', 'ditolak', 'Bukan hari libur sekolah. Mohon hadir.', $now, $now);
ins('leave_requests', array('id', 'student_id', 'parent_id', 'leave_type', 'date_from', 'date_to', 'reason', 'status', 'review_note', 'created_at', 'updated_at'), $lr);

// ---- Pengumuman
$P = function ($n) use ($TODAY) { return (clone $TODAY)->modify(($n >= 0 ? '+' : '') . $n . ' day')->format('Y-m-d'); };
$ann = array(
    array(1, 'Libur Peringatan Hari Besar Nasional', 'libur', "Diberitahukan kepada seluruh orang tua/wali bahwa kegiatan belajar mengajar diliburkan pada tanggal tersebut. Pembelajaran kembali normal pada hari sekolah berikutnya.\n\n$tag", NULL, $P(10), NULL, NULL, 0, 1, $P(-1)),
    array(2, 'Jadwal Penilaian Akhir Semester', 'ujian', "Penilaian Akhir Semester dilaksanakan mulai tanggal yang tertera. Siswa wajib hadir 15 menit sebelum ujian dimulai dan membawa kartu pelajar. Jadwal rinci per mata pelajaran dibagikan melalui wali kelas.\n\n$tag", NULL, $P(21), '07.00 - 12.00', 'Ruang kelas masing-masing', 0, 1, $P(-2)),
    array(3, 'Rapat Wali Murid Semester Ganjil', 'rapat', "Mengundang Bapak/Ibu wali murid untuk menghadiri rapat pembagian laporan perkembangan dan pembahasan program semester genap. Mohon konfirmasi kehadiran melalui portal.\n\n$tag", NULL, $P(7), '08.00 - 10.00', 'Aula Sekolah', 1, 1, $P(-3)),
    array(4, 'Perubahan Jadwal Pelajaran Hari Jumat', 'jadwal', "Khusus kelas VII, pelajaran Jumat dimulai pukul 07.30 karena ada kegiatan Jumat Bersih. Jadwal kembali normal pekan berikutnya.\n\n$tag", 1, $P(4), NULL, NULL, 0, 0, $P(-1)),
    array(5, 'Pekan Olahraga dan Seni Sekolah', 'kegiatan', "Sekolah mengadakan pekan olahraga dan seni. Siswa mengenakan seragam olahraga. Orang tua dipersilakan menyaksikan pada hari penutupan.\n\n$tag", NULL, $P(14), '07.30 - selesai', 'Lapangan Sekolah', 0, 0, $P(-5)),
    array(6, 'Pengumuman Umum: Pembaruan Portal Orang Tua', 'umum', "Orang tua kini dapat memantau kehadiran, jadwal, izin, dan pembayaran anak melalui portal. Segera ganti password awal pada login pertama.\n\n$tag", NULL, NULL, NULL, NULL, 0, 0, $P(0)),
);
$ar = array(); foreach ($ann as $a) { $ar[] = array($a[0], $a[1], $a[2], $a[3], $a[4], $a[5], $a[6], $a[7], $a[8], $a[9], $a[10], 1, $now, $now); }
ins('announcements', array('id', 'title', 'category', 'body', 'class_id', 'event_date', 'event_time', 'location', 'need_rsvp', 'is_pinned', 'publish_date', 'is_active', 'created_at', 'updated_at'), $ar);
$rs = array(); for ($p = 1; $p <= 6; $p++) { $rs[] = array(3, $p, $p === 4 ? 'tidak' : 'hadir', $p === 4 ? 'Ada tugas luar kota' : NULL, $now); }
ins('announcement_rsvps', array('announcement_id', 'parent_id', 'response', 'note', 'updated_at'), $rs);

// ---- Kalender akademik
$ev = array(
    array(1, 'Ujian Tengah Semester', 'ujian', $P(-20), $P(-15), "Selesai. $tag"),
    array(2, 'Libur Hari Besar Nasional', 'libur', $P(10), $P(10), "Sekolah libur. $tag"),
    array(3, 'Rapat Wali Murid', 'rapat', $P(7), $P(7), "Pukul 08.00 di aula. $tag"),
    array(4, 'Pekan Olahraga dan Seni', 'kegiatan', $P(14), $P(16), $tag),
    array(5, 'Penilaian Akhir Semester', 'ujian', $P(21), $P(26), $tag),
    array(6, 'Pembagian Laporan Hasil Belajar', 'kegiatan', $P(33), $P(33), $tag),
    array(7, 'Libur Akhir Semester', 'libur', $P(34), $P(48), "Libur panjang semester. $tag"),
);
$er = array(); foreach ($ev as $e) { $er[] = array($e[0], $e[1], $e[2], $e[3], $e[4], $e[5], $now, $now); }
ins('academic_events', array('id', 'title', 'event_type', 'start_date', 'end_date', 'description', 'created_at', 'updated_at'), $er);

// ---- Ekstrakurikuler
$ek = array(
    array(1, 'Pramuka', "Kegiatan kepramukaan: baris-berbaris, tali-temali, dan perkemahan. $tag", 'Kak Hendro', 5, '15:00:00', '16:30:00', 'Lapangan', 40),
    array(2, 'Futsal', "Latihan futsal dan persiapan turnamen antar sekolah. $tag", 'Pak Dimas', 3, '15:00:00', '16:30:00', 'Lapangan Futsal', 20),
    array(3, 'Paduan Suara', "Latihan vokal dan harmonisasi untuk acara sekolah. $tag", 'Bu Laras', 2, '14:30:00', '16:00:00', 'Ruang Musik', 25),
    array(4, 'Robotik', "Dasar elektronika dan pemrograman robot sederhana. $tag", 'Pak Yoga', 4, '15:00:00', '17:00:00', 'Lab Komputer', 15),
    array(5, 'Tahfiz Quran', "Setoran dan murajaah hafalan bersama pembina. $tag", 'Ustadz Malik', 1, '15:00:00', '16:00:00', 'Musala', NULL),
);
$xr = array(); foreach ($ek as $x) { $xr[] = array($x[0], $x[1], $x[2], $x[3], $x[4], $x[5], $x[6], $x[7], $x[8], 1, $now, $now); }
ins('extracurriculars', array('id', 'name', 'description', 'coach', 'day', 'start_time', 'end_time', 'location', 'quota', 'is_active', 'created_at', 'updated_at'), $xr);
$mr = array(); $mid = 0; $seen = array();
for ($s = 1; $s <= 30; $s++) {
    $x1 = (h($s, 'ek1') % 5) + 1; $x2 = (h($s, 'ek2') % 5) + 1;
    foreach (array_unique(array($x1, ($s % 3 === 0 ? $x2 : $x1))) as $x) { $mid++; $mr[] = array($mid, $x, $s, ($s === 7 || $s === 16) ? 'menunggu' : 'aktif', ($s === 7 || $s === 16) ? 1 : 0, $now, $now); }
}
ins('extracurricular_members', array('id', 'extracurricular_id', 'student_id', 'status', 'requested_by_parent', 'created_at', 'updated_at'), $mr);

// ---- Iuran / SPP / Infaq
$ft = array(array(1, 'SPP Bulanan', 'spp', 'bulanan', 150000, "SPP per bulan. $tag"), array(2, 'Iuran Kegiatan', 'iuran', 'sekali', 50000, "Iuran kegiatan per semester. $tag"), array(3, 'Infaq Jumat', 'infaq', 'bebas', 0, "Infaq sukarela. $tag"));
$fr = array(); foreach ($ft as $f) { $fr[] = array($f[0], $f[1], $f[2], $f[3], $f[4], $f[5], 1, $now, $now); }
ins('fee_types', array('id', 'name', 'category', 'frequency', 'amount', 'description', 'is_active', 'created_at', 'updated_at'), $fr);
$inv = array(); $pay = array(); $iid = 0; $payId = 0;
$addPay = function ($invoiceId, $amount, $method, $paidAt, $status, $proof, $note, $byParent) use (&$pay, &$payId, $now) {
    $payId++;
    $rc = $status === 'terverifikasi' ? 'KW' . substr(str_replace('-', '', $paidAt), 0, 6) . sprintf('%06d', $payId) : NULL;
    $pay[] = array($payId, $invoiceId, $amount, $method, $paidAt . ' 09:00:00', $proof, $note, $status, $rc, $byParent, $status === 'menunggu' ? NULL : $now, $now, $now);
};
$months = array(-2, -1, 0);
foreach ($students as $stId => $st) {
    foreach ($months as $mo) {
        $first = (clone $TODAY)->modify('first day of this month')->modify(($mo >= 0 ? '+' : '') . $mo . ' month');
        $period = $first->format('Y-m'); $due = $first->format('Y-m') . '-10';
        $iid++; $r = h($stId, $period, 'spp'); $amount = 150000; $paidTotal = 0; $status = 'belum';
        if ($mo === 0 && in_array($stId, array(6, 13), true)) { $paidTotal = 100000; $status = 'sebagian'; }
        elseif ($mo < 0 && $r < 85) { $paidTotal = $amount; $status = 'lunas'; }
        elseif ($mo === 0 && $r < 40) { $paidTotal = $amount; $status = 'lunas'; }
        elseif ($mo === 0 && $r >= 40 && $r < 50) { $paidTotal = 100000; $status = 'sebagian'; }
        $inv[] = array($iid, $stId, 1, $period, 'SPP ' . bulan($first), $amount, $paidTotal, $due, $status, $now, $now);
        if ($paidTotal > 0) { $paidAt = $first->format('Y-m') . '-0' . (1 + $r % 8); $addPay($iid, $paidTotal, $r % 3 === 0 ? 'tunai' : 'transfer', $paidAt, 'terverifikasi', NULL, NULL, 0); }
        if ($mo === 0 && in_array($stId, array(2, 9, 17), true) && $status === 'belum') { $addPay($iid, 150000, 'transfer', $TODAY->format('Y-m-d'), 'menunggu', NULL, 'Transfer via mobile banking (contoh, tanpa berkas bukti)', 1); }
    }
    $iid++; $r = h($stId, 'kegiatan'); $paid = $r < 50 ? 50000 : 0;
    $inv[] = array($iid, $stId, 2, 'Semester Ganjil', 'Iuran Kegiatan - Semester Ganjil', 50000, $paid, $P(14), $paid ? 'lunas' : 'belum', $now, $now);
    if ($paid) $addPay($iid, 50000, 'tunai', $P(-4), 'terverifikasi', NULL, NULL, 0);
}
foreach (array(4, 11, 25) as $k => $stId) { $iid++; $amt = array(20000, 50000, 100000)[$k]; $inv[] = array($iid, $stId, 3, 'INF-' . $TODAY->format('Ymd') . '-' . ($k + 1), 'Infaq Jumat', $amt, $amt, NULL, 'lunas', $now, $now); $addPay($iid, $amt, 'transfer', $P(-6 - $k), 'terverifikasi', NULL, NULL, 1); }
function bulan($d) { $b = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'); return $b[(int)$d->format('n')] . ' ' . $d->format('Y'); }
ins('invoices', array('id', 'student_id', 'fee_type_id', 'period', 'title', 'amount', 'paid_total', 'due_date', 'status', 'created_at', 'updated_at'), $inv);
ins('payments', array('id', 'invoice_id', 'amount', 'method', 'paid_at', 'proof', 'note', 'status', 'receipt_no', 'submitted_by_parent', 'verified_at', 'created_at', 'updated_at'), $pay);

// ---- Rekening contoh (obviously fake)
$out[] = "INSERT INTO school_settings (skey, svalue, updated_at) VALUES ('pay_bank','Bank Contoh (DUMMY)','$now'),('pay_account','0000-000-000','$now'),('pay_holder','Sekolah Contoh (DUMMY)','$now'),('pay_note','Rekening contoh untuk uji coba. Ganti di Pengaturan > Pembayaran.','$now')
 ON DUPLICATE KEY UPDATE svalue=VALUES(svalue);";
$out[] = '';
$out[] = "COMMIT;";
$out[] = "SET FOREIGN_KEY_CHECKS=1;";
echo implode("\n", $out) . "\n";
