<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $db = \Config\Database::connect();
        
        // Obtener servicios con sus imágenes
        $serviciosQuery = $db->table('servicio')
            ->select('servicio.*, imagen.ruta as imagen_ruta')
            ->join('imagen', 'imagen.id_servicio = servicio.id_servicio', 'left')
            ->where('servicio.activo', 1)
            ->groupBy('servicio.id_servicio')
            ->orderBy('servicio.orden', 'ASC')
            ->get();
        $servicios = $serviciosQuery->getResultArray();

        // Obtener profesionales con sus nombres
        $profesionalesQuery = $db->table('profesional')
            ->select('profesional.*, usuario.nombre_completo, usuario.avatar')
            ->join('usuario', 'usuario.id_usuario = profesional.id_usuario')
            ->where('usuario.activo', 1)
            ->get();
        $profesionales = $profesionalesQuery->getResultArray();
        
        $redesSocialesQuery = $db->table('red_social')->get();
        $redesSociales = $redesSocialesQuery->getResultArray();
        
        foreach ($profesionales as &$prof) {
            $prof['redes'] = array_filter($redesSociales, function($red) use ($prof) {
                return $red['id_profesional'] == $prof['id_profesional'];
            });
        }

        return view('index', [
            'servicios' => $servicios,
            'profesionales' => $profesionales
        ]);
    }
}
