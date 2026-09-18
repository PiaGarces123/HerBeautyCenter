<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfesionalServicioModel extends Model
{
    protected $table            = 'profesional_servicio';
    // Nota: Esta tabla tiene una clave primaria compuesta (id_profesional, id_servicio).
    // CodeIgniter 4 no soporta claves primarias compuestas nativamente en el Model para operaciones como find(),
    // pero se pueden usar los constructores de consultas (Query Builder) para insertar/eliminar.
    // Lo ideal es no usar operaciones que dependan de un solo $primaryKey.
    // Dejaremos returnType y protectFields pero omitiremos primaryKey.
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_profesional',
        'id_servicio'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'id_profesional' => 'required|numeric',
        'id_servicio'    => 'required|numeric'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
