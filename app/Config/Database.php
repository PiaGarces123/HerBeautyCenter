<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database
 * Her Beauty Center
 *
 * Configura las conexiones a las bases de datos utilizadas
 * por la aplicación.
 *
 * La conexión principal utiliza MySQLi para trabajar
 * con una base de datos MySQL/MariaDB.
 */
class Database extends Config
{
    /**
     * Directorio donde se encuentran las migraciones
     * y los archivos de semillas (Seeds).
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Grupo de conexión que se utilizará por defecto.
     */
    public string $defaultGroup = 'default';

    /**
     * Configuración de la conexión principal.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN' => '',
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'herbeautycenter_db',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug' => true,
        'charset' => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre' => '',
        'encrypt' => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port' => 3306,
        'numberNative' => false,
        'foundRows' => false,
        'dateFormat' => [
            'date' => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time' => 'H:i:s',
        ],
    ];

    /**
     * Configuración utilizada para las pruebas automatizadas.
     *
     * Utiliza SQLite en memoria para evitar que las pruebas
     * modifiquen accidentalmente los datos de producción.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN' => '',
        'hostname' => '127.0.0.1',
        'username' => '',
        'password' => '',
        'database' => ':memory:',
        'DBDriver' => 'SQLite3',
        'DBPrefix' => 'db_',
        'pConnect' => false,
        'DBDebug' => true,
        'charset' => 'utf8',
        'DBCollat' => '',
        'swapPre' => '',
        'encrypt' => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
        'port' => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'synchronous' => null,
        'dateFormat' => [
            'date' => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time' => 'H:i:s',
        ],
    ];

    /**
     * Constructor de la configuración.
     *
     * Si CodeIgniter se encuentra ejecutando pruebas automatizadas,
     * cambia la conexión predeterminada al grupo 'tests'.
     */
    public function __construct()
    {
        parent::__construct();

        // Evita utilizar la base de datos principal durante los tests.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
