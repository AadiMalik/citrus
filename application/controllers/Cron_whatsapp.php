<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cron_whatsapp extends CI_Controller
{
    public function index()
    {
        $this->load->database();

        $companies = $this->db->get_where('tblperfex_saas_companies', ['auto_invoice_whatsapp' => 1])->result();

        foreach ($companies as $company) {
            $prefix = $company->slug; // e.g. jfswimming
            $optionsTable = $prefix . '_tbloptions';
            $invoiceTable = $prefix . '_tblinvoices';
            $clientsTable = $prefix . '_tblclients';

            // Load WhatsApp configuration
            $opts = $this->db->select('name, value')->get($optionsTable)->result_array();
            $config = array_column($opts, 'value', 'name');

            if (empty($config['greenapi_instance_id']) || empty($config['greenapi_token'])) {
                $this->log("{$company->name}: Missing WhatsApp credentials");
                continue;
            }

            $instance = $config['greenapi_instance_id'];
            $token = $config['greenapi_token'];
            $today = date('Y-m-d');

            // Reminder configurations
            $reminders = [
                ['days' => (int)($config['reminder1_invoice_day'] ?? 0), 'message' => $config['reminder1_invoice_message'] ?? ''],
                ['days' => (int)($config['reminder2_invoice_day'] ?? 0), 'message' => $config['reminder2_invoice_message'] ?? ''],
                ['days' => (int)($config['reminder3_invoice_day'] ?? 0), 'message' => $config['reminder3_invoice_message'] ?? ''],
                ['days' => (int)($config['reminder4_invoice_day'] ?? 0), 'message' => $config['reminder4_invoice_message'] ?? ''],
            ];

            // --- REMINDERS 1 → 4 ---
            if (!empty($config['auto_invoice_message']) && $config['auto_invoice_message'] == 1) {
                foreach ($reminders as $index => $rem) {
                    if ($rem['days'] <= 0 || empty($rem['message'])) continue;

                    // Optionally: filter unpaid invoices
                    $invoices = $this->db->where('status', 1)->get($invoiceTable)->result();

                    foreach ($invoices as $inv) {
                        if (empty($inv->duedate)) continue;

                        // Calculate due_date + reminder_days
                        $duePlusReminder = date('Y-m-d', strtotime($inv->duedate . " +{$rem['days']} days"));

                        // Send message if today matches
                        if ($today == $duePlusReminder) {
                            $this->send_whatsapp(
                                $inv,
                                $clientsTable,
                                $rem['message'],
                                $instance,
                                $token,
                                $company->slug
                            );
                            $this->log("{$company->name}: Reminder " . ($index + 1) . " sent for Invoice #{$inv->id}");
                        }
                    }
                }
            }
        }

        $this->log("Cron completed successfully.");
    }

    private function send_whatsapp($invoice, $clientsTable, $template, $instance, $token, $tag)
    {
        $CI = &get_instance();
        $client = $CI->db->where('userid', $invoice->clientid)->get($clientsTable)->row();
        if (!$client) return;

        $phone = preg_replace('/\D/', '', $client->phonenumber);

        // 1️⃣ Check WhatsApp existence
        $urlCheck = "https://api.green-api.com/waInstance{$instance}/checkWhatsapp/{$token}";
        $response = $this->curl_post($urlCheck, ['phoneNumber' => $phone]);
        if (empty($response['existsWhatsapp'])) {
            $this->log("{$tag}: {$phone} not found on WhatsApp");
            return;
        }

        // 2️⃣ Prepare message
        $msg = str_replace(
            ['{customer_name}', '{invoice_number}', '{due_date}'],
            [$client->company, $invoice->prefix . ' ' . $invoice->number, $invoice->duedate],
            $template
        );

        // 3️⃣ Send Message
        $urlSend = "https://api.green-api.com/waInstance{$instance}/sendMessage/{$token}";
        $res = $this->curl_post($urlSend, [
            'chatId' => "{$phone}@c.us",
            'message' => $msg
        ]);

        $this->log("{$tag}: Sent to {$phone} - Invoice #{$invoice->id}");
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

    private function log($msg)
    {
        file_put_contents(FCPATH . 'whatsapp_cron_log.txt', date('Y-m-d H:i:s') . " - $msg\n", FILE_APPEND);
    }
}
