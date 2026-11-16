<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/**
 * ServiciosApi
 * Her Beauty Center — Incremento N° 3
 *
 * API RESTful encargada de administrar los servicios ofrecidos
 * por el centro de estética y sus imágenes asociadas.
 *
 * Sus responsabilidades principales son:
 * 1. Crear, editar y eliminar servicios.
 * 2. Gestionar la asignación de profesionales a los servicios.
 * 3. Ordenar los servicios para controlar su presentación visual.
 * 4. Activar o desactivar servicios sin eliminarlos físicamente.
 * 5. Subir, convertir, consultar, ordenar y eliminar imágenes.
 * 6. Mantener la relación entre los servicios y sus imágenes.
 */
class ServiciosApi extends BaseController
{
    // Permite utilizar métodos para generar respuestas HTTP y JSON,
    // como respond(), respondCreated(), respondDeleted(), fail(), entre otros.
    use ResponseTrait;

    /**
     * Convierte una imagen a formato WebP.
     *
     * Obtiene información del archivo, identifica su formato original
     * y utiliza las funciones de GD para procesarlo y guardarlo como WebP.
     *
     * Si la imagen ya está en formato WebP, intenta trasladarla directamente
     * al destino indicado, sin realizar una nueva conversión.
     *
     * @param string $source Ruta temporal del archivo original.
     * @param string $destination Ruta donde se guardará la imagen convertida.
     * @return bool Indica si la operación se realizó correctamente.
     */
    private function convertToWebp($source, $destination)
    {
        // Obtener información de la imagen, incluyendo su tipo MIME.
        // Si el archivo no puede identificarse como imagen, finalizar.
        $info = getimagesize($source);
        if (!$info)
            return false;

        // Recuperar el tipo MIME para determinar cómo abrir la imagen.
        $mime = $info['mime'];

        // Identificar el formato original y utilizar la función correspondiente
        // de la biblioteca GD para cargar la imagen en memoria.
        switch ($mime) {

            // Si es JPEG, crear el recurso de imagen a partir del archivo.
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                break;

            // Si es PNG, cargar la imagen y configurar el tratamiento
            // de colores y transparencia antes de convertirla.
            case 'image/png':
                $image = imagecreatefrompng($source);
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;

            // Si ya es WebP, no es necesario convertirla.
            // Se intenta mover, renombrar o copiar el archivo al destino.
            case 'image/webp':
                // It's already webp, just move it
                return move_uploaded_file($source, $destination) || rename($source, $destination) || copy($source, $destination);

            // Si el formato no está contemplado, rechazar el archivo.
            default:
                return false;
        }

        // Comprobar que la imagen se haya cargado correctamente en memoria.
        if (!$image)
            return false;

        // Convertir la imagen al formato WebP y guardarla en el destino.
        // El valor 85 establece la calidad de compresión de la imagen.
        $result = imagewebp($image, $destination, 85);

        // Liberar la memoria utilizada por el recurso de imagen.
        imagedestroy($image);

        // Devolver el resultado de la conversión.
        return $result;
    }

    /**
     * Crea un nuevo servicio y permite asociarle imágenes.
     *
     * Recibe los datos del servicio, valida sus campos principales,
     * comprueba que no exista otro servicio con el mismo nombre,
     * registra el servicio y procesa las imágenes recibidas.
     *
     * Requiere permisos de administrador.
     */
    public function crear()
    {
        // Verificar que exista una sesión iniciada y que el usuario
        // tenga el rol de administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Recuperar los datos enviados mediante POST o JSON.
        $data = $this->getRequestData();

        // Obtener los datos del servicio.
        // Se contemplan nombres alternativos para admitir distintos
        // formatos de envío desde el formulario o desde la API.
        $nombre = trim($data['nombre'] ?? $data['s_nbre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? $data['s_desc'] ?? '');
        $duracion = $data['duracion_minutos'] ?? $data['s_duracionMinutos'] ?? null;
        $precio = $data['precio'] ?? $data['s_precio'] ?? 0;
        $activo = $data['activo'] ?? $data['s_activo'] ?? $data['u_activo'] ?? 1;

        // Verificar que se hayan proporcionado los campos obligatorios:
        // nombre y duración del servicio.
        if ($nombre === '' || $duracion === null || $duracion === '') {
            return $this->fail('Faltan datos obligatorios (nombre o duración).');
        }

        // Validar que la duración esté comprendida entre 5 y 480 minutos.
        if (intval($duracion) < 5 || intval($duracion) > 480) {
            return $this->fail('La duración debe estar entre 5 y 480 minutos.');
        }

        // Impedir que el nombre o la descripción comiencen con un guión.
        if ((!empty($nombre) && str_starts_with($nombre, '-')) || (!empty($descripcion) && str_starts_with($descripcion, '-'))) {
            return $this->fail('El nombre o descripción no pueden empezar con guión.');
        }

        // Establecer la conexión con la base de datos.
        $db = \Config\Database::connect();

        // Validar si el servicio ya existe (pasando a mayúsculas).
        // Se transforma el nombre recibido a mayúsculas para realizar
        // la comparación sin depender de las diferencias entre mayúsculas
        // y minúsculas en el texto ingresado.
        $nombreMayus = strtoupper(trim($nombre));
        $existe = $db->table('servicio')
            ->where('UPPER(s_nbre)', $nombreMayus)
            ->countAllResults();

        // Si ya existe un servicio con ese nombre, impedir su creación.
        if ($existe > 0) {
            return $this->fail('Ya existe un servicio con ese nombre. Por favor, utiliza otro nombre.');
        }

        // Iniciar una transacción para agrupar el registro del servicio
        // y la inserción de sus imágenes.
        $db->transStart();

        // Desplazar los demás servicios.
        // Se incrementa en una unidad el valor de s_orden de todos los servicios
        // para reservar la posición inicial para el nuevo registro.
        // El tercer parámetro false indica que no se debe escapar
        // la expresión SQL s_orden + 1.
        $db->table('servicio')->set('s_orden', 's_orden + 1', false)->update();

        // Preparar los datos que se insertarán en la tabla servicio.
        $servicioData = [
            's_nbre' => trim($nombre),
            's_desc' => trim($descripcion),
            's_duracionMinutos' => intval($duracion),
            's_precio' => floatval($precio ?? 0),
            's_activo' => intval($activo),
            's_orden' => 0
        ];

        // Insertar el nuevo servicio y recuperar el identificador generado.
        $db->table('servicio')->insert($servicioData);
        $servicioId = $db->insertID();

        // Manejar imagenes (soporta múltiples fotos o una sola).
        // Primero se intenta recuperar el conjunto de archivos enviados
        // mediante el campo "imagenes".
        $files = $this->request->getFileMultiple('imagenes');

        // Si no se recibieron múltiples archivos, se intenta recuperar
        // una única imagen mediante el campo "imagen".
        if (empty($files)) {
            $single = $this->request->getFile('imagen');

            // Comprobar que el archivo exista, sea válido y todavía no se haya movido.
            if ($single && $single->isValid() && !$single->hasMoved()) {
                $files = [$single];
            }
        }

        // Si se recibieron archivos, comenzar su procesamiento.
        if (!empty($files)) {

            // Definir la ruta física donde se almacenarán las imágenes.
            // FCPATH representa la ruta absoluta del directorio público del proyecto.
            $dirPath = FCPATH . 'assets/media/Servicios/';

            // Si el directorio no existe, crearlo junto con los directorios
            // intermedios necesarios.
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0755, true);
            }

            // Generar una versión segura del nombre del servicio,
            // eliminando todos los caracteres que no sean letras o números.
            $safeName = preg_replace('/[^a-zA-Z0-9]/', '', trim($nombre));

            // Si el resultado queda vacío, utilizar un nombre genérico.
            if ($safeName === '')
                $safeName = 'servicio';

            // Inicializar el orden de las imágenes.
            // La primera imagen procesada tendrá el orden 1.
            $ordenIdx = 1;

            // Recorrer los archivos recibidos.
            foreach ($files as $file) {

                // Procesar únicamente archivos existentes, válidos,
                // que no hayan sido movidos y que no superen los 2 MB.
                if ($file && $file->isValid() && !$file->hasMoved() && $file->getSize() <= 2 * 1024 * 1024) {

                    // Generar un nombre único para el archivo convertido.
                    // uniqid() agrega un identificador para evitar colisiones
                    // entre nombres de imágenes.
                    $newName = 'servicio' . ucfirst($safeName) . '_' . uniqid() . '.webp';

                    // Construir la ruta física completa del archivo de destino.
                    $destPath = $dirPath . $newName;

                    // Convertir la imagen a WebP.
                    // Si la conversión se realiza correctamente, registrar
                    // su relación con el servicio en la tabla imagen.
                    if ($this->convertToWebp($file->getTempName(), $destPath)) {
                        $db->table('imagen')->insert([
                            'img_sId' => $servicioId,
                            'img_orden' => $ordenIdx++,
                            'img_ruta' => base_url('assets/media/Servicios/' . $newName)
                        ]);
                    }
                }
            }
        }

        // Finalizar la transacción y comprobar si todas las operaciones
        // realizadas durante el proceso se completaron correctamente.
        $db->transComplete();

        // Si alguna operación falló, devolver un error del servidor.
        if ($db->transStatus() === false) {
            return $this->failServerError('Error al crear el servicio.');
        }

        // Devolver una respuesta HTTP 201 (Created) indicando que
        // el servicio fue creado correctamente.
        return $this->respondCreated(['success' => true, 'message' => 'Servicio creado exitosamente.']);
    }

    /**
     * Actualiza los datos de un servicio existente y permite reemplazar su imagen.
     *
     * Busca el servicio, valida los nuevos datos, comprueba que el nombre
     * no esté siendo utilizado por otro servicio y actualiza su información.
     *
     * Si se recibe una nueva imagen, elimina la anterior y registra la nueva.
     *
     * @param mixed $id Identificador del servicio que se desea editar.
     */
    public function editar($id)
    {
        // Verificar que el usuario tenga una sesión iniciada
        // y que su rol corresponda a un administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Verificar que se haya proporcionado el identificador del servicio.
        if (!$id) {
            return $this->fail('ID de servicio no proporcionado.');
        }

        // Recuperar los datos enviados por el formulario o la solicitud JSON.
        $data = $this->getRequestData();

        // Obtener los valores del servicio, contemplando nombres alternativos
        // para los campos recibidos.
        $nombre = trim($data['nombre'] ?? $data['s_nbre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? $data['s_desc'] ?? '');
        $duracion = $data['duracion_minutos'] ?? $data['s_duracionMinutos'] ?? null;
        $precio = $data['precio'] ?? $data['s_precio'] ?? 0;
        $activo = $data['activo'] ?? $data['s_activo'] ?? $data['u_activo'] ?? 1;

        // Validar la presencia de los datos obligatorios.
        if ($nombre === '' || $duracion === null || $duracion === '') {
            return $this->fail('Faltan datos obligatorios.');
        }

        // Comprobar que la duración se encuentre dentro del rango permitido.
        if (intval($duracion) < 5 || intval($duracion) > 480) {
            return $this->fail('La duración debe estar entre 5 y 480 minutos.');
        }

        // Impedir que el nombre o la descripción comiencen con un guión.
        if ((!empty($nombre) && str_starts_with($nombre, '-')) || (!empty($descripcion) && str_starts_with($descripcion, '-'))) {
            return $this->fail('El nombre o descripción no pueden empezar con guión.');
        }

        // Establecer la conexión con la base de datos.
        $db = \Config\Database::connect();

        // Validar si el servicio ya existe con el mismo nombre (excluyendo el actual).
        // Se compara el nombre en mayúsculas y se excluye el registro
        // que se está editando mediante su identificador.
        $nombreMayus = strtoupper(trim($nombre));
        $existe = $db->table('servicio')
            ->where('UPPER(s_nbre)', $nombreMayus)
            ->where('s_id !=', $id)
            ->countAllResults();

        // Si otro servicio ya utiliza ese nombre, impedir la actualización.
        if ($existe > 0) {
            return $this->fail('Ya existe otro servicio con ese nombre. Por favor, utiliza otro nombre.');
        }

        // Buscar el servicio que se desea modificar.
        $servicio = $db->table('servicio')->where('s_id', $id)->get()->getRow();

        // Si no existe, devolver un error 404.
        if (!$servicio) {
            return $this->failNotFound('Servicio no encontrado.');
        }

        // Iniciar una transacción para agrupar la actualización
        // de los datos y el posible reemplazo de la imagen.
        $db->transStart();

        // Preparar los nuevos valores del servicio.
        $servicioData = [
            's_nbre' => trim($nombre),
            's_desc' => trim($descripcion),
            's_duracionMinutos' => intval($duracion),
            's_precio' => floatval($precio ?? 0),
            's_activo' => intval($activo)
        ];

        // Actualizar el registro correspondiente al servicio.
        $db->table('servicio')->where('s_id', $id)->update($servicioData);

        // Manejar imagen si se subió una nueva.
        // Recuperar el archivo enviado mediante el campo "imagen".
        $file = $this->request->getFile('imagen');

        // Continuar únicamente si existe un archivo válido que todavía no se movió.
        if ($file && $file->isValid() && !$file->hasMoved()) {

            // Borrar imagen vieja si existe físicamente.
            // Buscar una imagen asociada al servicio para reemplazarla.
            $oldImage = $db->table('imagen')->where('img_sId', $id)->get()->getRow();

            // Si existe una imagen y tiene una ruta registrada, intentar eliminarla.
            if ($oldImage && $oldImage->img_ruta) {

                // Try to find the file locally and delete it.
                // basename() obtiene únicamente el nombre del archivo,
                // descartando los directorios incluidos en la ruta almacenada.
                $filename = basename($oldImage->img_ruta);

                // Construir la ruta física donde se espera encontrar la imagen.
                $localPath = FCPATH . 'assets/media/Servicios/' . $filename;

                // Si no se encuentra en el directorio de Servicios,
                // intentar localizarla en el directorio general de medios.
                if (!file_exists($localPath)) {
                    // Try old path just in case.
                    $localPath = FCPATH . 'assets/media/' . $filename;
                }

                // Si el archivo existe, eliminarlo físicamente.
                // @ evita mostrar advertencias si ocurre un problema al eliminarlo.
                if (file_exists($localPath)) {
                    @unlink($localPath);
                }

                // Eliminar de la base de datos los registros de imágenes
                // asociados al servicio.
                $db->table('imagen')->where('img_sId', $id)->delete();
            }

            // Definir el directorio físico donde se guardará la nueva imagen.
            $dirPath = FCPATH . 'assets/media/Servicios/';

            // Crear el directorio si todavía no existe.
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0755, true);
            }

            // Generar un nombre seguro a partir del nombre del servicio.
            $safeName = preg_replace('/[^a-zA-Z0-9]/', '', trim($nombre));

            // Generar un nombre único para la nueva imagen en formato WebP.
            $newName = 'servicio' . ucfirst($safeName) . '_' . uniqid() . '.webp';

            // Construir la ruta completa del archivo de destino.
            $destPath = $dirPath . $newName;

            // Convertir la nueva imagen y, si el proceso es exitoso,
            // insertar su información en la tabla imagen.
            if ($this->convertToWebp($file->getTempName(), $destPath)) {
                $db->table('imagen')->insert([
                    'img_sId' => $id,
                    'img_ruta' => base_url('assets/media/Servicios/' . $newName)
                ]);
            }
        }

        // Finalizar la transacción y verificar si se produjeron errores.
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al actualizar el servicio.');
        }

        // Informar que la actualización se completó correctamente.
        return $this->respond(['success' => true, 'message' => 'Servicio actualizado exitosamente.']);
    }

    /**
     * Elimina un servicio y sus registros relacionados.
     *
     * Antes de eliminarlo, comprueba que no tenga turnos registrados
     * en la agenda. Si cumple esta condición, elimina sus imágenes,
     * las relaciones con profesionales, sus horarios y el servicio.
     *
     * @param mixed $id Identificador del servicio que se desea eliminar.
     */
    public function eliminar($id)
    {
        // Verificar que el usuario esté autenticado y sea administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Validar que se haya proporcionado el identificador del servicio.
        if (!$id) {
            return $this->fail('ID de servicio no proporcionado.');
        }

        // Conectar con la base de datos y buscar el servicio.
        $db = \Config\Database::connect();
        $servicio = $db->table('servicio')->where('s_id', $id)->get()->getRow();

        // Si no existe, devolver un error 404.
        if (!$servicio) {
            return $this->failNotFound('Servicio no encontrado.');
        }

        // Control de Integridad: Verificar si posee turnos registrados en la agenda.
        // Se relacionan las tablas horario y turno para comprobar si existen
        // turnos asociados a alguno de los horarios del servicio.
        $tieneTurnos = $db->table('horario')
            ->join('turno', 'turno.t_hId = horario.h_id')
            ->where('horario.h_sId', $id)
            ->countAllResults();

        // Si existen turnos relacionados, impedir la eliminación física.
        // Se indica utilizar la baja lógica, desactivando el servicio.
        if ($tieneTurnos > 0) {
            return $this->fail('No es posible eliminar el servicio porque posee turnos registrados en la agenda. Utilice la baja lógica desactivándolo.');
        }

        // Iniciar una transacción para agrupar las eliminaciones.
        $db->transStart();

        // Delete physical image.
        // Buscar una imagen asociada al servicio para eliminar también
        // el archivo correspondiente del almacenamiento físico.
        $oldImage = $db->table('imagen')->where('img_sId', $id)->get()->getRow();

        // Si existe una imagen con una ruta registrada, obtener su nombre.
        if ($oldImage && $oldImage->img_ruta) {
            $filename = basename($oldImage->img_ruta);

            // Construir la ruta local donde se espera encontrar el archivo.
            $localPath = FCPATH . 'assets/media/Servicios/' . $filename;

            // Si no se encuentra en la carpeta de Servicios,
            // intentar buscarlo en la carpeta general de medios.
            if (!file_exists($localPath)) {
                $localPath = FCPATH . 'assets/media/' . $filename;
            }

            // Si existe físicamente, eliminar el archivo.
            if (file_exists($localPath)) {
                @unlink($localPath);
            }

            // Eliminar los registros de imágenes asociados al servicio.
            $db->table('imagen')->where('img_sId', $id)->delete();
        }

        // Delete dependencies.
        // Eliminar las relaciones entre el servicio y las profesionales.
        $db->table('profesional_servicio')->where('p_sId', $id)->delete();

        // Eliminar los horarios asociados al servicio.
        $db->table('horario')->where('h_sId', $id)->delete();

        // Finalmente, eliminar el registro principal del servicio.
        $db->table('servicio')->where('s_id', $id)->delete();

        // Finalizar la transacción y comprobar su resultado.
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al eliminar el servicio.');
        }

        // Devolver una respuesta HTTP 200 indicando que el servicio
        // y sus dependencias fueron eliminados correctamente.
        return $this->respondDeleted(['success' => true, 'message' => 'Servicio eliminado correctamente.']);
    }

    /**
     * Actualiza las profesionales asignadas a un servicio.
     *
     * Recibe una lista de identificadores de profesionales,
     * elimina las relaciones existentes y registra las nuevas.
     *
     * La operación se realiza dentro de una transacción.
     *
     * @param mixed $id Identificador del servicio.
     */
    public function profesionales($id)
    {
        // Verificar que el usuario esté autenticado y tenga rol de administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Validar que se haya recibido el identificador del servicio.
        if (!$id) {
            return $this->fail('ID de servicio no proporcionado.');
        }

        // Recuperar los datos enviados.
        $data = $this->getRequestData();

        // Obtener el arreglo de profesionales seleccionados.
        // Si el campo no existe o no es un arreglo, se utiliza un arreglo vacío.
        $profesionales = isset($data['profesionales']) && is_array($data['profesionales']) ? $data['profesionales'] : [];

        // Conectar con la base de datos e iniciar una transacción.
        $db = \Config\Database::connect();
        $db->transStart();

        // Borrar relaciones actuales.
        // Se eliminan las asignaciones existentes del servicio en la tabla intermedia.
        $db->table('profesional_servicio')->where('p_sId', $id)->delete();

        // Si se recibieron profesionales, preparar las nuevas relaciones.
        if (!empty($profesionales)) {
            $insertData = [];

            // Recorrer los identificadores recibidos y construir
            // un arreglo de registros para la tabla profesional_servicio.
            foreach ($profesionales as $id_profesional) {
                $insertData[] = [
                    'p_sId' => intval($id),
                    'p_id' => intval($id_profesional)
                ];
            }

            // Insertar todas las relaciones en una única operación.
            $db->table('profesional_servicio')->insertBatch($insertData);
        }

        // Finalizar la transacción.
        $db->transComplete();

        // Comprobar si hubo errores durante la eliminación o inserción.
        if ($db->transStatus() === false) {
            return $this->failServerError('Error al asignar los profesionales.');
        }

        // Informar que las profesionales fueron asignadas correctamente.
        return $this->respond(['success' => true, 'message' => 'Profesionales asignados correctamente.']);
    }

    /**
     * Actualiza el orden de visualización de los servicios.
     *
     * Recibe una lista de identificadores y actualiza el campo s_orden
     * de cada servicio según la posición que ocupa dentro del arreglo.
     *
     * @return mixed Respuesta JSON con el resultado de la operación.
     */
    public function ordenar()
    {
        // Verificar que el usuario tenga una sesión iniciada
        // y que su rol sea administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Recuperar los datos enviados.
        $data = $this->getRequestData();

        // Obtener el arreglo con el orden solicitado.
        // Se admiten dos nombres posibles para el campo recibido.
        $orden = $data['s_orden'] ?? $data['orden'] ?? null;

        // Verificar que se haya recibido un arreglo no vacío.
        if (!is_array($orden) || empty($orden)) {
            return $this->fail('Se requiere un array de IDs en orden.');
        }

        // Conectar con la base de datos e iniciar una transacción.
        $db = \Config\Database::connect();
        $db->transStart();

        // Recorrer los identificadores manteniendo su posición dentro del arreglo.
        foreach ($orden as $position => $id_servicio) {

            // Actualizar el orden del servicio correspondiente.
            // Se suma 1 a la posición porque los órdenes comienzan en 1.
            $db->table('servicio')
                ->where('s_id', intval($id_servicio))
                ->update(['s_orden' => intval($position) + 1]);
        }

        // Finalizar la transacción.
        $db->transComplete();

        // Si alguna actualización falló, devolver un error del servidor.
        if ($db->transStatus() === false) {
            return $this->failServerError('Error al guardar el orden.');
        }

        // Confirmar que el nuevo orden se guardó correctamente.
        return $this->respond(['success' => true, 'message' => 'Orden guardado correctamente.']);
    }

    // =========================================================================
    // GESTIÓN DE MÚLTIPLES IMÁGENES Y ORDENAMIENTO (DRAG & DROP)
    // =========================================================================

    /**
     * GET /api/servicios/imagenes/(:num)
     *
     * Obtiene el listado completo de fotos de un servicio ordenadas por img_orden.
     *
     * Devuelve los datos básicos del servicio y todas sus imágenes,
     * ordenadas primero por img_orden y luego por img_id.
     *
     * @param mixed $id Identificador del servicio.
     */
    public function imagenes($id)
    {
        // Verificar que el usuario tenga una sesión iniciada
        // y que su rol sea administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Convertir el identificador recibido a entero.
        $id = (int) $id;

        // Conectar con la base de datos y buscar el servicio.
        $db = \Config\Database::connect();
        $servicio = $db->table('servicio')->where('s_id', $id)->get()->getRow();

        // Si el servicio no existe, devolver un error 404.
        if (!$servicio) {
            return $this->failNotFound('Servicio no encontrado.');
        }

        // Obtener todas las imágenes asociadas al servicio.
        // Se ordenan de forma ascendente por img_orden y, en caso de empate,
        // por img_id para mantener un orden estable.
        $imagenes = $db->table('imagen')
            ->where('img_sId', $id)
            ->orderBy('img_orden', 'ASC')
            ->orderBy('img_id', 'ASC')
            ->get()
            ->getResultArray();

        // Devolver los datos del servicio junto con su colección de imágenes.
        return $this->respond([
            'success' => true,
            'servicio' => [
                's_id' => $servicio->s_id,
                's_nbre' => $servicio->s_nbre
            ],
            'imagenes' => $imagenes
        ]);
    }

    /**
     * POST /api/servicios/imagenes/subir/(:num)
     *
     * Permite subir una o múltiples fotos a un servicio, convirtiéndolas a WebP.
     *
     * Comprueba que el servicio exista, recupera los archivos enviados,
     * valida su tamaño, los convierte al formato WebP y registra sus rutas
     * en la tabla imagen, asignándoles un orden consecutivo.
     *
     * @param mixed $id Identificador del servicio al que se asociarán las imágenes.
     */
    public function subirImagenes($id)
    {
        // Verificar que el usuario esté autenticado y sea administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Convertir el ID a entero y comprobar que el servicio exista.
        $id = (int) $id;
        $db = \Config\Database::connect();
        $servicio = $db->table('servicio')->where('s_id', $id)->get()->getRow();

        if (!$servicio) {
            return $this->failNotFound('Servicio no encontrado.');
        }

        // Obtener archivos enviados (soporta input 'imagenes[]' o 'imagen').
        // Primero se intenta recuperar una colección de archivos.
        $files = $this->request->getFileMultiple('imagenes');

        // Si no hay archivos múltiples, intentar recuperar un único archivo.
        if (empty($files)) {
            $single = $this->request->getFile('imagen');

            // Incorporar el archivo al arreglo si existe, es válido
            // y todavía no fue movido.
            if ($single && $single->isValid() && !$single->hasMoved()) {
                $files = [$single];
            }
        }

        // Si no se recibió ningún archivo, impedir que continúe la operación.
        if (empty($files)) {
            return $this->fail('No se seleccionó ningún archivo de imagen para subir.');
        }

        // Obtener el mayor img_orden actual del servicio.
        // selectMax() recupera el valor máximo registrado en ese campo.
        $maxRow = $db->table('imagen')
            ->selectMax('img_orden', 'max_orden')
            ->where('img_sId', $id)
            ->get()
            ->getRow();

        // Calcular el próximo orden disponible.
        // Si existen imágenes, se utiliza el máximo actual más uno.
        // Si todavía no hay imágenes, el orden inicial será 1.
        $nextOrden = $maxRow && $maxRow->max_orden !== null ? ((int) $maxRow->max_orden) + 1 : 1;

        // Definir el directorio físico de almacenamiento.
        $dirPath = FCPATH . 'assets/media/Servicios/';

        // Crear el directorio si no existe.
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }

        // Generar un nombre seguro a partir del nombre del servicio.
        // Se eliminan los caracteres que no sean letras o números.
        $safeName = preg_replace('/[^a-zA-Z0-9]/', '', trim($servicio->s_nbre));

        // Si el resultado queda vacío, asignar un nombre genérico.
        if ($safeName === '') {
            $safeName = 'servicio';
        }

        // Inicializar el contador de imágenes procesadas correctamente.
        $subidasCount = 0;

        // Iniciar una transacción para registrar los datos de las imágenes.
        $db->transStart();

        // Recorrer todos los archivos recibidos.
        foreach ($files as $file) {

            // Ignorar archivos inexistentes, inválidos o que ya fueron movidos.
            if (!$file || !$file->isValid() || $file->hasMoved()) {
                continue;
            }

            // Validar tamaño máximo (2MB).
            // Los archivos que superen ese límite se omiten y se continúa
            // con el siguiente elemento del arreglo.
            if ($file->getSize() > 2 * 1024 * 1024) {
                continue;
            }

            // Generar un nombre único para la imagen convertida.
            $newName = 'servicio' . ucfirst($safeName) . '_' . uniqid() . '.webp';

            // Construir la ruta física del archivo de destino.
            $destPath = $dirPath . $newName;

            // Intentar convertir la imagen a WebP.
            if ($this->convertToWebp($file->getTempName(), $destPath)) {

                // Si la conversión fue exitosa, registrar la imagen
                // en la base de datos con su servicio, orden y ruta pública.
                $db->table('imagen')->insert([
                    'img_sId' => $id,
                    'img_orden' => $nextOrden++,
                    'img_ruta' => base_url('assets/media/Servicios/' . $newName)
                ]);

                // Incrementar la cantidad de imágenes procesadas correctamente.
                $subidasCount++;
            }
        }

        // Finalizar la transacción y comprobar el resultado.
        $db->transComplete();

        // Si la transacción falló o no se procesó ninguna imagen,
        // devolver un error indicando que no se pudieron completar las subidas.
        if ($db->transStatus() === false || $subidasCount === 0) {
            return $this->failServerError('No se pudieron procesar las imágenes. Verificá que los archivos sean válidos (máx. 2MB).');
        }

        // Retornar la lista actualizada de imágenes.
        // Se consulta nuevamente la tabla para devolver los registros
        // con el orden que tienen almacenado después de la operación.
        $imagenesActualizadas = $db->table('imagen')
            ->where('img_sId', $id)
            ->orderBy('img_orden', 'ASC')
            ->orderBy('img_id', 'ASC')
            ->get()
            ->getResultArray();

        // Devolver una respuesta HTTP 201 (Created) con la cantidad de imágenes
        // procesadas y el listado actualizado.
        return $this->respondCreated([
            'success' => true,
            'message' => "Se subieron {$subidasCount} imagen(es) correctamente.",
            'imagenes' => $imagenesActualizadas
        ]);
    }

    /**
     * POST /api/servicios/imagenes/ordenar/(:num)
     *
     * Actualiza el orden de las fotos mediante Drag and Drop.
     * La primera imagen (img_orden = 1) es la portada del servicio.
     *
     * Recibe los identificadores de las imágenes en el orden deseado
     * y actualiza su posición dentro del servicio correspondiente.
     *
     * @param mixed $id Identificador del servicio.
     */
    public function ordenarImagenes($id)
    {
        // Verificar que el usuario esté autenticado y sea administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Convertir el identificador del servicio a entero.
        $id = (int) $id;

        // Recuperar los datos enviados y obtener el arreglo con el orden deseado.
        $data = $this->getRequestData();
        $orden = $data['orden'] ?? [];

        // Validar que se haya recibido un arreglo no vacío.
        if (!is_array($orden) || empty($orden)) {
            return $this->fail('Se requiere un listado de IDs en el orden deseado.');
        }

        // Conectar con la base de datos e iniciar una transacción.
        $db = \Config\Database::connect();
        $db->transStart();

        // Recorrer los identificadores conservando la posición de cada imagen.
        foreach ($orden as $index => $imgId) {

            // Actualizar img_orden para la imagen correspondiente.
            // La condición img_sId garantiza que la imagen pertenezca
            // al servicio cuyo orden se está modificando.
            $db->table('imagen')
                ->where('img_id', (int) $imgId)
                ->where('img_sId', $id)
                ->update(['img_orden' => $index + 1]);
        }

        // Finalizar la transacción.
        $db->transComplete();

        // Verificar que las actualizaciones se hayan completado correctamente.
        if ($db->transStatus() === false) {
            return $this->failServerError('No se pudo guardar el nuevo orden de imágenes.');
        }

        // Obtener portada actualizada.
        // La primera imagen según el orden ascendente será considerada portada.
        $portada = $db->table('imagen')
            ->where('img_sId', $id)
            ->orderBy('img_orden', 'ASC')
            ->orderBy('img_id', 'ASC')
            ->get()
            ->getRowArray();

        // Devolver el resultado y la ruta de la nueva portada.
        // Si no se encuentra una imagen, portada_url será null.
        return $this->respond([
            'success' => true,
            'message' => 'Orden de imágenes actualizado con éxito.',
            'portada_url' => $portada['img_ruta'] ?? null
        ]);
    }

    /**
     * DELETE /api/servicios/imagenes/eliminar/(:num)
     *
     * Elimina una imagen físicamente y reindexa el orden de las restantes.
     *
     * Primero localiza el registro y su archivo físico, luego elimina ambos
     * y reorganiza las posiciones de las imágenes que permanecen asociadas
     * al mismo servicio.
     *
     * @param mixed $imgId Identificador de la imagen que se desea eliminar.
     */
    public function eliminarImagen($imgId)
    {
        // Verificar que el usuario tenga una sesión iniciada
        // y que su rol sea administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Convertir el identificador de imagen a entero.
        $imgId = (int) $imgId;

        // Conectar con la base de datos y buscar el registro de la imagen.
        $db = \Config\Database::connect();
        $imagen = $db->table('imagen')->where('img_id', $imgId)->get()->getRow();

        // Si no existe, devolver un error 404.
        if (!$imagen) {
            return $this->failNotFound('Imagen no encontrada.');
        }

        // Recuperar el ID del servicio al que pertenece la imagen.
        $sId = (int) $imagen->img_sId;

        // Iniciar una transacción para agrupar las operaciones sobre la base de datos.
        $db->transStart();

        // Borrar archivo físico si existe.
        // Comprobar que la imagen tenga una ruta registrada.
        if (!empty($imagen->img_ruta)) {

            // Obtener únicamente el nombre del archivo.
            $filename = basename($imagen->img_ruta);

            // Construir la ruta local esperada.
            $localPath = FCPATH . 'assets/media/Servicios/' . $filename;

            // Si no existe en Servicios, intentar encontrarlo en la carpeta general.
            if (!file_exists($localPath)) {
                $localPath = FCPATH . 'assets/media/' . $filename;
            }

            // Eliminar el archivo físico si se encuentra.
            if (file_exists($localPath)) {
                @unlink($localPath);
            }
        }

        // Eliminar fila de BD.
        // Borrar el registro correspondiente a la imagen de la tabla imagen.
        $db->table('imagen')->where('img_id', $imgId)->delete();

        // Reindexar img_orden de las fotos que quedaron.
        // Recuperar las imágenes restantes del mismo servicio,
        // ordenadas según su posición anterior.
        $restantes = $db->table('imagen')
            ->where('img_sId', $sId)
            ->orderBy('img_orden', 'ASC')
            ->orderBy('img_id', 'ASC')
            ->get()
            ->getResult();

        // Recorrer las imágenes restantes y asignarles un orden consecutivo.
        // El índice comienza en cero, por eso se suma uno.
        foreach ($restantes as $idx => $rest) {
            $db->table('imagen')
                ->where('img_id', $rest->img_id)
                ->update(['img_orden' => $idx + 1]);
        }

        // Finalizar la transacción y verificar el resultado.
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al eliminar la imagen.');
        }

        // Obtener portada restante si existe.
        // Se vuelve a consultar el listado para recuperar la primera imagen
        // después de haber eliminado y reordenado los registros.
        $nuevaPortada = $db->table('imagen')
            ->where('img_sId', $sId)
            ->orderBy('img_orden', 'ASC')
            ->orderBy('img_id', 'ASC')
            ->get()
            ->getRowArray();

        // Devolver una respuesta indicando que la imagen fue eliminada,
        // junto con la cantidad de imágenes restantes y la nueva portada.
        return $this->respondDeleted([
            'success' => true,
            'message' => 'Imagen eliminada correctamente.',
            'total' => count($restantes),
            'portada_url' => $nuevaPortada['img_ruta'] ?? null
        ]);
    }

    /**
     * Activa o desactiva un servicio.
     *
     * Invierte el valor actual del campo s_activo:
     * si está activo, lo desactiva; si está inactivo, lo activa.
     *
     * No elimina el registro de la base de datos.
     *
     * @param mixed $id Identificador del servicio.
     */
    public function toggleEstado($id)
    {
        // Verificar que el usuario esté autenticado y tenga rol de administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Comprobar que se haya proporcionado el ID del servicio.
        if (!$id) {
            return $this->fail('ID de servicio no proporcionado.');
        }

        // Conectar con la base de datos y buscar el servicio.
        $db = \Config\Database::connect();
        $servicio = $db->table('servicio')->where('s_id', intval($id))->get()->getRow();

        // Si no existe, devolver un error 404.
        if (!$servicio) {
            return $this->failNotFound('Servicio no encontrado.');
        }

        // Calcular el nuevo estado invirtiendo el valor actual.
        // Si s_activo es verdadero, pasa a 0; si es falso, pasa a 1.
        $nuevoEstado = $servicio->s_activo ? 0 : 1;

        // Actualizar el estado del servicio en la base de datos.
        $db->table('servicio')->where('s_id', intval($id))->update(['s_activo' => $nuevoEstado]);

        // Preparar el mensaje según el estado resultante.
        $accion = $nuevoEstado ? 'activado' : 'desactivado (inactivo)';

        // Devolver el resultado de la operación y el nuevo estado.
        return $this->respond([
            'success' => true,
            'message' => "Servicio {$accion} exitosamente.",
            'nuevo_estado' => $nuevoEstado
        ]);
    }
}
