<?php
// Script de actualización de base de datos para Clientes
require_once __DIR__ . '/../config/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conectado a la base de datos...\n";
    
    // 1. Crear tabla clientes
    $sql_clientes = "CREATE TABLE IF NOT EXISTS clientes (
        id_cliente INT AUTO_INCREMENT PRIMARY KEY,
        rif_cedula VARCHAR(20) NOT NULL UNIQUE,
        nombre_cliente VARCHAR(200) NOT NULL,
        telefono VARCHAR(20),
        email VARCHAR(100),
        
        -- Campos de dirección desglosada
        estado VARCHAR(100),
        municipio VARCHAR(100),
        parroquia VARCHAR(100),
        direccion TEXT, -- Detalle
        
        estatus ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql_clientes);
    echo "Tabla 'clientes' creada o verificada.\n";
    
    // 2. Agregar id_cliente a puestos
    try {
        $sql_alter_puestos = "ALTER TABLE puestos ADD COLUMN id_cliente INT AFTER id_puesto";
        $pdo->exec($sql_alter_puestos);
        echo "Columna 'id_cliente' agregada a 'puestos'.\n";
        
        // Agregar FK (opcional por ahora, o nullable)
        $sql_fk = "ALTER TABLE puestos ADD CONSTRAINT fk_puesto_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE SET NULL";
        $pdo->exec($sql_fk);
        echo "Foreign Key agregada.\n";
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "Columna 'id_cliente' ya existe en 'puestos'.\n";
        } else {
            echo "Nota en alter puestos: " . $e->getMessage() . "\n";
        }
    }

    echo "Migración de Clientes completada.\n";
    
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
