<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AdministradorModel
 * Her Beauty Center
 *
 * Gestiona los registros de administradores del sistema
 * (tabla `administrador`).
 *
 * Sus responsabilidades principales son:
 * 1. Registrar la asociación entre un profesional y el rol de administrador.
 * 2. Validar que cada profesional se encuentre registrado una sola vez como administrador.
 * 3. Permitir la consulta, actualización y eliminación de registros de administradores.
 */

class AdministradorModel extends Model
{
    protected $table = 'administrador';
    protected $primaryKey = 'a_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'a_pId'
    ];

    // CONFIGURACIÓN DE FECHAS

    // Desactiva automáticamente la gestión de fechas de creación y modificación.
    // El modelo no completará campos como created_at o updated_at.
    protected $useTimestamps = false;

    // REGLAS DE VALIDACIÓN
    protected $validationRules = [
        'a_pId' => 'required|is_unique[administrador.a_pId,a_id,{a_id}]'
    ];

    // MENSAJES PERSONALIZADOS DE VALIDACIÓN
    protected $validationMessages = [
        'a_pId' => [
            'is_unique' => 'Este profesional ya está registrado como administrador.'
        ]
    ];

    // Indica que las validaciones no deben omitirse al insertar o actualizar registros.
    protected $skipValidation = false;


    // Indica que CodeIgniter debe limpiar las reglas de validación
    // antes de utilizarlas, eliminando reglas vacías o innecesarias.
    protected $cleanValidationRules = true;
}
