<?php

define('CRON', true); // Marks this as a CLI cron-safe execution
require_once('index.php');

// Call the default cron controller logic
$CI =& get_instance();
$CI->load->library('session');
$CI->load->helper('url');
$CI->load->database();
$CI->load->model('cron_model');

$CI->cron_model->run();

echo "Manual cron execution completed.";


