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
                return move_uploaded_file($source, $destination) || rename($source, $destination);
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

        $nombre = $this->request->getPost('s_nbre');
        $descripcion = $this->request->getPost('s_desc');
        $duracion = $this->request->getPost('s_duracionMinutos');
        $precio = $this->request->getPost('s_precio');
        $activo = $this->request->getPost('u_activo') !== null ? $this->request->getPost('u_activo') : 1;

        if (empty(trim($nombre)) || empty(trim($duracion))) {
            return $this->fail('Faltan datos obligatorios (nombre o duración).');
        }
        if (intval($duracion) < 5 || intval($duracion) > 480) {
            return $this->fail('La duración debe estar entre 5 y 480 minutos.');
        }
        if (trim($nombre)[0] === '-' || trim($descripcion)[0] === '-') {
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
            'u_activo' => intval($activo),
            's_orden' => 0
        ];

        $db->table('servicio')->insert($servicioData);
        $servicioId = $db->insertID();

        // Manejar imagen
        $file = $this->request->getFile('imagen');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $dirPath = FCPATH . 'assets/media/Servicios/';
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0755, true);
            }

            // Sanitizar nombre para la ruta web
            $safeName = preg_replace('/[^a-zA-Z0-9]/', '', trim($nombre));
            $newName = 'servicio' . ucfirst($safeName) . '_' . uniqid() . '.webp';
            $destPath = $dirPath . $newName;

            if ($this->convertToWebp($file->getTempName(), $destPath)) {
                $db->table('imagen')->insert([
                    'img_sId' => $servicioId,
                    'img_ruta' => base_url('assets/media/Servicios/' . $newName)
                ]);
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

        $nombre = $this->request->getPost('s_nbre');
        $descripcion = $this->request->getPost('s_desc');
        $duracion = $this->request->getPost('s_duracionMinutos');
        $precio = $this->request->getPost('s_precio');
        $activo = $this->request->getPost('u_activo');

        if (empty(trim($nombre)) || empty(trim($duracion))) {
            return $this->fail('Faltan datos obligatorios.');
        }
        if (intval($duracion) < 5 || intval($duracion) > 480) {
            return $this->fail('La duración debe estar entre 5 y 480 minutos.');
        }
        if (trim($nombre)[0] === '-' || trim($descripcion)[0] === '-') {
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
            'u_activo' => intval($activo)
        ];

        $db->table('servicio')->where('s_id', $id)->update($servicioData);

        // Manejar imagen si se subió una nueva
        $file = $this->request->getFile('imagen');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Borrar imagen vieja si existe físicamente
            $oldImage = $db->table('imagen')->where('s_id', $id)->get()->getRow();
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
                $db->table('imagen')->where('s_id', $id)->delete();
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
        $oldImage = $db->table('imagen')->where('s_id', $id)->get()->getRow();
        if ($oldImage && $oldImage->img_ruta) {
            $filename = basename($oldImage->img_ruta);
            $localPath = FCPATH . 'assets/media/Servicios/' . $filename;
            if (!file_exists($localPath)) {
                $localPath = FCPATH . 'assets/media/' . $filename;
            }
            if (file_exists($localPath)) {
                @unlink($localPath);
            }
            $db->table('imagen')->where('s_id', $id)->delete();
        }

        // Delete dependencies
        $db->table('profesional_servicio')->where('s_id', $id)->delete();
        $db->table('horario')->where('s_id', $id)->delete();
        $db->table('turno')->where('s_id', $id)->delete();
        
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

        $request = $this->request->getJSON();
        $profesionales = isset($request->profesionales) && is_array($request->profesionales) ? $request->profesionales : [];

        $db = \Config\Database::connect();
        $db->transStart();

        // Borrar relaciones actuales
        $db->table('profesional_servicio')->where('s_id', $id)->delete();

        if (!empty($profesionales)) {
            $insertData = [];
            foreach ($profesionales as $id_profesional) {
                $insertData[] = [
                    's_id' => intval($id),
                    'p_id' => intval($id_profesional)
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

        $request = $this->request->getJSON();
        if (!isset($request->s_orden) || !is_array($request->s_orden) || empty($request->s_orden)) {
            return $this->fail('Se requiere un array de IDs en orden.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($request->s_orden as $position => $id_servicio) {
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
}
