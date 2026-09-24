<?php

namespace App\Models;

use CodeIgniter\Model;

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

    // Dates
    protected $useTimestamps = false; // Manejado por DEFAULT CURRENT_TIMESTAMP en DB, pero si se quiere administrar desde CI, cambiar a true.
    // protected $createdField  = 'u_fRegistro'; // Si $useTimestamps = true

    // Validation
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

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Hashea el password antes de insertar o actualizar (RN03)
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['u_pass'])) {
            $data['data']['u_pass'] = password_hash($data['data']['u_pass'], PASSWORD_DEFAULT);
        }
        return $data;
    }
}
