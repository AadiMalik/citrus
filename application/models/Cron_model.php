<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run()
    {
        // Load necessary models
        $this->load->model('invoices_model');
        $this->load->model('tasks_model');
        $this->load->model('proposals_model');
        $this->load->model('estimates_model');
        $this->load->model('contracts_model');
        $this->load->model('projects_model');

        // Generate recurring invoices
        $this->invoices_model->recurring_invoices();

        // Optional: Add other cron job executions if required
        // $this->tasks_model->send_task_reminders();
        // $this->proposals_model->send_proposal_expiry_reminders();
        // $this->estimates_model->send_expiry_reminders();
        // $this->contracts_model->notify_expiring_contracts();
        // $this->projects_model->send_project_reminders();

        // Log the cron activity
        log_activity('Cron job executed successfully: Recurring invoices checked.');
    }
}

