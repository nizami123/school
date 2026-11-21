<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';

// Master data (CRUD generik)
$route['master/schedules/week'] = 'master/week';
$route['master/([a-z_]+)/form'] = 'master/form/$1';
$route['master/([a-z_]+)/form/([0-9]+)'] = 'master/form/$1/$2';
$route['master/([a-z_]+)/save'] = 'master/save/$1';
$route['master/([a-z_]+)/delete/([0-9]+)'] = 'master/delete/$1/$2';
$route['master/([a-z_]+)/toggle/([0-9]+)'] = 'master/toggle/$1/$2';
$route['master/([a-z_]+)/export'] = 'master/export/$1';
$route['master/([a-z_]+)/import'] = 'master/import/$1';
$route['master/([a-z_]+)/template'] = 'master/template/$1';
$route['master/([a-z_]+)'] = 'master/index/$1';
