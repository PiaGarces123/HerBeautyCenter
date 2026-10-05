<?php

// Importación de las clases necesarias del namespace de CodeIgniter
use CodeIgniter\Boot;
use Config\Paths;

/*
 *---------------------------------------------------------------
 * VERIFICACIÓN DE LA VERSIÓN DE PHP
 *---------------------------------------------------------------
 * Valida que el servidor cumpla con el requisito mínimo de PHP
 * necesario para ejecutar esta versión de CodeIgniter.
 */

// Versión mínima requerida de PHP (Si se actualiza, debe actualizarse también el archivo 'spark')
$minPhpVersion = '8.2';

if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    // Genera el mensaje de error informando la versión requerida y la versión actual instalada
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION,
    );

    // Devuelve un código de estado HTTP 503 (Servicio No Disponible)
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;

    // Finaliza la ejecución del script con código de error
    exit(1);
}

/*
 *---------------------------------------------------------------
 * CONFIGURACIÓN DEL DIRECTORIO ACTUAL DE TRABAJO
 *---------------------------------------------------------------
 * Define la ruta del controlador frontal y asegura que el script
 * se ejecute tomando como referencia esta carpeta raíz pública.
 */

// Define la constante FCPATH (Front Controller Path) apuntando al directorio actual
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Garantiza que el directorio de trabajo del proceso de PHP coincida con FCPATH
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 *---------------------------------------------------------------
 * ARRANQUE Y CARGA DE LA APLICACIÓN (BOOTSTRAP)
 *---------------------------------------------------------------
 * Este proceso establece las constantes de rutas, carga y registra
 * los autoloaders del sistema y Composer, carga las constantes
 * globales e inicia el entorno de la aplicación.
 */

// CARGA DEL ARCHIVO DE CONFIGURACIÓN DE RUTAS
// Esta línea especifica la ubicación de la configuración de rutas de la carpeta /app.
// Si mueves la estructura de carpetas de tu servidor, debes ajustar esta ruta.
require FCPATH . '../app/Config/Paths.php';

// Instancia la clase de rutas para acceder a las ubicaciones del sistema
$paths = new Paths();

// CARGA DEL ARCHIVO DE ARRANQUE DEL FRAMEWORK
// Requiere la clase Boot desde el directorio de sistema configurado
require $paths->systemDirectory . '/Boot.php';

// Ejecuta el arranque web de la aplicación pasando las rutas y devuelve la respuesta HTTP
exit(Boot::bootWeb($paths));