<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'usuario';
    protected $primaryKey       = 'id_usuario';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre_completo', 
        'correo', 
        'telefono', 
        'dni',
        'avatar',
        'activo',
        'password'
    ];

    // Dates
    protected $useTimestamps = false; // Manejado por DEFAULT CURRENT_TIMESTAMP en DB, pero si se quiere administrar desde CI, cambiar a true.
    // protected $createdField  = 'fecha_registro'; // Si $useTimestamps = true

    // Validation
    protected $validationRules      = [
        'nombre_completo' => 'required|min_length[3]|max_length[150]',
        'correo'          => 'required|valid_email|is_unique[usuario.correo,id_usuario,{id_usuario}]',
        'dni'             => 'permit_empty|is_unique[usuario.dni,id_usuario,{id_usuario}]',
        'password'        => 'required|min_length[8]' // Solo al crear es requerida usualmente, se puede ajustar en lógica
    ];
    protected $validationMessages   = [
        'correo' => [
            'is_unique' => 'Lo sentimos, este correo electrónico ya se encuentra registrado. (RN01)'
        ],
        'dni' => [
            'is_unique' => 'Este DNI ya está asociado a un usuario. (RN02)'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

    /**
     * Hashea el password antes de insertar o actualizar (RN03)
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }
}
