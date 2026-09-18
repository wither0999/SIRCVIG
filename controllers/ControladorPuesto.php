<?php
/**
 * Controlador de Puestos de Guardia
 */

require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

class ControladorPuesto extends ControladorPrincipal {
    private $modeloPuesto;
    
    public function __construct() {
        self::verificarSesion();
        
        // Solo Administrador y Secretario pueden gestionar puestos
        if (!self::esAdministrador() && !self::esSecretario()) {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta sección.';
            header('Location: ' . BASE_URL . '/views/dashboard.php');
            exit();
        }
        
        $this->modeloPuesto = new Puesto();
    }
    
    /**
     * Lista todos los puestos
     */
    public function listarPuestos() {
        $puestos = $this->modeloPuesto->obtenerTodos();
        
        $datos = [
            'puestos' => $puestos,
            'puede_modificar' => self::esAdministrador() || self::esSecretario()
        ];
        
        $this->renderizarVista('listado_puestos.php', $datos);
    }
    
    /**
     * Muestra el formulario de creación/edición
     */
    public function mostrarFormulario() {
        $id_puesto = $_GET['id'] ?? null;
        $puesto = null;
        
        if ($id_puesto) {
            $puesto = $this->modeloPuesto->obtenerPorId($id_puesto);
            if (!$puesto) {
                $_SESSION['error'] = 'Puesto no encontrado.';
                header('Location: ' . BASE_URL . '/views/listado_puestos.php');
                exit();
            }
        }
        
        $datos = ['puesto' => $puesto];
        $this->renderizarVista('formulario_puesto.php', $datos);
    }
    
    /**
     * Procesa la creación o edición de un puesto
     */
    public function procesarFormulario() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/views/listado_puestos.php');
            exit();
        }
        
        $id_puesto = $_POST['id_puesto'] ?? null;
        
        // Nuevo: Capturar ID de cliente
        $id_cliente = $_POST['id_cliente'] ?? null;
        $nombre_cliente = '';
        
        // Obtener nombre del cliente si se seleccionó uno
        if ($id_cliente) {
            $modeloCliente = new Cliente();
            $cliente = $modeloCliente->obtenerPorId($id_cliente);
            if ($cliente) {
                $nombre_cliente = $cliente['nombre_cliente'];
            }
        }
        
        // Construir Dirección Completa
        $estado = $_POST['estado'] ?? '';
        $municipio = $_POST['municipio'] ?? '';
        $parroquia = $_POST['parroquia'] ?? '';
        $detalle = trim($_POST['direccion_detalle'] ?? '');
        
        $partes_dir = [];
        if (!empty($estado)) $partes_dir[] = $estado;
        if (!empty($municipio)) $partes_dir[] = $municipio;
        if (!empty($parroquia)) $partes_dir[] = $parroquia;
        
        $prefijo = !empty($partes_dir) ? implode(', ', $partes_dir) . '. ' : '';
        $direccion = $prefijo . $detalle;
        
        // Teléfono Compuesto
        $codigo_operadora = $_POST['codigo_operadora'] ?? '';
        $telefono_numero = trim($_POST['telefono_numero'] ?? '');
        $telefono = ($codigo_operadora && $telefono_numero) ? $codigo_operadora . $telefono_numero : '';
        
        $contacto = trim($_POST['contacto'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $estatus = $_POST['estatus'] ?? 'Activo';
        
        // Validaciones
        if (empty($id_cliente) || empty($nombre_cliente) || empty($direccion) || empty($telefono)) {
            $_SESSION['error'] = 'El cliente, la dirección y el teléfono del puesto son obligatorios.';
            header('Location: ' . BASE_URL . '/views/formulario_puesto.php' . ($id_puesto ? '?id=' . $id_puesto : ''));
            exit();
        }
        
        // Validación de Contacto (Estandarización)
        if (!empty($contacto) && !preg_match("/^[a-zA-Z\x{00C0}-\x{00FF}\s]{3,25}$/u", $contacto)) {
            $_SESSION['error'] = 'El nombre del contacto debe tener entre 3 y 25 letras.';
            header('Location: ' . BASE_URL . '/views/formulario_puesto.php' . ($id_puesto ? '?id=' . $id_puesto : ''));
            exit();
        }
        
        $datos = [
            'id_cliente' => $id_cliente,
            'nombre_cliente' => $nombre_cliente, // Mantenemos compatibilidad
            'direccion' => $direccion,
            'telefono' => $telefono,
            'contacto' => $contacto,
            'descripcion' => $descripcion,
            'estatus' => $estatus
        ];
        
        if ($id_puesto) {
            // Actualizar
            if ($this->modeloPuesto->actualizar($id_puesto, $datos)) {
                $_SESSION['exito'] = 'Puesto actualizado exitosamente.';
            } else {
                $_SESSION['error'] = 'Error al actualizar el puesto.';
            }
        } else {
            // Crear
            if ($this->modeloPuesto->insertar($datos)) {
                $_SESSION['exito'] = 'Puesto creado exitosamente.';
            } else {
                $_SESSION['error'] = 'Error al crear el puesto.';
            }
        }
        
        header('Location: ' . BASE_URL . '/views/listado_puestos.php');
        exit();
    }
    
    /**
     * Elimina un puesto
     */
    public function eliminarPuesto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/views/listado_puestos.php');
            exit();
        }
        
        $id_puesto = $_POST['id_puesto'] ?? null;
        
        if (empty($id_puesto)) {
            $_SESSION['error'] = 'ID de puesto no especificado.';
            header('Location: ' . BASE_URL . '/views/listado_puestos.php');
            exit();
        }
        
        // Verificar si tiene asignaciones ACTIVAS
        $modeloAsignacion = new Asignacion();
        $asignacionesActivas = $modeloAsignacion->obtenerPorPuesto($id_puesto, true);
        
        if (!empty($asignacionesActivas)) {
            $_SESSION['error'] = 'No se puede eliminar el puesto porque tiene asignaciones ACTIVAS actuales. Debe reasignar al personal o cancelarlas primero.';
            header('Location: ' . BASE_URL . '/views/listado_puestos.php');
            exit();
        }
        
        try {
            // Eliminar manualmente todas las asignaciones pasadas/inactivas (Cascada manual) para evitar error de FK
            $sql = "DELETE FROM asignaciones WHERE id_puesto = :id_puesto";
            $stmt = $modeloAsignacion->getDB()->prepare($sql);
            $stmt->execute(['id_puesto' => $id_puesto]);

            if ($this->modeloPuesto->eliminar($id_puesto)) {
                $_SESSION['exito'] = 'Puesto eliminado exitosamente, junto con su historial de asignaciones pasadas.';
            } else {
                $_SESSION['error'] = 'Error al eliminar el puesto.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error'] = 'No se puede eliminar el puesto porque tiene registros asociados en otras tablas.';
            } else {
                $_SESSION['error'] = 'Error de base de datos: ' . $e->getMessage();
            }
        }
        
        header('Location: ' . BASE_URL . '/views/listado_puestos.php');
        exit();
    }
    
    /**
     * Muestra los detalles de un puesto
     */
    public function mostrarDetalles() {
        $id_puesto = $_GET['id'] ?? null;
        
        if (empty($id_puesto)) {
            $_SESSION['error'] = 'ID de puesto no especificado.';
            header('Location: ' . BASE_URL . '/views/listado_puestos.php');
            exit();
        }
        
        $puesto = $this->modeloPuesto->obtenerPorId($id_puesto);
        
        if (!$puesto) {
            $_SESSION['error'] = 'Puesto no encontrado.';
            header('Location: ' . BASE_URL . '/views/listado_puestos.php');
            exit();
        }
        
        // Obtener asignaciones activas
        $modeloAsignacion = new Asignacion();
        $asignaciones = $modeloAsignacion->obtenerPorPuesto($id_puesto, true);
        
        $datos = [
            'puesto' => $puesto,
            'asignaciones' => $asignaciones,
            'puede_modificar' => self::esAdministrador() || self::esSecretario()
        ];
        
        $this->renderizarVista('detalles_puesto.php', $datos);
    }
}
