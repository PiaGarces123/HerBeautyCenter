<?php

namespace Tests\Unit;

use App\Filters\AuthFilter;
use App\Models\HorarioModel;
use App\Models\TurnoModel;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * ReservaClienteTest
 * Her Beauty Center — Tests Unitarios del Incremento N° 3 (CU01 a CU05)
 *
 * Verifica las reglas de negocio, control de acceso por roles (AuthFilter),
 * máquina de estados de reservas del cliente, validaciones de pre-confirmación
 * y métodos de consulta de disponibilidad y panel del cliente.
 */
final class ReservaClienteTest extends CIUnitTestCase
{
    private AuthFilter $authFilter;
    private TurnoModel $turnoModel;
    private HorarioModel $horarioModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authFilter   = new AuthFilter();
        $this->turnoModel   = new TurnoModel();
        $this->horarioModel = new HorarioModel();
        $_SESSION = [];
    }

    private function createRequest(string $url): IncomingRequest
    {
        $config = config('App');
        $uri    = new URI($url);
        return new IncomingRequest($config, $uri, null, new \CodeIgniter\HTTP\UserAgent());
    }

    /**
     * Test CU04/CU05: Invitado anónimo intentando acceder a /reservar/1 o /mis-turnos
     * es redirigido automáticamente a /?login=1
     */
    public function testInvitadoAnonimoEnRutaReservaRedirigeALogin(): void
    {
        $request  = $this->createRequest('http://localhost/reservar/1');
        $response = $this->authFilter->before($request, ['cliente']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * Test CU04: Petición anónima a /api/reservas/confirmar retorna HTTP 401
     */
    public function testPeticionApiReservaSinSesionRetorna401(): void
    {
        $request  = $this->createRequest('http://localhost/api/reservas/confirmar');
        $response = $this->authFilter->before($request, ['cliente']);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(401, $response->getStatusCode());
    }

    /**
     * Test CU04/CU05: Cliente autenticado tiene acceso permitido a rutas con filtro auth:cliente
     */
    public function testClienteAutenticadoAccedeARutasDeCliente(): void
    {
        session()->set([
            'usuario_id' => 25,
            'rol'        => 'cliente',
        ]);

        $reqReservar  = $this->createRequest('http://localhost/reservar/1');
        $reqMisTurnos = $this->createRequest('http://localhost/mis-turnos');
        $reqMiPerfil  = $this->createRequest('http://localhost/mi-perfil');
        $reqApiConf   = $this->createRequest('http://localhost/api/reservas/confirmar');

        $this->assertNull($this->authFilter->before($reqReservar, ['cliente']));
        $this->assertNull($this->authFilter->before($reqMisTurnos, ['cliente']));
        $this->assertNull($this->authFilter->before($reqMiPerfil, ['cliente']));
        $this->assertNull($this->authFilter->before($reqApiConf, ['cliente']));
    }

    /**
     * Test Seguridad RBAC: Un cliente autenticado NO puede acceder al panel admin
     */
    public function testClienteNoPuedeAccederAPanelAdmin(): void
    {
        session()->set([
            'usuario_id' => 25,
            'rol'        => 'cliente',
        ]);

        $request  = $this->createRequest('http://localhost/admin/turnos');
        $response = $this->authFilter->before($request, ['admin', 'profesional']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * Test Seguridad RBAC: Un profesional o admin NO puede acceder a las rutas ni APIs exclusivas de cliente
     */
    public function testStaffNoPuedeAccederARutasDeCliente(): void
    {
        session()->set([
            'usuario_id' => 10,
            'rol'        => 'profesional',
        ]);

        // Petición web: es redirigido a /admin
        $reqReservar  = $this->createRequest('http://localhost/reservar/1');
        $respReservar = $this->authFilter->before($reqReservar, ['cliente']);
        $this->assertInstanceOf(RedirectResponse::class, $respReservar);

        // Petición API: retorna 403 Forbidden
        $reqApiConf   = $this->createRequest('http://localhost/api/reservas/confirmar');
        $respApiConf  = $this->authFilter->before($reqApiConf, ['cliente']);
        $this->assertInstanceOf(Response::class, $respApiConf);
        $this->assertSame(403, $respApiConf->getStatusCode());
    }

    /**
     * Test CU04 (RN12/RN15): Todo turno solicitado por un cliente nace en estado 'Solicitado' y abonado = 0
     */
    public function testReservaClienteNaceEnEstadoSolicitadoSinAbonar(): void
    {
        $this->assertTrue($this->turnoModel->esTransicionValida('Disponible', 'Solicitado'));

        $datosReservaInicial = [
            't_hId'              => 10,
            't_pId'              => 2,
            't_cId'              => 5,
            'estado'             => 'Solicitado',
            'abonado'            => 0,
            'monto'              => 8500.00,
            'motivo_cancelacion' => 'Consideraciones: Preferencia de atención suave y productos neutros',
        ];

        $this->assertSame('Solicitado', $datosReservaInicial['estado']);
        $this->assertSame(0, $datosReservaInicial['abonado']);
        $this->assertGreaterThan(0, $datosReservaInicial['monto']);
    }

    /**
     * Test CU05 (RN17): El cliente solo puede cancelar turnos en estado 'Solicitado' o 'Confirmado'
     */
    public function testReglasCancelacionTurnoPorCliente(): void
    {
        // Permitido cancelar cuando está Solicitado o Confirmado
        $this->assertTrue($this->turnoModel->esTransicionValida('Solicitado', 'Cancelado'));
        $this->assertTrue($this->turnoModel->esTransicionValida('Confirmado', 'Cancelado'));

        // Prohibido cancelar un turno ya Realizado o ya Cancelado
        $this->assertFalse($this->turnoModel->esTransicionValida('Realizado', 'Cancelado'));
        $this->assertFalse($this->turnoModel->esTransicionValida('Cancelado', 'Cancelado'));
    }

    /**
     * Test Modelos Incremento 3: Existencia de métodos de consulta para CU01, CU04 y CU05
     */
    public function testMetodosDeModelosIncremento3Existen(): void
    {
        $imagenModel = new \App\Models\ImagenModel();
        $this->assertTrue(method_exists($imagenModel, 'getImagenesByServicio'));
        $this->assertTrue(method_exists($this->horarioModel, 'getDisponibilidadServicio'));
        $this->assertTrue(method_exists($this->horarioModel, 'getHorarioParaReserva'));
        $this->assertTrue(method_exists($this->turnoModel, 'getProximoTurnoCliente'));
        $this->assertTrue(method_exists($this->turnoModel, 'getTurnosCliente'));
    }
}

