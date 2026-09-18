<?php

namespace App\Models;

use CodeIgniter\Model;

class AdministradorModel extends Model
{
    protected $table            = 'administrador';
    protected $primaryKey       = 'id_administrador';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_profesional'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'id_profesional' => 'required|is_unique[administrador.id_profesional,id_administrador,{id_administrador}]'
    ];
    protected $validationMessages   = [
        'id_profesional' => [
            'is_unique' => 'Este profesional ya está registrado como administrador.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
