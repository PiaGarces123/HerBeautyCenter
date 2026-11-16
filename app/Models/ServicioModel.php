<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ServicioModel
 * Her Beauty Center
 *
 * Gestiona los servicios ofrecidos por el centro de estética
 * (tabla `servicio`).
 *
 * Almacena la información necesaria para describir cada servicio,
 * establecer su duración, precio y disponibilidad.
 *
 * Sus responsabilidades principales son:
 * 1. Registrar y administrar los servicios del centro de estética.
 * 2. Validar los datos de cada servicio antes de guardarlos.
 * 3. Controlar que no existan servicios con nombres duplicados.
 * 4. Gestionar el estado activo o inactivo de los servicios.
 * 5. Mantener el orden de visualización de los servicios.
 */
class ServicioModel extends Model
{
    protected $table = 'servicio';
    protected $primaryKey = 's_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        's_nbre',
        's_desc',
        's_duracionMinutos',
        's_precio',
        's_activo',
        's_orden'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules = [
        's_nbre' => 'required|min_length[3]|max_length[100]|is_unique[servicio.s_nbre,s_id,{s_id}]',
        's_duracionMinutos' => 'required|is_natural_no_zero',
        's_precio' => 'required|numeric|greater_than[0]'
    ];
    protected $validationMessages = [
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
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}
