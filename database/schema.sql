-- Velano School - skema database multi sekolah (MySQL 5.7+/8, utf8mb4)
SET FOREIGN_KEY_CHECKS=0;

-- Tabel platform (multi sekolah)
CREATE TABLE IF NOT EXISTS schools (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  address VARCHAR(255) NULL,
  phone VARCHAR(30) NULL,
  email VARCHAR(120) NULL,
  logo VARCHAR(255) NULL,
  student_quota INT UNSIGNED NOT NULL DEFAULT 0,
  price_per_student INT UNSIGNED NOT NULL DEFAULT 0,
  notes VARCHAR(500) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  deactivated_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS super_admins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  failed_logins TINYINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  last_login DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_settings (
  skey VARCHAR(60) NOT NULL PRIMARY KEY,
  svalue MEDIUMTEXT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS roles (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  name VARCHAR(30) NOT NULL UNIQUE,
  label VARCHAR(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  role_id TINYINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  failed_logins TINYINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  last_login DATETIME NULL,
  must_change_password TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
  KEY idx_users_school (school_id),
  CONSTRAINT fk_users_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS teachers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  nip VARCHAR(30) NULL,
  phone VARCHAR(20) NULL,
  email VARCHAR(120) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_teachers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  KEY idx_teachers_school (school_id),
  CONSTRAINT fk_teachers_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS parents (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  father_name VARCHAR(120) NULL,
  mother_name VARCHAR(120) NULL,
  guardian_name VARCHAR(120) NULL,
  relation VARCHAR(30) NULL,
  whatsapp VARCHAR(20) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_parents_wa (whatsapp),
  CONSTRAINT fk_parents_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  KEY idx_parents_school (school_id),
  CONSTRAINT fk_parents_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS academic_years (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  name VARCHAR(20) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_years_name (school_id, name),
  KEY idx_academic_years_school (school_id),
  CONSTRAINT fk_academic_years_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS semesters (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  academic_year_id INT UNSIGNED NOT NULL,
  name VARCHAR(20) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_semester (academic_year_id, name),
  CONSTRAINT fk_sem_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
  KEY idx_semesters_school (school_id),
  CONSTRAINT fk_semesters_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS classes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  academic_year_id INT UNSIGNED NULL,
  name VARCHAR(40) NOT NULL,
  level VARCHAR(10) NULL,
  homeroom_teacher_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_class (academic_year_id, name),
  CONSTRAINT fk_class_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE SET NULL,
  CONSTRAINT fk_class_teacher FOREIGN KEY (homeroom_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
  KEY idx_classes_school (school_id),
  CONSTRAINT fk_classes_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS students (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  nis VARCHAR(30) NOT NULL,
  nisn VARCHAR(30) NULL,
  name VARCHAR(120) NOT NULL,
  gender ENUM('L','P') NOT NULL DEFAULT 'L',
  class_id INT UNSIGNED NULL,
  photo VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  deleted_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_students_class (class_id),
  KEY idx_students_name (name),
  CONSTRAINT fk_students_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
  UNIQUE KEY uq_students_nis (school_id, nis),
  UNIQUE KEY uq_students_nisn (school_id, nisn),
  KEY idx_students_school (school_id),
  CONSTRAINT fk_students_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_parents (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  parent_id INT UNSIGNED NOT NULL,
  relation VARCHAR(30) NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_sp (student_id, parent_id),
  CONSTRAINT fk_sp_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_sp_parent FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subjects (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  code VARCHAR(20) NOT NULL,
  name VARCHAR(100) NOT NULL,
  grp VARCHAR(60) NULL,
  hours TINYINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_subjects_code (school_id, code),
  KEY idx_subjects_school (school_id),
  CONSTRAINT fk_subjects_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS teacher_subjects (
  teacher_id INT UNSIGNED NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (teacher_id, subject_id),
  CONSTRAINT fk_ts_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
  CONSTRAINT fk_ts_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS schedules (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  semester_id INT UNSIGNED NOT NULL,
  day TINYINT UNSIGNED NOT NULL,
  class_id INT UNSIGNED NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  teacher_id INT UNSIGNED NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  room VARCHAR(30) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_sched_sem_day (semester_id, day),
  KEY idx_sched_teacher (teacher_id, day),
  KEY idx_sched_class (class_id, day),
  CONSTRAINT fk_sched_sem FOREIGN KEY (semester_id) REFERENCES semesters(id),
  CONSTRAINT fk_sched_class FOREIGN KEY (class_id) REFERENCES classes(id),
  CONSTRAINT fk_sched_subject FOREIGN KEY (subject_id) REFERENCES subjects(id),
  CONSTRAINT fk_sched_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id),
  KEY idx_schedules_school (school_id),
  CONSTRAINT fk_schedules_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_qr_codes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  token CHAR(32) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  reason VARCHAR(255) NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deactivated_at DATETIME NULL,
  KEY idx_qr_student (student_id, is_active),
  CONSTRAINT fk_qr_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attendance_sessions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  schedule_id INT UNSIGNED NULL,
  semester_id INT UNSIGNED NULL,
  class_id INT UNSIGNED NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  teacher_id INT UNSIGNED NOT NULL,
  session_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  late_limit TIME NOT NULL,
  total_students SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  started_by INT UNSIGNED NULL,
  started_at DATETIME NOT NULL,
  ended_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_session (schedule_id, session_date),
  KEY idx_ses_date (session_date),
  KEY idx_ses_teacher (teacher_id, session_date),
  KEY idx_ses_class (class_id, session_date),
  CONSTRAINT fk_ses_sched FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE SET NULL,
  CONSTRAINT fk_ses_sem FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL,
  CONSTRAINT fk_ses_class FOREIGN KEY (class_id) REFERENCES classes(id),
  CONSTRAINT fk_ses_subject FOREIGN KEY (subject_id) REFERENCES subjects(id),
  CONSTRAINT fk_ses_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id),
  KEY idx_attendance_sessions_school (school_id),
  CONSTRAINT fk_attendance_sessions_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attendance_records (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  session_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  qr_id INT UNSIGNED NULL,
  status ENUM('hadir','terlambat','sakit','izin','alpa','belum') NOT NULL DEFAULT 'belum',
  scanned_at DATETIME NULL,
  client_time DATETIME NULL,
  source ENUM('scan','offline_sync','manual','system') NOT NULL DEFAULT 'scan',
  client_uuid VARCHAR(64) NULL,
  note VARCHAR(255) NULL,
  corrected_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_rec_student (session_id, student_id),
  UNIQUE KEY uq_rec_uuid (session_id, client_uuid),
  KEY idx_rec_student (student_id),
  KEY idx_rec_status (status),
  CONSTRAINT fk_rec_session FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
  CONSTRAINT fk_rec_student FOREIGN KEY (student_id) REFERENCES students(id),
  CONSTRAINT fk_rec_qr FOREIGN KEY (qr_id) REFERENCES student_qr_codes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS whatsapp_notifications (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  record_id INT UNSIGNED NULL,
  session_id INT UNSIGNED NULL,
  student_id INT UNSIGNED NOT NULL,
  parent_id INT UNSIGNED NULL,
  notif_type VARCHAR(20) NOT NULL,
  notif_date DATE NOT NULL,
  phone VARCHAR(20) NULL,
  message TEXT NOT NULL,
  params TEXT NULL,
  template_name VARCHAR(100) NULL,
  status ENUM('menunggu','mengirim','terkirim','gagal') NOT NULL DEFAULT 'menunggu',
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  next_retry_at DATETIME NULL,
  wa_message_id VARCHAR(120) NULL,
  response TEXT NULL,
  sent_at DATETIME NULL,
  dedupe_key VARCHAR(100) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_wa_status (status, next_retry_at),
  KEY idx_wa_student_date (student_id, notif_date),
  KEY idx_wa_session (session_id),
  KEY idx_whatsapp_notifications_school (school_id),
  CONSTRAINT fk_whatsapp_notifications_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS school_settings (
  school_id INT UNSIGNED NOT NULL,
  skey VARCHAR(60) NOT NULL,
  svalue MEDIUMTEXT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (school_id, skey),
  CONSTRAINT fk_school_settings_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NULL,
  user_id INT UNSIGNED NULL,
  action VARCHAR(60) NOT NULL,
  detail TEXT NULL,
  ip VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_audit_created (created_at),
  KEY idx_audit_user (user_id),
  KEY idx_audit_logs_school (school_id),
  CONSTRAINT fk_audit_logs_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS scan_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  session_id INT UNSIGNED NULL,
  user_id INT UNSIGNED NULL,
  code_prefix VARCHAR(12) NULL,
  result VARCHAR(20) NOT NULL,
  message VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_scanlog_session (session_id, created_at),
  KEY idx_scanlog_created (created_at),
  KEY idx_scan_logs_school (school_id),
  CONSTRAINT fk_scan_logs_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS announcements (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  category ENUM('umum','libur','jadwal','kegiatan','ujian','rapat') NOT NULL DEFAULT 'umum',
  body TEXT NOT NULL,
  class_id INT UNSIGNED NULL,
  event_date DATE NULL,
  event_time VARCHAR(20) NULL,
  location VARCHAR(120) NULL,
  need_rsvp TINYINT(1) NOT NULL DEFAULT 0,
  is_pinned TINYINT(1) NOT NULL DEFAULT 0,
  publish_date DATE NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_ann_pub (is_active, publish_date),
  CONSTRAINT fk_ann_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
  KEY idx_announcements_school (school_id),
  CONSTRAINT fk_announcements_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS announcement_rsvps (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  announcement_id INT UNSIGNED NOT NULL,
  parent_id INT UNSIGNED NOT NULL,
  response ENUM('hadir','tidak') NOT NULL,
  note VARCHAR(255) NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_rsvp (announcement_id, parent_id),
  CONSTRAINT fk_rsvp_ann FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE,
  CONSTRAINT fk_rsvp_parent FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS academic_events (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  event_type ENUM('libur','ujian','kegiatan','rapat','lainnya') NOT NULL DEFAULT 'kegiatan',
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  description VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_evt_date (start_date, end_date),
  KEY idx_academic_events_school (school_id),
  CONSTRAINT fk_academic_events_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leave_requests (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  parent_id INT UNSIGNED NOT NULL,
  leave_type ENUM('izin','sakit') NOT NULL,
  date_from DATE NOT NULL,
  date_to DATE NOT NULL,
  reason VARCHAR(500) NOT NULL,
  attachment VARCHAR(255) NULL,
  status ENUM('menunggu','disetujui','ditolak','dibatalkan') NOT NULL DEFAULT 'menunggu',
  review_note VARCHAR(255) NULL,
  reviewed_by INT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_leave_status (status, date_from),
  KEY idx_leave_student (student_id, date_from),
  CONSTRAINT fk_leave_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_leave_parent FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE,
  KEY idx_leave_requests_school (school_id),
  CONSTRAINT fk_leave_requests_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fee_types (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  category ENUM('spp','iuran','infaq','lainnya') NOT NULL DEFAULT 'iuran',
  frequency ENUM('bulanan','sekali','bebas') NOT NULL DEFAULT 'bulanan',
  amount INT UNSIGNED NOT NULL DEFAULT 0,
  description VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_fee_types_school (school_id),
  CONSTRAINT fk_fee_types_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoices (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  fee_type_id INT UNSIGNED NOT NULL,
  period VARCHAR(30) NOT NULL,
  title VARCHAR(160) NOT NULL,
  amount INT UNSIGNED NOT NULL,
  paid_total INT UNSIGNED NOT NULL DEFAULT 0,
  due_date DATE NULL,
  status ENUM('belum','sebagian','lunas','batal') NOT NULL DEFAULT 'belum',
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_invoice (student_id, fee_type_id, period),
  KEY idx_inv_status (status, due_date),
  CONSTRAINT fk_inv_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_inv_fee FOREIGN KEY (fee_type_id) REFERENCES fee_types(id),
  KEY idx_invoices_school (school_id),
  CONSTRAINT fk_invoices_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT UNSIGNED NOT NULL,
  amount INT UNSIGNED NOT NULL,
  method ENUM('tunai','transfer','lainnya') NOT NULL DEFAULT 'transfer',
  paid_at DATETIME NOT NULL,
  proof VARCHAR(255) NULL,
  note VARCHAR(255) NULL,
  status ENUM('menunggu','terverifikasi','ditolak') NOT NULL DEFAULT 'menunggu',
  receipt_no VARCHAR(30) NULL UNIQUE,
  submitted_by_parent TINYINT(1) NOT NULL DEFAULT 0,
  verified_by INT UNSIGNED NULL,
  verified_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_pay_invoice (invoice_id),
  KEY idx_pay_status (status),
  CONSTRAINT fk_pay_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS extracurriculars (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT NULL,
  coach VARCHAR(120) NULL,
  day TINYINT UNSIGNED NULL,
  start_time TIME NULL,
  end_time TIME NULL,
  location VARCHAR(100) NULL,
  quota SMALLINT UNSIGNED NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_extracurriculars_school (school_id),
  CONSTRAINT fk_extracurriculars_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS extracurricular_members (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  extracurricular_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  status ENUM('menunggu','aktif','ditolak','keluar') NOT NULL DEFAULT 'menunggu',
  requested_by_parent TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ekskul_member (extracurricular_id, student_id),
  CONSTRAINT fk_em_ekskul FOREIGN KEY (extracurricular_id) REFERENCES extracurriculars(id) ON DELETE CASCADE,
  CONSTRAINT fk_em_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
