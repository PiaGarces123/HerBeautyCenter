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
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $usuarioId = $session->get('usuario_id');
        $rol = $session->get('rol');

        $routePath = ltrim(uri_string(), '/');
        $rawPath   = $request->getUri()->getPath();

        $isApi = str_starts_with($routePath, 'api/')
              || str_contains($rawPath, '/api/')
              || str_starts_with(trim($rawPath, '/'), 'api/')
              || (method_exists($request, 'isAJAX') && $request->isAJAX())
              || (method_exists($request, 'hasHeader') && $request->hasHeader('Accept') && str_contains($request->getHeaderLine('Accept'), 'application/json'));

        // 1. Verificación de Autenticación
        if (!$usuarioId) {
            if ($isApi) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Sesión expirada o no iniciada. Por favor, inicia sesión nuevamente.',
                        'error'   => 'Unauthorized'
                    ]);
            }

            return redirect()->to(base_url('/?login=1'));
        }

        // 2. Verificación de Roles (si se pasaron roles como argumento: auth:admin,profesional)
        if (!empty($arguments)) {
            // Permitir 'all' o comprobar que el rol esté en la lista permitida
            if (!in_array('all', $arguments, true) && !in_array($rol, $arguments, true)) {
                if ($isApi) {
                    return service('response')
                        ->setStatusCode(403)
                        ->setJSON([
                            'success' => false,
                            'message' => 'No cuentas con los permisos requeridos para realizar esta acción.',
                            'error'   => 'Forbidden'
                        ]);
                }

                // Si es un cliente intentando entrar al panel admin, redirigir a inicio
                if ($rol === 'cliente') {
                    return redirect()->to(base_url('/'));
                }

                // Si es un profesional intentando entrar a ruta exclusiva de admin, redirigir al panel
                return redirect()->to(base_url('admin'));
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se requiere acción posterior
    }
}
