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
        's_id',
        'img_ruta'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        's_id' => 'required|numeric',
        'img_ruta'        => 'required|max_length[255]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
