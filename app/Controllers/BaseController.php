<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController
 * Her Beauty Center
 *
 * Clase base de la que heredan los controladores
 * de la aplicación.
 *
 * Su finalidad es centralizar configuraciones y funcionalidades
 * que pueden ser compartidas por los distintos controladores,
 * evitando repetir código.
 *
 * Sus responsabilidades principales son:
 *
 * 1. Inicializar los componentes comunes de los controladores.
 * 2. Proporcionar un método reutilizable para obtener los datos
 *    enviados mediante formularios o solicitudes JSON.
 *
 * Al ser una clase abstracta, no se instancia directamente.
 * Los controladores específicos deben heredar de ella.
 */
abstract class BaseController extends Controller
{
    // Ejemplo de una propiedad para almacenar la sesión.
    // Si se necesitara utilizar, primero habría que declararla
    // y luego inicializarla dentro de initController().
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        //CodeIgniter ejecuta este método al inicializar
        // un controlador que hereda de BaseController.
        // Recibe los objetos principales que permiten trabajar
        //con la solicitud, la respuesta y el registro de eventos.

        // En este espacio se pueden cargar los helpers
        // que estarán disponibles en todos los controladores
        // que hereden de BaseController.
        parent::initController($request, $response, $logger);

    }

    /**
     * Devuelve de forma segura los datos enviados tanto por POST (FormData / x-www-form-urlencoded)
     * como por JSON, sin arrojar excepciones de parseo cuando la petición es multipart/form-data.
     */
    protected function getRequestData(): array
    {

        // Obtiene los datos enviados mediante POST.

        // getPost() recupera los campos enviados por formularios.
        $postData = $this->request->getPost();

        // Si los datos recibidos son un array, se conservan.
        // En caso contrario, se inicializa un array vacío.
        $data = is_array($postData) ? $postData : [];

        // Obtiene el encabezado HTTP Content-Type.
        // Este encabezado informa el formato del contenido
        // enviado en el cuerpo de la solicitud.
        $cType = $this->request->getHeaderLine('Content-Type');

        // Comprueba si el contenido recibido corresponde a JSON.
        // stripos() busca una cadena dentro de otra sin distinguir
        // entre mayúsculas y minúsculas.
        // Se utiliza en lugar de una comparación exacta porque
        // el encabezado también puede incluir parámetros adicionales,
        // por ejemplo: application/json; charset=UTF-8.
        if (stripos($cType, 'application/json') !== false) {

            // Obtiene el cuerpo completo de la solicitud HTTP.
            $body = $this->request->getBody();

            // Si contiene información, intenta interpretarla como JSON.
            if (!empty($body)) {

                // Convierte el contenido JSON en un array asociativo.
                $json = json_decode($body, true);

                // Verifica que la conversión haya sido correcta
                // y que el resultado sea un array.
                if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {

                    // Combina los datos recibidos mediante POST
                    // con los datos obtenidos desde JSON.
                    $data = array_merge($data, $json);
                }
            }
        }

        // Devuelve todos los datos recopilados en un único array.
        //
        // Los controladores pueden acceder a ellos mediante
        // sus claves, por ejemplo:
        // $data['email'], $data['password'] o $data['nombre'].
        return $data;
    }
}
