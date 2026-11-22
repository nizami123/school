<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Manifest PWA dinamis: sebelum login = Velano School; setelah login = nama & logo sekolah. */
class Pwa extends MY_Controller
{
    public function manifest()
    {
        $name = 'Velano School'; $short = 'Velano';
        $icons = array(
            array('src' => base_url('assets/icons/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'),
            array('src' => base_url('assets/icons/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'));
        if ($this->uid && $this->sid) {
            $name = setting('school_name', $name);
            $short = mb_substr($name, 0, 12);
            $logo = setting('school_logo', '');
            if ($logo !== '') {
                $ext = strtolower(pathinfo($logo, PATHINFO_EXTENSION));
                $icons = array(array('src' => upload_url('logo/' . $logo), 'sizes' => '512x512', 'type' => $ext === 'png' ? 'image/png' : 'image/jpeg', 'purpose' => 'any'));
            }
        }
        $this->output->set_content_type('application/manifest+json', 'UTF-8')->set_header('Cache-Control: no-cache')->set_output(json_encode(array(
            'name' => $name, 'short_name' => $short, 'description' => 'Absensi QR, jadwal, portal orang tua, dan administrasi sekolah',
            'start_url' => site_url('dashboard'), 'scope' => site_url('/'), 'display' => 'standalone', 'orientation' => 'portrait',
            'background_color' => '#14532d', 'theme_color' => '#14532d', 'lang' => 'id', 'icons' => $icons), JSON_UNESCAPED_SLASHES));
    }
}
