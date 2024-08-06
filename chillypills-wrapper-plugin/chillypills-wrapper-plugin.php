<?php
/*
Plugin Name: Chillypills Wrapper Plugin
Description: Plugin principal para gestionar las licencias de otros plugins Chillypills y gestionar la instalación y activación de plugins.
Version: 1.1.0
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
        add_action('admin_post_chillypills_download_plugin', array($this, 'handle_download_plugin'));
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
                    <tr>
                        <td class="plugin-title">
                            <strong>Chillypills Wrapper Plugin</strong> (v1.1.0)
                        </td>
                        <td class="column-description">Plugin principal para gestionar las licencias de otros plugins Chillypills y gestionar la instalación y activación de plugins.</td>
                        <td class="column-actions">
                            <span class="button button-disabled">Activo</span>
                        </td>
                    </tr>
                    <?php foreach ($chillypills_plugins as $plugin_slug => $plugin_data): ?>
                        <?php
                        $plugin_file = $plugin_slug . '/' . $plugin_slug . '.php';
                        $plugin_info = isset($installed_plugins[$plugin_file]) ? $installed_plugins[$plugin_file] : null;
                        $plugin_version = isset($plugin_data['current_version']) ? $plugin_data['current_version'] : 'Desconocida';
                        ?>
                        <tr>
                            <td class="plugin-title">
                                <strong><?php echo esc_html($plugin_info['Name'] ?? $plugin_slug); ?></strong> (v<?php echo esc_html($plugin_version); ?>)
                            </td>
                            <td class="column-description"><?php echo esc_html($plugin_info['Description'] ?? $plugin_data['description']); ?></td>
                            <td class="column-actions">
                                <?php if ($plugin_info): ?>
                                    <?php if (is_plugin_active($plugin_file)): ?>
                                        <a href="<?php echo esc_url(wp_nonce_url('plugins.php?action=deactivate&plugin=' . $plugin_file, 'deactivate-plugin_' . $plugin_file)); ?>" class="button">Desactivar</a>
                                    <?php else: ?>
                                        <a href="<?php echo esc_url(wp_nonce_url('plugins.php?action=activate&plugin=' . $plugin_file, 'activate-plugin_' . $plugin_file)); ?>" class="button button-primary">Activar</a>
                                    <?php endif; ?>
                                    <a href="<?php echo esc_url(wp_nonce_url('plugins.php?action=delete-selected&checked[]=' . $plugin_file, 'bulk-plugins')); ?>" class="button button-secondary">Borrar</a>
                                <?php else: ?>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=chillypills_download_plugin')); ?>">
                                        <input type="hidden" name="plugin_slug" value="<?php echo esc_attr($plugin_slug); ?>">
                                        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('chillypills_download_plugin_' . $plugin_slug); ?>">
                                        <button type="submit" class="button button-primary">Descargar e Instalar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function handle_download_plugin() {
        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos suficientes para realizar esta acción.');
        }

        if (!isset($_POST['plugin_slug']) || !isset($_POST['_wpnonce'])) {
            wp_die('No se especificó el plugin o el nonce.');
        }

        $plugin_slug = sanitize_text_field($_POST['plugin_slug']);
        $nonce = sanitize_text_field($_POST['_wpnonce']);

        if (!wp_verify_nonce($nonce, 'chillypills_download_plugin_' . $plugin_slug)) {
            wp_die('Nonce inválido.');
        }

        $plugins_json_url = 'https://plugins-control.chillypills.com/plugins.json';
        $response = wp_remote_get($plugins_json_url);

        if (is_wp_error($response)) {
            wp_die('Error al obtener la lista de plugins: ' . esc_html($response->get_error_message()));
        }

        $plugins_data = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($plugins_data['plugins'][$plugin_slug])) {
            wp_die('El plugin especificado no se encuentra en la lista.');
        }

        $plugin_data = $plugins_data['plugins'][$plugin_slug];
        $download_url = isset($plugin_data['download_url']) ? esc_url_raw($plugin_data['download_url']) : '';

        if (empty($download_url)) {
            wp_die('No se ha facilitado una URL válida para la descarga.');
        }

        // Obtener el sistema de archivos de WordPress
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());
        if (!WP_Filesystem($creds)) {
            wp_die('No ha sido posible acceder al sistema de archivos.');
        }

        global $wp_filesystem;

        // Descargar el archivo ZIP
        $tmp_file = download_url($download_url);
        if (is_wp_error($tmp_file)) {
            wp_die('Error al descargar el plugin: ' . esc_html($tmp_file->get_error_message()));
        }

        // Definir el directorio temporal y el destino del plugin
        $destination = WP_PLUGIN_DIR . '/' . $plugin_slug;

        // Descomprimir el archivo ZIP
        $unzip_result = unzip_file($tmp_file, WP_PLUGIN_DIR);

        if (is_wp_error($unzip_result)) {
            $wp_filesystem->delete($tmp_file);
            wp_die('Error al instalar el plugin: ' . esc_html($unzip_result->get_error_message()));
        }

        // Eliminar el archivo temporal
        $wp_filesystem->delete($tmp_file);

        // Verificar si el plugin se ha instalado correctamente
        $plugin_file = $plugin_slug . '/' . $plugin_slug . '.php';
        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)) {
            wp_die('El archivo del plugin no se encontró después de la instalación.');
        }

        // Redirigir de nuevo a la página de gestión de plugins
        wp_redirect(admin_url('admin.php?page=chillypills-plugins-management&installed=' . $plugin_slug));
        exit;
    }

    public function enqueue_styles() {
        wp_enqueue_style('chillypills-wrapper-plugin', plugins_url('chillypills-wrapper-plugin.css', __FILE__));
    }
}

// Inicializar el plugin
new Chillypills_Wrapper_Plugin();
