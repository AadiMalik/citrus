<?php
defined('BASEPATH') or exit('No direct script access allowed');

$hook['pre_system'] = function () {
    require_once(APPPATH . 'third_party/Composer/autoload.php');
};

/**
 * Hook into the cron event system to trigger invoice generation
 */
hooks()->add_action('before_cron_run', function ($manually) {
    $CI =& get_instance();
    $CI->load->model('invoices_model');
    log_message('debug', '>>> Hook triggered: generate_recurring_invoices()');
    $CI->invoices_model->generate_recurring_invoices();
});
