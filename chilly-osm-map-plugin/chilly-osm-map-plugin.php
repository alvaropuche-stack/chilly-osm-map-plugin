<?php
/*
Plugin Name: Chilly OSM Map Plugin
Description: Plugin para mostrar un mapa de OpenStreetMap con direcciones configurables.
Version: 0.0.7
Author: Álvaro Puche Ortiz x Chillypills Comunicación S.L.
Author URI: https://chillypills.com
*/

if (!defined('ABSPATH')) {
    exit;
}

// Incluir plugin.php si la función is_plugin_active no está definida
if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

// Comprobar si el plugin Chillypills Wrapper está activo
if (!is_plugin_active('chillypills-wrapper-plugin/chillypills-wrapper-plugin.php')) {
    add_action('admin_notices', 'chilly_osm_map_plugin_dependency_error');
    function chilly_osm_map_plugin_dependency_error() {
        echo '<div class="error"><p>Chilly OSM Map Plugin requiere el plugin Chillypills Wrapper activo.</p></div>';
    }
    return;
}

// Incluir el archivo de control de licencia
require_once plugin_dir_path(dirname(__FILE__)) . 'chillypills-wrapper-plugin/license-control.php';

// Validar la licencia global
if (!Chillypills_License_Control::validate_global_license()) {
    add_action('admin_notices', 'chilly_osm_map_plugin_license_error');
    function chilly_osm_map_plugin_license_error() {
        echo '<div class="error"><p>Chilly OSM Map Plugin requiere una licencia global válida. Por favor, ingrese una licencia válida en la configuración del plugin Chillypills Wrapper.</p></div>';
    }
    return;
}

// Función para comprobar actualizaciones
function chilly_osm_map_plugin_check_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    $plugin_name = 'chilly-osm-map-plugin';
    $current_version = '0.0.7';
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
add_filter('pre_set_site_transient_update_plugins', 'chilly_osm_map_plugin_check_update');

// Añadir un menú de configuración en el administrador de WordPress
function chilly_osm_map_plugin_menu() {
    add_options_page(
        'Ajustes del mapa OSM',
        'Ajustes del mapa OSM',
        'manage_options',
        'chilly-osm-map-plugin',
        'chilly_osm_map_plugin_settings_page'
    );
}
add_action('admin_menu', 'chilly_osm_map_plugin_menu');

// Página de configuración del plugin
function chilly_osm_map_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h1>Ajustes del mapa OSM</h1>
        <form method="post" action="options.php">
            <?php settings_errors(); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Direcciones</th>
                    <td>
                        <textarea id="chilly_osm_map_plugin_addresses" name="chilly_osm_map_plugin_addresses" rows="5" cols="50"><?php echo esc_textarea(get_option('chilly_osm_map_plugin_addresses', '')); ?></textarea>
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
function chilly_osm_map_plugin_settings() {
    register_setting('chilly_osm_map_plugin_settings', 'chilly_osm_map_plugin_addresses', 'sanitize_textarea_field');

    add_settings_section(
        'chilly_osm_map_plugin_settings_section',
        'Ajustes del Mapa',
        null,
        'chilly-osm-map-plugin'
    );

    add_settings_field(
        'chilly_osm_map_plugin_addresses_field',
        'Direcciones',
        'chilly_osm_map_plugin_addresses_field_callback',
        'chilly-osm-map-plugin',
        'chilly_osm_map_plugin_settings_section'
    );
}
add_action('admin_init', 'chilly_osm_map_plugin_settings');

function chilly_osm_map_plugin_addresses_field_callback() {
    $addresses = get_option('chilly_osm_map_plugin_addresses', '');
    echo '<textarea id="chilly_osm_map_plugin_addresses" name="chilly_osm_map_plugin_addresses" rows="5" cols="50">' . esc_textarea($addresses) . '</textarea>';
    echo '<p>Introduce cada dirección en una nueva línea.</p>';
}

// Registrar el widget de Elementor
function register_chilly_osm_map_widget($widgets_manager) {
    require_once(__DIR__ . '/chilly-osm-map-widget.php');
    $widgets_manager->register(new \Elementor_Chillypills_OSM_Map_Widget());
}
add_action('elementor/widgets/register', 'register_chilly_osm_map_widget');

// Cargar archivos de Leaflet
function chilly_osm_map_plugin_enqueue_scripts() {
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), null, true);
    wp_enqueue_script('leaflet-geocoder-js', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js', array('leaflet-js'), null, true);
    wp_enqueue_style('leaflet-geocoder-css', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css');
}
add_action('wp_enqueue_scripts', 'chilly_osm_map_plugin_enqueue_scripts');
?>
