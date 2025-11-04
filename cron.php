<?php

//define('FCPATH', __DIR__ . '/');
//require_once(FCPATH . 'index.php');

if (!defined('FCPATH')) {
    define('FCPATH', __DIR__ . '/');
}
require_once(__DIR__ . '/index.php');


$CI =& get_instance();
$CI->load->model('cron_model');
$CI->cron_model->run();