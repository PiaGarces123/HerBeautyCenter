<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicioModel extends Model
{
    protected $table            = 'servicio';
    protected $primaryKey       = 'id_servicio';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_categoria',
        'nombre',
        'descripcion',
        'duracion_minutos',
        'precio',
        'activo'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'id_categoria'     => 'required|numeric',
        'nombre'           => 'required|min_length[3]|max_length[100]|is_unique[servicio.nombre,id_servicio,{id_servicio}]',
        'duracion_minutos' => 'required|is_natural_no_zero',
        'precio'           => 'required|numeric|greater_than[0]'
    ];
    protected $validationMessages   = [
        'nombre' => [
            'is_unique' => 'Ya existe un servicio registrado con este nombre.'
        ],
        'duracion_minutos' => [
            'is_natural_no_zero' => 'La duración debe ser un número entero mayor a cero.'
        ],
        'precio' => [
            'greater_than' => 'El precio debe ser un número mayor a cero.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
