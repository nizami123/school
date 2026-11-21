<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Klien WhatsApp via Fonnte (https://fonnte.com).
 * Endpoint : POST https://api.fonnte.com/send
 * Header   : Authorization: <TOKEN perangkat>
 * Body     : target, message (+ opsional countryCode, delay)
 *
 * Token disimpan terenkripsi di tabel school_settings (Pengaturan > WhatsApp),
 * bukan ditulis di dalam kode. Nomor tujuan = nomor WhatsApp orang tua siswa.
 */
class Whatsapp
{
    private $CI;

    public function __construct() { $this->CI =& get_instance(); }

    public function token()
    {
        $enc = setting('wa_token', '');
        if ($enc === '') return '';
        $this->CI->load->library('encryption');
        $t = $this->CI->encryption->decrypt($enc);
        return $t ? $t : '';
    }

    public function encrypt_token($plain)
    {
        $this->CI->load->library('encryption');
        return $this->CI->encryption->encrypt($plain);
    }

    public function configured()
    {
        return setting('wa_enabled', '0') === '1' && $this->token() !== '';
    }

    /** Kirim satu baris antrean (whatsapp_notifications). Mengembalikan array(ok, id, raw). */
    public function send($n)
    {
        return $this->send_text($n->phone, $n->message);
    }

    /**
     * Kirim pesan teks ke satu nomor. $phone boleh 08xx / 628xx / +628xx.
     * Mengembalikan array('ok' => bool, 'id' => string|NULL, 'raw' => string).
     */
    public function send_text($phone, $text)
    {
        $target = preg_replace('/\D+/', '', (string)$phone);
        if ($target === '') return array('ok' => FALSE, 'id' => NULL, 'raw' => 'Nomor tujuan kosong.');
        $token = $this->token();
        if ($token === '') return array('ok' => FALSE, 'id' => NULL, 'raw' => 'Token Fonnte belum diisi.');

        $fields = array('target' => $target, 'message' => (string)$text, 'countryCode' => '62');
        $delay = trim((string)setting('wa_delay', '2'));
        if ($delay !== '' && preg_match('/^\d{1,3}(-\d{1,3})?$/', $delay)) $fields['delay'] = $delay;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST           => TRUE,
            CURLOPT_POSTFIELDS     => $fields,
            CURLOPT_HTTPHEADER     => array('Authorization: ' . $token),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
        ));
        $raw = curl_exec($curl);
        $http = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);

        if ($raw === FALSE) return array('ok' => FALSE, 'id' => NULL, 'raw' => 'cURL: ' . $err);

        $j = json_decode($raw, TRUE);
        if (is_array($j) && !empty($j['status'])) {
            $id = NULL;
            if (isset($j['id'])) $id = is_array($j['id']) ? (isset($j['id'][0]) ? (string)$j['id'][0] : NULL) : (string)$j['id'];
            return array('ok' => TRUE, 'id' => $id, 'raw' => $raw);
        }
        $why = is_array($j) ? (isset($j['reason']) ? $j['reason'] : (isset($j['detail']) ? $j['detail'] : $raw)) : $raw;
        return array('ok' => FALSE, 'id' => NULL, 'raw' => 'HTTP ' . $http . ': ' . (is_string($why) ? $why : json_encode($why)));
    }
}
