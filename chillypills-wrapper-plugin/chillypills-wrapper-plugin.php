<?php
/*
Plugin Name: Chillypills Wrapper Plugin
Description: Plugin principal para gestionar la instalación y activación de otros plugins Chillypills.
Version: 0.0.2
Author: Álvaro Puche Ortiz x Chillypills Comunicación S.L.
Author URI: https://chillypills.com
*/

if (!defined('ABSPATH')) {
    exit;
}

class Chillypills_Wrapper_Plugin {

    public function __construct() {
        add_action('admin_menu', array($this, 'create_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('pre_set_site_transient_update_plugins', array($this, 'check_for_plugin_updates'));
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
            'Gestión de Plugins',
            'Gestión de Plugins',
            'manage_options',
            'chillypills-plugins-management',
            array($this, 'plugins_management_page')
        );
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Ajustes de Plugins Chillypills</h1>
            <p>Gestiona tus plugins de Chillypills desde aquí.</p>
        </div>
        <?php
    }

    public function plugins_management_page() {
        $plugins_json_url = 'https://plugins-control.chillypills.com/plugins.json';

        $plugins_response = wp_remote_get($plugins_json_url);

        if (is_wp_error($plugins_response)) {
            echo '<div class="error"><p>Error al obtener la información de plugins.</p></div>';
            return;
        }

        $plugins_data = json_decode(wp_remote_retrieve_body($plugins_response), true);

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
                        $is_installed = isset($installed_plugins[$plugin_file]);
                        $is_active = $is_installed && is_plugin_active($plugin_file);
                        $plugin_version = isset($plugin_data['current_version']) ? $plugin_data['current_version'] : 'Desconocida';
                        ?>
                        <tr>
                            <td class="plugin-title">
                                <strong><?php echo esc_html($plugin_data['name']); ?></strong>
                            </td>
                            <td class="column-description"><?php echo esc_html($plugin_data['description']); ?></td>
                            <td class="column-actions">
                                <?php if ($is_installed): ?>
                                    <?php if ($is_active): ?>
                                        <span class="button button-disabled">Activo</span>
                                    <?php else: ?>
                                        <a href="<?php echo esc_url(wp_nonce_url('plugins.php?action=activate&plugin=' . $plugin_file, 'activate-plugin_' . $plugin_file)); ?>" class="button button-primary">Activar</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="<?php echo esc_url($plugin_data['download_url']); ?>" class="button button-primary" download>Descargar e Instalar</a>
                                <?php endif; ?>
                                <?php if ($is_installed && version_compare($installed_plugins[$plugin_file]['Version'], $plugin_version, '<')): ?>
                                    <a href="<?php echo esc_url($plugin_data['download_url']); ?>" class="button button-secondary" download>Actualizar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function check_for_plugin_updates($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $plugins_json_url = 'https://plugins-control.chillypills.com/plugins.json';
        $response = wp_remote_get($plugins_json_url);

        if (is_wp_error($response)) {
            return $transient;
        }

        $plugins_data = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($plugins_data['plugins'])) {
            return $transient;
        }

        foreach ($plugins_data['plugins'] as $plugin_slug => $plugin_info) {
            $plugin_file = $plugin_slug . '/' . $plugin_slug . '.php';
            if (isset($transient->checked[$plugin_file]) && version_compare($plugin_info['current_version'], $transient->checked[$plugin_file], '>')) {
                $transient->response[$plugin_file] = (object) [
                    'slug' => $plugin_file,
                    'new_version' => $plugin_info['current_version'],
                    'package' => $plugin_info['download_url'],
                ];
            }
        }

        return $transient;
    }

    public function enqueue_styles() {
        wp_enqueue_style('chillypills-wrapper-plugin', plugins_url('chillypills-wrapper-plugin.css', __FILE__));
    }
}

// Inicializar el plugin
new Chillypills_Wrapper_Plugin();
