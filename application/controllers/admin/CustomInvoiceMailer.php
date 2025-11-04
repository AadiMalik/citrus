<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CustomInvoiceMailer extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoices_model');
        $this->load->model('clients_model');
        $this->load->helper('pdf');
        $this->load->library('emails'); // Ensure this is correct for your setup
    }

    public function send_august_01_invoices()
    {
        $this->db->where('date', '2025-08-01');
        $invoices = $this->db->get(db_prefix() . 'invoices')->result();

        foreach ($invoices as $invoice) {
            $pdf = invoice_pdf($invoice);
            $pdf_string = $pdf->Output('', 'S');

            $client = $this->clients_model->get($invoice->clientid);
            if (!$client || empty($client->email)) {
                continue; // skip if no client or email
            }

            $to = $client->email;
            $subject = 'Invoice ' . format_invoice_number($invoice->id);
            $message = 'Dear ' . $client->company . ',<br><br>'
                     . 'Please find attached your invoice dated 01 August 2025.'
                     . '<br><br>Regards,<br>Finance Team';

            $attach = [
                [
                    'content' => $pdf_string,
                    'filename' => slug_it('Invoice-' . $invoice->id) . '.pdf',
                    'type' => 'application/pdf',
                ]
            ];

            $this->emails->send_email($to, $subject, $message, $attach);
        }

        echo "Emails sent for all invoices dated 2025-08-01.";
    }
}
