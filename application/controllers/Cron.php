<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends App_Controller
{
    public function index($key = '')
    {
        // Mark that the cron is running from CLI or via direct access
        update_option('cron_has_run_from_cli', 1);

        // SECURITY: Only allow execution if the correct APP_CRON_KEY is provided
        if (defined('APP_CRON_KEY') && (APP_CRON_KEY != $key)) {
            header('HTTP/1.0 401 Unauthorized');
            die('Passed cron job key is not correct. The cron job key should be the same like the one defined in APP_CRON_KEY constant.');
        }

        // Get the timestamp of the last cron run
        $last_cron_run = get_option('last_cron_run');

        // Allow override via hook: default = 300 seconds (5 minutes)
        $seconds = hooks()->apply_filters('cron_functions_execute_seconds', 300);

        // Run cron if it's time
        if ($last_cron_run == '' || (time() > ($last_cron_run + $seconds))) {
            $this->load->model('cron_model');
            $this->cron_model->run();
        }
    }
}
