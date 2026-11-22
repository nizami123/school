<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** CRUD generik untuk data master (definisi di config/entities.php). Khusus Admin. */
class Master extends MY_Controller
{
    private $errors = array();

    public function __construct()
    {
        parent::__construct();
        $this->need('admin');
        $this->config->load('entities', TRUE);
    }

    private function ent($key)
    {
        $all = $this->config->item('entities', 'entities');
        if (!isset($all[$key])) show_404();
        return $all[$key];
    }

    protected function lookup($k)
    {
        switch ($k) {
            case 'classes': $q = 'SELECT id, name AS label FROM classes ORDER BY name'; break;
            case 'subjects': $q = "SELECT id, CONCAT(code,' - ',name) AS label FROM subjects ORDER BY name"; break;
            case 'teachers': $q = 'SELECT id, name AS label FROM teachers WHERE is_active=1 ORDER BY name'; break;
            case 'parents': $q = "SELECT id, CONCAT(name,' (',IFNULL(whatsapp,'-'),')') AS label FROM parents ORDER BY name"; break;
            case 'years': $q = 'SELECT id, name AS label FROM academic_years ORDER BY start_date DESC'; break;
            case 'semesters': $q = "SELECT s.id, CONCAT(y.name,' ',s.name) AS label FROM semesters s JOIN academic_years y ON y.id=s.academic_year_id ORDER BY y.start_date DESC, s.start_date DESC"; break;
            case 'days': return array(1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu');
            default: return array();
        }
        $o = array();
        foreach ($this->db->query($q)->result() as $r) { $o[$r->id] = $r->label; }
        return $o;
    }

    private function list_sql($e)
    {
        $sql = 'SELECT ' . $e['select'] . ' FROM ' . $e['from'] . ' ' . implode(' ', $e['joins']);
        if (!empty($e['where'])) $sql .= ' WHERE ' . $e['where'];
        if (!empty($e['order'])) $sql .= ' ORDER BY ' . $e['order'];
        return $sql;
    }

    public function index($key)
    {
        $e = $this->ent($key);
        $rows = $this->db->query($this->list_sql($e))->result_array();
        $this->render('master/list', array('e' => $e, 'key' => $key, 'rows' => $rows, 'title' => $e['title']));
    }

    public function form($key, $id = 0)
    {
        $e = $this->ent($key);
        $id = (int)$id;
        $row = array();
        $copy = (int)$this->input->get('copy');
        $load = $id ?: $copy;
        if ($load) {
            $row = $this->db->get_where($e['table'], array('id' => $load))->row_array();
            if (!$row) show_404();
            $row = $this->virtual_values($key, $load, $row);
            if ($copy) { unset($row['id']); }
        } else {
            $row = array('is_active' => 1);
            if (!empty($e['defaults'])) $row = array_merge($row, $e['defaults']);
            if ($key === 'schedules') { $s = $this->active_semester(); if ($s) $row['semester_id'] = $s->id; }
            if ($key === 'classes') { $y = $this->db->get_where('academic_years', array('is_active' => 1))->row(); if ($y) $row['academic_year_id'] = $y->id; }
        }
        $old = $this->session->flashdata('old');
        if (is_array($old)) $row = array_merge($row, $old);
        $opts = array();
        foreach ($e['fields'] as $f) {
            if (isset($f['options']) && is_string($f['options'])) {
                $opts[$f['name']] = $this->lookup(substr($f['options'], 7));
            }
        }
        $this->render('master/form', array('e' => $e, 'key' => $key, 'row' => $row, 'id' => $id, 'opts' => $opts,
            'title' => ($id ? 'Ubah ' : 'Tambah ') . $e['singular']));
    }

    private function virtual_values($key, $id, $row)
    {
        if ($key === 'students') {
            $p = $this->db->get_where('student_parents', array('student_id' => $id, 'is_primary' => 1))->row();
            $row['parent_id'] = $p ? $p->parent_id : '';
        } elseif ($key === 'teachers') {
            $u = $row['user_id'] ? $this->db->get_where('users', array('id' => $row['user_id']))->row() : NULL;
            $row['username'] = $u ? $u->username : '';
            $row['subject_ids'] = array();
            foreach ($this->db->get_where('teacher_subjects', array('teacher_id' => $id))->result() as $r) { $row['subject_ids'][] = $r->subject_id; }
        }
        if (isset($row['start_time'])) $row['start_time'] = substr($row['start_time'], 0, 5);
        if (isset($row['end_time'])) $row['end_time'] = substr($row['end_time'], 0, 5);
        return $row;
    }

    public function save($key)
    {
        $this->require_post();
        $e = $this->ent($key);
        $id = (int)$this->input->post('id');
        $data = array();
        $this->errors = array();
        foreach ($e['fields'] as $f) {
            $n = $f['name'];
            if (!empty($f['virtual']) || $f['type'] === 'file') continue;
            $v = $this->input->post($n);
            if ($f['type'] === 'bool') { $data[$n] = $v ? 1 : 0; continue; }
            if (is_string($v)) $v = trim($v);
            if (!empty($f['required']) && ($v === NULL || $v === '')) { $this->errors[] = $f['label'] . ' wajib diisi.'; continue; }
            if (($v === '' || $v === NULL) && !empty($f['nullable'])) $v = NULL;
            if ($f['type'] === 'number' && $v !== NULL) $v = (int)$v;
            if ($f['type'] === 'email' && $v !== NULL && !filter_var($v, FILTER_VALIDATE_EMAIL)) $this->errors[] = 'Format email tidak valid.';
            if ($f['type'] === 'select' && $v !== NULL && is_array($f['options']) && !isset($f['options'][$v])) $this->errors[] = $f['label'] . ' tidak valid.';
            if (!empty($f['unique']) && $v !== NULL) {
                $this->db->where($n, $v);
                if ($id) $this->db->where('id !=', $id);
                if ($this->db->count_all_results($e['table']) > 0) $this->errors[] = $f['label'] . ' "' . $v . '" sudah digunakan.';
            }
            $data[$n] = $v;
        }
        $h = 'before_' . $key;
        if (method_exists($this, $h)) $data = $this->$h($data, $id);

        if ($this->errors) {
            $this->session->set_flashdata('old', $this->input->post());
            $this->flash('danger', implode('<br>', array_map('e', $this->errors)));
            redirect('master/' . $key . '/form' . ($id ? '/' . $id : ''));
        }
        $now = date('Y-m-d H:i:s');
        $data['updated_at'] = $now;
        if ($id) {
            $this->db->update($e['table'], $data, array('id' => $id));
            $new = FALSE;
        } else {
            $data['created_at'] = $now;
            $this->db->insert($e['table'], $data);
            $id = $this->db->insert_id();
            $new = TRUE;
        }
        $a = 'after_' . $key;
        if (method_exists($this, $a)) $this->$a($id, $new);
        audit(($new ? 'tambah_' : 'ubah_') . $key, 'id=' . $id);
        $this->flash('success', $e['singular'] . ' berhasil disimpan.');
        redirect('master/' . $key);
    }

    public function toggle($key, $id)
    {
        $this->require_post();
        $e = $this->ent($key);
        if (empty($e['toggle'])) show_404();
        $row = $this->db->get_where($e['table'], array('id' => (int)$id))->row();
        if (!$row) show_404();
        if ($key === 'users' && (int)$id === $this->uid) { $this->flash('danger', 'Anda tidak dapat menonaktifkan akun sendiri.'); redirect('master/users'); }
        $new = $row->is_active ? 0 : 1;
        $this->db->update($e['table'], array('is_active' => $new, 'updated_at' => date('Y-m-d H:i:s')), array('id' => (int)$id));
        if ($key === 'teachers' && $row->user_id) $this->db->update('users', array('is_active' => $new), array('id' => $row->user_id));
        audit('toggle_' . $key, 'id=' . $id . ' => ' . $new);
        $this->flash('success', 'Status berhasil diubah.');
        redirect('master/' . $key);
    }

    public function delete($key, $id)
    {
        $this->require_post();
        $e = $this->ent($key);
        $id = (int)$id;
        if ($key === 'users' && $id === $this->uid) { $this->flash('danger', 'Anda tidak dapat menghapus akun sendiri.'); redirect('master/users'); }
        if (!empty($e['soft'])) {
            $now = date('Y-m-d H:i:s');
            $this->db->update($e['table'], array('deleted_at' => $now, 'is_active' => 0, 'updated_at' => $now), array('id' => $id));
            $this->db->where(array('student_id' => $id, 'is_active' => 1))->update('student_qr_codes', array('is_active' => 0, 'deactivated_at' => $now));
            audit('hapus_' . $key, 'id=' . $id);
            $this->flash('success', 'Data dihapus (riwayat absensi tetap tersimpan).');
            redirect('master/' . $key);
        }
        $user_id = NULL;
        if ($key === 'teachers') { $t = $this->db->get_where('teachers', array('id' => $id))->row(); $user_id = $t ? $t->user_id : NULL; }
        $old = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        $ok = $this->db->delete($e['table'], array('id' => $id));
        $this->db->db_debug = $old;
        if (!$ok) {
            $this->flash('danger', 'Data tidak dapat dihapus karena masih dipakai data lain (mis. jadwal/absensi). Nonaktifkan saja bila tersedia.');
        } else {
            if ($user_id) $this->db->delete('users', array('id' => $user_id));
            audit('hapus_' . $key, 'id=' . $id);
            $this->flash('success', 'Data berhasil dihapus.');
        }
        redirect('master/' . $key);
    }

    /* ---------- Hook per entitas ---------- */

    private function before_students($d, $id)
    {
        if (!empty($_FILES['photo']['name'])) {
            $this->load->library('upload', array('upload_path' => FCPATH . 'uploads/students/', 'allowed_types' => 'jpg|jpeg|png',
                'max_size' => 2048, 'encrypt_name' => TRUE));
            if ($this->upload->do_upload('photo')) {
                $d['photo'] = $this->upload->data('file_name');
            } else {
                $this->errors[] = strip_tags($this->upload->display_errors('', ''));
            }
        }
        return $d;
    }

    private function after_students($id, $new)
    {
        $pid = (int)$this->input->post('parent_id');
        $this->db->delete('student_parents', array('student_id' => $id));
        if ($pid) $this->db->insert('student_parents', array('student_id' => $id, 'parent_id' => $pid, 'is_primary' => 1));
        if ($new) {
            $this->load->model('Qr_model');
            $this->Qr_model->generate($id, 'Dibuat otomatis', $this->uid);
        }
    }

    private function before_announcements($d, $id)
    {
        if (!empty($d['class_id'])) $d['class_id'] = (int)$d['class_id'];
        foreach (array('publish_date', 'event_date') as $k) {
            if (!empty($d[$k]) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $d[$k])) $this->errors[] = 'Format tanggal tidak valid.';
        }
        if (!$id) $d['created_by'] = $this->uid;
        return $d;
    }

    private function before_academic_events($d, $id)
    {
        if (!empty($d['start_date']) && !empty($d['end_date']) && $d['end_date'] < $d['start_date']) $this->errors[] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        return $d;
    }

    private function before_extracurriculars($d, $id)
    {
        if (!empty($d['start_time']) && !empty($d['end_time']) && $d['end_time'] <= $d['start_time']) $this->errors[] = 'Jam selesai harus setelah jam mulai.';
        if (isset($d['quota']) && $d['quota'] !== NULL && $d['quota'] < 1) $d['quota'] = NULL;
        return $d;
    }

    private function before_fee_types($d, $id)
    {
        if (isset($d['amount']) && $d['amount'] < 0) $this->errors[] = 'Nominal tidak boleh negatif.';
        if (isset($d['frequency']) && $d['frequency'] !== 'bebas' && (int)$d['amount'] < 1) $this->errors[] = 'Nominal wajib diisi (lebih dari 0) kecuali jenis Bebas.';
        return $d;
    }

    private function before_classes($d, $id)
    {
        $this->db->where('name', $d['name']);
        if (empty($d['academic_year_id'])) $this->db->where('academic_year_id IS NULL', NULL, FALSE); else $this->db->where('academic_year_id', $d['academic_year_id']);
        if ($id) $this->db->where('id !=', $id);
        if ($this->db->count_all_results('classes') > 0) $this->errors[] = 'Kelas "' . $d['name'] . '" sudah ada pada tahun ajaran tersebut.';
        return $d;
    }

    private function before_parents($d, $id)
    {
        $d['whatsapp'] = normalize_phone($d['whatsapp']);
        if (!valid_phone($d['whatsapp'])) $this->errors[] = 'Nomor WhatsApp tidak valid. Gunakan format 08xx..., 628xx..., atau +628xx....';
        return $d;
    }

    private function before_teachers($d, $id)
    {
        $username = trim((string)$this->input->post('username'));
        $pw = (string)$this->input->post('password');
        if (!preg_match('/^[A-Za-z0-9_.\-]{3,60}$/', $username)) $this->errors[] = 'Username 3-60 karakter (huruf, angka, titik, strip, underscore).';
        $t = $id ? $this->db->get_where('teachers', array('id' => $id))->row() : NULL;
        $this->db->where('username', $username);
        if ($t && $t->user_id) $this->db->where('id !=', $t->user_id);
        if ($this->db->count_all_results('users') > 0) $this->errors[] = 'Username "' . $username . '" sudah digunakan.';
        if ((!$t || !$t->user_id) && strlen($pw) < 6) $this->errors[] = 'Password wajib diisi (min. 6 karakter) untuk guru baru.';
        if ($pw !== '' && strlen($pw) < 6) $this->errors[] = 'Password minimal 6 karakter.';
        if (!empty($d['phone'])) $d['phone'] = normalize_phone($d['phone']);
        return $d;
    }

    private function after_teachers($id, $new)
    {
        $username = trim((string)$this->input->post('username'));
        $pw = (string)$this->input->post('password');
        $t = $this->db->get_where('teachers', array('id' => $id))->row();
        $now = date('Y-m-d H:i:s');
        if (!$t->user_id) {
            $this->db->insert('users', array('role_id' => 2, 'name' => $t->name, 'username' => $username, 'password' => password_hash($pw, PASSWORD_DEFAULT),
                'is_active' => $t->is_active, 'created_at' => $now, 'updated_at' => $now));
            $this->db->update('teachers', array('user_id' => $this->db->insert_id()), array('id' => $id));
        } else {
            $u = array('name' => $t->name, 'username' => $username, 'is_active' => $t->is_active, 'updated_at' => $now);
            if ($pw !== '') $u['password'] = password_hash($pw, PASSWORD_DEFAULT);
            $this->db->update('users', $u, array('id' => $t->user_id));
        }
        $this->db->delete('teacher_subjects', array('teacher_id' => $id));
        $subs = $this->input->post('subject_ids');
        if (is_array($subs)) {
            foreach (array_unique($subs) as $sid) { $this->db->insert('teacher_subjects', array('teacher_id' => $id, 'subject_id' => (int)$sid)); }
        }
    }

    private function before_users($d, $id)
    {
        $pw = (string)$this->input->post('password');
        if (!$id && strlen($pw) < 8) $this->errors[] = 'Password wajib diisi (min. 8 karakter).';
        if ($pw !== '' && strlen($pw) < 8) $this->errors[] = 'Password minimal 8 karakter.';
        if ($pw !== '') $d['password'] = password_hash($pw, PASSWORD_DEFAULT);
        if (!$id) $d['role_id'] = 1;
        return $d;
    }

    private function before_academic_years($d, $id)
    {
        if (!empty($d['start_date']) && $d['start_date'] >= $d['end_date']) $this->errors[] = 'Tanggal selesai harus setelah tanggal mulai.';
        return $d;
    }

    private function after_academic_years($id, $new)
    {
        $r = $this->db->get_where('academic_years', array('id' => $id))->row();
        if ($r->is_active) $this->db->where('id !=', $id)->update('academic_years', array('is_active' => 0));
    }

    private function before_semesters($d, $id)
    {
        if (!empty($d['start_date']) && $d['start_date'] >= $d['end_date']) $this->errors[] = 'Tanggal selesai harus setelah tanggal mulai.';
        if (!empty($d['academic_year_id']) && !empty($d['name'])) {
            $this->db->where(array('academic_year_id' => $d['academic_year_id'], 'name' => $d['name']));
            if ($id) $this->db->where('id !=', $id);
            if ($this->db->count_all_results('semesters') > 0) $this->errors[] = 'Semester tersebut sudah ada pada tahun ajaran ini.';
        }
        return $d;
    }

    private function after_semesters($id, $new)
    {
        $r = $this->db->get_where('semesters', array('id' => $id))->row();
        if ($r->is_active) $this->db->where('id !=', $id)->update('semesters', array('is_active' => 0));
    }

    private function before_schedules($d, $id)
    {
        foreach (array('start_time', 'end_time') as $k) { if (!empty($d[$k])) $d[$k] = substr($d[$k], 0, 5) . ':00'; }
        if (empty($d['start_time']) || empty($d['end_time'])) return $d;
        foreach ($this->schedule_check($d, $id) as $m) { $this->errors[] = $m; }
        return $d;
    }

    /** Validasi jadwal: urutan jam, hari efektif, jam istirahat, bentrok guru & kelas. */
    private function schedule_check($d, $id)
    {
        $err = array();
        if ($d['start_time'] >= $d['end_time']) $err[] = 'Jam selesai harus setelah jam mulai.';
        $eff = explode(',', setting('effective_days', '1,2,3,4,5'));
        if (!in_array((string)$d['day'], $eff, TRUE)) $err[] = hari_id($d['day']) . ' bukan hari efektif sekolah (Pengaturan > Hari Efektif).';
        $bs = setting('break_start', ''); $be = setting('break_end', '');
        if ($bs !== '' && $be !== '' && $d['start_time'] < $be . ':00' && $d['end_time'] > $bs . ':00') $err[] = 'Jadwal bertabrakan dengan jam istirahat (' . $bs . '-' . $be . ').';
        $rows = $this->db->query('SELECT sc.id, sc.teacher_id, sc.class_id, c.name cn, t.name tn, sc.start_time, sc.end_time FROM schedules sc JOIN classes c ON c.id=sc.class_id JOIN teachers t ON t.id=sc.teacher_id
            WHERE sc.semester_id=? AND sc.day=? AND sc.id<>? AND sc.start_time<? AND sc.end_time>? AND (sc.teacher_id=? OR sc.class_id=?)',
            array($d['semester_id'], $d['day'], (int)$id, $d['end_time'], $d['start_time'], $d['teacher_id'], $d['class_id']))->result();
        foreach ($rows as $r) {
            $w = ' (' . substr($r->start_time, 0, 5) . '-' . substr($r->end_time, 0, 5) . ')';
            if ($r->teacher_id == $d['teacher_id']) $err[] = 'Bentrok: guru ' . $r->tn . ' sudah mengajar di ' . $r->cn . $w . '.';
            if ($r->class_id == $d['class_id']) $err[] = 'Bentrok: kelas ' . $r->cn . ' sudah memiliki jadwal pada ' . $w . '.';
        }
        return $err;
    }

    /* ---------- Jadwal mingguan ---------- */

    public function week()
    {
        $cid = (int)$this->input->get('class_id');
        $tid = (int)$this->input->get('teacher_id');
        $sem = (int)$this->input->get('semester_id');
        if (!$sem) { $s = $this->active_semester(); $sem = $s ? (int)$s->id : 0; }
        $sql = 'SELECT sc.*, c.name class_name, sb.name subject_name, t.name teacher_name FROM schedules sc JOIN classes c ON c.id=sc.class_id JOIN subjects sb ON sb.id=sc.subject_id JOIN teachers t ON t.id=sc.teacher_id WHERE sc.semester_id=?';
        $args = array($sem);
        if ($cid) { $sql .= ' AND sc.class_id=?'; $args[] = $cid; }
        if ($tid) { $sql .= ' AND sc.teacher_id=?'; $args[] = $tid; }
        $sql .= ' ORDER BY sc.day, sc.start_time';
        $by = array();
        foreach ($this->db->query($sql, $args)->result() as $r) { $by[$r->day][] = $r; }
        $this->render('master/week', array('title' => 'Jadwal Mingguan', 'by' => $by, 'classes' => $this->lookup('classes'), 'teachers' => $this->lookup('teachers'),
            'semesters' => $this->lookup('semesters'), 'cid' => $cid, 'tid' => $tid, 'sem' => $sem));
    }

    /* ---------- Ekspor / Impor ---------- */

    private function has_xlsx() { return class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet'); }

    /** Format keluaran: XLSX bila tersedia (kecuali diminta ?format=csv), selain itu CSV. */
    private function out_format()
    {
        if (strtolower((string)$this->input->get('format')) === 'csv') return 'csv';
        return $this->has_xlsx() ? 'xlsx' : 'csv';
    }

    /** Indeks kolom yang harus berformat Teks (nomor panjang / berawalan 0). */
    private function text_cols($headers)
    {
        $t = array('nis', 'nisn', 'nip', 'whatsapp', 'wa_ortu', 'no. hp', 'kode_mapel', 'username_guru', 'jam_mulai', 'jam_selesai', 'mulai', 'selesai');
        $o = array();
        foreach ($headers as $i => $h) { if (in_array(strtolower($h), $t, TRUE)) $o[] = $i; }
        return $o;
    }

    private function row_one($sql, $args = array())
    {
        $q = $this->db->query($sql, $args);
        return $q ? $q->row() : NULL;
    }

    private function g($r, $k) { return isset($r[$k]) ? (string)$r[$k] : ''; }

    private function key_norm($s) { return preg_replace('/\s+/', '', strtolower(trim((string)$s))); }

    public function export($key)
    {
        $e = $this->ent($key);
        if ($key === 'users') show_404();
        $headers = array(); $out = array();
        if ($key === 'schedules') {
            // Format sama dengan template impor sehingga hasil ekspor bisa diimpor kembali.
            $headers = array('hari', 'kelas', 'kode_mapel', 'username_guru', 'jam_mulai', 'jam_selesai', 'ruang', 'mata_pelajaran', 'guru');
            $rows = $this->db->query('SELECT sc.day, c.name class_name, sb.code, u.username, sc.start_time, sc.end_time, sc.room, sb.name subject_name, t.name teacher_name
                FROM schedules sc JOIN classes c ON c.id=sc.class_id JOIN subjects sb ON sb.id=sc.subject_id JOIN teachers t ON t.id=sc.teacher_id
                LEFT JOIN users u ON u.id=t.user_id JOIN semesters sm ON sm.id=sc.semester_id
                WHERE sm.is_active=1 ORDER BY sc.day, c.name, sc.start_time')->result_array();
            foreach ($rows as $r) {
                $out[] = array(hari_id($r['day']), $r['class_name'], $r['code'], (string)$r['username'], substr($r['start_time'], 0, 5), substr($r['end_time'], 0, 5),
                    (string)$r['room'], $r['subject_name'], $r['teacher_name']);
            }
        } else {
            $rows = $this->db->query($this->list_sql($e))->result_array();
            foreach ($e['columns'] as $c) {
                if (isset($c[2]) && $c[2] === 'photo') continue;
                $headers[] = $c[1];
                if ($key === 'students' && $c[0] === 'name') $headers[] = 'JK';
            }
            foreach ($rows as $r) {
                $line = array();
                foreach ($e['columns'] as $c) {
                    $t = isset($c[2]) ? $c[2] : '';
                    if ($t === 'photo') continue;
                    $v = isset($r[$c[0]]) ? $r[$c[0]] : '';
                    if ($t === 'active') $v = $v ? 'Aktif' : 'Nonaktif';
                    if ($t === 'day') $v = hari_id($v);
                    if ($t === 'time') $v = $v ? substr($v, 0, 5) : '';
                    if ($t === 'date') $v = $v ? tanggal_id($v, FALSE) : '';
                    $line[] = $v === NULL ? '' : $v;
                    if ($key === 'students' && $c[0] === 'name') $line[] = isset($r['gender']) ? $r['gender'] : '';
                }
                $out[] = $line;
            }
        }
        audit('ekspor_' . $key, count($out) . ' baris');
        export_table($key . '-' . date('Ymd'), $headers, $out, $this->out_format(), array('sheet' => $e['title'], 'text_cols' => $this->text_cols($headers)));
    }

    public function template($key)
    {
        $fmt = $this->out_format();
        if ($key === 'students') {
            $h = array('nis', 'nisn', 'nama', 'jk', 'kelas', 'nama_ortu', 'wa_ortu');
            export_table('template-impor-siswa', $h, array(
                array('1001', '0012345678', 'Ahmad Fauzi', 'L', 'VII A', 'Budi Santoso', '081234567890'),
                array('1002', '', 'Siti Aminah', 'P', 'VII A', 'Dewi Lestari', '6281298765432')),
                $fmt, array('sheet' => 'Siswa', 'text_cols' => $this->text_cols($h)));
        }
        if ($key === 'schedules') {
            $h = array('hari', 'kelas', 'kode_mapel', 'username_guru', 'jam_mulai', 'jam_selesai', 'ruang');
            export_table('template-impor-jadwal', $h, array(array('Senin', 'VII A', 'MTK', 'andi', '07:00', '08:30', 'R1')),
                $fmt, array('sheet' => 'Jadwal', 'text_cols' => $this->text_cols($h)));
        }
        show_404();
    }

    /** Nama kolom baku (huruf kecil, garis bawah) + alias umum. */
    private function norm_header($h)
    {
        static $alias = array(
            'nama_siswa' => 'nama', 'nama_lengkap' => 'nama', 'name' => 'nama',
            'jenis_kelamin' => 'jk', 'l_p' => 'jk', 'gender' => 'jk', 'sex' => 'jk',
            'class' => 'kelas',
            'orang_tua' => 'nama_ortu', 'nama_orang_tua' => 'nama_ortu', 'ortu' => 'nama_ortu', 'wali' => 'nama_ortu', 'nama_wali' => 'nama_ortu',
            'wa' => 'wa_ortu', 'whatsapp' => 'wa_ortu', 'no_wa' => 'wa_ortu', 'no_whatsapp' => 'wa_ortu', 'wa_orang_tua' => 'wa_ortu',
            'whatsapp_ortu' => 'wa_ortu', 'hp_ortu' => 'wa_ortu', 'no_hp_ortu' => 'wa_ortu', 'telepon_ortu' => 'wa_ortu',
            'kode' => 'kode_mapel', 'kode_mata_pelajaran' => 'kode_mapel',
            'username' => 'username_guru', 'guru_username' => 'username_guru',
            'mulai' => 'jam_mulai', 'jam_awal' => 'jam_mulai', 'selesai' => 'jam_selesai', 'jam_akhir' => 'jam_selesai',
            'ruangan' => 'ruang', 'room' => 'ruang',
        );
        $h = strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string)$h)));
        $h = trim(preg_replace('/[^a-z0-9]+/', '_', $h), '_');
        return isset($alias[$h]) ? $alias[$h] : $h;
    }

    /** Ubah nilai sel (termasuk angka/pecahan hari dari Excel) menjadi teks bersih. */
    private function cell_str($v, $h)
    {
        if ($v === NULL) return '';
        if (is_bool($v)) return $v ? '1' : '0';
        if (is_float($v)) {
            if (($h === 'jam_mulai' || $h === 'jam_selesai') && $v >= 0 && $v < 1) {   // jam disimpan Excel sebagai pecahan hari
                $sec = (int)round($v * 86400);
                return sprintf('%02d:%02d', intdiv($sec, 3600) % 24, intdiv($sec % 3600, 60));
            }
            $s = (floor($v) == $v && abs($v) < 1e15) ? sprintf('%.0f', $v) : rtrim(rtrim(sprintf('%.10F', $v), '0'), '.');   // tanpa notasi ilmiah (6.28E+11)
        } else {
            $s = (string)$v;
        }
        return trim(str_replace("\xC2\xA0", ' ', $s));
    }

    /** Baca CSV/XLSX. Mengembalikan array(daftar_header, baris[]) ; melempar RuntimeException bila gagal. */
    private function read_table($tmp, $ext)
    {
        $all = array();
        if (in_array($ext, array('xlsx', 'xls'), TRUE)) {
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($tmp);
                $reader->setReadDataOnly(TRUE);
                $ss = $reader->load($tmp);
                $all = $ss->getSheet(0)->toArray(NULL, TRUE, FALSE, FALSE);   // nilai mentah, bukan hasil format tampilan
                $ss->disconnectWorksheets();
            } catch (\Throwable $ex) {
                throw new \RuntimeException('File Excel tidak dapat dibaca (' . $ex->getMessage() . '). Pastikan file .xlsx asli, bukan CSV yang diganti ekstensi.');
            }
        } else {
            $raw = file_get_contents($tmp);
            if ($raw === FALSE) throw new \RuntimeException('File tidak dapat dibaca.');
            $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
            if (!mb_check_encoding($raw, 'UTF-8')) $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');   // CSV dari Excel versi lama
            $first = (string)strtok($raw, "\n");
            $delim = ','; $best = substr_count($first, ',');
            foreach (array(';', "\t") as $d) { $n = substr_count($first, $d); if ($n > $best) { $best = $n; $delim = $d; } }
            $fh = fopen('php://temp', 'r+');
            fwrite($fh, $raw);
            rewind($fh);
            while (($r = fgetcsv($fh, 0, $delim)) !== FALSE) { $all[] = $r; }
            fclose($fh);
        }
        $hdr = NULL; $rows = array();
        foreach ($all as $n => $r) {
            if ($hdr === NULL) {
                $any = FALSE;
                foreach ($r as $c) { if (trim((string)$c) !== '') { $any = TRUE; break; } }
                if (!$any) continue;
                $hdr = array();
                foreach ($r as $c) { $hdr[] = $this->norm_header($c); }
                continue;
            }
            $row = array('_line' => $n + 1);
            $any = FALSE;
            foreach ($hdr as $i => $h) {
                if ($h === '') continue;
                $v = $this->cell_str(isset($r[$i]) ? $r[$i] : '', $h);
                if ($v !== '') $any = TRUE;
                if (!isset($row[$h]) || $row[$h] === '') $row[$h] = $v;
            }
            if ($any) $rows[] = $row;
        }
        if ($hdr === NULL) throw new \RuntimeException('File kosong.');
        return array($hdr, $rows);
    }

    private function parse_time($s)
    {
        $s = trim((string)$s);
        if (preg_match('/^(\d{1,2})\s*[:.]\s*(\d{2})(?:\s*[:.]\s*\d{2})?$/', $s, $m) || preg_match('/^(\d{2})(\d{2})$/', $s, $m)) {
            if ((int)$m[1] <= 23 && (int)$m[2] <= 59) return sprintf('%02d:%02d:00', (int)$m[1], (int)$m[2]);
        }
        return NULL;
    }

    public function import($key)
    {
        $this->require_post();
        $e = $this->ent($key);
        if (empty($e['import'])) show_404();
        $back = 'master/' . $key;
        $f = isset($_FILES['file']) ? $_FILES['file'] : NULL;
        if (!$f || empty($f['tmp_name']) || (int)$f['error'] !== UPLOAD_ERR_OK) {
            $why = ($f && in_array((int)$f['error'], array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE), TRUE))
                ? 'File terlalu besar untuk batas unggah server (upload_max_filesize).' : 'Pilih file CSV/XLSX terlebih dahulu.';
            $this->flash('danger', $why);
            redirect($back);
        }
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, array('csv', 'xlsx', 'xls'), TRUE)) { $this->flash('danger', 'Format file harus .csv atau .xlsx.'); redirect($back); }
        if ($ext !== 'csv' && !$this->has_xlsx()) {
            $this->flash('danger', 'Impor Excel memerlukan folder vendor/ (PhpSpreadsheet). Simpan file sebagai CSV lalu unggah ulang.');
            redirect($back);
        }
        @set_time_limit(300);
        try {
            list($hdr, $rows) = $this->read_table($f['tmp_name'], $ext);
        } catch (\Throwable $ex) {
            $this->flash('danger', e($ex->getMessage()));
            redirect($back);
        }
        $need = $key === 'students' ? array('nis', 'nama', 'kelas') : array('hari', 'kelas', 'kode_mapel', 'username_guru', 'jam_mulai', 'jam_selesai');
        $miss = array_diff($need, $hdr);
        if ($miss) { $this->flash('danger', 'Kolom wajib tidak ditemukan: ' . e(implode(', ', $miss)) . '. Unduh dan gunakan template impor.'); redirect($back); }
        if (!$rows) { $this->flash('warning', 'Tidak ada baris data di bawah judul kolom.'); redirect($back); }

        $old = $this->db->db_debug;
        $this->db->db_debug = FALSE;   // galat DB per baris dilaporkan, tidak menghentikan impor
        $res = $key === 'students' ? $this->import_students($rows) : $this->import_schedules($rows);
        $this->db->db_debug = $old;

        list($okc, $errs, $skip) = $res;
        audit('impor_' . $key, 'ok=' . $okc . ' lewati=' . $skip . ' gagal=' . count($errs));
        $msg = 'Impor selesai: ' . $okc . ' baris berhasil' . ($skip ? ', ' . $skip . ' dilewati (sudah ada)' : '') . ', ' . count($errs) . ' baris gagal.';
        if ($errs) {
            $msg .= '<br><small>' . implode('<br>', array_map('e', array_slice($errs, 0, 15))) . (count($errs) > 15 ? '<br>... dan ' . (count($errs) - 15) . ' kesalahan lain' : '') . '</small>';
        }
        $this->flash($errs ? 'warning' : 'success', $msg);
        redirect($back);
    }

    private function import_students($rows)
    {
        $this->load->model('Qr_model');
        $ok = 0; $err = array(); $now = date('Y-m-d H:i:s');
        $classes = array();   // nama kelas (tanpa spasi, huruf kecil) => id ; kelas tahun ajaran aktif menimpa yang lama
        $q = $this->db->query('SELECT c.id, c.name FROM classes c LEFT JOIN academic_years y ON y.id=c.academic_year_id ORDER BY IFNULL(y.is_active,0), c.id');
        if ($q) foreach ($q->result() as $c) { $classes[$this->key_norm($c->name)] = (int)$c->id; }

        foreach ($rows as $r) {
            $ln = $r['_line'];
            $nis = $this->g($r, 'nis'); $name = mb_substr($this->g($r, 'nama'), 0, 120);
            if ($nis === '' || $name === '') { $err[] = "Baris $ln: NIS dan nama wajib diisi."; continue; }
            if (strlen($nis) > 30) { $err[] = "Baris $ln: NIS terlalu panjang."; continue; }
            $ck = $this->key_norm($this->g($r, 'kelas'));
            if (!isset($classes[$ck])) { $err[] = "Baris $ln: kelas \"" . $this->g($r, 'kelas') . '" belum ada di Data Kelas.'; continue; }
            $gs = strtoupper(trim($this->g($r, 'jk')));
            $g = in_array(substr($gs, 0, 1), array('P', 'F', 'W'), TRUE) ? 'P' : 'L';
            $nisn = $this->g($r, 'nisn'); if ($nisn === '') $nisn = NULL;
            $active = in_array(strtolower($this->g($r, 'status')), array('nonaktif', 'non aktif', 'tidak aktif', '0'), TRUE) ? 0 : 1;

            $ex = $this->row_one('SELECT id FROM students WHERE nis=? LIMIT 1', array($nis));
            if ($nisn !== NULL) {
                $this->db->where('nisn', $nisn);
                if ($ex) $this->db->where('id !=', $ex->id);
                if ($this->db->count_all_results('students') > 0) { $err[] = "Baris $ln: NISN $nisn sudah dipakai siswa lain."; continue; }
            }
            $pid = NULL;
            $wa_raw = $this->g($r, 'wa_ortu');
            if ($wa_raw !== '') {
                $wa = normalize_phone($wa_raw);
                if (!valid_phone($wa)) { $err[] = "Baris $ln: nomor WhatsApp orang tua \"$wa_raw\" tidak valid."; continue; }
                $p = $this->row_one('SELECT id FROM parents WHERE whatsapp=? LIMIT 1', array($wa));
                if ($p) { $pid = $p->id; }
                else {
                    $pn = $this->g($r, 'nama_ortu') !== '' ? mb_substr($this->g($r, 'nama_ortu'), 0, 120) : mb_substr('Orang Tua ' . $name, 0, 120);
                    if (!$this->db->insert('parents', array('name' => $pn, 'whatsapp' => $wa, 'created_at' => $now, 'updated_at' => $now))) {
                        $e2 = $this->db->error(); $err[] = "Baris $ln: gagal menyimpan orang tua (" . $e2['message'] . ').'; continue;
                    }
                    $pid = $this->db->insert_id();
                }
            }
            $data = array('nisn' => $nisn, 'name' => $name, 'gender' => $g, 'class_id' => $classes[$ck], 'is_active' => $active, 'updated_at' => $now);
            if ($ex) {
                $data['deleted_at'] = NULL;
                if (!$this->db->update('students', $data, array('id' => $ex->id))) { $e2 = $this->db->error(); $err[] = "Baris $ln: gagal memperbarui siswa (" . $e2['message'] . ').'; continue; }
                $sid = $ex->id; $new = FALSE;
            } else {
                $data['nis'] = $nis; $data['created_at'] = $now;
                if (!$this->db->insert('students', $data)) { $e2 = $this->db->error(); $err[] = "Baris $ln: gagal menyimpan siswa (" . $e2['message'] . ').'; continue; }
                $sid = $this->db->insert_id(); $new = TRUE;
            }
            if ($pid) {
                $this->db->delete('student_parents', array('student_id' => $sid));
                $this->db->insert('student_parents', array('student_id' => $sid, 'parent_id' => $pid, 'is_primary' => 1));
            }
            if ($new || !$this->Qr_model->active_token($sid)) $this->Qr_model->generate($sid, 'Dibuat otomatis (impor)', $this->uid);
            $ok++;
        }
        return array($ok, $err, 0);
    }

    private function import_schedules($rows)
    {
        $sem = $this->active_semester();
        if (!$sem) return array(0, array('Belum ada semester aktif. Aktifkan semester di menu Semester terlebih dahulu.'), 0);
        $ok = 0; $skip = 0; $err = array(); $now = date('Y-m-d H:i:s');
        $days = array('senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, 'sabtu' => 6, 'minggu' => 7, 'ahad' => 7,
                      'sen' => 1, 'sel' => 2, 'rab' => 3, 'kam' => 4, 'jum' => 5, 'sab' => 6, 'min' => 7);
        $cc = array(); $sc = array(); $tc = array();
        foreach ($rows as $r) {
            $ln = $r['_line'];
            $dk = strtolower(str_replace(array("'", "\xE2\x80\x99", '`'), '', $this->g($r, 'hari')));
            $day = isset($days[$dk]) ? $days[$dk] : (ctype_digit($dk) ? (int)$dk : 0);
            if ($day < 1 || $day > 7) { $err[] = "Baris $ln: hari \"" . $this->g($r, 'hari') . '" tidak valid.'; continue; }

            $kn = $this->g($r, 'kelas');
            if (!array_key_exists($kn, $cc)) {
                $cc[$kn] = $this->row_one('SELECT id FROM classes WHERE REPLACE(name, " ", "")=REPLACE(?, " ", "") ORDER BY (academic_year_id <=> ?) DESC, id DESC LIMIT 1', array($kn, $sem->academic_year_id));
            }
            $c = $cc[$kn];
            if (!$c) { $err[] = "Baris $ln: kelas \"$kn\" tidak ditemukan."; continue; }

            $kd = $this->g($r, 'kode_mapel');
            if (!array_key_exists($kd, $sc)) $sc[$kd] = $this->row_one('SELECT id FROM subjects WHERE code=? LIMIT 1', array($kd));
            $s = $sc[$kd];
            if (!$s) { $err[] = "Baris $ln: kode mapel \"$kd\" tidak ditemukan."; continue; }

            $un = $this->g($r, 'username_guru');
            if (!array_key_exists($un, $tc)) $tc[$un] = $this->row_one('SELECT t.id FROM teachers t JOIN users u ON u.id=t.user_id WHERE u.username=? LIMIT 1', array($un));
            $t = $tc[$un];
            if (!$t) { $err[] = "Baris $ln: username guru \"$un\" tidak ditemukan."; continue; }

            $st = $this->parse_time($this->g($r, 'jam_mulai')); $en = $this->parse_time($this->g($r, 'jam_selesai'));
            if ($st === NULL || $en === NULL) { $err[] = "Baris $ln: format jam harus HH:MM (mis. 07:00)."; continue; }

            $d = array('semester_id' => $sem->id, 'day' => $day, 'class_id' => $c->id, 'subject_id' => $s->id, 'teacher_id' => $t->id,
                'start_time' => $st, 'end_time' => $en, 'room' => $this->g($r, 'ruang') !== '' ? mb_substr($this->g($r, 'ruang'), 0, 30) : NULL,
                'created_at' => $now, 'updated_at' => $now);
            $dup = $this->row_one('SELECT id FROM schedules WHERE semester_id=? AND day=? AND class_id=? AND subject_id=? AND teacher_id=? AND start_time=? AND end_time=? LIMIT 1',
                array($sem->id, $day, $c->id, $s->id, $t->id, $st, $en));
            if ($dup) { $skip++; continue; }
            $cks = $this->schedule_check($d, 0);
            if ($cks) { $err[] = "Baris $ln: " . $cks[0]; continue; }
            if (!$this->db->insert('schedules', $d)) { $e2 = $this->db->error(); $err[] = "Baris $ln: gagal menyimpan (" . $e2['message'] . ').'; continue; }
            $ok++;
        }
        return array($ok, $err, $skip);
    }
}
