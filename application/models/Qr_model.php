<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Qr_model extends CI_Model
{
    /** Buat token QR baru; QR aktif lama otomatis dinonaktifkan. */
    public function generate($student_id, $reason = '', $by = NULL)
    {
        $now = date('Y-m-d H:i:s');
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
        $r = $this->db->get_where('student_qr_codes', array('student_id' => $student_id, 'is_active' => 1))->row();
        return $r ? $r->token : NULL;
    }

    /** Pastikan setiap siswa aktif punya QR aktif. */
    public function ensure_all($by = NULL)
    {
        $rows = $this->db->query("SELECT s.id FROM students s WHERE s.deleted_at IS NULL AND NOT EXISTS (SELECT 1 FROM student_qr_codes q WHERE q.student_id=s.id AND q.is_active=1)")->result();
        foreach ($rows as $r) { $this->generate($r->id, 'Dibuat otomatis', $by); }
        return count($rows);
    }
}
