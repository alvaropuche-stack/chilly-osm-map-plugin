<?php
/*
Plugin Name: OSM Map Plugin
Description: Plugin para mostrar un mapa de OpenStreetMap con direcciones configurables.
Version: 1.0.0
Author: Álvaro Puche Ortiz x Chillypills Comunicación S.L.
Author URI: https://chillypills.com
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(dirname(__FILE__)) . '/chillypills-wrapper-plugin/license-control.php';

// Función para comprobar actualizaciones
function osm_map_plugin_check_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    $plugin_name = 'osm-map-plugin';
    $current_version = '1.0.0';
    $response_data = Chillypills_License_Control::check_update($plugin_name, $current_version);

    if ($response_data['success'] && version_compare($response_data['version'], $current_version, '>')) {
        $transient->response[plugin_basename(__FILE__)] = (object) [
            'new_version' => $response_data['version'],
            'package' => $response_data['download_url'],
            'slug' => plugin_basename(__FILE__),
        ];
    }

    return $transient;
}
add_filter('pre_set_site_transient_update_plugins', 'osm_map_plugin_check_update');

// Añadir un menú de configuración en el administrador de WordPress
function osm_map_plugin_menu() {
    add_options_page(
        'Ajustes del mapa OSM',
        'Ajustes del mapa OSM',
        'manage_options',
        'osm-map-plugin',
        'osm_map_plugin_settings_page'
    );
}
add_action('admin_menu', 'osm_map_plugin_menu');

// Página de configuración del plugin
function osm_map_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h1>Ajustes del mapa OSM</h1>
        <form method="post" action="options.php">
            <?php settings_errors(); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Direcciones</th>
                    <td>
                        <textarea id="osm_map_plugin_addresses" name="osm_map_plugin_addresses" rows="5" cols="50"><?php echo esc_textarea(get_option('osm_map_plugin_addresses', '')); ?></textarea>
                        <p>Introduce cada dirección en una nueva línea.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Registrar ajustes del plugin
function osm_map_plugin_settings() {
    register_setting('osm_map_plugin_settings', 'osm_map_plugin_addresses', 'sanitize_textarea_field');

    add_settings_section(
        'osm_map_plugin_settings_section',
        'Ajustes del Mapa',
        null,
        'osm-map-plugin'
    );

    add_settings_field(
        'osm_map_plugin_addresses_field',
        'Direcciones',
        'osm_map_plugin_addresses_field_callback',
        'osm-map-plugin',
        'osm_map_plugin_settings_section'
    );
}
add_action('admin_init', 'osm_map_plugin_settings');

function osm_map_plugin_addresses_field_callback() {
    $addresses = get_option('osm_map_plugin_addresses', '');
    echo '<textarea id="osm_map_plugin_addresses" name="osm_map_plugin_addresses" rows="5" cols="50">' . esc_textarea($addresses) . '</textarea>';
    echo '<p>Introduce cada dirección en una nueva línea.</p>';
}

// Registrar el widget de Elementor
function register_osm_map_widget($widgets_manager) {
    require_once(__DIR__ . '/osm-map-widget.php');
    $widgets_manager->register(new \Elementor_Chillypills_OSM_Map_Widget());
}
add_action('elementor/widgets/register', 'register_osm_map_widget');

// Cargar archivos de Leaflet
function osm_map_plugin_enqueue_scripts() {
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), null, true);
    wp_enqueue_script('leaflet-geocoder-js', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js', array('leaflet-js'), null, true);
    wp_enqueue_style('leaflet-geocoder-css', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css');
}
add_action('wp_enqueue_scripts', 'osm_map_plugin_enqueue_scripts');
