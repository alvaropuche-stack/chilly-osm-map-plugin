# Chillypills Wrapper Plugin

**Plugin principal para gestionar las licencias de otros plugins Chillypills y gestionar la instalación y activación de plugins.**

## Descripción

El Chillypills Wrapper Plugin es el núcleo de nuestra infraestructura de plugins. Gestiona las licencias de otros plugins desarrollados por Chillypills Comunicación S.L., así como la instalación, activación y actualización de estos plugins.

## Características

- Gestión centralizada de licencias.
- Instalación y activación de plugins desde una interfaz de administración.
- Comprobación y actualización automática de plugins.
- Integración con Elementor para widgets personalizados.

## Requisitos

- WordPress 5.0 o superior.
- PHP 7.4 o superior.

## Instalación

1. Sube el plugin al directorio `/wp-content/plugins/`.
2. Activa el plugin a través del menú ‘Plugins’ en WordPress.

## Uso

1. Navega a "Chillypills Plugins" en el menú de administración para gestionar tus plugins.
2. Ingresa tu clave de licencia en la página de ajustes de licencias.
3. Descarga, instala y activa los plugins desde la página de gestión de plugins.

## Arquitectura del Sistema de Plugins

El sistema de plugins de Chillypills se estructura en torno a un plugin principal (Chillypills Wrapper Plugin) que centraliza la gestión de licencias y la instalación de otros plugins. A continuación, se detalla la arquitectura y el flujo de trabajo para la creación y gestión de nuevos plugins.

### Chillypills Wrapper Plugin

- **Ajustes de Licencia**: Permite la introducción y validación de una clave de licencia que autoriza el uso de los plugins.
- **Gestión de Plugins**: Interfaz para descargar, instalar, activar y desactivar otros plugins desarrollados por Chillypills.
- **Comprobación de Actualizaciones**: Verifica si hay nuevas versiones de los plugins y permite actualizarlos.

### License-Control

- **Función de Validación de Licencia**: Verifica la validez de la licencia contra un servidor remoto.
- **Función de Comprobación de Actualizaciones**: Comprueba si hay actualizaciones disponibles para los plugins y devuelve los detalles de la actualización.

### Integración con Elementor

Los plugins pueden registrar widgets personalizados con Elementor para ofrecer funcionalidades avanzadas directamente desde el constructor visual de WordPress.

### Creación de Nuevos Plugins

Para crear nuevos plugins que se integren con el sistema de Chillypills, sigue estos pasos:

1. **Estructura del Plugin**: Cada plugin debe seguir la estructura estándar de WordPress.
my-plugin/
├── my-plugin.php
├── my-plugin-widget.php
├── license-control.php (opcional, si se necesita lógica específica. Las licencias las deberá controlar el wrapper que es un plugin obligatorio para poder instalar el resto de plugins.)
└── assets/
├── css/
└── js/


2. **Integración con el Wrapper**:
- Incluir `license-control.php` desde el Wrapper para gestionar la lógica de licencias.
- Implementar funciones para la comprobación de actualizaciones utilizando la clase `Chillypills_License_Control`.


3. **Registrar el Plugin en plugins.json**:
- Añadir una entrada para el nuevo plugin en el archivo `plugins.json` en el servidor de control de plugins.
```json
{
    "plugins": {
        "my-plugin": {
            "current_version": "1.0.0",
            "download_url": "https://plugins-control.chillypills.com/downloads/my-plugin.zip",
            "description": "Descripción de My Plugin."
        }
    }
}
```
4. **Implementar la Funcionalidad del Plugin**:
Añadir funciones de configuración en el administrador de WordPress.
Registrar cualquier widget de Elementor si es necesario.
Asegurar que las funciones de activación y desactivación del plugin se manejan correctamente.

### 5.**Prompt para la IA - Creación de Plugins de WordPress para Chillypills**
```Eres un asistente de desarrollo especializado en la creación de plugins de WordPress para Chillypills Comunicación S.L. Aquí tienes los detalles de cómo funciona nuestro sistema de plugins y cómo debes proceder para crear nuevos plugins:

### Arquitectura del Sistema de Plugins

Nuestro sistema de plugins está centralizado alrededor del "Chillypills Wrapper Plugin". Este plugin se encarga de gestionar las licencias de los otros plugins, así como de su instalación, activación y actualización. Aquí están los componentes clave:

1. **Chillypills Wrapper Plugin**:
   - Gestiona las licencias de los plugins.
   - Proporciona una interfaz de administración para instalar, activar y actualizar plugins.
   - Comprueba si hay actualizaciones disponibles para los plugins y facilita su actualización.

2. **License-Control**:
   - Valida las licencias contra nuestro servidor de control.
   - Comprueba las actualizaciones disponibles y proporciona detalles sobre las nuevas versiones.

3. **Integración con Elementor**:
   - Los plugins pueden registrar widgets personalizados con Elementor para ofrecer funcionalidades avanzadas en el constructor visual de WordPress.

### Pasos para Crear un Nuevo Plugin

1. **Estructura del Plugin**:
   - Cada plugin debe seguir la estructura estándar de WordPress:
     ```
     my-plugin/
     ├── my-plugin.php
     ├── my-plugin-widget.php (si aplica)
     ├── license-control.php (opcional, si se necesita lógica específica)
     └── assets/
         ├── css/
         └── js/
     ```

2. **Integración con el Wrapper**:
   - Incluir el archivo `license-control.php` del Wrapper para gestionar la lógica de licencias.
   - Implementar funciones para la comprobación de actualizaciones utilizando la clase `Chillypills_License_Control`.

3. **Registrar el Plugin en plugins.json**:
   - Añadir una entrada para el nuevo plugin en el archivo `plugins.json` en el servidor de control de plugins:
     ```json
     {
         "plugins": {
             "my-plugin": {
                 "current_version": "1.0.0",
                 "download_url": "https://plugins-control.chillypills.com/downloads/my-plugin.zip",
                 "description": "Descripción de My Plugin."
             }
         }
     }
     ```

4. **Implementar la Funcionalidad del Plugin**:
   - Añadir funciones de configuración en el administrador de WordPress.
   - Registrar cualquier widget de Elementor si es necesario.
   - Asegurar que las funciones de activación y desactivación del plugin se manejan correctamente.

### Ejemplo de Código

Aquí tienes un ejemplo de cómo debería verse el archivo principal de un nuevo plugin (my-plugin.php).:

```php
<?php
/*
Plugin Name: My Plugin
Description: Plugin para [funcionalidad específica].
Version: 1.0.0
Author: Chillypills Comunicación S.L.
Author URI: https://chillypills.com
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(dirname(__FILE__)) . '/chillypills-wrapper-plugin/license-control.php';

// Comprobar actualizaciones
function my_plugin_check_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    $plugin_name = 'my-plugin';
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
add_filter('pre_set_site_transient_update_plugins', 'my_plugin_check_update');

// Añadir un menú de configuración en el administrador de WordPress
function my_plugin_menu() {
    add_options_page(
        'Ajustes de My Plugin',
        'Ajustes de My Plugin',
        'manage_options',
        'my-plugin',
        'my_plugin_settings_page'
    );
}
add_action('admin_menu', 'my_plugin_menu');

// Página de configuración del plugin
function my_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h1>Ajustes de My Plugin</h1>
        <form method="post" action="options.php">
            <?php settings_errors(); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Ajuste 1</th>
                    <td><input type="text" name="my_plugin_option" value="<?php echo esc_attr(get_option('my_plugin_option', '')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Registrar ajustes del plugin
function my_plugin_settings() {
    register_setting('my_plugin_settings', 'my_plugin_option', 'sanitize_text_field');
}
add_action('admin_init', 'my_plugin_settings');
