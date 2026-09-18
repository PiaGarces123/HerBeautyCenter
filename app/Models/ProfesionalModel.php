<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfesionalModel extends Model
{
    protected $table            = 'profesional';
    protected $primaryKey       = 'id_profesional';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_usuario',
        'titulo',
        'descripcion',
        'anio_inicio_actividades'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'id_usuario'              => 'required|is_unique[profesional.id_usuario,id_profesional,{id_profesional}]',
        'anio_inicio_actividades' => 'permit_empty|exact_length[4]|numeric'
    ];
    protected $validationMessages   = [
        'id_usuario' => [
            'is_unique' => 'Este usuario ya está registrado como profesional.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
