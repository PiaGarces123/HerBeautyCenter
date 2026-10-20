<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\TurnoModel;
use CodeIgniter\API\ResponseTrait;

/**
 * TurnosApi
 * Her Beauty Center — Incremento N° 2 (CU07 - Gestionar Turnos)
 *
 * Endpoints para visualizar y administrar el ciclo de vida de los turnos.
 * Ubicación: app/Controllers/Api/TurnosApi.php
 */
class TurnosApi extends BaseController
{
    use ResponseTrait;

    // -------------------------------------------------------
    // Helper: verificar sesión activa y rol válido
    // -------------------------------------------------------
    private function verificarSesion(): ?array
    {
        $session = session();
        $uid     = $session->get('usuario_id');
        $rol     = $session->get('rol');
        if (!$uid || !in_array($rol, ['admin', 'profesional'], true)) return null;
        return ['uid' => $uid, 'rol' => $rol];
    }

    private function getProfesionalId(int $uid): ?int
    {
        $row = \Config\Database::connect()
            ->table('profesional')->select('p_id')->where('u_id', $uid)
            ->get()->getRowArray();
        return $row ? (int) $row['p_id'] : null;
    }

    // -------------------------------------------------------
    // GET /api/turnos/listar
    // Lista turnos de la profesional con filtros opcionales
    // -------------------------------------------------------
    public function listar(): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $filtros = [
            'estado'      => $this->request->getGet('estado')      ?? '',
            'fecha_desde' => $this->request->getGet('fecha_desde') ?? '',
            'fecha_hasta' => $this->request->getGet('fecha_hasta') ?? '',
        ];

        $model  = new TurnoModel();
        $turnos = $model->getTurnosConDetalle($pId, $filtros);

        return $this->respond(['turnos' => $turnos]);
    }

    // -------------------------------------------------------
    // GET /api/turnos/clientes
    // Retorna clientes activos para asignación de turnos
    // -------------------------------------------------------
    public function clientes(): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $db = \Config\Database::connect();
        $clientes = $db->table('cliente')
            ->select('cliente.c_id, usuario.u_nbreCompleto, usuario.u_correo, usuario.u_tel')
            ->join('usuario', 'usuario.u_id = cliente.c_uId')
            ->where('usuario.u_activo', 1)
            ->orderBy('usuario.u_nbreCompleto', 'ASC')
            ->get()->getResultArray();

        return $this->respond(['clientes' => $clientes]);
    }

    // -------------------------------------------------------
    // POST /api/turnos/crear
    // Permite al profesional / admin crear y asignar un turno a un cliente
    // -------------------------------------------------------
    public function crear(): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $db   = \Config\Database::connect();
        $data = $this->request->getJSON(true) ?: $this->request->getPost();

        $sId            = (int) ($data['s_id'] ?? 0);
        $fecha          = trim($data['fecha'] ?? '');
        $horaDesde      = trim($data['hora_desde'] ?? '');
        $horaHasta      = trim($data['hora_hasta'] ?? '');
        $nuevoCliente   = !empty($data['nuevo_cliente']);
        $cId            = (int) ($data['c_id'] ?? 0);
        $clienteNombre  = trim($data['cliente_nombre'] ?? '');
        $clienteTel     = trim($data['cliente_tel'] ?? '');
        $clienteEmail   = trim($data['cliente_email'] ?? '');
        $monto          = isset($data['monto']) && $data['monto'] !== '' ? floatval($data['monto']) : null;
        $abonado        = !empty($data['abonado']) ? 1 : 0;
        $estado         = in_array($data['estado'] ?? '', ['Solicitado', 'Confirmado', 'Realizado'], true)
                          ? $data['estado']
                          : 'Confirmado';

        // 1. Validar servicio
        if (!$sId) {
            return $this->fail('Debés seleccionar un servicio.');
        }
        $servicio = $db->table('servicio')->where('s_id', $sId)->where('s_activo', 1)->get()->getRowArray();
        if (!$servicio) {
            return $this->fail('El servicio seleccionado no existe o no está disponible.');
        }

        // 2. Validar fecha
        if (!$fecha || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $this->fail('La fecha seleccionada no es válida.');
        }
        if ($fecha < date('Y-m-d')) {
            return $this->fail('No se pueden programar turnos en fechas pasadas.');
        }

        // 3. Validar horarios
        if (!$horaDesde || !$horaHasta) {
            return $this->fail('Las horas de inicio y fin son obligatorias.');
        }
        $horaDesdeNorm = substr($horaDesde, 0, 5) . ':00';
        $horaHastaNorm = substr($horaHasta, 0, 5) . ':00';
        if ($horaHastaNorm <= $horaDesdeNorm) {
            return $this->fail('La hora de fin debe ser posterior a la hora de inicio.');
        }

        // 4. Resolver o crear cliente
        if ($nuevoCliente) {
            if (mb_strlen($clienteNombre) < 3) {
                return $this->fail('El nombre del cliente debe tener al menos 3 caracteres.');
            }
            if (mb_strlen($clienteTel) < 6) {
                return $this->fail('El teléfono del cliente debe tener al menos 6 caracteres.');
            }
            if (!empty($clienteEmail)) {
                if (!filter_var($clienteEmail, FILTER_VALIDATE_EMAIL)) {
                    return $this->fail('El correo electrónico ingresado no tiene un formato válido.');
                }
                $existUser = $db->table('usuario')->where('u_correo', $clienteEmail)->get()->getRowArray();
                if ($existUser) {
                    $existCli = $db->table('cliente')->where('c_uId', $existUser['u_id'])->get()->getRowArray();
                    if ($existCli) {
                        $cId = (int) $existCli['c_id'];
                    } else {
                        return $this->fail('Ya existe un usuario con este correo electrónico.');
                    }
                }
            }
            if (!$cId) {
                if (empty($clienteEmail)) {
                    $clienteEmail = 'cliente_' . time() . '_' . rand(100, 999) . '@herbeauty.local';
                }
                $db->table('usuario')->insert([
                    'u_nbreCompleto' => $clienteNombre,
                    'u_correo'       => $clienteEmail,
                    'u_tel'          => $clienteTel,
                    'u_pass'         => password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT),
                    'u_activo'       => 1,
                ]);
                $newUId = (int) $db->insertID();
                $db->table('cliente')->insert(['c_uId' => $newUId]);
                $cId = (int) $db->insertID();
            }
        } else {
            if (!$cId) {
                return $this->fail('Debés seleccionar un cliente de la lista o marcar la opción para registrar uno nuevo.');
            }
            $checkCli = $db->table('cliente')->where('c_id', $cId)->get()->getRowArray();
            if (!$checkCli) {
                return $this->fail('El cliente seleccionado no fue encontrado.');
            }
        }

        // 5. Verificar solapamiento de turno para la profesional
        $solapado = $db->table('turno')
            ->where('t_pId', $pId)
            ->where('t_fecha', $fecha)
            ->whereIn('estado', ['Solicitado', 'Confirmado'])
            ->where('t_horaDesde <', $horaHastaNorm)
            ->where('t_horaHasta >', $horaDesdeNorm)
            ->get()->getRowArray();

        if ($solapado) {
            $desdeSol = substr($solapado['t_horaDesde'], 0, 5);
            $hastaSol = substr($solapado['t_horaHasta'], 0, 5);
            return $this->fail("Ya tenés un turno reservado en ese horario ({$desdeSol} a {$hastaSol}).");
        }

        // 6. Gestionar horario asociado (respetando constraint UNIQUE en turno.t_hId)
        $horarioExistente = $db->table('horario')
            ->where('h_pId', $pId)
            ->where('h_sId', $sId)
            ->where('h_fecha', $fecha)
            ->where('h_horaDesde', $horaDesdeNorm)
            ->where('h_horaHasta', $horaHastaNorm)
            ->get()->getRowArray();

        $hId = null;
        if ($horarioExistente) {
            $turnoAsoc = $db->table('turno')->where('t_hId', $horarioExistente['h_id'])->get()->getRowArray();
            if (!$turnoAsoc) {
                $hId = (int) $horarioExistente['h_id'];
            }
        }

        if (!$hId) {
            $db->table('horario')->insert([
                'h_pId'       => $pId,
                'h_sId'       => $sId,
                'h_fecha'     => $fecha,
                'h_horaDesde' => $horaDesdeNorm,
                'h_horaHasta' => $horaHastaNorm,
            ]);
            $hId = (int) $db->insertID();
        }

        // 7. Insertar el turno
        if ($monto === null) {
            $monto = floatval($servicio['s_precio']);
        }

        $turnoModel = new TurnoModel();
        $tId = $turnoModel->insert([
            't_hId'       => $hId,
            't_pId'       => $pId,
            't_cId'       => $cId,
            't_fecha'     => $fecha,
            't_horaDesde' => $horaDesdeNorm,
            't_horaHasta' => $horaHastaNorm,
            'estado'      => $estado,
            'abonado'     => $abonado,
            'monto'       => $monto,
        ]);

        return $this->respondCreated([
            'success' => true,
            'message' => 'Turno creado y asignado exitosamente.',
            't_id'    => $tId,
        ]);
    }

    // -------------------------------------------------------
    // GET /api/turnos/detalle/:id
    // -------------------------------------------------------
    public function detalle(int $tId): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $model = new TurnoModel();
        $turno = $model->getTurnoDetalle($tId);

        if (!$turno || (int) $turno['t_pId'] !== $pId) {
            return $this->failNotFound('Turno no encontrado o sin permiso.');
        }

        return $this->respond(['turno' => $turno]);
    }

    // -------------------------------------------------------
    // PUT /api/turnos/estado/:id
    // Actualiza el estado de un turno (con validación de transición)
    // -------------------------------------------------------
    public function actualizarEstado(int $tId): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $model = new TurnoModel();
        $turno = $model->find($tId);

        if (!$turno || (int) $turno['t_pId'] !== $pId) {
            return $this->failNotFound('Turno no encontrado o sin permiso.');
        }

        $data        = $this->request->getJSON(true) ?: $this->request->getPost();
        $estadoNuevo = trim($data['estado'] ?? '');
        $abonado     = isset($data['abonado']) ? (int) (bool) $data['abonado'] : null;
        $monto       = isset($data['monto']) ? floatval($data['monto']) : null;
        $motivo      = trim($data['motivo_cancelacion'] ?? '');

        if (!$estadoNuevo) {
            return $this->fail('El nuevo estado es obligatorio.');
        }

        // -- Validar transición de estado (RN12) --
        if (!$model->esTransicionValida($turno['estado'], $estadoNuevo)) {
            return $this->fail("Transición inválida: no se puede pasar de \"{$turno['estado']}\" a \"$estadoNuevo\".");
        }

        // -- Cancelación requiere motivo --
        if ($estadoNuevo === 'Cancelado' && empty($motivo)) {
            return $this->fail('Debes ingresar un motivo para la cancelación.');
        }

        $updateData = ['estado' => $estadoNuevo];
        if ($estadoNuevo === 'Cancelado')  $updateData['motivo_cancelacion'] = $motivo;
        if ($abonado !== null)             $updateData['abonado']            = $abonado;
        if ($monto    !== null)            $updateData['monto']              = $monto;

        $model->update($tId, $updateData);

        // -- Si se cancela, liberamos la disponibilidad (RN12) --
        // (La disponibilidad queda intacta; el turno queda en historial — baja lógica)

        return $this->respond([
            'success' => true,
            'message' => "Turno actualizado a \"$estadoNuevo\" correctamente.",
        ]);
    }

    // -------------------------------------------------------
    // PUT /api/turnos/pago/:id
    // Actualiza el estado de abono y monto
    // -------------------------------------------------------
    public function actualizarPago(int $tId): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $model = new TurnoModel();
        $turno = $model->find($tId);

        if (!$turno || (int) $turno['t_pId'] !== $pId) {
            return $this->failNotFound('Turno no encontrado o sin permiso.');
        }

        $data    = $this->request->getJSON(true) ?: $this->request->getPost();
        $abonado = isset($data['abonado']) ? (int) (bool) $data['abonado'] : (int) $turno['abonado'];
        $monto   = isset($data['monto'])   ? floatval($data['monto'])      : floatval($turno['monto']);

        if ($monto < 0) {
            return $this->fail('El monto no puede ser negativo.');
        }

        $model->update($tId, ['abonado' => $abonado, 'monto' => $monto]);

        return $this->respond(['success' => true, 'message' => 'Información de pago actualizada.']);
    }

    // -------------------------------------------------------
    // POST /api/turnos/google-calendar/:id
    // Retorna datos estructurados del turno para exportar a GCal
    // La integración real OAuth2 requiere credenciales de cada profesional
    // -------------------------------------------------------
    public function exportarGoogleCalendar(int $tId): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $model = new TurnoModel();
        $turno = $model->getTurnoDetalle($tId);

        if (!$turno || (int) $turno['t_pId'] !== $pId) {
            return $this->failNotFound('Turno no encontrado o sin permiso.');
        }

        if ($turno['estado'] !== 'Confirmado') {
            return $this->fail('Solo se pueden exportar a Google Calendar los turnos en estado Confirmado.');
        }

        // Datos mínimos necesarios para el evento de Google Calendar
        $eventoGCal = [
            'summary'     => 'Turno: ' . $turno['nombre_servicio'],
            'description' => 'Cliente: ' . ($turno['nombre_cliente'] ?? 'Sin asignar'),
            'start'       => [
                'dateTime' => $turno['fecha'] . 'T' . $turno['h_horaDesde'],
                'timeZone' => 'America/Argentina/Buenos_Aires',
            ],
            'end'         => [
                'dateTime' => $turno['fecha'] . 'T' . $turno['h_horaHasta'],
                'timeZone' => 'America/Argentina/Buenos_Aires',
            ],
        ];

        // Construir enlace "Agregar a Google Calendar" (RFC 5545 compatible)
        $startTs = strtotime($turno['fecha'] . ' ' . $turno['h_horaDesde']);
        $endTs   = strtotime($turno['fecha'] . ' ' . $turno['h_horaHasta']);
        $startDt = date('Ymd\THis', $startTs);
        $endDt   = date('Ymd\THis', $endTs);

        $detalles = "Her Beauty Center — Turno Confirmado\n"
                  . "Servicio: " . ($turno['nombre_servicio'] ?? 'Servicio') . "\n"
                  . "Cliente: " . ($turno['nombre_cliente'] ?? 'Sin asignar') . "\n"
                  . (!empty($turno['tel_cliente']) ? "Teléfono: " . $turno['tel_cliente'] . "\n" : "")
                  . (!empty($turno['correo_cliente']) ? "Email: " . $turno['correo_cliente'] . "\n" : "")
                  . "Monto: $" . number_format($turno['monto'] ?? 0, 0, ',', '.') . "\n"
                  . "Estado: Confirmado";

        $link = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            . '&text=' . urlencode('Turno: ' . ($turno['nombre_servicio'] ?? 'Estética') . ' — ' . ($turno['nombre_cliente'] ?? 'Cliente'))
            . '&dates=' . $startDt . '/' . $endDt
            . '&details=' . urlencode($detalles)
            . '&location=' . urlencode('Her Beauty Center')
            . '&ctz=' . urlencode('America/Argentina/Buenos_Aires');

        return $this->respond([
            'success'       => true,
            'evento'        => $eventoGCal,
            'google_link'   => $link,
            'message'       => 'Datos del turno preparados para Google Calendar.',
        ]);
    }
}
