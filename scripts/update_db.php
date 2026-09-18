<?php
// Script de actualización de base de datos
require_once __DIR__ . '/../config/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conectado a la base de datos...\n";
    
    // Columnas a verificar/agregar
    $columns = [
        "ADD COLUMN cedula VARCHAR(20) UNIQUE AFTER nombre_usuario",
        "ADD COLUMN nombres VARCHAR(100) AFTER cedula",
        "ADD COLUMN apellidos VARCHAR(100) AFTER nombres",
        "ADD COLUMN email VARCHAR(100) UNIQUE AFTER apellidos",
        "ADD COLUMN telefono VARCHAR(20) AFTER email",
        "ADD COLUMN pregunta_seguridad VARCHAR(255) AFTER contrasena_hash",
        "ADD COLUMN respuesta_seguridad VARCHAR(255) AFTER pregunta_seguridad",
        "ADD COLUMN pregunta_seguridad_2 VARCHAR(255) AFTER respuesta_seguridad",
        "ADD COLUMN respuesta_seguridad_2 VARCHAR(255) AFTER pregunta_seguridad_2"
    ];
    
    foreach ($columns as $sql_part) {
        try {
            $sql = "ALTER TABLE usuarios " . $sql_part;
            $pdo->exec($sql);
            echo "Ejecutado: $sql_part\n";
        } catch (PDOException $e) {
            // Ignorar error si la columna ya existe (Código 42S21 usualmente, o mensaje 'Duplicate column')
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "Columna ya existe (saltado): " . substr($sql_part, 0, 30) . "...\n";
            } else {
                echo "Error al ejecutar '$sql_part': " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Actualización completada exitosamente.\n";
    
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
