<?php
/**
 * Controlador de Usuarios
 */

require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

class ControladorUsuario extends ControladorPrincipal {
    private $modeloUsuario;
    private $modeloRol;
    
    public function __construct() {
        self::verificarSesion();
        $this->modeloUsuario = new Usuario();
        $this->modeloRol = new Rol();
    }
    
    // --- MÉTODOS DE ADMINISTRACIÓN (Requieren Permiso) ---

    public function mostrarGestion() {
        if (!self::esAdministrador()) return $this->redirigirError();
        
        $usuarios = $this->modeloUsuario->obtenerUsuariosConRoles();
        $roles = $this->modeloRol->obtenerTodos();
        $this->renderizarVista('gestion_roles.php', ['usuarios' => $usuarios, 'roles' => $roles]);
    }
    
    public function crearUsuario() {
        if (!self::esAdministrador()) return $this->redirigirError();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') header('Location: ' . BASE_URL . '/views/gestion_roles.php');
        
        // ... (Logica Existente de validación y creación) ...
        // Simplificado para brevedad en overwrite, pero debo mantener la lógica original.
        // Copiaré la lógica original revisada.
        
        $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $id_rol = intval($_POST['id_rol'] ?? 0);
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $tipo_cedula = $_POST['tipo_cedula'] ?? '';
        $cedula_numero = preg_replace('/[^0-9]/', '', trim($_POST['cedula_numero'] ?? ''));
        $cedula = $tipo_cedula . '-' . $cedula_numero;
        $email = trim($_POST['email'] ?? '');
        $codigo_operadora = $_POST['codigo_operadora'] ?? '';
        $telefono_numero = trim($_POST['telefono_numero'] ?? '');
        $telefono = $codigo_operadora . $telefono_numero;
        $pregunta_1 = $_POST['pregunta_seguridad'] ?? '';
        $respuesta_1 = $_POST['respuesta_seguridad'] ?? '';
        
        if (empty($nombre_usuario) || empty($contrasena) || $id_rol <= 0 || empty($nombres) || empty($cedula_numero)) {
             $_SESSION['error'] = 'Campos obligatorios faltantes.';
             header('Location: ' . BASE_URL . '/views/gestion_roles.php');
             exit();
        }
        
        // Verificación de nombres de usuario comunes/prohibidos
        $nombres_prohibidos = ['admin', 'owner', 'administrador', 'root', 'sistema'];
        if (in_array(strtolower($nombre_usuario), $nombres_prohibidos)) {
            $_SESSION['error'] = 'El nombre de usuario elegido no está permitido. Por favor elija otro.';
            header('Location: ' . BASE_URL . '/views/gestion_roles.php');
            exit();
        }

        // Verificación de existencia del usuario en la base de datos
        if ($this->modeloUsuario->existeUsuario($nombre_usuario)) {
            $_SESSION['error'] = 'Ya existe un usuario con ese nombre de usuario. Por favor, elija un nombre diferente.';
            header('Location: ' . BASE_URL . '/views/gestion_roles.php');
            exit();
        }

        // Validaciones Regla de Negocio
        if (!preg_match("/^[a-zA-Z\x{00C0}-\x{00FF}\s]{3,25}$/u", $nombres) || !preg_match("/^[a-zA-Z\x{00C0}-\x{00FF}\s]{3,25}$/u", $apellidos)) {
            $_SESSION['error'] = 'Nombres/Apellidos inválidos (3-25 letras).';
            header('Location: ' . BASE_URL . '/views/gestion_roles.php');
            exit();
        }

        $datos = [
            'nombre_usuario' => $nombre_usuario, 'contrasena' => $contrasena, 'id_rol' => $id_rol,
            'nombres' => $nombres, 'apellidos' => $apellidos, 'cedula' => $cedula,
            'email' => $email, 'telefono' => $telefono,
            'pregunta_seguridad' => $pregunta_1, 'respuesta_seguridad' => $respuesta_1
        ];
        
        if ($this->modeloUsuario->crearUsuario($datos)) $_SESSION['exito'] = 'Usuario creado.';
        else $_SESSION['error'] = 'Error al crear.';
        
        header('Location: ' . BASE_URL . '/views/gestion_roles.php');
        exit();
    }
    
    public function editarUsuario() {
        if (!self::esAdministrador()) return $this->redirigirError();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') header('Location: ' . BASE_URL . '/views/gestion_roles.php');
        
        $id_usuario = intval($_POST['id_usuario'] ?? 0);
        $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
        $id_rol = intval($_POST['id_rol'] ?? 0);
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $tipo_cedula = $_POST['tipo_cedula'] ?? '';
        $cedula_numero = preg_replace('/[^0-9]/', '', trim($_POST['cedula_numero'] ?? ''));
        $cedula = $tipo_cedula . '-' . $cedula_numero;
        $email = trim($_POST['email'] ?? '');
        $codigo_operadora = $_POST['codigo_operadora'] ?? '';
        $telefono_numero = preg_replace('/[^0-9]/', '', trim($_POST['telefono_numero'] ?? ''));
        $telefono = $codigo_operadora . $telefono_numero;
        $contrasena = $_POST['contrasena'] ?? '';
        
        if ($id_usuario <= 0 || empty($nombre_usuario) || $id_rol <= 0 || empty($nombres) || empty($cedula_numero)) {
             $_SESSION['error'] = 'Campos obligatorios faltantes.';
             $_SESSION['old_data'] = $_POST;
             header('Location: ' . BASE_URL . '/views/editar_usuario.php?id=' . $id_usuario);
             exit();
        }
        
        if (!preg_match("/^[a-zA-Z\x{00C0}-\x{00FF}\s]{3,25}$/u", $nombres) || !preg_match("/^[a-zA-Z\x{00C0}-\x{00FF}\s]{3,25}$/u", $apellidos)) {
            $_SESSION['error'] = 'Nombres/Apellidos inválidos (3-25 letras).';
            $_SESSION['old_data'] = $_POST;
            header('Location: ' . BASE_URL . '/views/editar_usuario.php?id=' . $id_usuario);
            exit();
        }

        $datos = [
            'nombre_usuario' => $nombre_usuario, 'id_rol' => $id_rol,
            'nombres' => $nombres, 'apellidos' => $apellidos, 'cedula' => $cedula,
            'email' => $email, 'telefono' => $telefono
        ];
        
        if (!empty($contrasena)) {
            $datos['contrasena'] = $contrasena;
        }
        
        if ($this->modeloUsuario->actualizarUsuarioAdmin($id_usuario, $datos)) {
            $_SESSION['exito'] = 'Usuario actualizado correctamente.';
            header('Location: ' . BASE_URL . '/views/gestion_roles.php');
        } else {
            $_SESSION['error'] = 'Error al actualizar usuario.';
            header('Location: ' . BASE_URL . '/views/editar_usuario.php?id=' . $id_usuario);
        }
        exit();
    }
    
    public function modificarRol() {
        if (!self::esAdministrador()) return $this->redirigirError();
        // ... Lógica original ...
        $id_usuario = intval($_POST['id_usuario'] ?? 0);
        $id_rol = intval($_POST['id_rol'] ?? 0);
        if ($this->modeloUsuario->asignarRol($id_usuario, $id_rol)) $_SESSION['exito'] = 'Rol actualizado.';
        header('Location: ' . BASE_URL . '/views/gestion_roles.php');
    }
    
    public function cambiarEstado() {
        if (!self::esAdministrador()) return $this->redirigirError();
        $id = intval($_POST['id_usuario'] ?? 0);
        $accion = $_POST['accion'] ?? '';
        if ($accion == 'bloquear') $this->modeloUsuario->bloquearCuenta($id);
        else $this->modeloUsuario->desbloquearCuenta($id);
        header('Location: ' . BASE_URL . '/views/gestion_roles.php');
    }

    public function eliminarUsuario() {
        if (!self::esAdministrador()) return $this->redirigirError();
        
        $id_usuario = intval($_POST['id_usuario'] ?? 0);
        
        if ($id_usuario <= 0) {
            $_SESSION['error'] = 'Usuario no válido.';
            header('Location: ' . BASE_URL . '/views/gestion_roles.php');
            exit();
        }

        // Evitar que el usuario se elimine a sí mismo
        if ($id_usuario === $_SESSION['id_usuario']) {
            $_SESSION['error'] = 'No puede eliminar su propio usuario.';
            header('Location: ' . BASE_URL . '/views/gestion_roles.php');
            exit();
        }

        if ($this->modeloUsuario->eliminar($id_usuario)) {
            $_SESSION['exito'] = 'Usuario eliminado exitosamente.';
        } else {
            $_SESSION['error'] = 'Error al eliminar el usuario.';
        }
        
        header('Location: ' . BASE_URL . '/views/gestion_roles.php');
        exit();
    }

    private function redirigirError() {
        $_SESSION['error'] = 'Acceso denegado.';
        header('Location: ' . BASE_URL . '/views/dashboard.php');
        exit();
    }

    // --- MÉTODOS DE PERFIL (Públicos p/ Logueados) ---

    public function mostrarPerfil() {
        $id_usuario = $_SESSION['id_usuario'];
        $usuario = $this->modeloUsuario->obtenerPorId($id_usuario);
        
        if (!$usuario) {
            session_destroy();
            header('Location: ' . BASE_URL . '/login.php');
            exit();
        }
        
        // Obtener nombre del rol para mostrar (readonly)
        $rol = $this->modeloRol->obtenerPorId($usuario['id_rol']);
        $usuario['nombre_rol'] = $rol['nombre_rol'] ?? 'Usuario';
        
        $this->renderizarVista('perfil.php', ['usuario' => $usuario]);
    }

    public function procesarPerfil() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/views/perfil.php');
            exit();
        }
        
        $id_usuario = $_SESSION['id_usuario'];
        
        // 1. Datos Personales
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $telefono = trim($_POST['telefono'] ?? ''); // Ya vendrá formateado o simple? Asumiremos simple y validamos
        
        // Validación Nombre/Apellido
        if (!preg_match("/^[a-zA-Z\x{00C0}-\x{00FF}\s]{3,25}$/u", $nombres) || !preg_match("/^[a-zA-Z\x{00C0}-\x{00FF}\s]{3,25}$/u", $apellidos)) {
            $_SESSION['error'] = 'Nombres y Apellidos deben tener 3-25 letras.';
            header('Location: ' . BASE_URL . '/views/perfil.php');
            exit();
        }
        
        // Actualizar datos
        $this->modeloUsuario->actualizarDatosPersonales($id_usuario, $nombres, $apellidos, $telefono);
        
        // Actualizar sesión para saludo
        $_SESSION['nombre'] = $nombres; 
        $_SESSION['apellido'] = $apellidos;
        
        // 2. Cambio de Contraseña (Opcional)
        $pass_actual = $_POST['password_actual'] ?? '';
        $pass_nueva = $_POST['password_nueva'] ?? '';
        $pass_conf = $_POST['password_confirmar'] ?? '';
        
        $mensaje_pass = '';
        
        if (!empty($pass_actual) || !empty($pass_nueva)) {
            if (empty($pass_actual) || empty($pass_nueva) || empty($pass_conf)) {
                $_SESSION['error'] = 'Para cambiar la contraseña, complete todos los campos de seguridad.';
                header('Location: ' . BASE_URL . '/views/perfil.php');
                exit();
            }
            
            if ($pass_nueva !== $pass_conf) {
                $_SESSION['error'] = 'Las nuevas contraseñas no coinciden.';
                header('Location: ' . BASE_URL . '/views/perfil.php');
                exit();
            }
            
            // Verificar actual
            $usuario_db = $this->modeloUsuario->obtenerPorId($id_usuario);
            $hash_actual_input = hash('sha256', $pass_actual); // Sistema usa SHA256 manual según modelo visto
            
            if ($hash_actual_input !== $usuario_db['contrasena_hash']) {
                $_SESSION['error'] = 'La contraseña actual es incorrecta.';
                header('Location: ' . BASE_URL . '/views/perfil.php');
                exit();
            }
            
            // Actualizar
            $this->modeloUsuario->actualizarContrasena($id_usuario, $pass_nueva);
            $mensaje_pass = ' y contraseña';
        }
        
        $_SESSION['exito'] = 'Perfil' . $mensaje_pass . ' actualizado correctamente.';
        header('Location: ' . BASE_URL . '/views/perfil.php');
        exit();
    }
}

