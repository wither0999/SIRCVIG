<?php

class ControladorRecuperacion {
    private $modeloUsuario;

    public function __construct() {
        $this->modeloUsuario = new Usuario();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Prevent browser caching for all recovery pages
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
    }

    // Paso 1: Mostrar formulario para buscar usuario por cédula
    public function index() {
        require_once __DIR__ . '/../views/recuperar/buscar_usuario.php';
    }

    // Procesar búsqueda de usuario
    public function buscar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipo_cedula = $_POST['tipo_cedula'] ?? '';
            $cedula_numero = trim($_POST['cedula_numero'] ?? '');
            
            // Reconstruir cédula completa (ej: V-12345678)
            $cedula = $tipo_cedula . '-' . $cedula_numero;
            
            $usuario = $this->modeloUsuario->obtenerPorCedula($cedula);
            
            if ($usuario) {
                // Verificar si tiene preguntas de seguridad configuradas
                if (!empty($usuario['pregunta_seguridad'])) {
                    $_SESSION['recuperacion_id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['recuperacion_pregunta'] = $usuario['pregunta_seguridad'];
                    header('Location: ' . BASE_URL . '/index.php?controller=Recuperacion&action=pregunta');
                } else {
                    $_SESSION['error'] = "El usuario no tiene preguntas de seguridad configuradas. Contacte al administrador.";
                    header('Location: ' . BASE_URL . '/index.php?controller=Recuperacion&action=index');
                }
            } else {
                $_SESSION['error'] = "Usuario no encontrado o inactivo.";
                header('Location: ' . BASE_URL . '/index.php?controller=Recuperacion&action=index');
            }
        }
    }

    // Paso 2: Mostrar pregunta de seguridad
    public function pregunta() {
        if (!isset($_SESSION['recuperacion_id_usuario'])) {
            header('Location: ' . BASE_URL . '/index.php?controller=Recuperacion&action=index');
            exit;
        }
        require_once __DIR__ . '/../views/recuperar/pregunta_seguridad.php';
    }

    // Procesar respuesta de seguridad
    public function verificar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $respuesta = $_POST['respuesta'] ?? '';
            $id_usuario = $_SESSION['recuperacion_id_usuario'] ?? null;
            
            if ($this->modeloUsuario->verificarRespuestaSeguridad($id_usuario, $respuesta)) {
                $_SESSION['recuperacion_verificado'] = true;
                header('Location: ' . BASE_URL . '/index.php?controller=Recuperacion&action=cambiar');
            } else {
                $_SESSION['error'] = "Respuesta incorrecta.";
                header('Location: ' . BASE_URL . '/index.php?controller=Recuperacion&action=pregunta');
            }
        }
    }

    // Paso 3: Mostrar formulario de cambio de contraseña
    public function cambiar() {
        if (!isset($_SESSION['recuperacion_verificado']) || !$_SESSION['recuperacion_verificado']) {
            header('Location: ' . BASE_URL . '/index.php?controller=Recuperacion&action=index');
            exit;
        }
        require_once __DIR__ . '/../views/recuperar/cambiar_contrasena.php';
    }

    // Procesar cambio de contraseña
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contrasena = $_POST['contrasena'] ?? '';
            $confirmar = $_POST['confirmar_contrasena'] ?? '';
            $id_usuario = $_SESSION['recuperacion_id_usuario'] ?? null;
            
            if ($contrasena !== $confirmar) {
                $_SESSION['error'] = "Las contraseñas no coinciden.";
                header('Location: ' . BASE_URL . '/index.php?controller=Recuperacion&action=cambiar');
                return;
            }
            
            // Validar política de contraseñas
            if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $contrasena)) {
                $_SESSION['error'] = "La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.";
                header('Location: ' . BASE_URL . '/index.php?controller=Recuperacion&action=cambiar');
                return;
            }
            
            if ($this->modeloUsuario->actualizarContrasena($id_usuario, $contrasena)) {
                // Limpiar sesión de recuperación
                unset($_SESSION['recuperacion_id_usuario']);
                unset($_SESSION['recuperacion_pregunta']);
                unset($_SESSION['recuperacion_verificado']);
                
                $_SESSION['exito'] = "Contraseña actualizada correctamente. Por favor inicie sesión.";
                header('Location: ' . BASE_URL . '/index.php');
            } else {
                $_SESSION['error'] = "Error al actualizar la contraseña.";
                header('Location: ' . BASE_URL . '/index.php?controller=Recuperacion&action=cambiar');
            }
        }
    }
}
