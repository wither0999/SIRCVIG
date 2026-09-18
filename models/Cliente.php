<?php
/**
 * Modelo de Cliente
 */

class Cliente extends Modelo {
    protected $tabla = 'clientes';
    protected $pk = 'id_cliente';
    
    /**
     * Obtiene todos los clientes (opcionalmente filtrados por estatus)
     */
    /**
     * Obtiene todos los clientes
     */
    public function obtenerTodos($condiciones = '', $params = []) {
        if (empty($condiciones)) {
            $sql = "SELECT * FROM {$this->tabla} ORDER BY nombre_cliente ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        return parent::obtenerTodos($condiciones, $params);
    }

    /**
     * Buscar clientes por término
     */
    public function buscar($termino) {
        $sql = "SELECT * FROM {$this->tabla} 
                WHERE nombre_cliente LIKE :termino 
                OR rif_cedula LIKE :termino 
                ORDER BY nombre_cliente ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['termino' => "%$termino%"]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener por RIF/Cédula
     */
    public function obtenerPorRif($rif) {
        $sql = "SELECT * FROM {$this->tabla} WHERE rif_cedula = :rif";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['rif' => $rif]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener solo activos (para selectores)
     */
    public function obtenerActivos() {
        $sql = "SELECT * FROM {$this->tabla} WHERE estatus = 'Activo' ORDER BY nombre_cliente ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
