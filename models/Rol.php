<?php
/**
 * Modelo de Rol
 */

class Rol extends Modelo {
    protected $tabla = 'roles';
    
    /**
     * Obtiene todos los roles
     */
    public function obtenerTodos($condiciones = '', $params = []) {
        if (empty($condiciones)) {
            // Si no hay condiciones, usar ordenamiento personalizado
            $sql = "SELECT * FROM roles ORDER BY id_rol";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        // Si hay condiciones, usar el método padre pero agregar ordenamiento
        $resultado = parent::obtenerTodos($condiciones, $params);
        // Ordenar resultados
        usort($resultado, function($a, $b) {
            return $a['id_rol'] - $b['id_rol'];
        });
        return $resultado;
    }
    
    /**
     * Obtiene un rol por nombre
     */
    public function obtenerPorNombre($nombre_rol) {
        $sql = "SELECT * FROM roles WHERE nombre_rol = :nombre_rol";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nombre_rol' => $nombre_rol]);
        return $stmt->fetch();
    }
}

