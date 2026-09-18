<?php
/**
 * Autoloader para clases del sistema
 */

spl_autoload_register(function ($class) {
    $paths = [
        MODELS_PATH . '/' . $class . '.php',
        CONTROLLERS_PATH . '/' . $class . '.php',
        BASE_PATH . '/core/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Cargar configuración
require_once __DIR__ . '/config.php';

// Verificación de Integridad de Base de Datos (Portable)
if (!isset($_SESSION['db_verified'])) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        // Verificar si existen tablas clave (usuarios)
        $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
        if ($stmt->rowCount() == 0) {
            die("<div style='font-family:sans-serif; text-align:center; padding:50px; color:#c0392b;'>
                <h1>⚠️ Base de Datos Vacía o no Encontrada</h1>
                <p>Conexión exitosa, pero no se encuentran las tablas del sistema.</p>
                <p>Por favor, importa el archivo <strong>sircvig.sql</strong> incluido en la carpeta del proyecto usando phpMyAdmin.</p>
                <hr>
                <small>Base de datos: " . DB_NAME . "</small>
            </div>");
        }
        $_SESSION['db_verified'] = true;
    } catch (PDOException $e) {
         die("<div style='font-family:sans-serif; text-align:center; padding:50px; color:#c0392b;'>
                <h1>⚠️ Error de Conexión a Base de Datos</h1>
                <p>El sistema no puede conectarse a MySQL.</p>
                <ul>
                    <li>Asegúrese de que XAMPP (Apache y MySQL) esté iniciado.</li>
                    <li>Verifique que la base de datos <strong>'" . DB_NAME . "'</strong> exista.</li>
                </ul>
                <div style='background:#f9f9f9; padding:10px; border:1px solid #ddd; display:inline-block;'>
                    Error técnico: " . $e->getMessage() . "
                </div>
            </div>");
    }
}

