<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class ServiciosApi extends BaseController
{
    use ResponseTrait;

    private function convertToWebp($source, $destination)
    {
        $info = getimagesize($source);
        if (!$info) return false;

        $mime = $info['mime'];
        
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/webp':
                // It's already webp, just move it
                return move_uploaded_file($source, $destination) || rename($source, $destination) || copy($source, $destination);
            default:
                return false;
        }

        if (!$image) return false;

        $result = imagewebp($image, $destination, 85);
        imagedestroy($image);
        
        return $result;
    }

    public function crear()
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        $data = $this->getRequestData();

        $nombre = trim($data['nombre'] ?? $data['s_nbre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? $data['s_desc'] ?? '');
        $duracion = $data['duracion_minutos'] ?? $data['s_duracionMinutos'] ?? null;
        $precio = $data['precio'] ?? $data['s_precio'] ?? 0;
        $activo = $data['activo'] ?? $data['s_activo'] ?? $data['u_activo'] ?? 1;

        if ($nombre === '' || $duracion === null || $duracion === '') {
            return $this->fail('Faltan datos obligatorios (nombre o duración).');
        }
        if (intval($duracion) < 5 || intval($duracion) > 480) {
            return $this->fail('La duración debe estar entre 5 y 480 minutos.');
        }
        if ((!empty($nombre) && str_starts_with($nombre, '-')) || (!empty($descripcion) && str_starts_with($descripcion, '-'))) {
            return $this->fail('El nombre o descripción no pueden empezar con guión.');
        }

        $db = \Config\Database::connect();
        
        // Validar si el servicio ya existe (pasando a mayúsculas)
        $nombreMayus = strtoupper(trim($nombre));
        $existe = $db->table('servicio')
                     ->where('UPPER(s_nbre)', $nombreMayus)
                     ->countAllResults();
                     
        if ($existe > 0) {
            return $this->fail('Ya existe un servicio con ese nombre. Por favor, utiliza otro nombre.');
        }
        
        $db->transStart();

        // Desplazar los demás servicios
        $db->table('servicio')->set('s_orden', 's_orden + 1', false)->update();

        $servicioData = [
            's_nbre' => trim($nombre),
            's_desc' => trim($descripcion),
            's_duracionMinutos' => intval($duracion),
            's_precio' => floatval($precio ?? 0),
            's_activo' => intval($activo),
            's_orden' => 0
        ];

        $db->table('servicio')->insert($servicioData);
        $servicioId = $db->insertID();

        // Manejar imagenes (soporta múltiples fotos o una sola)
        $files = $this->request->getFileMultiple('imagenes');
        if (empty($files)) {
            $single = $this->request->getFile('imagen');
            if ($single && $single->isValid() && !$single->hasMoved()) {
                $files = [$single];
            }
        }

        if (!empty($files)) {
            $dirPath = FCPATH . 'assets/media/Servicios/';
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0755, true);
            }

            $safeName = preg_replace('/[^a-zA-Z0-9]/', '', trim($nombre));
            if ($safeName === '') $safeName = 'servicio';

            $ordenIdx = 1;
            foreach ($files as $file) {
                if ($file && $file->isValid() && !$file->hasMoved() && $file->getSize() <= 2 * 1024 * 1024) {
                    $newName = 'servicio' . ucfirst($safeName) . '_' . uniqid() . '.webp';
                    $destPath = $dirPath . $newName;

                    if ($this->convertToWebp($file->getTempName(), $destPath)) {
                        $db->table('imagen')->insert([
                            'img_sId'   => $servicioId,
                            'img_orden' => $ordenIdx++,
                            'img_ruta'  => base_url('assets/media/Servicios/' . $newName)
                        ]);
                    }
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al crear el servicio.');
        }

        return $this->respondCreated(['success' => true, 'message' => 'Servicio creado exitosamente.']);
    }

    public function editar($id)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        if (!$id) {
            return $this->fail('ID de servicio no proporcionado.');
        }

        $data = $this->getRequestData();

        $nombre = trim($data['nombre'] ?? $data['s_nbre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? $data['s_desc'] ?? '');
        $duracion = $data['duracion_minutos'] ?? $data['s_duracionMinutos'] ?? null;
        $precio = $data['precio'] ?? $data['s_precio'] ?? 0;
        $activo = $data['activo'] ?? $data['s_activo'] ?? $data['u_activo'] ?? 1;

        if ($nombre === '' || $duracion === null || $duracion === '') {
            return $this->fail('Faltan datos obligatorios.');
        }
        if (intval($duracion) < 5 || intval($duracion) > 480) {
            return $this->fail('La duración debe estar entre 5 y 480 minutos.');
        }
        if ((!empty($nombre) && str_starts_with($nombre, '-')) || (!empty($descripcion) && str_starts_with($descripcion, '-'))) {
            return $this->fail('El nombre o descripción no pueden empezar con guión.');
        }

        $db = \Config\Database::connect();

        // Validar si el servicio ya existe con el mismo nombre (excluyendo el actual)
        $nombreMayus = strtoupper(trim($nombre));
        $existe = $db->table('servicio')
                     ->where('UPPER(s_nbre)', $nombreMayus)
                     ->where('s_id !=', $id)
                     ->countAllResults();
                     
        if ($existe > 0) {
            return $this->fail('Ya existe otro servicio con ese nombre. Por favor, utiliza otro nombre.');
        }
        $servicio = $db->table('servicio')->where('s_id', $id)->get()->getRow();
        if (!$servicio) {
            return $this->failNotFound('Servicio no encontrado.');
        }

        $db->transStart();

        $servicioData = [
            's_nbre' => trim($nombre),
            's_desc' => trim($descripcion),
            's_duracionMinutos' => intval($duracion),
            's_precio' => floatval($precio ?? 0),
            's_activo' => intval($activo)
        ];

        $db->table('servicio')->where('s_id', $id)->update($servicioData);

        // Manejar imagen si se subió una nueva
        $file = $this->request->getFile('imagen');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Borrar imagen vieja si existe físicamente
            $oldImage = $db->table('imagen')->where('img_sId', $id)->get()->getRow();
            if ($oldImage && $oldImage->img_ruta) {
                // Try to find the file locally and delete it
                $filename = basename($oldImage->img_ruta);
                $localPath = FCPATH . 'assets/media/Servicios/' . $filename;
                if (!file_exists($localPath)) {
                    // Try old path just in case
                    $localPath = FCPATH . 'assets/media/' . $filename;
                }
                if (file_exists($localPath)) {
                    @unlink($localPath);
                }
                $db->table('imagen')->where('img_sId', $id)->delete();
            }

            $dirPath = FCPATH . 'assets/media/Servicios/';
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0755, true);
            }

            $safeName = preg_replace('/[^a-zA-Z0-9]/', '', trim($nombre));
            $newName = 'servicio' . ucfirst($safeName) . '_' . uniqid() . '.webp';
            $destPath = $dirPath . $newName;

            if ($this->convertToWebp($file->getTempName(), $destPath)) {
                $db->table('imagen')->insert([
                    'img_sId' => $id,
                    'img_ruta' => base_url('assets/media/Servicios/' . $newName)
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al actualizar el servicio.');
        }

        return $this->respond(['success' => true, 'message' => 'Servicio actualizado exitosamente.']);
    }

    public function eliminar($id)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        if (!$id) {
            return $this->fail('ID de servicio no proporcionado.');
        }

        $db = \Config\Database::connect();
        $servicio = $db->table('servicio')->where('s_id', $id)->get()->getRow();

        if (!$servicio) {
            return $this->failNotFound('Servicio no encontrado.');
        }

        $db->transStart();

        // Delete physical image
        $oldImage = $db->table('imagen')->where('img_sId', $id)->get()->getRow();
        if ($oldImage && $oldImage->img_ruta) {
            $filename = basename($oldImage->img_ruta);
            $localPath = FCPATH . 'assets/media/Servicios/' . $filename;
            if (!file_exists($localPath)) {
                $localPath = FCPATH . 'assets/media/' . $filename;
            }
            if (file_exists($localPath)) {
                @unlink($localPath);
            }
            $db->table('imagen')->where('img_sId', $id)->delete();
        }

        // Delete dependencies
        $db->table('profesional_servicio')->where('p_sId', $id)->delete();
        $db->table('horario')->where('h_sId', $id)->delete();
        
        $db->table('servicio')->where('s_id', $id)->delete();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al eliminar el servicio.');
        }

        return $this->respondDeleted(['success' => true, 'message' => 'Servicio eliminado correctamente.']);
    }

    public function profesionales($id)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        if (!$id) {
            return $this->fail('ID de servicio no proporcionado.');
        }

        $data = $this->getRequestData();
        $profesionales = isset($data['profesionales']) && is_array($data['profesionales']) ? $data['profesionales'] : [];

        $db = \Config\Database::connect();
        $db->transStart();

        // Borrar relaciones actuales
        $db->table('profesional_servicio')->where('p_sId', $id)->delete();

        if (!empty($profesionales)) {
            $insertData = [];
            foreach ($profesionales as $id_profesional) {
                $insertData[] = [
                    'p_sId' => intval($id),
                    'p_id'  => intval($id_profesional)
                ];
            }
            $db->table('profesional_servicio')->insertBatch($insertData);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al asignar los profesionales.');
        }

        return $this->respond(['success' => true, 'message' => 'Profesionales asignados correctamente.']);
    }

    public function ordenar()
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        $data = $this->getRequestData();
        $orden = $data['s_orden'] ?? $data['orden'] ?? null;
        if (!is_array($orden) || empty($orden)) {
            return $this->fail('Se requiere un array de IDs en orden.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($orden as $position => $id_servicio) {
            $db->table('servicio')
               ->where('s_id', intval($id_servicio))
               ->update(['s_orden' => intval($position) + 1]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al guardar el orden.');
        }

        return $this->respond(['success' => true, 'message' => 'Orden guardado correctamente.']);
    }

    // =========================================================================
    // GESTIÓN DE MÚLTIPLES IMÁGENES Y ORDENAMIENTO (DRAG & DROP)
    // =========================================================================

    /**
     * GET /api/servicios/imagenes/(:num)
     * Obtiene el listado completo de fotos de un servicio ordenadas por img_orden.
     */
    public function imagenes($id)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        $id = (int) $id;
        $db = \Config\Database::connect();
        $servicio = $db->table('servicio')->where('s_id', $id)->get()->getRow();
        if (!$servicio) {
            return $this->failNotFound('Servicio no encontrado.');
        }

        $imagenes = $db->table('imagen')
            ->where('img_sId', $id)
            ->orderBy('img_orden', 'ASC')
            ->orderBy('img_id', 'ASC')
            ->get()
            ->getResultArray();

        return $this->respond([
            'success'  => true,
            'servicio' => [
                's_id'   => $servicio->s_id,
                's_nbre' => $servicio->s_nbre
            ],
            'imagenes' => $imagenes
        ]);
    }

    /**
     * POST /api/servicios/imagenes/subir/(:num)
     * Permite subir una o múltiples fotos a un servicio, convirtiéndolas a WebP.
     */
    public function subirImagenes($id)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        $id = (int) $id;
        $db = \Config\Database::connect();
        $servicio = $db->table('servicio')->where('s_id', $id)->get()->getRow();
        if (!$servicio) {
            return $this->failNotFound('Servicio no encontrado.');
        }

        // Obtener archivos enviados (soporta input 'imagenes[]' o 'imagen')
        $files = $this->request->getFileMultiple('imagenes');
        if (empty($files)) {
            $single = $this->request->getFile('imagen');
            if ($single && $single->isValid() && !$single->hasMoved()) {
                $files = [$single];
            }
        }

        if (empty($files)) {
            return $this->fail('No se seleccionó ningún archivo de imagen para subir.');
        }

        // Obtener el mayor img_orden actual del servicio
        $maxRow = $db->table('imagen')
            ->selectMax('img_orden', 'max_orden')
            ->where('img_sId', $id)
            ->get()
            ->getRow();
        $nextOrden = $maxRow && $maxRow->max_orden !== null ? ((int) $maxRow->max_orden) + 1 : 1;

        $dirPath = FCPATH . 'assets/media/Servicios/';
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9]/', '', trim($servicio->s_nbre));
        if ($safeName === '') {
            $safeName = 'servicio';
        }

        $subidasCount = 0;
        $db->transStart();

        foreach ($files as $file) {
            if (!$file || !$file->isValid() || $file->hasMoved()) {
                continue;
            }

            // Validar tamaño máximo (2MB)
            if ($file->getSize() > 2 * 1024 * 1024) {
                continue;
            }

            $newName = 'servicio' . ucfirst($safeName) . '_' . uniqid() . '.webp';
            $destPath = $dirPath . $newName;

            if ($this->convertToWebp($file->getTempName(), $destPath)) {
                $db->table('imagen')->insert([
                    'img_sId'   => $id,
                    'img_orden' => $nextOrden++,
                    'img_ruta'  => base_url('assets/media/Servicios/' . $newName)
                ]);
                $subidasCount++;
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false || $subidasCount === 0) {
            return $this->failServerError('No se pudieron procesar las imágenes. Verificá que los archivos sean válidos (máx. 2MB).');
        }

        // Retornar la lista actualizada de imágenes
        $imagenesActualizadas = $db->table('imagen')
            ->where('img_sId', $id)
            ->orderBy('img_orden', 'ASC')
            ->orderBy('img_id', 'ASC')
            ->get()
            ->getResultArray();

        return $this->respondCreated([
            'success'   => true,
            'message'   => "Se subieron {$subidasCount} imagen(es) correctamente.",
            'imagenes'  => $imagenesActualizadas
        ]);
    }

    /**
     * POST /api/servicios/imagenes/ordenar/(:num)
     * Actualiza el orden de las fotos mediante Drag and Drop.
     * La primera imagen (img_orden = 1) es la portada del servicio.
     */
    public function ordenarImagenes($id)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        $id = (int) $id;
        $data = $this->getRequestData();
        $orden = $data['orden'] ?? [];

        if (!is_array($orden) || empty($orden)) {
            return $this->fail('Se requiere un listado de IDs en el orden deseado.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($orden as $index => $imgId) {
            $db->table('imagen')
                ->where('img_id', (int) $imgId)
                ->where('img_sId', $id)
                ->update(['img_orden' => $index + 1]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('No se pudo guardar el nuevo orden de imágenes.');
        }

        // Obtener portada actualizada
        $portada = $db->table('imagen')
            ->where('img_sId', $id)
            ->orderBy('img_orden', 'ASC')
            ->orderBy('img_id', 'ASC')
            ->get()
            ->getRowArray();

        return $this->respond([
            'success'     => true,
            'message'     => 'Orden de imágenes actualizado con éxito.',
            'portada_url' => $portada['img_ruta'] ?? null
        ]);
    }

    /**
     * DELETE /api/servicios/imagenes/eliminar/(:num)
     * Elimina una imagen físicamente y reindexa el orden de las restantes.
     */
    public function eliminarImagen($imgId)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        $imgId = (int) $imgId;
        $db = \Config\Database::connect();
        $imagen = $db->table('imagen')->where('img_id', $imgId)->get()->getRow();

        if (!$imagen) {
            return $this->failNotFound('Imagen no encontrada.');
        }

        $sId = (int) $imagen->img_sId;

        $db->transStart();

        // Borrar archivo físico si existe
        if (!empty($imagen->img_ruta)) {
            $filename = basename($imagen->img_ruta);
            $localPath = FCPATH . 'assets/media/Servicios/' . $filename;
            if (!file_exists($localPath)) {
                $localPath = FCPATH . 'assets/media/' . $filename;
            }
            if (file_exists($localPath)) {
                @unlink($localPath);
            }
        }

        // Eliminar fila de BD
        $db->table('imagen')->where('img_id', $imgId)->delete();

        // Reindexar img_orden de las fotos que quedaron
        $restantes = $db->table('imagen')
            ->where('img_sId', $sId)
            ->orderBy('img_orden', 'ASC')
            ->orderBy('img_id', 'ASC')
            ->get()
            ->getResult();

        foreach ($restantes as $idx => $rest) {
            $db->table('imagen')
                ->where('img_id', $rest->img_id)
                ->update(['img_orden' => $idx + 1]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al eliminar la imagen.');
        }

        // Obtener portada restante si existe
        $nuevaPortada = $db->table('imagen')
            ->where('img_sId', $sId)
            ->orderBy('img_orden', 'ASC')
            ->orderBy('img_id', 'ASC')
            ->get()
            ->getRowArray();

        return $this->respondDeleted([
            'success'     => true,
            'message'     => 'Imagen eliminada correctamente.',
            'total'       => count($restantes),
            'portada_url' => $nuevaPortada['img_ruta'] ?? null
        ]);
    }
}
