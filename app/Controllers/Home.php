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

        // Obtener profesionales con sus nombres
        $profesionalesQuery = $db->table('profesional')
            ->select('profesional.*, usuario.u_nbreCompleto, usuario.u_avatar')
            ->join('usuario', 'usuario.u_id = profesional.u_id')
            ->where('usuario.u_activo', 1)
            ->get();
        $profesionales = $profesionalesQuery->getResultArray();
        
        $redesSocialesQuery = $db->table('red_social')->get();
        $redesSociales = $redesSocialesQuery->getResultArray();
        
        foreach ($profesionales as &$prof) {
            $prof['redes'] = array_filter($redesSociales, function($red) use ($prof) {
                return $red['rs_pId'] == $prof['p_id'];
            });
        }

        return view('index', [
            'servicios' => $servicios,
            'profesionales' => $profesionales
        ]);
    }
}
