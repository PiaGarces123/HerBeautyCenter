<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ProfesionalServicioModel
 * Her Beauty Center
 *
 * Gestiona la relación entre los profesionales y los servicios
 * que cada uno está habilitado para realizar
 * (tabla `profesional_servicio`).
 *
 * Esta tabla intermedia permite establecer una relación
 * de muchos a muchos entre profesionales y servicios.
 *
 * Sus responsabilidades principales son:
 * 1. Registrar la asociación entre un profesional y un servicio.
 * 2. Validar que ambos identificadores sean obligatorios y numéricos.
 * 3. Permitir administrar las asociaciones mediante consultas
 *    construidas con Query Builder.
 */
class ProfesionalServicioModel extends Model
{
    protected $table = 'profesional_servicio';
    // Nota: Esta tabla tiene una clave primaria compuesta (id_profesional, id_servicio).
    // CodeIgniter 4 no soporta claves primarias compuestas nativamente en el Model para operaciones como find(),
    // pero se pueden usar los constructores de consultas (Query Builder) para insertar/eliminar.
    // Lo ideal es no usar operaciones que dependan de un solo $primaryKey.
    // Dejaremos returnType y protectFields pero omitiremos primaryKey.
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'p_id',
        'p_sId'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules = [
        'p_id' => 'required|numeric',
        'p_sId' => 'required|numeric'
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}
