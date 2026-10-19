<?php

namespace Tests\Unit;

use App\Filters\AuthFilter;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * AuthFilterTest
 * Her Beauty Center — Tests Unitarios para el Filtro Centralizado de Autenticación
 */
final class AuthFilterTest extends CIUnitTestCase
{
    private AuthFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new AuthFilter();
        // Reset sesión
        $_SESSION = [];
    }

    private function createRequest(string $url): IncomingRequest
    {
        $config  = config('App');
        $uri     = new URI($url);
        return new IncomingRequest($config, $uri, null, new \CodeIgniter\HTTP\UserAgent());
    }

    /**
     * Test: AuthFilter implementa la interfaz de Filtros de CodeIgniter 4
     */
    public function testImplementaFilterInterface(): void
    {
        $this->assertInstanceOf(FilterInterface::class, $this->filter);
    }

    /**
     * Test: Petición anónima a la API debe retornar 401 Unauthorized
     */
    public function testPeticionApiAnonimaRetorna401(): void
    {
        $request = $this->createRequest('http://localhost/api/turnos/listar');
        $response = $this->filter->before($request, ['admin', 'profesional']);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(401, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertSame('Unauthorized', $body['error']);
    }

    /**
     * Test: Petición anónima Web debe redirigir a login
     */
    public function testPeticionWebAnonimaRedirigeALogin(): void
    {
        $request = $this->createRequest('http://localhost/admin/turnos');
        $response = $this->filter->before($request, ['admin', 'profesional']);

        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $response);
    }

    /**
     * Test: Usuario con rol no autorizado en API recibe 403 Forbidden
     */
    public function testRolNoAutorizadoApiRetorna403(): void
    {
        session()->set([
            'usuario_id' => 99,
            'rol'        => 'cliente'
        ]);

        $request = $this->createRequest('http://localhost/api/turnos/listar');
        $response = $this->filter->before($request, ['admin', 'profesional']);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(403, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertSame('Forbidden', $body['error']);
    }

    /**
     * Test: Usuario con sesión y rol correcto es permitido (retorna null)
     */
    public function testUsuarioAutorizadoPasaFiltro(): void
    {
        session()->set([
            'usuario_id' => 10,
            'rol'        => 'profesional'
        ]);

        $request = $this->createRequest('http://localhost/api/turnos/listar');
        $response = $this->filter->before($request, ['admin', 'profesional']);

        $this->assertNull($response);
    }
}
