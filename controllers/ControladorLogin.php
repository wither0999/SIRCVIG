<?php
/**
 * Controlador de Login
 */

require_once __DIR__ . '/../config/autoload.php';

class ControladorLogin {
    private $modeloUsuario;
    
    public function __construct() {
        $this->modeloUsuario = new Usuario();
    }
    
    /**
     * Muestra el formulario de login
     */
    public function mostrarLogin() {
        // Prevent browser caching of login page
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        // Si ya está logueado, redirigir al dashboard
        if (isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/views/dashboard.php');
            exit();
        }
        
        require_once VIEWS_PATH . '/login.php';
    }
    
    /**
     * Procesa el inicio de sesión
     */
    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php');
            exit();
        }
        
        $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        
        // Validación básica
        if (empty($nombre_usuario) || empty($contrasena)) {
            $_SESSION['error'] = 'Por favor, complete todos los campos.';
            header('Location: ' . BASE_URL . '/index.php');
            exit();
        }
        
        // Intentar autenticar
        try {
            $usuario = $this->modeloUsuario->autenticarUsuario($nombre_usuario, $contrasena);
            
            if ($usuario) {
                // Login exitoso
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre_usuario'];
                $_SESSION['usuario_nombres'] = $usuario['nombres'];
                $_SESSION['usuario_apellidos'] = $usuario['apellidos'];
                $_SESSION['id_rol'] = $usuario['id_rol'];
                $_SESSION['nombre_rol'] = $usuario['nombre_rol'];
                
                header('Location: ' . BASE_URL . '/views/dashboard.php');
                exit();
            } else {
                // Login fallido - mensaje genérico
                $_SESSION['error'] = 'Usuario o contraseña incorrecta.';
                header('Location: ' . BASE_URL . '/index.php');
                exit();
            }
        } catch (Exception $e) {
            // Capturar errores
            error_log("Error en autenticación: " . $e->getMessage());
            // Mostrar mensaje de la excepción (ej: usuario bloqueado)
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/index.php');
            exit();
        }
    }
    
    /**
     * Cierra la sesión
     */
    public function cerrarSesion() {
        // Limpiar todas las variables de sesión
        $_SESSION = array();
        
        // Si se desea destruir la sesión completamente, borre también la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir la sesión final
        session_destroy();
        
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}

// El enrutamiento ahora se maneja en index.php

