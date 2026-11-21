<?php defined('BASEPATH') OR exit('No direct script access allowed');
return array(
    'jpg' => array('image/jpeg', 'image/pjpeg'),
    'jpeg' => array('image/jpeg', 'image/pjpeg'),
    'png' => array('image/png', 'image/x-png'),
    'gif' => 'image/gif',
    'csv' => array('text/csv', 'text/x-comma-separated-values', 'application/csv', 'application/x-csv', 'text/plain', 'application/vnd.ms-excel', 'application/octet-stream'),
    'xlsx' => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'),
    'html' => array('text/html', 'text/plain'),
    'json' => array('application/json', 'text/json'),
    'txt' => 'text/plain',
    'pdf' => array('application/pdf', 'application/octet-stream'),
);
