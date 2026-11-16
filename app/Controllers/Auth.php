<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

/**
 * Auth
 * Her Beauty Center
 *
 * Controlador encargado de gestionar la autenticación
 * y el acceso de los usuarios al sistema.
 *
 * Sus responsabilidades principales son:
 *
 * 1. Iniciar sesión mediante la validación del correo
 *    electrónico y la contraseña.
 * 2. Identificar el rol del usuario (cliente, profesional
 *    o administrador).
 * 3. Registrar nuevos usuarios como clientes.
 * 4. Iniciar automáticamente la sesión después del registro.
 * 5. Cerrar la sesión del usuario.
 */
class Auth extends BaseController
{
    //----------------------
    // INICIAR SESIÒN
    //----------------------
    public function login()
    {
        // Obtiene los datos enviados por el cliente.
        // getRequestData() permite trabajar con los datos
        // recibidos en el formato contemplado por BaseController.
        $data = $this->getRequestData();
        $email = $data['email'] ?? $data['u_correo'] ?? '';
        $password = $data['u_pass'] ?? $data['password'] ?? $data['pass'] ?? '';

        // Comprueba que ambos campos hayan sido completados.
        if (empty($email) || empty($password)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos. Por favor ingresá email y contraseña.']);
        }

        $db = \Config\Database::connect();
        // Busca un usuario cuyo correo coincida con el recibido.
        $usuario = $db->table('usuario')
            ->where('u_correo', $email)
            ->get()->getRowArray();


        // Comprueba dos situaciones:
        // 1. Que el usuario exista en la base de datos.
        // 2. Que la contraseña ingresada coincida con su hash almacenado.
        //
        // password_verify() permite comparar una contraseña en texto
        // plano con el hash generado previamente mediante password_hash().
        //
        // Si alguna condición falla, se devuelve un mensaje genérico
        // para no revelar si el correo está registrado o no.
        if (!$usuario || !password_verify($password, $usuario['u_pass'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
        }

        // Verifica que la cuenta del usuario se encuentre activa.
        if (empty($usuario['u_activo']) || (int) $usuario['u_activo'] !== 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cuenta Inactiva. Por favor comunicate con la administración.'
            ]);
        }

        try {
            // Verificar si es profesional
            $profesional = $db->table('profesional')
                ->where('u_id', $usuario['u_id'])
                ->get()->getRowArray();
            // Si no encuentra un registro, getRowArray() devuelve null.

            // Por defecto, todo usuario autenticado se considera cliente.
            $rol = 'cliente';
            $id_profesional = null;

            //SI ES PROFESIONAL
            if ($profesional) {
                $rol = 'profesional';
                $id_profesional = $profesional['p_id'];

                // Verificar si además de profesional, es administrador
                $admin = $db->table('administrador')
                    ->where('a_pId', $id_profesional)
                    ->get()->getRowArray();

                if ($admin) {
                    $rol = 'admin';
                }
            }

            // Una vez identificado el usuario y su rol,
            // se almacenan sus datos principales en la sesión.
            session()->set([
                'usuario_id' => $usuario['u_id'],
                'usuario_nombre' => $usuario['u_nbreCompleto'],
                'usuario_correo' => $usuario['u_correo'],
                'usuario_avatar' => $usuario['u_avatar'],
                'rol' => $rol,
                'p_id' => $id_profesional,
            ]);

            // Devuelve una respuesta JSON indicando que el acceso
            // fue exitoso e informa el rol identificado.
            return $this->response->setJSON(['success' => true, 'message' => 'Login exitoso.', 'rol' => $rol]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al iniciar sesión: ' . $e->getMessage()]);
        }
    }


    //----------------------
    // REGISTRARSE
    //----------------------
    public function register()
    {
        // Obtiene los datos enviados desde el formulario de registro.
        $data = $this->getRequestData();
        $name = $data['name'] ?? $data['u_nbreCompleto'] ?? $data['nombre'] ?? '';
        $email = $data['email'] ?? $data['u_correo'] ?? $data['correo'] ?? '';
        $phone = $data['phone'] ?? $data['u_tel'] ?? $data['tel'] ?? $data['telefono'] ?? '';
        $password = $data['u_pass'] ?? $data['password'] ?? $data['pass'] ?? '';

        // Verifica que todos los campos obligatorios estén completos.
        // Si alguno está vacío, interrumpe el registro y devuelve
        // una respuesta JSON indicando qué ocurrió.
        if (empty($name) || empty($email) || empty($phone) || empty($password)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos. Por favor completá todos los campos.']);
        }

        // Instancia el modelo de usuarios.
        $usuarioModel = new UsuarioModel();

        // Prepara los datos que se guardarán en la tabla usuario
        $data = [
            'u_nbreCompleto' => $name,
            'u_correo' => $email,
            'u_tel' => $phone,
            'u_pass' => $password,
            'u_activo' => 1
        ];
        $db = \Config\Database::connect();

        try {

            // Inicia una transacción de base de datos.

            // transException(true) configura el tratamiento
            // de errores mediante excepciones.

            // transStart() comienza la transacción.
            // Las operaciones realizadas a continuación
            // podrán confirmarse o revertirse en conjunto.
            $db->transException(true)->transStart();

            // el callback beforeInsert se encarga de generar el hash de la contraseña.
            if (!$usuarioModel->insert($data)) {
                $errors = $usuarioModel->errors();
                $errorMsg = empty($errors) ? 'Error al registrar usuario.' : implode(' ', $errors);
                return $this->response->setJSON(['success' => false, 'message' => $errorMsg]);
            }

            // Recupera el identificador generado automáticamente
            // al insertar el nuevo usuario.

            // Este ID será utilizado para crear su registro asociado
            // en la tabla cliente.
            $userId = $usuarioModel->getInsertID();

            // Crear el registro de cliente asociado (la columna es c_uId)
            $db->table('cliente')->insert([
                'c_uId' => $userId
            ]);

            // Finaliza la transacción.

            // Si todas las operaciones se ejecutaron correctamente,
            // los cambios se confirman en la base de datos.
            $db->transComplete();

            // Iniciar sesión automáticamente
            session()->set([
                'usuario_id' => $userId,
                'usuario_nombre' => $name,
                'usuario_correo' => $email,
                'usuario_avatar' => null,
                'rol' => 'cliente',
            ]);

            // Informa que el registro se completó correctamente.
            return $this->response->setJSON(['success' => true, 'message' => 'Registro exitoso.']);

        } catch (\Exception $e) {

            // Si ocurre una excepción durante el proceso,
            // se comprueba el estado de la transacción.

            // Si todavía no fue revertida, se ejecuta rollback
            // para deshacer las operaciones realizadas.
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            // Devuelve una respuesta JSON indicando que ocurrió
            return $this->response->setJSON(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
        }
    }

    //----------------------
    // CERRAR SESIÓN
    //----------------------
    public function logout()
    {
        session()->destroy();

        // Comprueba si la solicitud fue enviada mediante AJAX
        // o si el cliente acepta recibir una respuesta JSON.

        // isAJAX() identifica solicitudes AJAX.
        // getHeaderLine('accept') recupera el encabezado HTTP Accept.
        // strpos() busca si contiene el tipo application/json.

        // Si cualquiera de las condiciones se cumple,
        // devuelve una respuesta JSON sin realizar una redirección.
        if ($this->request->isAJAX() || strpos($this->request->getHeaderLine('accept'), 'application/json') !== false) {
            return $this->response->setJSON(['success' => true, 'message' => 'Sesión cerrada correctamente.']);
        }

        return redirect()->to(base_url());
    }
}
