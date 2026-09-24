<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfesionalModel extends Model
{
    protected $table            = 'profesional';
    protected $primaryKey       = 'p_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'u_id',
        'p_titulo',
        'p_desc',
        'p_anioInicioAct'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'u_id'              => 'required|is_unique[profesional.u_id,p_id,{p_id}]',
        'p_anioInicioAct' => 'permit_empty|exact_length[4]|numeric'
    ];
    protected $validationMessages   = [
        'u_id' => [
            'is_unique' => 'Este usuario ya está registrado como profesional.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
