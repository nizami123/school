<?php defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
    'dsn' => '', 'hostname' => '163.223.227.8', 'username' => 'smmmsqnq_school', 'password' => 'Lamongan@123', 'database' => 'smmmsqnq_school',
    'dbdriver' => 'mysqli', 'dbprefix' => '', 'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE, 'cachedir' => '', 'char_set' => 'utf8mb4', 'dbcollat' => 'utf8mb4_unicode_ci',
    'swap_pre' => '', 'encrypt' => FALSE, 'compress' => FALSE, 'stricton' => FALSE,
    'failover' => array(), 'save_queries' => FALSE
);

// Kredensial ditulis oleh installer ke database_local.php
$local = APPPATH . 'config/database_local.php';
if (file_exists($local)) {
    $db['default'] = array_merge($db['default'], include $local);
}
