<?php defined('BASEPATH') OR exit('No direct script access allowed');

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
if (isset($_SERVER['HTTP_HOST'])) {
    $config['base_url'] = ($https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
        . str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
} else {
    $config['base_url'] = 'https://school.velano.my.id/';
}

$config['index_page'] = '';
$config['uri_protocol'] = 'REQUEST_URI';
$config['url_suffix'] = '';
$config['language'] = 'english';
$config['charset'] = 'UTF-8';
$config['enable_hooks'] = FALSE;
$config['subclass_prefix'] = 'MY_';
$config['composer_autoload'] = file_exists(FCPATH . 'vendor/autoload.php') ? FCPATH . 'vendor/autoload.php' : FALSE;
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';
$config['allow_get_array'] = TRUE;
$config['enable_query_strings'] = FALSE;
$config['controller_trigger'] = 'c';
$config['function_trigger'] = 'm';
$config['directory_trigger'] = 'd';
$config['log_threshold'] = 1;
$config['log_path'] = APPPATH . 'logs/';
$config['log_file_extension'] = '';
$config['log_file_permissions'] = 0644;
$config['log_date_format'] = 'Y-m-d H:i:s';
$config['error_views_path'] = '';
$config['cache_path'] = '';
$config['cache_query_string'] = FALSE;

$secret = array('encryption_key' => '');
if (file_exists(APPPATH . 'config/secret.php')) {
    $secret = include APPPATH . 'config/secret.php';
}
$config['encryption_key'] = $secret['encryption_key'];

$config['sess_driver'] = 'files';
$config['sess_cookie_name'] = 'qrsa_session';
$config['sess_expiration'] = 14400;
$config['sess_save_path'] = APPPATH . 'cache/sessions';
$config['sess_match_ip'] = FALSE;
$config['sess_time_to_update'] = 600;
$config['sess_regenerate_destroy'] = FALSE;

$config['cookie_prefix'] = '';
$config['cookie_domain'] = '';
$config['cookie_path'] = '/';
$config['cookie_secure'] = $https;
$config['cookie_httponly'] = TRUE;

$config['standardize_newlines'] = FALSE;
$config['global_xss_filtering'] = FALSE;

// CSRF aktif. csrf_regenerate=FALSE agar scanner (AJAX berurutan) tidak gagal.
$config['csrf_protection'] = TRUE;
$config['csrf_token_name'] = 'csrf_token';
$config['csrf_cookie_name'] = 'csrf_cookie';
$config['csrf_expire'] = 14400;
$config['csrf_regenerate'] = FALSE;
$config['csrf_exclude_uris'] = array();

$config['compress_output'] = FALSE;
$config['time_reference'] = 'local';
$config['rewrite_short_tags'] = FALSE;
$config['proxy_ips'] = '';
