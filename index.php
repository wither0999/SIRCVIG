<?php
/**
 * Punto de entrada principal del sistema
 */

require_once __DIR__ . '/config/autoload.php';

// Enrutamiento básico para acciones de expediente (Legacy/Specific)
if (isset($_GET['action']) && $_GET['action'] === 'editar_vigilante') {
    // Nota: El autoloader cargará la clase si es necesario, pero mantenemos el require explícito si se prefiere
    $controlador = new ControladorExpediente();
    $controlador->mostrarFormularioEdicion();
    exit();
}

// Router Dinámico
$controllerName = isset($_GET['controller']) ? 'Controlador' . $_GET['controller'] : 'ControladorLogin';
$action = $_GET['action'] ?? 'index';

// Manejo especial para el ControladorLogin (para mantener compatibilidad con flujo anterior)
if ($controllerName === 'ControladorLogin') {
    $controlador = new ControladorLogin();
    
    if ($action === 'logout') {
        $controlador->cerrarSesion();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Si es POST y no hay action específica, asumimos login
        $controlador->procesarLogin();
    } else {
        // Default: mostrar form de login
        $controlador->mostrarLogin();
    }
} else {
    // Rutas dinámicas para otros controladores (Recuperacion, etc)
    if (class_exists($controllerName)) {
        $controlador = new $controllerName();
        if (method_exists($controlador, $action)) {
            $controlador->$action();
        } else {
            // Acción no válida, volver al inicio
            header('Location: ' . BASE_URL);
            exit();
        }
    } else {
        // Controlador no encontrado
        header('Location: ' . BASE_URL);
        exit();
    }
}

