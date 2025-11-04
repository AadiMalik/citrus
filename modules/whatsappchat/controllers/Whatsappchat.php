<?php

defined('BASEPATH') or exit('No direct script access allowed');

class whatsappchat extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!is_admin()) {
            access_denied('WhatsApp Chat');
        }
        //\modules\whatsappchat\core\Apiinit::ease_of_mind('whatsappchat');
		//\modules\whatsappchat\core\Apiinit::the_da_vinci_code('whatsappchat');
        $this->load->helper('/whatsappchat');
    }

    public function index()
    {
        $data['title'] = _l('whatsappchat');
        $this->load->view('whatsappchat', $data);
        //\modules\whatsappchat\core\Apiinit::ease_of_mind('whatsappchat');
		//\modules\whatsappchat\core\Apiinit::the_da_vinci_code('whatsappchat');
    }

    public function reset()
    {
        update_option('whatsappchat', 'enable');
        redirect(admin_url('whatsappchat'));
    }

    public function save()
    {
        hooks()->do_action('before_save_whatsappchat');

        foreach(['admin_area','clients_area','clients_and_admin'] as $css_area) {
            // Also created the variables
            $$css_area = $this->input->post($css_area, FALSE);
            $$css_area = trim($$css_area);
            $$css_area = nl2br($$css_area);
        }

        update_option('whatsappchat_admin_area', $admin_area);
        update_option('whatsappchat_clients_area', $clients_area);
        update_option('whatsappchat_clients_and_admin_area', $clients_and_admin);
    }

    public function enable()
    {
        hooks()->do_action('before_save_whatsappchat');

        update_option('whatsappchat', 'enable');
    }

    public function disable()
    {
        hooks()->do_action('before_save_whatsappchat');

        update_option('whatsappchat', 'disable');
    }
}
