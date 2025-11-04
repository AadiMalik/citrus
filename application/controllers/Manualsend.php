<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Manualsend extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        // Load any necessary models or helpers here
        // Example: $this->load->model('invoices_model');
    }

    public function august()
    {
        // Sample output to confirm it’s working
        echo '✅ Manual invoice sending triggered for August 2025.';

        // Example: You could loop over August invoices and send emails here
        /*
        $this->load->model('invoices_model');
        $invoices = $this->invoices_model->get_august_invoices();

        foreach ($invoices as $invoice) {
            $this->invoices_model->send_invoice_to_client($invoice['id']);
        }
        */
    }
}

