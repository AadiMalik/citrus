<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ManualSend extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoices_model');
        $this->load->model('emails_model');
    }

    public function august()
    {
        $invoices = $this->db
            ->where('date', '2025-08-01')
            ->where('sent', 0)
            ->get(db_prefix() . 'invoices')
            ->result();

        if (empty($invoices)) {
            echo "✅ No invoices found to send for 01/08/2025.";
            return;
        }

        foreach ($invoices as $invoice) {
            $invoice_data = $this->invoices_model->get($invoice->id);

            if (!$invoice_data) {
                echo "❌ Invoice ID {$invoice->id} not found.<br>";
                continue;
            }

            $pdf = invoice_pdf($invoice_data);
            $pdf_content = $pdf->Output('', 'S');

            $client = get_client($invoice_data->clientid);

            if (!$client || empty($client->email)) {
                echo "⚠️ Skipping invoice {$invoice->id} – no client email.<br>";
                continue;
            }

            $success = $this->emails_model->send_email_template(
                'invoice-send-to-client',
                $client->email,
                ['invoice_id' => $invoice_data->id],
                '',
                '',
                '',
                [
                    [
                        'content'  => $pdf_content,
                        'filename' => format_invoice_number($invoice_data->id) . '.pdf',
                        'type'     => 'application/pdf',
                    ]
                ]
            );

            if ($success) {
                $this->db->where('id', $invoice_data->id)->update(db_prefix() . 'invoices', [
                    'sent'     => 1,
                    'datesend' => date('Y-m-d H:i:s')
                ]);
                echo "✅ Sent invoice ID {$invoice->id} successfully.<br>";
            } else {
                echo "❌ Failed to send invoice ID {$invoice->id}.<br>";
            }
        }
    }
}