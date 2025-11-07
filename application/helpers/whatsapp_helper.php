<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * WhatsApp Settings Tab Register
 */
hooks()->add_filter('settings_tabs', 'register_whatsapp_tab');

function register_whatsapp_tab($tabs)
{
    $CI = &get_instance();
    $current_url = current_full_url();

    // Extract slug from URL (e.g. citrus/jfswimming/ps)
    $slug = null;
    if (preg_match('#citrus/([^/]+)/ps#', $current_url, $matches)) {
        $slug = $matches[1];
    }

    // Debugging
    file_put_contents(APPPATH . 'tabs_debug.txt',
        date('Y-m-d H:i:s') . " - URL: $current_url | Slug: $slug\n",
        FILE_APPEND
    );

    if (!$slug) return $tabs;

    // Database connection
    $hostname = $CI->db->hostname;
    $username = $CI->db->username;
    $password = $CI->db->password;
    $database = $CI->db->database;

    $conn = new mysqli($hostname, $username, $password, $database);
    if ($conn->connect_error) {
        file_put_contents(APPPATH . 'tabs_debug.txt',
            "DB Connection failed: " . $conn->connect_error . "\n",
            FILE_APPEND
        );
        return $tabs;
    }

    $query = "SELECT metadata FROM tblperfex_saas_companies WHERE slug = '$slug' LIMIT 1";
    $result = $conn->query($query);
    $company = $result ? $result->fetch_assoc() : null;

    if ($company && isset($company['metadata'])) {
        // Decode metadata twice (double encoded JSON)
        $decoded = json_decode($company['metadata'], true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        $auto_invoice_whatsapp = isset($decoded['auto_invoice_whatsapp'])
            ? (int)$decoded['auto_invoice_whatsapp']
            : 0;

        file_put_contents(APPPATH . 'tabs_debug.txt',
            date('Y-m-d H:i:s') . " - Slug: $slug | auto_invoice_whatsapp: {$auto_invoice_whatsapp}\n",
            FILE_APPEND
        );

        // Only add tab if enabled
        if ($auto_invoice_whatsapp === 1) {
            $tabs['whatsapp'] = [
                'slug'     => 'whatsapp',
                'name'     => 'WhatsApp',
                'icon'     => 'fa-brands fa-whatsapp',
                'view'     => 'admin/settings/includes/whatsapp',
                'position' => 100,
            ];
        }
    } else {
        file_put_contents(APPPATH . 'tabs_debug.txt',
            date('Y-m-d H:i:s') . " - No metadata found for slug: {$slug}\n",
            FILE_APPEND
        );
    }

    $conn->close();
    return $tabs;
}
