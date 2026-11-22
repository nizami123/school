-- Khusus untuk database yang SUDAH menjalankan migrate_multi_sekolah.sql versi sebelumnya (tanpa mode absensi harian).
-- Jalankan SEKALI. Instalasi baru / migrasi baru TIDAK perlu file ini (sudah termasuk di schema.sql & migrate_multi_sekolah.sql).
ALTER TABLE attendance_sessions ADD COLUMN session_type ENUM('mapel','harian') NOT NULL DEFAULT 'mapel' AFTER school_id, MODIFY teacher_id INT UNSIGNED NULL;
