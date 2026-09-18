<?php
// Script de actualización de base de datos para dirección
require_once __DIR__ . '/../config/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conectado a la base de datos...\n";
    
    // Agregar columnas de dirección separadas
    $columns = [
        "ADD COLUMN estado VARCHAR(100) AFTER fecha_nacimiento",
        "ADD COLUMN municipio VARCHAR(100) AFTER estado",
        "ADD COLUMN parroquia VARCHAR(100) AFTER municipio",
        "MODIFY COLUMN direccion TEXT AFTER parroquia" // Mover dirección (detalle) al final
    ];
    
    foreach ($columns as $sql_part) {
        try {
            $sql = "ALTER TABLE vigilantes " . $sql_part;
            $pdo->exec($sql);
            echo "Ejecutado: $sql_part\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "Columna ya existe (saltado): " . $sql_part . "\n";
            } else {
                echo "Nota (puede ser ignorado si es reordenamiento): " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Actualización de dirección completada.\n";
    
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
