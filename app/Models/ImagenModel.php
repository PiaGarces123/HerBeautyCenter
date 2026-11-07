<?php

namespace App\Models;

use CodeIgniter\Model;

class ImagenModel extends Model
{
    protected $table            = 'imagen';
    protected $primaryKey       = 'img_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'img_sId',
        'img_orden',
        's_id',
        'img_ruta'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'img_sId'  => 'required|numeric',
        'img_ruta' => 'required|max_length[255]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Obtiene todas las imágenes asociadas a un servicio ordenadas por img_orden.
     * La primera foto es la portada del servicio.
     */
    public function getImagenesByServicio(int $sId): array
    {
        return $this->where('img_sId', $sId)
            ->orderBy('img_orden', 'ASC')
            ->orderBy('img_id', 'ASC')
            ->findAll();
    }
}
