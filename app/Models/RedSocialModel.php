<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * RedSocialModel
 * Her Beauty Center
 *
 * Gestiona los enlaces de redes sociales asociados a los profesionales
 * del centro de estética (tabla `red_social`).
 *
 * Cada registro representa una red social vinculada a un profesional.
 *
 * Sus responsabilidades principales son:
 * 1. Registrar las redes sociales de los profesionales.
 * 2. Almacenar el tipo de red social y su enlace correspondiente.
 * 3. Validar que los datos ingresados cumplan con los requisitos establecidos.
 * 4. Permitir consultar, actualizar y eliminar los registros de redes sociales.
 */

class RedSocialModel extends Model
{
    protected $table = 'red_social';
    protected $primaryKey = 'rs_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'rs_pId',
        'rs_tipo',
        'rs_link'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules = [
        'rs_pId' => 'required|numeric',
        'rs_tipo' => 'required|max_length[50]',
        'rs_link' => 'required|valid_url_strict|max_length[255]'
    ];
    protected $validationMessages = [
        'rs_link' => [
            'valid_url_strict' => 'El enlace proporcionado no es una URL válida.'
        ]
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}
