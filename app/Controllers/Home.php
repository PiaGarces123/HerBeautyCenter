<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $db = \Config\Database::connect();
        
        // Obtener servicios con sus imágenes
        $serviciosQuery = $db->table('servicio')
            ->select('servicio.*, imagen.img_ruta as imagen_ruta')
            ->join('imagen', 'imagen.img_sId = servicio.s_id', 'left')
            ->where('servicio.s_activo', 1)
            ->groupBy('servicio.s_id')
            ->orderBy('servicio.s_orden', 'ASC')
            ->get();
        $servicios = $serviciosQuery->getResultArray();

        // Obtener profesionales con sus nombres ordenados
        $profesionalesQuery = $db->table('profesional')
            ->select('profesional.*, usuario.u_nbreCompleto, usuario.u_avatar')
            ->join('usuario', 'usuario.u_id = profesional.u_id')
            ->where('usuario.u_activo', 1)
            ->orderBy('profesional.p_orden', 'ASC')
            ->get();
        $profesionales = $profesionalesQuery->getResultArray();
        
        $redesSocialesQuery = $db->table('red_social')->get();
        $redesSociales = $redesSocialesQuery->getResultArray();
        
        foreach ($profesionales as &$prof) {
            $filtered = array_filter($redesSociales, function($red) use ($prof) {
                return (int)$red['rs_pId'] === (int)$prof['p_id'];
            });
            $prof['redes'] = array_values(array_map(function($red) {
                return [
                    'id'      => $red['rs_id'],
                    'tipo'    => $red['rs_tipo'],
                    'link'    => $red['rs_link'],
                    'rs_tipo' => $red['rs_tipo'],
                    'rs_link' => $red['rs_link'],
                ];
            }, $filtered));
        }

        return view('index', [
            'servicios' => $servicios,
            'profesionales' => $profesionales
        ]);
    }
}
