<?php
/**
 * Clase Modelo Base
 * Maneja la conexión a la base de datos y métodos básicos de consulta
 */

class Modelo {
    protected $db;
    protected $tabla;
    
    public function __construct() {
        $this->conectar();
    }
    
    /**
     * Establece la conexión a la base de datos usando PDO
     */
    private function conectar() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $this->db = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene el nombre de la columna ID para la tabla actual
     */
    protected function obtenerColumnaId() {
        // Mapeo de nombres de tabla a nombres de columna ID
        $idColumnMap = [
            'usuarios' => 'id_usuario',
            'asignaciones' => 'id_asignacion',
            'vigilantes' => 'id_vigilante',
            'puestos' => 'id_puesto',
            'roles' => 'id_rol',
            'documentos' => 'id_documento',
            'clientes' => 'id_cliente'
        ];
        
        return $idColumnMap[$this->tabla] ?? "id_{$this->tabla}";
    }
    
    /**
     * Obtiene un registro por su ID
     */
    public function obtenerPorId($id) {
        $idColumn = $this->obtenerColumnaId();
        $sql = "SELECT * FROM {$this->tabla} WHERE {$idColumn} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Ejecuta una consulta SQL
     */
    public function ejecutarConsulta($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en consulta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene todos los registros de la tabla
     */
    public function obtenerTodos($condiciones = '', $params = []) {
        $sql = "SELECT * FROM {$this->tabla}";
        if (!empty($condiciones)) {
            $sql .= " WHERE " . $condiciones;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Inserta un nuevo registro
     */
    public function insertar($datos) {
        $campos = implode(', ', array_keys($datos));
        $valores = ':' . implode(', :', array_keys($datos));
        
        $sql = "INSERT INTO {$this->tabla} ({$campos}) VALUES ({$valores})";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($datos)) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Actualiza un registro
     */
    public function actualizar($id, $datos) {
        $idColumn = $this->obtenerColumnaId();
        
        $campos = [];
        foreach (array_keys($datos) as $campo) {
            $campos[] = "$campo = :$campo";
        }
        $campos = implode(', ', $campos);
        
        $datos['id'] = $id;
        $sql = "UPDATE {$this->tabla} SET {$campos} WHERE {$idColumn} = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($datos);
    }
    
    /**
     * Elimina un registro
     */
    public function eliminar($id) {
        $idColumn = $this->obtenerColumnaId();
        $sql = "DELETE FROM {$this->tabla} WHERE {$idColumn} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Obtiene la conexión PDO
     */
    public function getDB() {
        return $this->db;
    }
}

