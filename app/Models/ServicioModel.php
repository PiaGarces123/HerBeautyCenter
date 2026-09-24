<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicioModel extends Model
{
    protected $table            = 'servicio';
    protected $primaryKey       = 's_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        's_nbre',
        's_desc',
        's_duracionMinutos',
        's_precio',
        'activo'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        's_nbre'           => 'required|min_length[3]|max_length[100]|is_unique[servicio.s_nbre,id_servicio,{s_id}]',
        's_duracionMinutos' => 'required|is_natural_no_zero',
        's_precio'           => 'required|numeric|greater_than[0]'
    ];
    protected $validationMessages   = [
        's_nbre' => [
            'is_unique' => 'Ya existe un servicio registrado con este nombre.'
        ],
        's_duracionMinutos' => [
            'is_natural_no_zero' => 'La duración debe ser un número entero mayor a cero.'
        ],
        's_precio' => [
            'greater_than' => 'El precio debe ser un número mayor a cero.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
