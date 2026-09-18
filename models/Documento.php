<?php
/**
 * Modelo de Documento
 */

class Documento extends Modelo {
    protected $tabla = 'documentos';
    
    /**
     * Guarda la información de un documento
     */
    public function guardarDocumento($cedula_vigilante, $tipo_documento, $ruta_archivo, $nombre_archivo) {
        $datos = [
            'cedula_vigilante' => $cedula_vigilante,
            'tipo_documento' => $tipo_documento,
            'ruta_archivo' => $ruta_archivo,
            'nombre_archivo' => $nombre_archivo
        ];
        
        return $this->insertar($datos);
    }
    
    /**
     * Obtiene todos los documentos de un vigilante
     */
    public function obtenerPorVigilante($cedula_vigilante) {
        $sql = "SELECT * FROM documentos WHERE cedula_vigilante = :cedula ORDER BY tipo_documento";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cedula' => $cedula_vigilante]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene un documento específico por tipo
     */
    public function obtenerPorTipo($cedula_vigilante, $tipo_documento) {
        $sql = "SELECT * FROM documentos 
                WHERE cedula_vigilante = :cedula 
                AND tipo_documento = :tipo 
                ORDER BY fecha_subida DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'cedula' => $cedula_vigilante,
            'tipo' => $tipo_documento
        ]);
        return $stmt->fetch();
    }
    
    /**
     * Elimina un documento
     */
    public function eliminarDocumento($id_documento) {
        // Obtener la ruta del archivo antes de eliminar
        $documento = $this->obtenerPorId($id_documento);
        
        if ($documento && $this->eliminar($id_documento)) {
            // Eliminar archivo físico si existe
            $ruta_completa = BASE_PATH . $documento['ruta_archivo'];
            if (file_exists($ruta_completa)) {
                unlink($ruta_completa);
            }
            return true;
        }
        
        return false;
    }
}

