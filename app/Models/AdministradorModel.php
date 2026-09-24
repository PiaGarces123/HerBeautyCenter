<?php

namespace App\Models;

use CodeIgniter\Model;

class AdministradorModel extends Model
{
    protected $table            = 'administrador';
    protected $primaryKey       = 'a_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'a_pId'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'a_pId' => 'required|is_unique[administrador.a_pId,a_id,{a_id}]'
    ];
    protected $validationMessages   = [
        'a_pId' => [
            'is_unique' => 'Este profesional ya está registrado como administrador.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
