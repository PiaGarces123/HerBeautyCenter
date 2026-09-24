<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use CodeIgniter\Boot;
use Config\Paths;

/*
 *---------------------------------------------------------------
 * Archivo de muestra para Precarga (Preloading)
 *---------------------------------------------------------------
 * Más información en: https://www.php.net/manual/es/opcache.preloading.php
 *
 * Cómo usarlo:
 *   0. Copia este archivo a la carpeta raíz de tu proyecto.
 *   1. Configura la lista de archivos (la propiedad $paths de abajo).
 *   2. Activa opcache.preload en tu archivo php.ini de la siguiente manera:
 *     php.ini:
 *     opcache.preload=/ruta/a/tu/preload.php
 *
 * (Nota: Esto sirve para cargar todo en memoria y hacer la web súper rápida)
 */

// Carga el archivo de configuración de rutas
require __DIR__ . '/app/Config/Paths.php';

// Ruta al Front Controller (El portero principal del sistema)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

class preload
{
    /**
     * @var array Rutas a los archivos que queremos precargar en la RAM.
     */
    private array $paths = [
        [
            'include' => __DIR__ . '/vendor/codeigniter4/framework/system', // Change this path if using manual installation
            'exclude' => [
                // Not needed if you don't use them.
                '/system/Database/OCI8/',
                '/system/Database/Postgre/',
                '/system/Database/SQLite3/',
                '/system/Database/SQLSRV/',
                // Not needed for web apps.
                '/system/Database/Seeder.php',
                '/system/Test/',
                '/system/CLI/',
                '/system/Commands/',
                '/system/Publisher/',
                '/system/ComposerScripts.php',
                // Not Class/Function files.
                '/system/Config/Routes.php',
                '/system/Language/',
                '/system/bootstrap.php',
                '/system/util_bootstrap.php',
                '/system/rewrite.php',
                '/Views/',
                // Errors occur.
                '/system/ThirdParty/',
            ],
        ],
    ];

    public function __construct()
    {
        $this->loadAutoloader();
    }

    private function loadAutoloader(): void
    {
        $paths = new Paths();
        require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'Boot.php';

        Boot::preload($paths);
    }

    /**
     * Load PHP files.
     */
    public function load(): void
    {
        foreach ($this->paths as $path) {
            $directory = new RecursiveDirectoryIterator($path['include']);
            $fullTree  = new RecursiveIteratorIterator($directory);
            $phpFiles  = new RegexIterator(
                $fullTree,
                '/.+((?<!Test)+\.php$)/i',
                RecursiveRegexIterator::GET_MATCH,
            );

            foreach ($phpFiles as $key => $file) {
                foreach ($path['exclude'] as $exclude) {
                    if (str_contains($file[0], $exclude)) {
                        continue 2;
                    }
                }

                require_once $file[0];
                // Uncomment only for debugging (to inspect which files are included).
                // Never use this in production - preload scripts must not generate output.
                // echo 'Loaded: ' . $file[0] . "\n";
            }
        }
    }
}

(new preload())->load();
