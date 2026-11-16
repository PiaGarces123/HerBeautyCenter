<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UsuarioModel
 * Her Beauty Center
 *
 * Gestiona los datos generales de los usuarios del sistema
 * (tabla `usuario`).
 *
 * Esta tabla almacena la información común a los distintos
 * tipos de usuarios: clientes, profesionales y administradores.
 *
 * Sus responsabilidades principales son:
 * 1. Registrar y administrar los datos personales y de acceso de los usuarios.
 * 2. Validar la información ingresada antes de guardarla.
 * 3. Controlar que no existan cuentas con direcciones de correo duplicadas.
 * 4. Hashear las contraseñas antes de almacenarlas en la base de datos.
 * 5. Permitir gestionar el estado activo o inactivo de las cuentas.
 */

class UsuarioModel extends Model
{
    protected $table = 'usuario';
    protected $primaryKey = 'u_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'u_nbreCompleto',
        'u_correo',
        'u_tel',
        'u_avatar',
        'u_activo',
        'u_pass'
    ];

    // Desactiva la gestión automática de fechas de creación y actualización.
    // En este caso, la fecha de registro se administra desde la base de datos
    // mediante DEFAULT CURRENT_TIMESTAMP.
    protected $useTimestamps = false;

    // Reglas de validación para los datos que se desean registrar.
    protected $validationRules = [
        'u_nbreCompleto' => 'required|min_length[3]|max_length[150]',
        'u_correo' => 'required|valid_email|is_unique[usuario.u_correo,u_id,{u_id}]',
        'u_tel' => 'required|min_length[8]',
        'u_pass' => 'required|min_length[8]' // Solo al crear es requerida usualmente, se puede ajustar en lógica
    ];
    protected $validationMessages = [
        'u_correo' => [
            'is_unique' => 'Correo Electrónico ya registrado'
        ]
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // CALLBACKS
    // Permite ejecutar funciones automáticamente durante las operaciones
    // de inserción y actualización del modelo.
    protected $allowCallbacks = true;

    // Ejecuta hashPassword() antes de insertar un nuevo usuario.
    protected $beforeInsert = ['hashPassword'];

    // Ejecuta hashPassword() antes de actualizar un usuario.
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Hashea la contraseña antes de almacenarla en la base de datos.
     *
     * Se ejecuta automáticamente mediante los callbacks
     * beforeInsert y beforeUpdate.
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['u_pass'])) {
            $data['data']['u_pass'] = password_hash($data['data']['u_pass'], PASSWORD_DEFAULT);
        }
        return $data;
    }
}
