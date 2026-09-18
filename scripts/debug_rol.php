<?php
require_once __DIR__ . '/../config/config.php';

// Conexión Directa para debugging
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Debug Rol de Guardia</h1>";
    
    // 1. Verificar Estructura de Tabla
    echo "<h2>1. Estructura de Tabla 'asignaciones'</h2>";
    $stmt = $pdo->query("SHOW COLUMNS FROM asignaciones");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $rolColumnFound = false;
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th></tr>";
    foreach ($columns as $col) {
        $highlight = ($col['Field'] == 'rol_guardia') ? 'style="background:yellow"' : '';
        if ($col['Field'] == 'rol_guardia') $rolColumnFound = true;
        
        echo "<tr $highlight>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if (!$rolColumnFound) {
        echo "<h3 style='color:red'>CRÍTICO: La columna 'rol_guardia' NO EXISTE en la tabla.</h3>";
        // Intentar agregarla si no existe
        echo "<p>Intentando agregar columna...</p>";
        try {
            $pdo->exec("ALTER TABLE asignaciones ADD COLUMN rol_guardia VARCHAR(50) AFTER fecha_fin");
            echo "<p style='color:green'>Columna 'rol_guardia' agregada exitosamente.</p>";
        } catch (Exception $e) {
            echo "<p style='color:red'>Error al agregar columna: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<h3 style='color:green'>La columna existe.</h3>";
    }
    
    // 2. Verificar Últimos Datos
    echo "<h2>2. Últimas 5 Asignaciones</h2>";
    $stmt = $pdo->query("SELECT id_asignacion, fecha_inicio, rol_guardia FROM asignaciones ORDER BY id_asignacion DESC LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'><tr><th>ID</th><th>Fecha Inicio</th><th>Rol de Guardia</th></tr>";
    foreach ($rows as $row) {
        echo "<tr>";
        echo "<td>" . $row['id_asignacion'] . "</td>";
        echo "<td>" . $row['fecha_inicio'] . "</td>";
        echo "<td>" . ($row['rol_guardia'] ? $row['rol_guardia'] : '<span style="color:red">NULL/EMPTY</span>') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    die("Error DB: " . $e->getMessage());
}
?>
