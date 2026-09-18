<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoriaModel extends Model
{
    protected $table            = 'categoria';
    protected $primaryKey       = 'id_categoria';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'descripcion'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'nombre' => 'required|min_length[3]|max_length[100]|is_unique[categoria.nombre,id_categoria,{id_categoria}]'
    ];
    protected $validationMessages   = [
        'nombre' => [
            'is_unique' => 'Ya existe una categoría registrada con este nombre.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
