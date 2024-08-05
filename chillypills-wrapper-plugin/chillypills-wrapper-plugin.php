<?php
/*
Plugin Name: Chillypills Wrapper Plugin
Description: Plugin principal para gestionar las licencias de otros plugins Chillypills y gestionar la instalación y activación de plugins.
Version: 1.0.0
Author: Álvaro Puche Ortiz x Chillypills Comunicación S.L.
Author URI: https://chillypills.com
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'license-control.php';

class Chillypills_Wrapper_Plugin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'create_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    public function create_menu() {
        add_menu_page(
            'Ajustes de Plugins Chillypills',
            'Chillypills Plugins',
            'manage_options',
            'chillypills-wrapper-plugin',
            array($this, 'settings_page'),
            'dashicons-admin-plugins',
            60
        );
        add_submenu_page(
            'chillypills-wrapper-plugin',
            'Gestión de Licencia',
            'Gestión de Licencia',
            'manage_options',
            'chillypills-wrapper-plugin',
            array($this, 'settings_page')
        );
        add_submenu_page(
            'chillypills-wrapper-plugin',
            'Gestión de Plugins',
            'Gestión de Plugins',
            'manage_options',
            'chillypills-plugins-management',
            array($this, 'plugins_management_page')
        );
    }

    public function register_settings() {
        register_setting('chillypills_wrapper_plugin_settings', 'chillypills_license_key', 'sanitize_text_field');
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Ajustes de Licencia Chillypills</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('chillypills_wrapper_plugin_settings');
                do_settings_sections('chillypills-wrapper-plugin');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Clave de licencia</th>
                        <td><input type="text" name="chillypills_license_key" value="<?php echo esc_attr(get_option('chillypills_license_key')); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function plugins_management_page() {
        $plugins_json_url = 'https://plugins-control.chillypills.com/plugins.json'; // URL del archivo plugins.json
        $response = wp_remote_get($plugins_json_url);

        if (is_wp_error($response)) {
            echo '<div class="error"><p>Error al obtener la lista de plugins.</p></div>';
            return;
        }

        $plugins_data = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($plugins_data['plugins'])) {
            echo '<div class="error"><p>No se encontró la información de plugins.</p></div>';
            return;
        }

        $chillypills_plugins = $plugins_data['plugins'];
        $installed_plugins = get_plugins();

        ?>
        <div class="wrap">
            <h1>Gestión de Plugins Chillypills</h1>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th scope="col" id="name" class="manage-column column-name">Plugin</th>
                        <th scope="col" id="description" class="manage-column column-description">Descripción</th>
                        <th scope="col" id="actions" class="manage-column column-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chillypills_plugins as $plugin_slug => $plugin_data): ?>
                        <?php
                        $plugin_file = $plugin_slug . '/' . $plugin_slug . '.php';
                        $plugin_info = isset($installed_plugins[$plugin_file]) ? $installed_plugins[$plugin_file] : null;
                        ?>
                        <tr>
                            <td class="plugin-title">
                                <strong><?php echo esc_html($plugin_info['Name'] ?? $plugin_slug); ?></strong>
                            </td>
                            <td class="column-description"><?php echo esc_html($plugin_info['Description'] ?? 'No disponible'); ?></td>
                            <td class="column-actions">
                                <?php if (is_plugin_active($plugin_file)): ?>
                                    <a href="<?php echo esc_url(wp_nonce_url('plugins.php?action=deactivate&amp;plugin=' . $plugin_file, 'deactivate-plugin_' . $plugin_file)); ?>" class="button">Desactivar</a>
                                <?php else: ?>
                                    <a href="<?php echo esc_url(wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin_file, 'activate-plugin_' . $plugin_file)); ?>" class="button button-primary">Activar</a>
                                <?php endif; ?>
                                <a href="<?php echo esc_url(wp_nonce_url('plugins.php?action=delete-selected&amp;checked[]=' . $plugin_file, 'bulk-plugins')); ?>" class="button button-secondary">Borrar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function enqueue_styles() {
        wp_enqueue_style('chillypills-wrapper-plugin', plugins_url('css/main.css', __FILE__));
    }
}

new Chillypills_Wrapper_Plugin();
