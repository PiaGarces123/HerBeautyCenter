<?php

namespace App\Models;

use CodeIgniter\Model;


/**
 * ProfesionalModel
 * Her Beauty Center
 *
 * Gestiona los perfiles profesionales del centro de estética
 * (tabla `profesional`).
 *
 * Cada registro representa el perfil profesional de un usuario
 * previamente registrado en el sistema.
 *
 * Sus responsabilidades principales son:
 * 1. Gestionar los perfiles de las profesionales del centro.
 * 2. Vincular cada perfil profesional con un usuario del sistema.
 * 3. Almacenar información profesional, como título, descripción
 *    y año de inicio de actividad.
 * 4. Definir el orden de visualización de los perfiles profesionales.
 * 5. Validar que un usuario no se encuentre registrado más de una vez
 *    como profesional.
 *
 */
class ProfesionalModel extends Model
{

    // ------------------------------------------------------------
    // CONFIGURACIÓN GENERAL DEL MODELO
    // ------------------------------------------------------------

    protected $table = 'profesional';
    protected $primaryKey = 'p_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'u_id',
        'p_titulo',
        'p_desc',
        'p_anioInicioAct',
        'p_orden'
    ];

    // ------------------------------------------------------------
    // CONFIGURACIÓN DE FECHAS
    // ------------------------------------------------------------

    // Desactiva la gestión automática de fechas de creación y modificación.
    // El modelo no completará campos como created_at o updated_at.
    protected $useTimestamps = false;

    // ------------------------------------------------------------
    // REGLAS DE VALIDACIÓN
    // ------------------------------------------------------------

    // Define las condiciones que deben cumplir los datos
    // antes de insertarse o actualizarse mediante el modelo.
    protected $validationRules = [
        'u_id' => 'required|is_unique[profesional.u_id,p_id,{p_id}]',
        'p_anioInicioAct' => 'permit_empty|exact_length[4]|numeric'
    ];

    // ------------------------------------------------------------
    // MENSAJES PERSONALIZADOS DE VALIDACIÓN
    // ------------------------------------------------------------
    protected $validationMessages = [
        'u_id' => [
            'is_unique' => 'Este usuario ya está registrado como profesional.'
        ]
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}
