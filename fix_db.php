<?php
require_once __DIR__ . '/config/autoload.php';

try {
    $db = Conexion::conectar();
    $sql = "ALTER TABLE asignaciones MODIFY COLUMN estatus ENUM('Activa','Completada','Cancelada','Eliminada','Caducada') DEFAULT 'Activa';";
    $db->exec($sql);
    echo "ENUM estatus modificado exitosamente.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
