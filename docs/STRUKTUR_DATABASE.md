# Struktur Database

Skema lengkap: `database/schema.sql` (MySQL, InnoDB, utf8mb4, FK + index + unique).

| Tabel | Fungsi | Kunci/Relasi penting |
|---|---|---|
| roles | admin, guru | users.role_id |
| users | akun login (hash `password_hash`) | username unik; kunci akun setelah 5 gagal login |
| teachers | profil guru | user_id → users |
| teacher_subjects | mapel yang diampu | PK (teacher_id, subject_id) |
| parents | orang tua/wali + WhatsApp (+62) | |
| students | siswa (NIS, NISN, kelas, foto, soft delete `deleted_at`) | class_id → classes |
| student_parents | relasi siswa–orang tua | unik (student_id, parent_id); `is_primary` = penerima WA |
| classes | kelas | academic_year_id, homeroom_teacher_id |
| subjects | mata pelajaran | code unik |
| academic_years / semesters | tahun ajaran & semester | semesters.academic_year_id |
| schedules | jadwal mingguan (hari, kelas, mapel, guru, jam) | indeks per semester/hari/guru/kelas |
| student_qr_codes | token QR acak 32 hex, `is_active`, riwayat penggantian | token unik; hanya 1 aktif per siswa |
| attendance_sessions | sesi absensi satu jadwal pada satu tanggal | **unik (schedule_id, session_date)**; menyimpan salinan class/subject/teacher/jam |
| attendance_records | kehadiran siswa per sesi | **unik (session_id, student_id)** & (session_id, client_uuid) → anti-ganda |
| whatsapp_notifications | antrean & log pengiriman | `dedupe_key` unik; status menunggu/mengirim/terkirim/gagal; simpan respons API |
| school_settings | pengaturan key-value (token WA terenkripsi) | |
| audit_logs | jejak aktivitas admin/guru | |
| scan_logs | log tiap percobaan scan (berhasil & gagal) | |

## Riwayat tetap utuh
`attendance_sessions` menyimpan `class_id`, `subject_id`, `teacher_id`, `semester_id`, jam & tanggal saat absensi terjadi.
Memindahkan siswa ke kelas lain, mengganti jadwal (FK `ON DELETE SET NULL`), atau berganti tahun ajaran/semester **tidak mengubah** catatan lama.
Laporan mengelompokkan berdasarkan kelas pada sesi, bukan kelas siswa saat ini. Siswa "dihapus" hanya soft delete.

## Status kehadiran
`hadir`, `terlambat` (scan setelah `late_limit` = jam mulai + toleransi), `sakit`, `izin`, `alpa`, `belum` (belum dikonfirmasi guru; **bukan** alpa otomatis).
