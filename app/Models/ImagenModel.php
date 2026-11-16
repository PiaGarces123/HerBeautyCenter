<?php

namespace App\Models;

use CodeIgniter\Model;


/**
 * ImagenModel
 * Her Beauty Center
 *
 * Gestiona las imágenes asociadas a los servicios del centro de estética
 * (tabla `imagen`).
 *
 * Sus responsabilidades principales son:
 * 1. Registrar y administrar las imágenes vinculadas a cada servicio.
 * 2. Obtener las imágenes correspondientes a un servicio determinado.
 * 3. Mantener el orden de visualización de las imágenes.
 * 4. Identificar la imagen de portada de cada servicio mediante su posición.
 *
 */

class ImagenModel extends Model
{

    // ------------------------------------------------------------
    // CONFIGURACIÓN GENERAL DEL MODELO
    // ------------------------------------------------------------
    protected $table = 'imagen';
    protected $primaryKey = 'img_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;


    // Activa la protección de campos.
    // Solo permite insertar o modificar los campos declarados en $allowedFields
    // mediante las operaciones de escritura del modelo.
    protected $protectFields = true;
    protected $allowedFields = [
        'img_sId',
        'img_orden',
        's_id',
        'img_ruta'
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
        'img_sId' => 'required|numeric',
        'img_ruta' => 'required|max_length[255]'
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // ------------------------------------------------------------
    // OBTENER IMÁGENES DE UN SERVICIO
    // Recupera todas las imágenes asociadas a un servicio y las ordena.
    // ------------------------------------------------------------

    public function getImagenesByServicio(int $sId): array
    {
        return $this->where('img_sId', $sId)
            ->orderBy('img_orden', 'ASC')
            ->orderBy('img_id', 'ASC')
            ->findAll();
    }
}
