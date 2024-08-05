# Chillypills WordPress Plugins

Este repositorio contiene múltiples plugins de WordPress desarrollados y mantenidos por Chillypills Comunicación S.L. Cada plugin reside en su propio directorio y se gestiona de forma independiente para facilitar su desarrollo y despliegue.

## Plugins Disponibles

- **Chilly OSM Map Plugin** (`chilly-osm-map-plugin`): Plugin para mostrar un mapa de OpenStreetMap con direcciones configurables.

## Estructura del Repositorio

Cada plugin se encuentra en su propio directorio bajo la raíz del repositorio. La estructura del repositorio es la siguiente:

## Despliegue Automatizado

El repositorio está configurado para utilizar GitHub Actions para automatizar el proceso de despliegue de los plugins. Cada vez que se hace un push a la rama principal (`main`), se realiza lo siguiente:

1. **Empaquetado**: Cada directorio de plugin se empaqueta en un archivo ZIP.
2. **Subida**: Cada archivo ZIP se sube a un servidor SFTP.
3. **Actualización de `plugins.json`**: Se actualiza el archivo `plugins.json` en el servidor para reflejar la nueva versión del plugin.

### Configuración de GitHub Actions

El flujo de trabajo de GitHub Actions se define en `.github/workflows/deploy.yml`. Este flujo de trabajo:

- Empaqueta cada directorio de plugin en un archivo ZIP.
- Sube cada archivo ZIP al servidor SFTP.
- Actualiza `plugins.json` en el servidor.

### Secrets

Para que el despliegue automatizado funcione, asegúrate de configurar los siguientes secrets en tu repositorio de GitHub:

- **FTP_SERVER**: Dirección del servidor SFTP.
- **FTP_USERNAME**: Nombre de usuario para el servidor SFTP.
- **FTP_PASSWORD**: Contraseña para el servidor SFTP.
- **FTP_REMOTE_PATH**: Ruta remota en el servidor donde se subirán los archivos ZIP.

## Añadir un Nuevo Plugin

Para añadir un nuevo plugin al repositorio:

1. Crea un nuevo directorio en la raíz del repositorio con el nombre de tu plugin.
2. Añade los archivos del plugin en el nuevo directorio.
3. Asegúrate de seguir la estructura y convenciones de los otros plugins.
4. Realiza un commit y push de los cambios a la rama principal.

## Soporte

Para soporte, contacta a Chillypills Comunicación S.L. en [https://chillypills.com](https://chillypills.com).

## Licencia

Este repositorio y sus contenidos 
