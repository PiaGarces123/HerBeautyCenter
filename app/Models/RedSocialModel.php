<?php

namespace App\Models;

use CodeIgniter\Model;

class RedSocialModel extends Model
{
    protected $table            = 'red_social';
    protected $primaryKey       = 'id_red_social';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_profesional',
        'tipo',
        'link'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'id_profesional' => 'required|numeric',
        'tipo'           => 'required|max_length[50]',
        'link'           => 'required|valid_url_strict|max_length[255]'
    ];
    protected $validationMessages   = [
        'link' => [
            'valid_url_strict' => 'El enlace proporcionado no es una URL válida.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
