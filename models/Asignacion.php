<?php
/**
 * Modelo de Asignación
 */

class Asignacion extends Modelo {
    protected $tabla = 'asignaciones';
    
    public function __construct() {
        parent::__construct();
        $this->actualizarEstadosCaducados();
    }
    
    /**
     * Crea una nueva asignación
     */
    public function crearAsignacion($datos) {
        return $this->insertar($datos);
    }
    
    /**
     * Obtiene una asignación por ID con sus relaciones
     */
    public function obtenerPorId($id) {
        $sql = "SELECT a.*, 
                       v.nombres, v.apellidos, v.cedula, v.telefono as telefono_vigilante,
                       COALESCE(c.nombre_cliente, p.nombre_cliente) as nombre_cliente,
                       p.direccion as direccion_puesto
                FROM asignaciones a
                INNER JOIN vigilantes v ON a.cedula_vigilante = v.cedula
                INNER JOIN puestos p ON a.id_puesto = p.id_puesto
                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                WHERE a.id_asignacion = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Verifica si existe un conflicto de horario
     * Fórmula: (A < Y) Y (B > X) donde [A, B] es la nueva asignación y [X, Y] es la existente
     */
    public function verificarConflicto($cedula_vigilante, $fecha_inicio, $fecha_fin) {
        $sql = "SELECT COUNT(*) as total 
                FROM asignaciones 
                WHERE cedula_vigilante = :cedula 
                AND estatus = 'Activa'
                AND (
                    (fecha_inicio < :fecha_fin AND fecha_fin > :fecha_inicio)
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'cedula' => $cedula_vigilante,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ]);
        
        $resultado = $stmt->fetch();
        return $resultado['total'] > 0;
    }

    /**
     * Obtiene IDs de vigilantes ocupados en una fecha dada
     */
    public function obtenerVigilantesOcupados($fecha) {
        $sql = "SELECT DISTINCT cedula_vigilante 
                FROM asignaciones 
                WHERE estatus = 'Activa' 
                AND :fecha BETWEEN fecha_inicio AND fecha_fin";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['fecha' => $fecha]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Obtiene el cronograma de guardia semanal
     */
    public function obtenerCronograma($fecha_inicio = null, $fecha_fin = null, $id_cliente = null) {
        $sql = "SELECT a.*, 
                       v.nombres, v.apellidos, v.cedula,
                       COALESCE(c.nombre_cliente, p.nombre_cliente) as nombre_cliente,
                       p.direccion as direccion_puesto
                FROM asignaciones a
                INNER JOIN vigilantes v ON a.cedula_vigilante = v.cedula
                INNER JOIN puestos p ON a.id_puesto = p.id_puesto
                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                WHERE 1=1";
        
        $params = [];

        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(a.fecha_inicio) >= :fecha_inicio";
            $params['fecha_inicio'] = $fecha_inicio;
        }

        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(a.fecha_fin) <= :fecha_fin";
            $params['fecha_fin'] = $fecha_fin;
        }

        if (!empty($id_cliente)) {
            $sql .= " AND p.id_cliente = :id_cliente";
            $params['id_cliente'] = $id_cliente;
        }

        $sql .= " ORDER BY a.fecha_inicio DESC, nombre_cliente ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene asignaciones por vigilante
     */
    public function obtenerPorVigilante($cedula_vigilante, $solo_activas = true) {
        $sql = "SELECT a.*, p.nombre_cliente, p.direccion 
                FROM asignaciones a
                INNER JOIN puestos p ON a.id_puesto = p.id_puesto
                WHERE a.cedula_vigilante = :cedula";
        
        if ($solo_activas) {
            $sql .= " AND a.estatus = 'Activa'";
        }
        
        $sql .= " ORDER BY a.fecha_inicio DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cedula' => $cedula_vigilante]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene la última asignación de un vigilante
     */
    public function obtenerUltimaAsignacion($cedula_vigilante) {
        $sql = "SELECT * FROM asignaciones 
                WHERE cedula_vigilante = :cedula
                AND estatus IN ('Activa', 'Completada', 'Caducada')
                ORDER BY fecha_fin DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cedula' => $cedula_vigilante]);
        return $stmt->fetch();
    }
    
    /**
     * Obtiene asignaciones por puesto
     */
    public function obtenerPorPuesto($id_puesto, $solo_activas = true) {
        $sql = "SELECT a.*, v.nombres, v.apellidos, v.cedula
                FROM asignaciones a
                INNER JOIN vigilantes v ON a.cedula_vigilante = v.cedula
                WHERE a.id_puesto = :id_puesto";
        
        if ($solo_activas) {
            $sql .= " AND a.estatus = 'Activa'";
        }
        
        $sql .= " ORDER BY a.fecha_inicio DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_puesto' => $id_puesto]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene estadísticas de asignaciones
     */
    public function obtenerEstadisticas() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estatus = 'Activa' THEN 1 ELSE 0 END) as activas,
                    SUM(CASE WHEN estatus = 'Completada' THEN 1 ELSE 0 END) as completadas
                FROM asignaciones";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Obtiene la asignación actual (activa) de un puesto en este momento
     */
    public function obtenerAsignacionActualPorPuesto($id_puesto) {
        $ahora = date('Y-m-d H:i:s');
        
        $sql = "SELECT a.*, v.nombres, v.apellidos, v.cedula
                FROM asignaciones a
                INNER JOIN vigilantes v ON a.cedula_vigilante = v.cedula
                WHERE a.id_puesto = :id_puesto
                AND a.estatus = 'Activa'
                AND :ahora >= a.fecha_inicio
                AND :ahora <= a.fecha_fin
                ORDER BY a.fecha_inicio DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id_puesto' => $id_puesto,
            'ahora' => $ahora
        ]);
        
        return $stmt->fetch();
    }
    
    /**
     * Obtiene todos los puestos activos con su vigilante actual
     */
    public function obtenerPuestosConVigilanteActual() {
        $ahora = date('Y-m-d H:i:s');
        
        $sql = "SELECT p.*, 
                       a.id_asignacion,
                       a.fecha_inicio as asignacion_inicio,
                       a.fecha_fin as asignacion_fin,
                       a.rol_guardia,
                       v.nombres as vigilante_nombres,
                       v.apellidos as vigilante_apellidos,
                       v.cedula as vigilante_cedula
                FROM puestos p
                LEFT JOIN asignaciones a ON p.id_puesto = a.id_puesto
                    AND a.estatus = 'Activa'
                    AND (
                        (:ahora1 BETWEEN a.fecha_inicio AND a.fecha_fin)
                        OR
                        (DATE(a.fecha_inicio) = DATE(:ahora2))
                    )
                LEFT JOIN vigilantes v ON a.cedula_vigilante = v.cedula
                WHERE p.estatus = 'Activo'
                ORDER BY p.nombre_cliente, a.fecha_inicio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'ahora1' => $ahora,
            'ahora2' => $ahora
        ]);
        
        $raw = $stmt->fetchAll();
        $puestos = [];
        
        foreach ($raw as $row) {
            $id = $row['id_puesto'];
            // Priorizamos la primera coincidencia (por orden de fecha, la activa/más próxima hoy)
            if (!isset($puestos[$id])) {
                $puestos[$id] = $row;
            }
        }
        
        return array_values($puestos);
    }
    /**
     * Cambia el estatus de una asignación
     */
    public function cambiarEstatus($id_asignacion, $nuevo_estatus) {
        $sql = "UPDATE asignaciones SET estatus = :estatus WHERE id_asignacion = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['estatus' => $nuevo_estatus, 'id' => $id_asignacion]);
    }

    /**
     * Actualiza estados de asignaciones caducadas
     */
    public function actualizarEstadosCaducados() {
        $ahora = date('Y-m-d H:i:s');
        $sql = "UPDATE asignaciones 
                SET estatus = 'Completada' 
                WHERE estatus = 'Activa' 
                AND fecha_fin < :ahora";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ahora' => $ahora]);
    }
}

