<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Invoice extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    public function index($id = '', $hash = '')
    {
        check_invoice_restrictions($id, $hash);
        $invoice = $this->invoices_model->get($id);

        $invoice = hooks()->apply_filters('before_client_view_invoice', $invoice);

        if (!is_client_logged_in()) {
            load_client_language($invoice->clientid);
        }

        // Handle Invoice PDF generator
        if ($this->input->post('invoicepdf')) {
            try {
                $pdf = invoice_pdf($invoice);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }

            $invoice_number = format_invoice_number($invoice->id);
            $companyname    = get_option('invoice_company_name');
            if ($companyname != '') {
                $invoice_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
            }
            $pdf->Output(mb_strtoupper(slug_it($invoice_number), 'UTF-8') . '.pdf', 'D');
            die();
        }

        // Handle $_POST payment
        if ($this->input->post('make_payment')) {
            $this->load->model('payments_model');
            if (!$this->input->post('paymentmode')) {
                set_alert('warning', _l('invoice_html_payment_modes_not_selected'));
                redirect(site_url('invoice/' . $id . '/' . $hash));
            } elseif ((!$this->input->post('amount') || $this->input->post('amount') == 0) && get_option('allow_payment_amount_to_be_modified') == 1) {
                set_alert('warning', _l('invoice_html_amount_blank'));
                redirect(site_url('invoice/' . $id . '/' . $hash));
            }
            $this->payments_model->process_payment($this->input->post(), $id);
        }

        if ($this->input->post('paymentpdf')) {
            $payment = $this->payments_model->get($this->input->post('paymentpdf'));
            // Confirm that the payment is related to the invoice.
            if ($payment->invoiceid == $id) {
                $payment->invoice_data = $this->invoices_model->get($payment->invoiceid);
                $paymentpdf            = payment_pdf($payment);
                $paymentpdf->Output(mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid), 'UTF-8') . '.pdf', 'D');
                die;
            }
        }

        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');
        $this->load->library('app_number_to_word', [
            'clientid' => $invoice->clientid,
        ], 'numberword');
        $this->load->model('payment_modes_model');
        $this->load->model('payments_model');
        $data['payments']      = $this->payments_model->get_invoice_payments($id);
        $data['payment_modes'] = $this->payment_modes_model->get();
        $data['title']         = format_invoice_number($invoice->id);
        $this->disableNavigation();
        $this->disableSubMenu();
        $data['hash']      = $hash;
        $data['invoice']   = hooks()->apply_filters('invoice_html_pdf_data', $invoice);
        $data['bodyclass'] = 'viewinvoice';
        $this->data($data);
        $this->view('invoicehtml');
        add_views_tracking('invoice', $id);
        hooks()->do_action('invoice_html_viewed', $id);
        no_index_customers_area();
        $this->layout();
    }

    public function send_invoice_whatsapp()
    {
        // URL segment se slug (e.g., /jfswimming/ps/invoices/send)
        $slug = $this->uri->segment(1);

        // Invoice ID POST se
        $invoice_id = $this->input->post('invoice_id');
        if (!$invoice_id) {
            echo json_encode(['success' => false, 'message' => 'Invoice ID missing']);
            return;
        }

        // Slug-based tables
        $optionsTable = $slug . '_tbloptions';
        $invoiceTable = $slug . '_tblinvoices';
        $clientsTable = $slug . '_tblclients';

        // WhatsApp config
        $opts = $this->db->select('name,value')->get($optionsTable)->result_array();
        $config = array_column($opts, 'value', 'name');

        if (empty($config['greenapi_instance_id']) || empty($config['greenapi_token'])) {
            echo json_encode(['success' => false, 'message' => 'WhatsApp configuration missing']);
            return;
        }

        $instance_id = $config['greenapi_instance_id'];
        $api_token   = $config['greenapi_token'];

        // Invoice get
        $invoice = $this->db->where('id', $invoice_id)->get($invoiceTable)->row();
        if (!$invoice) {
            echo json_encode(['success' => false, 'message' => 'Invoice not found']);
            return;
        }

        // Client get
        $client = $this->db->where('userid', $invoice->clientid)->get($clientsTable)->row();
        if (!$client || empty($client->phonenumber)) {
            echo json_encode(['success' => false, 'message' => 'Client not found or phone missing']);
            return;
        }

        $client_number = $client->phonenumber;

        // WhatsApp check
        $urlCheck = "https://api.green-api.com/waInstance{$instance_id}/checkWhatsapp/{$api_token}";
        $response = $this->curl_post($urlCheck, ['phoneNumber' => $client_number]);

        if (empty($response['existsWhatsapp'])) {
            echo json_encode(['success' => false, 'message' => 'Client is not using WhatsApp']);
            return;
        }

        // Prepare message
        $pdf_link = site_url('invoices/pdf/' . $invoice->id . '?output_type=I');
        $message = "Dear Customer, your invoice #{$invoice->id} is ready. Download: {$pdf_link}";

        // Send WhatsApp
        $urlSend = "https://api.green-api.com/waInstance{$instance_id}/sendMessage/{$api_token}";
        $res = $this->curl_post($urlSend, [
            'chatId' => "{$client_number}@c.us",
            'message' => $message
        ]);

        if ($res && isset($res['sent']) && $res['sent'] == true) {
            echo json_encode(['success' => true, 'message' => 'Invoice sent successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send invoice']);
        }
    }

    private function curl_post($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }
}
