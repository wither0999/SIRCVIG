<?php
/**
 * Configuración del Sistema SIRCVIG
 */

// Prevenir acceso directo
if (!defined('SIRCVIG')) {
    define('SIRCVIG', true);
}

// Configuración de errores (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de zona horaria
date_default_timezone_set('America/Caracas');

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sircvig');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');

// Configuración de rutas
define('BASE_PATH', dirname(__DIR__));

// Detección Dinámica de BASE_URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
// Calcular ruta relativa desde el DOCUMENT_ROOT hasta la carpeta del proyecto
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$base_path = str_replace('\\', '/', BASE_PATH);
$folder = str_replace($doc_root, '', $base_path);
define('BASE_URL', $protocol . "://" . $host . $folder);

// Rutas de directorios
define('MODELS_PATH', BASE_PATH . '/models');
define('CONTROLLERS_PATH', BASE_PATH . '/controllers');
define('VIEWS_PATH', BASE_PATH . '/views');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('UPLOADS_URL', BASE_URL . '/uploads');

// Configuración de sesión
define('SESSION_LIFETIME', 3600); // 1 hora

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de subida de archivos
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
define('ALLOWED_DOC_TYPES', ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']);

// Roles del sistema
define('ROL_ADMINISTRADOR', 1);
define('ROL_SECRETARIO', 2);
define('ROL_SUPERVISOR', 3);

