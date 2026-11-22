<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Logika tagihan & pembayaran iuran/SPP/infaq (pembayaran manual: tunai atau transfer + bukti, diverifikasi admin). */
class Fee_model extends CI_Model
{
    public function recompute($invoice_id)
    {
        $inv = $this->db->get_where('invoices', array('id' => (int)$invoice_id, 'school_id' => school_id()))->row();
        if (!$inv || $inv->status === 'batal') return;
        $paid = (int)$this->db->query("SELECT IFNULL(SUM(amount),0) s FROM payments WHERE invoice_id=? AND status='terverifikasi'", array($inv->id))->row()->s;
        $status = $paid >= (int)$inv->amount ? 'lunas' : ($paid > 0 ? 'sebagian' : 'belum');
        $this->db->update('invoices', array('paid_total' => $paid, 'status' => $status, 'updated_at' => date('Y-m-d H:i:s')), array('id' => $inv->id));
    }

    public function remaining($inv)
    {
        return max(0, (int)$inv->amount - (int)$inv->paid_total);
    }

    /** Pembayaran tunai/langsung oleh admin: langsung terverifikasi. Mengembalikan pesan error atau '' bila sukses. */
    public function add_verified($inv, $amount, $method, $note, $uid)
    {
        $amount = (int)$amount;
        if ($inv->status === 'batal') return 'Tagihan sudah dibatalkan.';
        if ($amount < 1) return 'Nominal harus lebih dari 0.';
        if ($amount > $this->remaining($inv)) return 'Nominal melebihi sisa tagihan (' . rupiah($this->remaining($inv)) . ').';
        $now = date('Y-m-d H:i:s');
        $this->db->insert('payments', array('invoice_id' => $inv->id, 'amount' => $amount, 'method' => $method, 'paid_at' => $now, 'note' => $note !== '' ? $note : NULL,
            'status' => 'terverifikasi', 'submitted_by_parent' => 0, 'verified_by' => $uid, 'verified_at' => $now, 'created_at' => $now, 'updated_at' => $now));
        $pid = $this->db->insert_id();
        $this->db->update('payments', array('receipt_no' => $this->receipt_no($pid)), array('id' => $pid));
        $this->recompute($inv->id);
        return '';
    }

    public function receipt_no($payment_id)
    {
        return 'KW' . date('Ym') . sprintf('%06d', $payment_id);
    }

    /** Verifikasi/tolak pembayaran 'menunggu'. Mengembalikan pesan error atau ''. */
    public function review($payment_id, $approve, $uid, $note = '')
    {
        $p = $this->db->query('SELECT p.* FROM payments p JOIN invoices i ON i.id=p.invoice_id WHERE p.id=? AND i.school_id=?', array((int)$payment_id, school_id()))->row();
        if (!$p) return 'Pembayaran tidak ditemukan.';
        $inv = $this->db->get_where('invoices', array('id' => $p->invoice_id, 'school_id' => school_id()))->row();
        $now = date('Y-m-d H:i:s');
        if ($approve) {
            if ($inv->status === 'batal') return 'Tagihan sudah dibatalkan.';
            if ((int)$p->amount > $this->remaining($inv)) return 'Nominal melebihi sisa tagihan saat ini (' . rupiah($this->remaining($inv)) . '). Tolak atau minta orang tua mengirim ulang.';
        }
        $this->db->query("UPDATE payments SET status=?, verified_by=?, verified_at=?, note=IF(?<>'', ?, note), receipt_no=?, updated_at=? WHERE id=? AND status='menunggu'",
            array($approve ? 'terverifikasi' : 'ditolak', $uid, $now, $note, $note, $approve ? $this->receipt_no($p->id) : NULL, $now, $p->id));
        if ($this->db->affected_rows() < 1) return 'Pembayaran ini sudah diproses.';
        $this->recompute($inv->id);
        return '';
    }
}
