<?php
if (!defined('ABSPATH')) {
    exit;
}

class Chillypills_License_Control {

    public static function validate_license($license_key = '') {
        if (empty($license_key)) {
            $license_key = get_option('chillypills_license_key');
        }
        $site_url = get_site_url();
        $response = wp_remote_get("https://plugins-control.chillypills.com/validate_license.php?license_key={$license_key}&site_url={$site_url}");
        if (is_wp_error($response)) {
            return false;
        }
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        return $response_data['success'];
    }

    public static function validate_global_license() {
        $json_file_path = plugin_dir_path(__FILE__) . 'plugins.json';
        if (file_exists($json_file_path)) {
            $json_data = file_get_contents($json_file_path);
            $plugins = json_decode($json_data, true);
            $global_license_key = $plugins['global_license_key'] ?? '';

            // Aquí puedes realizar una validación de la licencia global
            // Por ejemplo, podrías hacer una solicitud a un servidor remoto para validar la licencia
            $site_url = get_site_url();
            $response = wp_remote_get("https://plugins-control.chillypills.com/validate_license.php?license_key={$global_license_key}&site_url={$site_url}");
            if (is_wp_error($response)) {
                return false;
            }
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);
            return $response_data['success'];
        }
        return false;
    }

    public static function check_update($plugin_name, $current_version) {
        $response = wp_remote_get("https://plugins-control.chillypills.com/check_update.php?plugin_name={$plugin_name}&current_version={$current_version}");
        if (is_wp_error($response)) {
            return ['success' => false];
        }
        return json_decode(wp_remote_retrieve_body($response), true);
    }
}
?>
