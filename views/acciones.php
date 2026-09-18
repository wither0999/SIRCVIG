<?php
/**
 * Archivo para procesar acciones POST desde las vistas
 */

require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

$accion = $_GET['accion'] ?? '';
$modulo = $_GET['modulo'] ?? '';

switch ($modulo) {
    case 'expediente':
        require_once __DIR__ . '/../controllers/ControladorExpediente.php';
        $controlador = new ControladorExpediente();
        
        switch ($accion) {
            case 'procesar_registro':
                $controlador->procesarRegistro();
                break;
            case 'modificar_estatus':
                $controlador->modificarEstatus();
                break;
            case 'eliminar':
                $controlador->eliminarVigilante();
                break;
            case 'procesar_edicion':
                $controlador->procesarEdicion();
                break;
            default:
                header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
                exit();
        }
        break;
        
    case 'asignacion':
        require_once __DIR__ . '/../controllers/ControladorAsignacion.php';
        $controlador = new ControladorAsignacion();
        
        switch ($accion) {
            case 'procesar':
                $controlador->procesarNuevaAsignacion();
                break;
            case 'generar_pdf':
                $controlador->generarCronogramaPDF();
                break;
            case 'eliminar':
                $controlador->eliminar();
                break;
            case 'anular':
                $controlador->anular();
                break;
            default:
                header('Location: ' . BASE_URL . '/views/cronograma_guardia.php');
                exit();
        }
        break;
        
    case 'usuario':
        require_once __DIR__ . '/../controllers/ControladorUsuario.php';
        $controlador = new ControladorUsuario();
        
        switch ($accion) {
            case 'crear':
                $controlador->crearUsuario();
                break;
            case 'editar':
                $controlador->editarUsuario();
                break;
            case 'modificar_rol':
                $controlador->modificarRol();
                break;
            case 'cambiar_estado':
                $controlador->cambiarEstado();
                break;
            case 'eliminar':
                $controlador->eliminarUsuario();
                break;
            case 'procesar_perfil':
                $controlador->procesarPerfil();
                break;
            default:
                header('Location: ' . BASE_URL . '/views/gestion_roles.php');
                exit();
        }
        break;
        
    case 'puesto':
        require_once __DIR__ . '/../controllers/ControladorPuesto.php';
        $controlador = new ControladorPuesto();
        
        switch ($accion) {
            case 'procesar':
                $controlador->procesarFormulario();
                break;
            case 'eliminar':
                $controlador->eliminarPuesto();
                break;
            default:
                header('Location: ' . BASE_URL . '/views/listado_puestos.php');
                exit();
        }
        break;
        
    case 'cliente':
        require_once __DIR__ . '/../controllers/ControladorCliente.php';
        $controlador = new ControladorCliente();
        
        switch ($accion) {
            case 'procesar':
                $controlador->procesar();
                break;
            case 'eliminar':
                $controlador->eliminar();
                break;
            default:
                header('Location: ' . BASE_URL . '/views/listado_clientes.php');
                exit();
        }
        break;
        
    default:
        header('Location: ' . BASE_URL . '/views/dashboard.php');
        exit();
}

