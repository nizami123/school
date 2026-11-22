-- Migrasi QR School Attendance (1 sekolah) -> Velano School (multi sekolah).
-- JALANKAN SEKALI, di database yang sudah berisi data lama. BACKUP database terlebih dahulu.
-- Semua data lama menjadi milik sekolah #1 (nama/alamat/logo diambil dari pengaturan lama).
SET FOREIGN_KEY_CHECKS=0;

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

INSERT INTO schools (id, name, address, phone, email, logo, is_active, created_at, updated_at)
SELECT 1,
  IFNULL((SELECT NULLIF(svalue,'') FROM school_settings WHERE skey='school_name'), 'Sekolah'),
  (SELECT NULLIF(svalue,'') FROM school_settings WHERE skey='school_address'),
  (SELECT NULLIF(svalue,'') FROM school_settings WHERE skey='school_phone'),
  (SELECT NULLIF(svalue,'') FROM school_settings WHERE skey='school_email'),
  (SELECT NULLIF(svalue,'') FROM school_settings WHERE skey='school_logo'),
  1, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM schools WHERE id=1);

INSERT IGNORE INTO app_settings (skey, svalue, updated_at)
SELECT 'cron_key', IFNULL((SELECT NULLIF(svalue,'') FROM school_settings WHERE skey='cron_key'), MD5(CONCAT(RAND(), NOW(), UUID()))), NOW() FROM DUAL;

DELETE FROM school_settings WHERE skey IN ('cron_key', 'dummy_ids', 'school_name', 'school_address', 'school_phone', 'school_email', 'school_logo');

ALTER TABLE users ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE users SET school_id=1;
ALTER TABLE users MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE users ADD KEY idx_users_school (school_id), ADD CONSTRAINT fk_users_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE teachers ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE teachers SET school_id=1;
ALTER TABLE teachers MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE teachers ADD KEY idx_teachers_school (school_id), ADD CONSTRAINT fk_teachers_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE parents ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE parents SET school_id=1;
ALTER TABLE parents MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE parents ADD KEY idx_parents_school (school_id), ADD CONSTRAINT fk_parents_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE academic_years ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE academic_years SET school_id=1;
ALTER TABLE academic_years MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE academic_years ADD KEY idx_academic_years_school (school_id), ADD CONSTRAINT fk_academic_years_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE semesters ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE semesters SET school_id=1;
ALTER TABLE semesters MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE semesters ADD KEY idx_semesters_school (school_id), ADD CONSTRAINT fk_semesters_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE classes ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE classes SET school_id=1;
ALTER TABLE classes MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE classes ADD KEY idx_classes_school (school_id), ADD CONSTRAINT fk_classes_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE students ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE students SET school_id=1;
ALTER TABLE students MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE students ADD KEY idx_students_school (school_id), ADD CONSTRAINT fk_students_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE subjects ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE subjects SET school_id=1;
ALTER TABLE subjects MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE subjects ADD KEY idx_subjects_school (school_id), ADD CONSTRAINT fk_subjects_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE schedules ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE schedules SET school_id=1;
ALTER TABLE schedules MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE schedules ADD KEY idx_schedules_school (school_id), ADD CONSTRAINT fk_schedules_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE attendance_sessions ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE attendance_sessions SET school_id=1;
ALTER TABLE attendance_sessions MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE attendance_sessions ADD KEY idx_attendance_sessions_school (school_id), ADD CONSTRAINT fk_attendance_sessions_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE whatsapp_notifications ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE whatsapp_notifications SET school_id=1;
ALTER TABLE whatsapp_notifications MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE whatsapp_notifications ADD KEY idx_whatsapp_notifications_school (school_id), ADD CONSTRAINT fk_whatsapp_notifications_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE announcements ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE announcements SET school_id=1;
ALTER TABLE announcements MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE announcements ADD KEY idx_announcements_school (school_id), ADD CONSTRAINT fk_announcements_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE academic_events ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE academic_events SET school_id=1;
ALTER TABLE academic_events MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE academic_events ADD KEY idx_academic_events_school (school_id), ADD CONSTRAINT fk_academic_events_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE leave_requests ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE leave_requests SET school_id=1;
ALTER TABLE leave_requests MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE leave_requests ADD KEY idx_leave_requests_school (school_id), ADD CONSTRAINT fk_leave_requests_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE fee_types ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE fee_types SET school_id=1;
ALTER TABLE fee_types MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE fee_types ADD KEY idx_fee_types_school (school_id), ADD CONSTRAINT fk_fee_types_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE invoices ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE invoices SET school_id=1;
ALTER TABLE invoices MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE invoices ADD KEY idx_invoices_school (school_id), ADD CONSTRAINT fk_invoices_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE extracurriculars ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE extracurriculars SET school_id=1;
ALTER TABLE extracurriculars MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE extracurriculars ADD KEY idx_extracurriculars_school (school_id), ADD CONSTRAINT fk_extracurriculars_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE scan_logs ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE scan_logs SET school_id=1;
ALTER TABLE scan_logs MODIFY school_id INT UNSIGNED NOT NULL;
ALTER TABLE scan_logs ADD KEY idx_scan_logs_school (school_id), ADD CONSTRAINT fk_scan_logs_school FOREIGN KEY (school_id) REFERENCES schools(id);

ALTER TABLE audit_logs ADD COLUMN school_id INT UNSIGNED NULL AFTER id;
UPDATE audit_logs SET school_id=1;
ALTER TABLE audit_logs ADD KEY idx_audit_logs_school (school_id), ADD CONSTRAINT fk_audit_logs_school FOREIGN KEY (school_id) REFERENCES schools(id);

-- kunci unik per sekolah
ALTER TABLE students DROP INDEX nis, DROP INDEX nisn, ADD UNIQUE KEY uq_students_nis (school_id, nis), ADD UNIQUE KEY uq_students_nisn (school_id, nisn);
ALTER TABLE subjects DROP INDEX code, ADD UNIQUE KEY uq_subjects_code (school_id, code);
ALTER TABLE academic_years DROP INDEX name, ADD UNIQUE KEY uq_years_name (school_id, name);

-- pengaturan per sekolah
ALTER TABLE school_settings ADD COLUMN school_id INT UNSIGNED NULL FIRST;
UPDATE school_settings SET school_id=1;
ALTER TABLE school_settings MODIFY school_id INT UNSIGNED NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (school_id, skey), ADD CONSTRAINT fk_school_settings_school FOREIGN KEY (school_id) REFERENCES schools(id);

SET FOREIGN_KEY_CHECKS=1;
-- Selesai. Buat akun Super Admin pertama lewat https://DOMAIN/setup-super (butuh password database).
