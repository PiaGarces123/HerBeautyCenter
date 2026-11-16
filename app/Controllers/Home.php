<?php

namespace App\Controllers;


/**
 * Home
 * Her Beauty Center
 *
 * Controlador encargado de gestionar las vistas públicas del sitio web.
 *
 * Sus responsabilidades principales son:
 * 1. Obtener los servicios activos y sus imágenes principales.
 * 2. Obtener los profesionales activos y sus datos de usuario.
 * 3. Asociar las redes sociales correspondientes a cada profesional.
 * 4. Cargar la página de inicio con la información obtenida.
 * 5. Mostrar la página de términos, condiciones y política de privacidad.
 */
class Home extends BaseController
{
    // =============================
    // INDEX
    // =============================

    public function index(): string
    {
        $db = \Config\Database::connect();

        // --------- SERVICIOS ----------
        // Obtener servicios con su primera imagen según orden establecido
        $serviciosQuery = $db->table('servicio')
            ->select('servicio.*, (SELECT img_ruta FROM imagen WHERE imagen.img_sId = servicio.s_id ORDER BY imagen.img_orden ASC, imagen.img_id ASC LIMIT 1) AS imagen_ruta')
            ->where('servicio.s_activo', 1)
            ->orderBy('servicio.s_orden', 'ASC')
            ->get();
        // Convierte los resultados obtenidos en un array asociativo.
        $servicios = $serviciosQuery->getResultArray();

        // --------- PROFESIONALES ----------
        // Obtener profesionales con sus nombres ordenados
        $profesionalesQuery = $db->table('profesional')
            ->select('profesional.*, usuario.u_nbreCompleto, usuario.u_avatar')
            ->join('usuario', 'usuario.u_id = profesional.u_id')
            ->where('usuario.u_activo', 1)
            ->orderBy('profesional.p_orden', 'ASC')
            ->get();
        // Almacena los profesionales obtenidos en un array asociativo.
        $profesionales = $profesionalesQuery->getResultArray();

        // --------- REDES SOCIALES ----------
        //Obtiene todos los registros de redes sociales
        $redesSocialesQuery = $db->table('red_social')->get();
        // Convierte los resultados en un array asociativo.
        $redesSociales = $redesSocialesQuery->getResultArray();

        //Recorre los profesionales y busca las redes sociales que le corresponden
        foreach ($profesionales as &$prof) {
            // Filtra las redes sociales y conserva únicamente
            // aquellas que pertenecen al profesional actual.
            $filtered = array_filter($redesSociales, function ($red) use ($prof) {
                return (int) $red['rs_pId'] === (int) $prof['p_id'];
            });

            // Agrega al profesional una nueva propiedad llamada "redes".
            // array_map transforma cada registro al formato que necesita la vista.
            $prof['redes'] = array_values(array_map(function ($red) {
                return [
                    'id' => $red['rs_id'],
                    'tipo' => $red['rs_tipo'],
                    'link' => $red['rs_link'],
                    'rs_tipo' => $red['rs_tipo'],
                    'rs_link' => $red['rs_link'],
                ];
            }, $filtered));
        }

        //Envía los servicios y profesionales a la página principal
        return view('index', [
            'servicios' => $servicios,
            'profesionales' => $profesionales
        ]);
    }

    // =============================
    // Política Privacidad
    // =============================
    public function politicaPrivacidad(): string
    {
        return view('legal/privacidad', [
            'pageTitle' => 'Términos, Condiciones y Política de Privacidad'
        ]);
    }
}
