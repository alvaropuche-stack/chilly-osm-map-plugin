<?php
function validate_license($license_key, $site_url) {
    $response = wp_remote_get("https://plugins-control.chillypills.com/validate_license.php?license_key={$license_key}&site_url={$site_url}");
    if (is_wp_error($response)) {
        return ['success' => false, 'message' => 'Error connecting to the license server'];
    }

    $response_body = wp_remote_retrieve_body($response);
    $response_data = json_decode($response_body, true);
    return $response_data;
}

function check_update($plugin_name, $current_version) {
    $response = wp_remote_get("https://plugins-control.chillypills.com/check_update.php?plugin_name={$plugin_name}&current_version={$current_version}");
    if (is_wp_error($response)) {
        return ['success' => false, 'message' => 'Error connecting to the update server'];
    }

    $response_body = wp_remote_retrieve_body($response);
    $response_data = json_decode($response_body, true);
    return $response_data;
}
?>
