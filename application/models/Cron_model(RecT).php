public function create_recurring_invoices($manual = false)
{
    log_message('error', '🔁 [CRON] Starting recurring invoice generation...');

    $this->load->model('invoices_model');

    $this->db->where('recurring !=', 0);
    $recurring_invoices = $this->db->get('tblinvoices')->result_array();

    if (empty($recurring_invoices)) {
        log_message('error', '⚠️ No recurring invoice profiles found.');
        return;
    }

    foreach ($recurring_invoices as $invoice) {
        log_message('error', '➡️ Checking invoice ID: ' . $invoice['id']);

        if ((int)$invoice['is_recurring_from'] > 0) {
            log_message('error', '⏭️ Skipped - is_recurring_from is not 0');
            continue;
        }

        if ($invoice['recurring_type'] == '') {
            log_message('error', '⏭️ Skipped - Missing recurring_type');
            continue;
        }

        if ($invoice['last_recurring_date']) {
            $last = $invoice['last_recurring_date'];
        } else {
            $last = $invoice['date'];
        }

        $next_date = date('Y-m-d', strtotime('+' . $invoice['recurring'] . ' ' . $invoice['recurring_type'], strtotime($last)));
        log_message('error', '📅 Calculated next recurring date: ' . $next_date);

        if (date('Y-m-d') < $next_date) {
            log_message('error', '⏭️ Skipped - Today is before the next recurring date');
            continue;
        }

        if ((int)$invoice['clientid'] == 0) {
            log_message('error', '⏭️ Skipped - No client ID');
            continue;
        }

        if ((int)$invoice['addedfrom'] == 0) {
            log_message('error', '⏭️ Skipped - No addedfrom (staff) ID');
            continue;
        }

        $new_invoice_id = $this->invoices_model->copy($invoice['id'], [
            'copy_recurring' => true,
        ]);

        if ($new_invoice_id) {
            log_message('error', '✅ Recurring invoice created: ' . $new_invoice_id);

            $this->db->where('id', $invoice['id']);
            $this->db->update('tblinvoices', [
                'last_recurring_date' => date('Y-m-d'),
            ]);
        } else {
            log_message('error', '❌ Failed to create invoice from ID: ' . $invoice['id']);
        }
    }

    log_message('error', '✅ [CRON] Recurring invoice generation complete.');
}
