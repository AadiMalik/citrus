<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: WhatsApp Chat
Module URI: https://codecanyon.net/item/whatsapp-module-for-perfex-crm/24915950
Description: WhatsApp chat module for Perfex CRM
Version: 1.0
Requires at least: 2.3.*
*/

define('WHATSAPPCHAT_MODULE', 'whatsappchat');
require_once __DIR__.'/vendor/autoload.php';
//modules\whatsappchat\core\Apiinit::the_da_vinci_code(WHATSAPPCHAT_MODULE);
//modules\whatsappchat\core\Apiinit::ease_of_mind(WHATSAPPCHAT_MODULE);

$CI = &get_instance();

/**
 * Load the module helper
 */
$CI->load->helper(WHATSAPPCHAT_MODULE . '/whatsappchat');

/**
 * Register activation module hook
 */
register_activation_hook(WHATSAPPCHAT_MODULE, 'whatsappchat_activation_hook');

function whatsappchat_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(WHATSAPPCHAT_MODULE, [WHATSAPPCHAT_MODULE]);

/**
 * Actions for inject the custom styles
 */
hooks()->add_action('app_admin_footer', 'whatsappchat_admin_head');
hooks()->add_action('app_customers_footer', 'whatsappchat_clients_area_head');
hooks()->add_filter('module_whatsappchat_action_links', 'module_whatsappchat_action_links');
hooks()->add_action('admin_init', 'whatsappchat_init_menu_items');
if (get_option('whatsappchat') == 'enable') {
    hooks()->add_action('app_customers_footer', 'whatsappchat_assets');
    hooks()->add_action('app_admin_footer', 'whatsappchat_assets');
}

/**
 * Chat assets
 * @return stylesheet / script
 */
function whatsappchat_assets()
{
    echo '<link href="' . base_url('modules/whatsappchat/assets/style.css') . '"  rel="stylesheet" type="text/css" >';
}

/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
function module_whatsappchat_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('whatsappchat') . '">' . _l('settings') . '</a>';

    return $actions;
}
/**
 * Admin area applied styles
 * @return null
 */
function whatsappchat_admin_head()
{
    whatsappchat_script('whatsappchat_admin_area');
}

/**
 * Clients area theme applied styles
 * @return null
 */
function whatsappchat_clients_area_head()
{
    if(is_client_logged_in()) {
        whatsappchat_script('whatsappchat_clients_area');
    }
}

/**
 * Detect mobile users and provide a different link URL, related to the app
 * @return null
 */
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

/**
 * Custom CSS
 * @param  string $main_area clients or admin area options
 * @return null
 */
function whatsappchat_script($main_area)
{

    if(isMobile()){
        $service = 'api.whatsapp.com';
    }
    else {
        $service = 'web.whatsapp.com';
    }


    $clients_or_admin_area = get_option($main_area);
    if (get_option('whatsappchat') == 'enable') {
        $whatsappchat_admin_and_clients_area = get_option('whatsappchat_clients_and_admin_area');
        if (!empty($clients_or_admin_area) || !empty($whatsappchat_admin_and_clients_area)) {
            if (!empty($clients_or_admin_area)) {
                $clients_or_admin_area = html_entity_decode(clear_textarea_breaks($clients_or_admin_area));
                $clients_or_admin_area = str_replace("+", "", $clients_or_admin_area);
                echo '<div class="integration">
                <a target="_blank" href="https://' . $service . '/send?phone=' . $clients_or_admin_area . '">
                <div class="whatsapp-message">
                <img class="whatsapp-image" src="' . base_url('modules/whatsappchat/assets/chaticon.svg') . '">
                </div>
                </a>
                </div>';
            }
            if (!empty($whatsappchat_admin_and_clients_area)) {
                $whatsappchat_admin_and_clients_area = html_entity_decode(clear_textarea_breaks($whatsappchat_admin_and_clients_area));
                $whatsappchat_admin_and_clients_area = str_replace("+", "", $whatsappchat_admin_and_clients_area);
                echo '<div class="integration">
                <a target="_blank" href="https://' . $service . '/send?phone=' . $whatsappchat_admin_and_clients_area . '">
                <div class="whatsapp-message">
                <img class="whatsapp-image" src="' . base_url('modules/whatsappchat/assets/chaticon.svg') . '">
                </div>
                </a>
                </div>';
            }
        }
    }
}

/**
 * Init theme style module menu items in setup in admin_init hook
 * @return null
 */
function whatsappchat_init_menu_items()
{
    if (is_admin()) {
        $CI = &get_instance();
        /**
         * If the logged in user is administrator, add custom menu in Setup
         */
        $CI->app_menu->add_setup_menu_item('whatsapp-chat', [
            'href'     => admin_url('whatsappchat'),
            'name'     => _l('whatsappchat'),
            'position' => 66,
        ]);
    }
}


hooks()->add_action('app_init', WHATSAPPCHAT_MODULE.'_actLib');
function whatsappchat_actLib()
{
    $CI = &get_instance();
    $CI->load->library(WHATSAPPCHAT_MODULE.'/Whatsappchat_aeiou');
    $envato_res = $CI->whatsappchat_aeiou->validatePurchase(WHATSAPPCHAT_MODULE);
    if ($envato_res) {
        set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
    }
}

hooks()->add_action('pre_activate_module', WHATSAPPCHAT_MODULE.'_sidecheck');
function whatsappchat_sidecheck($module_name)
{
   /**
    if (WHATSAPPCHAT_MODULE == $module_name['system_name']) {
        modules\whatsappchat\core\Apiinit::activate($module_name);
    }
    */
}

hooks()->add_action('pre_deactivate_module', WHATSAPPCHAT_MODULE.'_deregister');
function whatsappchat_deregister($module_name)
{
    if (WHATSAPPCHAT_MODULE == $module_name['system_name']) {
        delete_option(WHATSAPPCHAT_MODULE.'_verification_id');
        delete_option(WHATSAPPCHAT_MODULE.'_last_verification');
        delete_option(WHATSAPPCHAT_MODULE.'_product_token');
        delete_option(WHATSAPPCHAT_MODULE.'_heartbeat');
    }
}
