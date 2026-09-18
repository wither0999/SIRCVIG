<?php
require_once __DIR__ . '/../config/config.php';
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $stmt = $pdo->query("DESCRIBE asignaciones");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in 'asignaciones' table:\n";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
