<?php
/**
 * Modelo de Vigilante
 */

class Vigilante extends Modelo {
    protected $tabla = 'vigilantes';
    
    /**
     * Registra un nuevo vigilante
     */
    public function registrar($datos) {
        // Validación básica
        if (empty($datos['cedula']) || empty($datos['nombres']) || empty($datos['apellidos'])) {
            return false;
        }
        
        try {
            // Iniciar transacción
            $this->db->beginTransaction();
            
            // Verificar que la cédula sea única (con bloqueo para evitar race conditions)
            $sql_check = "SELECT cedula FROM vigilantes WHERE cedula = :cedula FOR UPDATE";
            $stmt_check = $this->db->prepare($sql_check);
            $stmt_check->execute(['cedula' => $datos['cedula']]);
            
            if ($stmt_check->fetch()) {
                $this->db->rollBack();
                return false; // Cédula ya existe
            }
            
            // Insertar
            $resultado = $this->insertar($datos);
            
            if ($resultado) {
                $this->db->commit();
                return $resultado;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en registro de vigilante: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Consulta un vigilante por teléfono
     */
    public function obtenerPorTelefono($telefono) {
        $sql = "SELECT * FROM vigilantes WHERE telefono = :telefono";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['telefono' => $telefono]);
        return $stmt->fetch();
    }
    
    /**
     * Consulta un vigilante por cédula
     */
    public function obtenerPorCedula($cedula) {
        $sql = "SELECT * FROM vigilantes WHERE cedula = :cedula";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cedula' => $cedula]);
        return $stmt->fetch();
    }
    
    /**
     * Obtiene todos los vigilantes
     * Compatible con el método padre, pero permite filtrar por estatus
     */
    public function obtenerTodos($condiciones = '', $params = []) {
        // Si se pasa un string como primer parámetro y es un estatus, usar lógica especial
        if (!empty($condiciones) && empty($params) && in_array($condiciones, ['Aspirante', 'Activo', 'Inactivo'])) {
            // Compatibilidad con código antiguo que pasaba estatus como string
            $sql = "SELECT * FROM vigilantes WHERE estatus = :estatus";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['estatus' => $condiciones]);
            return $stmt->fetchAll();
        }
        
        // Usar el método padre para otras condiciones
        return parent::obtenerTodos($condiciones, $params);
    }
    
    /**
     * Obtiene vigilantes filtrados por estatus
     */
    public function obtenerPorEstatus($estatus = null) {
        if ($estatus) {
            return $this->obtenerTodos('estatus = :estatus', ['estatus' => $estatus]);
        }
        return $this->obtenerTodos();
    }
    
    /**
     * Modifica un vigilante
     */
    public function modificar($cedula, $datos) {
        $sql = "UPDATE vigilantes SET ";
        $campos = [];
        $params = ['cedula' => $cedula];
        
        foreach ($datos as $campo => $valor) {
            if ($campo !== 'cedula') {
                $campos[] = "$campo = :$campo";
                $params[$campo] = $valor;
            }
        }
        
        $sql .= implode(', ', $campos) . " WHERE cedula = :cedula";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Cambia el estatus de un vigilante
     */
    public function cambiarEstatus($cedula, $estatus) {
        $sql = "UPDATE vigilantes SET estatus = :estatus WHERE cedula = :cedula";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['estatus' => $estatus, 'cedula' => $cedula]);
    }
    
    /**
     * Deshabilita un vigilante (cambia a Inactivo)
     */
    public function deshabilitar($cedula) {
        return $this->cambiarEstatus($cedula, 'Inactivo');
    }
    
    /**
     * Obtiene estadísticas de vigilantes
     */
    public function obtenerEstadisticas() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estatus = 'Activo' THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN estatus = 'Aspirante' THEN 1 ELSE 0 END) as aspirantes,
                    SUM(CASE WHEN estatus = 'Inactivo' THEN 1 ELSE 0 END) as inactivos
                FROM vigilantes";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Busca vigilantes por término
     */
    public function buscar($termino) {
        $sql = "SELECT * FROM vigilantes 
                WHERE cedula LIKE :termino1 
                OR nombres LIKE :termino2 
                OR apellidos LIKE :termino3
                ORDER BY nombres, apellidos";
        
        $stmt = $this->db->prepare($sql);
        $termino_busqueda = "%$termino%";
        $stmt->execute([
            'termino1' => $termino_busqueda,
            'termino2' => $termino_busqueda,
            'termino3' => $termino_busqueda
        ]);
        return $stmt->fetchAll();
    }
}

