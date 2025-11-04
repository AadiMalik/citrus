<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CustomInvoiceMailer extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoices_model');
        $this->load->model('clients_model');
    }

    public function send_august_01_invoices()
    {
        // Query to fetch invoices dated 2025-08-01
        $date = '2025-08-01';
        $invoices = $this->db->where('date', $date)->get(db_prefix() . 'invoices')->result();

        if (empty($invoices)) {
            echo "No invoices found for $date.";
            return;
        }

        $sentCount = 0;
        $failed = [];

        foreach ($invoices as $invoice) {
            $client = $this->clients_model->get($invoice->clientid);

            if (!$client || empty($client->email)) {
                $failed[] = [
                    'invoice_id' => $invoice->id,
                    'reason' => 'Missing client email or client not found'
                ];
                continue;
            }

            // Send the invoice email
            $success = $this->invoices_model->send_invoice_to_client($invoice->id, 'manual', '', '', true);

            if ($success) {
                $sentCount++;
            } else {
                $failed[] = [
                    'invoice_id' => $invoice->id,
                    'reason' => 'send_invoice_to_client failed'
                ];
            }
        }

        // Output summary
        echo "<h3>Invoice Email Report - $date</h3>";
        echo "<p><strong>Sent:</strong> $sentCount</p>";
        echo "<p><strong>Failed:</strong> " . count($failed) . "</p>";

        if (!empty($failed)) {
            echo "<ul>";
            foreach ($failed as $fail) {
                echo "<li>Invoice ID: {$fail['invoice_id']} - Reason: {$fail['reason']}</li>";
            }
            echo "</ul>";
        }
    }
}
