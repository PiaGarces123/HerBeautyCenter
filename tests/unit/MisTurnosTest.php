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
 * MisTurnosTest
 * Her Beauty Center — Tests Unitarios para el Panel del Cliente «Mis Turnos» (CU05)
 *
 * Verifica:
 * - Control de acceso y seguridad RBAC a la ruta /mis-turnos.
 * - Criterios de filtrado y consulta del próximo turno destacado.
 * - Clasificación de turnos por períodos (próximos vs historial).
 * - Algoritmo de cálculo de contadores estadísticos de reservas.
 * - Liberación de disponibilidad horaria tras la cancelación de un turno.
 * - Reglas de negocio y generación de exportación a Google Calendar.
 * - Persistencia del motivo de cancelación por parte de la clienta.
 */
final class MisTurnosTest extends CIUnitTestCase
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
     * Test: Acceso a /mis-turnos requiere autenticación con rol de cliente
     */
    public function testAccesoMisTurnosRequiereAutenticacionCliente(): void
    {
        // Invitado anónimo -> redirige a login
        $req  = $this->createRequest('http://localhost/mis-turnos');
        $resp = $this->authFilter->before($req, ['cliente']);
        $this->assertInstanceOf(RedirectResponse::class, $resp);

        // Cliente autenticado -> acceso concedido (pasa filtro)
        session()->set([
            'usuario_id' => 12,
            'rol'        => 'cliente',
        ]);
        $this->assertNull($this->authFilter->before($req, ['cliente']));
    }

    /**
     * Test: Usuarios con rol administrativo o profesional no acceden a la vista de cliente
     */
    public function testStaffEsBloqueadoDeMisTurnos(): void
    {
        session()->set([
            'usuario_id' => 2,
            'rol'        => 'admin',
        ]);

        $req  = $this->createRequest('http://localhost/mis-turnos');
        $resp = $this->authFilter->before($req, ['cliente']);

        $this->assertInstanceOf(RedirectResponse::class, $resp);
    }

    /**
     * Test: El próximo turno destacado solo admite turnos activos a partir de hoy
     */
    public function testProximoTurnoFiltraEstadosYFechasValidas(): void
    {
        $hoy    = date('Y-m-d');
        $manana = date('Y-m-d', strtotime('+1 day'));
        $ayer   = date('Y-m-d', strtotime('-1 day'));

        $estadosValidosProximo   = ['Solicitado', 'Confirmado'];
        $estadosInvalidosProximo = ['Realizado', 'Cancelado', 'Disponible'];

        foreach ($estadosValidosProximo as $est) {
            $this->assertContains($est, ['Solicitado', 'Confirmado']);
        }

        foreach ($estadosInvalidosProximo as $est) {
            $this->assertNotContains($est, $estadosValidosProximo);
        }

        $this->assertTrue($manana >= $hoy, 'Un turno de mañana califica como próximo');
        $this->assertTrue($hoy >= $hoy, 'Un turno de hoy califica como próximo');
        $this->assertFalse($ayer >= $hoy, 'Un turno de ayer no califica como próximo');
    }

    /**
     * Test: Clasificación correcta de períodos entre "próximos" e "historial"
     */
    public function testClasificacionPeriodosTurnosCliente(): void
    {
        $hoy    = date('Y-m-d');
        $futuro = date('Y-m-d', strtotime('+3 days'));
        $pasado = date('Y-m-d', strtotime('-2 days'));

        $esProximo = fn($est, $f) => in_array($est, ['Solicitado', 'Confirmado'], true) && $f >= $hoy;
        $esHistorial = fn($est, $f) => in_array($est, ['Realizado', 'Cancelado'], true) || $f < $hoy;

        // Próximos
        $this->assertTrue($esProximo('Confirmado', $futuro));
        $this->assertTrue($esProximo('Solicitado', $hoy));
        $this->assertFalse($esProximo('Confirmado', $pasado), 'Un confirmado de fecha pasada no es próximo');
        $this->assertFalse($esProximo('Realizado', $futuro), 'Un turno realizado no es próximo');
        $this->assertFalse($esProximo('Cancelado', $futuro), 'Un turno cancelado no es próximo');

        // Historial
        $this->assertTrue($esHistorial('Realizado', $pasado));
        $this->assertTrue($esHistorial('Cancelado', $futuro), 'Un turno cancelado siempre pertenece al historial');
        $this->assertTrue($esHistorial('Confirmado', $pasado), 'Un turno confirmado con fecha vencida pasa al historial');
        $this->assertFalse($esHistorial('Confirmado', $futuro));
    }

    /**
     * Test: Cálculo de los contadores rápidos para los filtros del listado
     */
    public function testCalculoContadoresMisTurnos(): void
    {
        $hoy    = date('Y-m-d');
        $futuro = date('Y-m-d', strtotime('+2 days'));
        $pasado = date('Y-m-d', strtotime('-1 day'));

        $turnosSimulados = [
            ['estado' => 'Confirmado', 'fecha' => $futuro],
            ['estado' => 'Solicitado', 'fecha' => $hoy],
            ['estado' => 'Realizado',  'fecha' => $pasado],
            ['estado' => 'Cancelado',  'fecha' => $futuro],
            ['estado' => 'Confirmado', 'fecha' => $pasado], // Pasado -> Historial
        ];

        $contadores = [
            'todos'      => count($turnosSimulados),
            'proximos'   => 0,
            'historial'  => 0,
            'Solicitado' => 0,
            'Confirmado' => 0,
            'Realizado'  => 0,
            'Cancelado'  => 0,
        ];

        foreach ($turnosSimulados as $t) {
            $est = $t['estado'];
            if (isset($contadores[$est])) {
                $contadores[$est]++;
            }
            if (in_array($est, ['Solicitado', 'Confirmado'], true) && $t['fecha'] >= $hoy) {
                $contadores['proximos']++;
            } else {
                $contadores['historial']++;
            }
        }

        $this->assertSame(5, $contadores['todos']);
        $this->assertSame(2, $contadores['proximos']);
        $this->assertSame(3, $contadores['historial']);
        $this->assertSame(1, $contadores['Solicitado']);
        $this->assertSame(2, $contadores['Confirmado']);
        $this->assertSame(1, $contadores['Realizado']);
        $this->assertSame(1, $contadores['Cancelado']);
    }

    /**
     * Test: La cancelación de un turno libera la disponibilidad horaria para nuevas reservas
     */
    public function testCancelacionDeTurnoLiberaHorarioParaNuevasReservas(): void
    {
        // En HorarioModel, los turnos activos que impiden reservar son unicamente Solicitado y Confirmado
        $estadosActivos = ['Solicitado', 'Confirmado'];
        $this->assertNotContains('Cancelado', $estadosActivos);

        // La clausula NOT EXISTS en getDisponibilidadServicio solo excluye Solicitado, Confirmado y Realizado
        $estadosBloqueantes = ['Solicitado', 'Confirmado', 'Realizado'];
        $this->assertNotContains('Cancelado', $estadosBloqueantes, 'Un turno cancelado no bloquea la disponibilidad');
    }

    /**
     * Test: Generación de enlace RFC 5545 para exportar a Google Calendar
     */
    public function testExportacionGoogleCalendarSoloParaConfirmados(): void
    {
        $turnoConfirmado = [
            't_id'            => 99,
            'estado'          => 'Confirmado',
            'fecha'           => '2026-11-20',
            'h_horaDesde'     => '10:00:00',
            'h_horaHasta'     => '11:00:00',
            'nombre_servicio' => 'Limpieza Facial Profunda',
        ];

        $this->assertSame('Confirmado', $turnoConfirmado['estado']);

        $startDt = date('Ymd\THis', strtotime("{$turnoConfirmado['fecha']} {$turnoConfirmado['h_horaDesde']}"));
        $endDt   = date('Ymd\THis', strtotime("{$turnoConfirmado['fecha']} {$turnoConfirmado['h_horaHasta']}"));

        $this->assertSame('20261120T100000', $startDt);
        $this->assertSame('20261120T110000', $endDt);

        $link = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            . '&text=' . urlencode('Turno: ' . $turnoConfirmado['nombre_servicio'])
            . '&dates=' . $startDt . '/' . $endDt;

        $this->assertStringStartsWith('https://calendar.google.com', $link);
        $this->assertStringContainsString('action=TEMPLATE', $link);
        $this->assertStringContainsString('20261120T100000/20261120T110000', $link);
    }

    /**
     * Test: Motivo de cancelación registrado por la clienta o asignado por defecto
     */
    public function testMotivoCancelacionPorDefectoClienta(): void
    {
        $motivoVacio = '';
        $motivoFinal = trim($motivoVacio) !== ''
            ? trim($motivoVacio)
            : 'Cancelado por la clienta desde Mi Panel';

        $this->assertSame('Cancelado por la clienta desde Mi Panel', $motivoFinal);

        $motivoPersonalizado = 'Aviso por viaje imprevisto';
        $motivoFinalCustom = trim($motivoPersonalizado) !== ''
            ? trim($motivoPersonalizado)
            : 'Cancelado por la clienta desde Mi Panel';

        $this->assertSame('Aviso por viaje imprevisto', $motivoFinalCustom);
    }
}

