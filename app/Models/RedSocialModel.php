<?php

namespace App\Models;

use CodeIgniter\Model;

class RedSocialModel extends Model
{
    protected $table            = 'red_social';
    protected $primaryKey       = 'rs_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'p_id',
        'rs_tipo',
        'rs_link'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'p_id' => 'required|numeric',
        'rs_tipo'           => 'required|max_length[50]',
        'rs_link'           => 'required|valid_url_strict|max_length[255]'
    ];
    protected $validationMessages   = [
        'rs_link' => [
            'valid_url_strict' => 'El enlace proporcionado no es una URL válida.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
