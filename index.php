<?php
/**
 * QR School Attendance - front controller (CodeIgniter 3.1.x)
 */
date_default_timezone_set('Asia/Jakarta');
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');

if (ENVIRONMENT === 'development') {
    error_reporting(-1);
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}

$system_path = 'system';
$application_folder = 'application';

if (defined('STDIN')) {
    chdir(dirname(__FILE__));
}

if (($_temp = realpath($system_path)) !== FALSE) {
    $system_path = $_temp . DIRECTORY_SEPARATOR;
} else {
    $system_path = strtr(rtrim($system_path, '/\\'), '/\\', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
}

if (!is_dir($system_path)) {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Folder "system" milik CodeIgniter 3 belum ada. Salin folder system dari CodeIgniter 3.1.13 ke folder ini (lihat docs/PANDUAN_INSTALASI_CPANEL.md).';
    exit(3);
}

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('BASEPATH', $system_path);
define('FCPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('SYSDIR', basename(BASEPATH));

if (($_temp = realpath($application_folder)) !== FALSE) {
    $application_folder = $_temp;
}
define('APPPATH', rtrim($application_folder, '/\\') . DIRECTORY_SEPARATOR);
define('VIEWPATH', APPPATH . 'views' . DIRECTORY_SEPARATOR);

require_once BASEPATH . 'core/CodeIgniter.php';
