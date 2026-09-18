<?php
/**
 * Modelo de Puesto
 */

class Puesto extends Modelo {
    protected $tabla = 'puestos';
    
    /**
     * Obtiene todos los puestos activos
     */
    public function obtenerActivos() {
        $sql = "SELECT * FROM puestos WHERE estatus = 'Activo' ORDER BY nombre_cliente";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene todos los puestos
     */
    public function obtenerTodos($condiciones = '', $params = []) {
        if (empty($condiciones)) {
            // Si no hay condiciones, usar ordenamiento personalizado
            $sql = "SELECT * FROM puestos ORDER BY nombre_cliente";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        // Si hay condiciones, usar el método padre pero agregar ordenamiento
        $resultado = parent::obtenerTodos($condiciones, $params);
        // Ordenar resultados
        usort($resultado, function($a, $b) {
            return strcmp($a['nombre_cliente'], $b['nombre_cliente']);
        });
        return $resultado;
    }
    
    /**
     * Obtiene estadísticas de puestos
     */
    public function obtenerEstadisticas() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estatus = 'Activo' THEN 1 ELSE 0 END) as activos
                FROM puestos";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    /**
     * Actualiza el estatus de los puestos de un cliente
     */
    public function actualizarEstatusPorCliente($id_cliente, $estatus) {
        $sql = "UPDATE puestos SET estatus = :estatus WHERE id_cliente = :id_cliente";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['estatus' => $estatus, 'id_cliente' => $id_cliente]);
    }

    /**
     * Cuenta puestos activos de un cliente
     */
    public function contarActivosPorCliente($id_cliente) {
        $sql = "SELECT COUNT(*) as total FROM puestos WHERE id_cliente = :id_cliente AND estatus = 'Activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_cliente' => $id_cliente]);
        $row = $stmt->fetch();
        return $row['total'];
    }
}

