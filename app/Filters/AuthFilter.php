<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter
 * Her Beauty Center — Filtro Centralizado de Autenticación y Autorización por Rol
 *
 * Intercepta las solicitudes antes de llegar a los controladores:
 * 1. Comprueba si el usuario tiene una sesión activa (usuario_id).
 * 2. Comprueba si el rol del usuario coincide con los argumentos del filtro (ej: auth:admin,profesional).
 * 3. Si la solicitud proviene de una API o AJAX, responde con JSON (401 o 403).
 * 4. Si la solicitud es web tradicional, redirige al login con parámetro para abrir modal.
 */
class AuthFilter implements FilterInterface
{

    //ANTES de que la solicitud llegue al controlador.
    public function before(RequestInterface $request, $arguments = null)
    {
        // Permite consultar los datos del usuario que inició sesión.
        $session = session();
        //Obtengo el id del user
        $usuarioId = $session->get('usuario_id');
        //Obtengo el rol del user
        $rol = $session->get('rol');

        // Obtiene la dirección de la ruta solicitada y elimina
        // las barras iniciales para facilitar su comparación.
        $routePath = ltrim(uri_string(), '/');

        // Obtiene la ruta completa de la solicitud HTTP,
        // incluyendo los posibles prefijos de la dirección.
        $rawPath = $request->getUri()->getPath();

        // Comprueba si la solicitud es una API o una solicitud AJAX
        $isApi = str_starts_with($routePath, 'api/') // Comprueba si la dirección empieza por /api/ 
            || str_contains($rawPath, '/api/') // Comprueba si la dirección contiene /api/
            || str_starts_with(trim($rawPath, '/'), 'api/') // Comprueba si la dirección empieza por /api/ CON TRIM
            || (method_exists($request, 'isAJAX') && $request->isAJAX()) // Comprueba si la solicitud es AJAX
            // Comprueba si la solicitud incluye una cabecera HTTP Accept
            // que indique que espera recibir información en formato JSON.
            || (method_exists($request, 'hasHeader') && $request->hasHeader('Accept') && str_contains($request->getHeaderLine('Accept'), 'application/json'));

        // =================================================================
        // 1. VERIFICACIÓN DE AUTENTICACIÓN
        // =================================================================

        if (!$usuarioId) { // Comprueba si NO existe un identificador de usuario en la sesión.
            if ($isApi) { // Comprueba si la solicitud es una API o una solicitud AJAX
                return service('response') // Obtiene el servicio de respuestas HTTP de CodeIgniter.
                    ->setStatusCode(401) // Establece el código de estado HTTP en 401 (No autorizado).
                    ->setJSON([
                        'success' => false,
                        'message' => 'Sesión expirada o no iniciada. Por favor, inicia sesión nuevamente.',
                        'error' => 'Unauthorized'
                    ]); // Convierte el array asociativo en una cadena de texto en formato JSON
            }

            // Redirige al inicio del sitio abriendo el modal de login
            return redirect()->to(base_url('/?login=1'));
        }

        // =================================================================
        // 2. VERIFICACIÓN DE CUENTA ACTIVA
        // =================================================================

        // Se stablece una conexión con la base de datos.
        // Es para comprobar que la cuenta del usuario
        // esté habilitada y no haya sido desactivada.
        $db = \Config\Database::connect();

        // Consulta la tabla usuario para recuperar el estado de la cuenta.
        $userCheck = $db->table('usuario')->select('u_activo')->where('u_id', $usuarioId)->get()->getRowArray();

        // Comprueba dos cosas:
        // 1. Que el usuario exista en la base de datos.
        // 2. Que su cuenta tenga un estado activo.
        if (!$userCheck || empty($userCheck['u_activo'])) {

            // Destruye la sesión actual para impedir que el usuario
            // continúe utilizando una cuenta que ya no está habilitada.
            $session->destroy();

            // Si la solicitud proviene de una API o AJAX,
            // devuelve un error en formato JSON.
            if ($isApi) {
                return service('response')
                    ->setStatusCode(403) // Establece el código de estado HTTP en 403 (Prohibido).
                    ->setJSON([
                        'success' => false,
                        'message' => 'Cuenta Inactiva. Por favor comunicate con la administración.',
                        'error' => 'Forbidden'
                    ]); // Convierte el array asociativo en una cadena de texto en formato JSON
            }

            // Redirige al inicio del sitio abriendo el modal de login
            return redirect()->to(base_url('/?login=1'));
        }



        // =================================================================
        // 3. VERIFICACIÓN DE ROLES Y PERMISOS
        // =================================================================

        // Comprueba si se especificaron roles autorizados
        // al definir la ruta en el archivo Routes.php.
        if (!empty($arguments)) { //$arguments contiene los roles permitidos.
            // Permite 'all' o comprueba que el rol esté en la lista permitida
            if (!in_array('all', $arguments, true) && !in_array($rol, $arguments, true)) {

                // Si la solicitud es de API o AJAX,
                // devuelve un error JSON indicando que el usuario
                // está autenticado, pero no tiene los permisos necesarios.
                if ($isApi) {
                    return service('response')
                        ->setStatusCode(403) // Establece el código de estado HTTP en 403 (Prohibido).
                        ->setJSON([
                            'success' => false,
                            'message' => 'No cuentas con los permisos requeridos para realizar esta acción.',
                            'error' => 'Forbidden'
                        ]); // Convierte el array asociativo en una cadena de texto en formato JSON
                }

                // Si es un cliente intentando entrar al panel admin, redirige a inicio
                if ($rol === 'cliente') {
                    return redirect()->to(base_url('/'));
                }

                // Si es un profesional intentando entrar a ruta exclusiva de admin, redirigir al panel
                return redirect()->to(base_url('admin'));
            }
        }
    }

    // Se ejecuta DESPUÉS de que el controlador procesa la solicitud y genera una respuesta.
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se requiere acción posterior
    }
}
