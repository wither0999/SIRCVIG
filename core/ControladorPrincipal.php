<?php
/**
 * Controlador Principal
 * Contiene funciones comunes para todos los controladores
 */

class ControladorPrincipal {
    
    /**
     * Verifica si existe una sesión activa
     * Redirige a login si no hay sesión
     */
    public static function verificarSesion() {
        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_nombre'])) {
            header('Location: ' . BASE_URL . '/index.php');
            exit();
        }

        // Prevent browser caching for back/forward navigation
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
    }
    
    /**
     * Verifica si el usuario tiene un rol específico
     */
    public static function verificarRol($rol_requerido) {
        self::verificarSesion();
        
        $rol_actual = $_SESSION['id_rol'] ?? 0;
        
        // Mapeo de roles
        $roles = [
            'Administrador' => ROL_ADMINISTRADOR,
            'Secretario' => ROL_SECRETARIO,
            'Supervisor' => ROL_SUPERVISOR
        ];
        
        $rol_id_requerido = $roles[$rol_requerido] ?? 0;
        
        if ($rol_actual != $rol_id_requerido) {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta sección.';
            header('Location: ' . BASE_URL . '/views/dashboard.php');
            exit();
        }
    }
    
    /**
     * Verifica si el usuario tiene uno de los roles permitidos
     */
    public static function verificarRolesPermitidos($roles_permitidos) {
        self::verificarSesion();
        
        $rol_actual = $_SESSION['id_rol'] ?? 0;
        
        $roles = [
            'Administrador' => ROL_ADMINISTRADOR,
            'Secretario' => ROL_SECRETARIO,
            'Supervisor' => ROL_SUPERVISOR
        ];
        
        $roles_id_permitidos = [];
        foreach ($roles_permitidos as $rol) {
            $roles_id_permitidos[] = $roles[$rol] ?? 0;
        }
        
        if (!in_array($rol_actual, $roles_id_permitidos)) {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta sección.';
            header('Location: ' . BASE_URL . '/views/dashboard.php');
            exit();
        }
    }
    
    /**
     * Verifica si el usuario es Administrador
     */
    public static function esAdministrador() {
        return isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == ROL_ADMINISTRADOR;
    }
    
    /**
     * Verifica si el usuario es Secretario
     */
    public static function esSecretario() {
        return isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == ROL_SECRETARIO;
    }
    
    /**
     * Verifica si el usuario es Supervisor
     */
    public static function esSupervisor() {
        return isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == ROL_SUPERVISOR;
    }
    
    /**
     * Renderiza una vista
     */
    protected function renderizarVista($vista, $datos = []) {
        extract($datos);
        require_once VIEWS_PATH . '/' . $vista;
    }
}

