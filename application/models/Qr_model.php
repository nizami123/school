<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Qr_model extends CI_Model
{
    /** Buat token QR baru; QR aktif lama otomatis dinonaktifkan. */
    public function generate($student_id, $reason = '', $by = NULL)
    {
        $now = date('Y-m-d H:i:s');
        if (!$this->db->where(array('id' => (int)$student_id, 'school_id' => school_id()))->count_all_results('students')) return NULL;   // bukan siswa sekolah ini
        $this->db->where(array('student_id' => $student_id, 'is_active' => 1))
                 ->update('student_qr_codes', array('is_active' => 0, 'deactivated_at' => $now));
        do {
            $token = bin2hex(random_bytes(16));
            $exists = $this->db->where('token', $token)->count_all_results('student_qr_codes');
        } while ($exists);
        $this->db->insert('student_qr_codes', array('student_id' => $student_id, 'token' => $token, 'is_active' => 1,
            'reason' => $reason, 'created_by' => $by, 'created_at' => $now));
        return $token;
    }

    public function active_token($student_id)
    {
        $r = $this->db->query('SELECT q.token FROM student_qr_codes q JOIN students s ON s.id=q.student_id WHERE q.student_id=? AND q.is_active=1 AND s.school_id=?', array($student_id, school_id()))->row();
        return $r ? $r->token : NULL;
    }

    /** Pastikan setiap siswa aktif punya QR aktif. */
    public function ensure_all($by = NULL)
    {
        $rows = $this->db->query("SELECT s.id FROM students s WHERE s.school_id=? AND s.deleted_at IS NULL AND NOT EXISTS (SELECT 1 FROM student_qr_codes q WHERE q.student_id=s.id AND q.is_active=1)", array(school_id()))->result();
        foreach ($rows as $r) { $this->generate($r->id, 'Dibuat otomatis', $by); }
        return count($rows);
    }
}
