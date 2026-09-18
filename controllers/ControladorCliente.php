<?php
/**
 * Controlador de Clientes
 */

require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

class ControladorCliente extends ControladorPrincipal {
    private $modeloCliente;
    
    public function __construct() {
        self::verificarSesion();
        
        // Permisos similares a vigilantes/puestos
        if (!self::esAdministrador() && !self::esSecretario() && !self::esSupervisor()) {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta sección.';
            header('Location: ' . BASE_URL . '/views/dashboard.php');
            exit();
        }
        
        $this->modeloCliente = new Cliente();
    }
    
    /**
     * Listar Clientes
     */
    public function listar() {
        $termino = $_GET['buscar'] ?? '';
        
        if (!empty($termino)) {
            $clientes = $this->modeloCliente->buscar($termino);
        } else {
            $clientes = $this->modeloCliente->obtenerTodos();
        }
        
        $datos = [
            'clientes' => $clientes,
            'termino_busqueda' => $termino,
            'puede_editar' => self::esAdministrador() || self::esSecretario()
        ];
        
        $this->renderizarVista('listado_clientes.php', $datos);
    }
    
    /**
     * Mostrar formulario crear/editar
     */
    public function formulario() {
        if (!self::esAdministrador() && !self::esSecretario()) {
            $_SESSION['error'] = 'No tiene permisos.';
            header('Location: ' . BASE_URL . '/views/listado_clientes.php');
            exit();
        }
        
        $id = $_GET['id'] ?? null;
        $cliente = null;
        
        if ($id) {
            $cliente = $this->modeloCliente->obtenerPorId($id);
            if (!$cliente) {
                $_SESSION['error'] = 'Cliente no encontrado.';
                header('Location: ' . BASE_URL . '/views/listado_clientes.php');
                exit();
            }
        }
        
        $this->renderizarVista('formulario_cliente.php', ['cliente' => $cliente]);
    }
    
    /**
     * Procesar Guardado
     */
    public function procesar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('listado_clientes.php');
        
        if (!self::esAdministrador() && !self::esSecretario()) {
             $_SESSION['error'] = 'No tiene permisos.';
             header('Location: ' . BASE_URL . '/views/listado_clientes.php');
             exit();
        }
        
        // Recoger datos
        $id_cliente = $_POST['id_cliente'] ?? null;
        
        // RIF/Cedula
        $tipo_doc = $_POST['tipo_doc'] ?? '';
        $num_doc = trim($_POST['num_doc'] ?? '');
        $rif_cedula = $tipo_doc . '-' . $num_doc;
        
        $nombre_cliente = trim($_POST['nombre_cliente'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        // Teléfono
        $cod_tlf = $_POST['cod_tlf'] ?? '';
        $num_tlf = trim($_POST['num_tlf'] ?? '');
        $telefono = $cod_tlf . $num_tlf;
        
        // Dirección Cascada
        $estado = trim($_POST['estado'] ?? '');
        $municipio = trim($_POST['municipio'] ?? '');
        $parroquia = trim($_POST['parroquia'] ?? '');
        $direccion_det = trim($_POST['direccion'] ?? '');
        
        $estatus = $_POST['estatus'] ?? 'Activo';
        
        // Validaciones
        if (empty($num_doc) || empty($nombre_cliente) || empty($estado) || empty($municipio) || empty($parroquia) || empty($direccion_det) || empty($num_tlf) || empty($cod_tlf)) {
            $_SESSION['error'] = 'Todos los campos son obligatorios, incluyendo teléfono válido.';
            $url = 'formulario_cliente.php';
            if ($id_cliente) $url .= '?id=' . $id_cliente;
            header('Location: ' . BASE_URL . '/views/' . $url);
            exit();
        }

        // La validación de Razón Social ahora solo requiere que no esté vacía (1 carácter mínimo), lo cual ya se valida arriba.
        

        
        // Validación RIF/Cédula (Estandarización)
        if (!ctype_digit($num_doc)) {
            $_SESSION['error'] = 'El número de documento debe contener solo dígitos.';
            $url = 'formulario_cliente.php' . ($id_cliente ? '?id=' . $id_cliente : '');
            header('Location: ' . BASE_URL . '/views/' . $url);
            exit();
        }
        
        if ($tipo_doc === 'V' && strlen($num_doc) > 8) {
            $_SESSION['error'] = 'La Cédula Venezolana no puede tener más de 8 dígitos.';
            $url = 'formulario_cliente.php' . ($id_cliente ? '?id=' . $id_cliente : '');
            header('Location: ' . BASE_URL . '/views/' . $url);
            exit();
        } elseif (strlen($num_doc) > 10) {
            $_SESSION['error'] = 'El documento no puede tener más de 10 dígitos.';
            $url = 'formulario_cliente.php' . ($id_cliente ? '?id=' . $id_cliente : '');
            header('Location: ' . BASE_URL . '/views/' . $url);
            exit();
        }
        
        // Verificar duplicados (solo si es nuevo o cambió RIF)
        $existente = $this->modeloCliente->obtenerPorRif($rif_cedula);
        if ($existente && (!$id_cliente || $existente['id_cliente'] != $id_cliente)) {
            $_SESSION['error'] = 'Ya existe un cliente con ese RIF/Cédula.';
            $url = 'formulario_cliente.php';
            if ($id_cliente) $url .= '?id=' . $id_cliente;
            header('Location: ' . BASE_URL . '/views/' . $url);
            exit();
        }
        
        $datos = [
            'rif_cedula' => $rif_cedula,
            'nombre_cliente' => $nombre_cliente,
            'email' => $email,
            'telefono' => $telefono,
            'estado' => $estado,
            'municipio' => $municipio,
            'parroquia' => $parroquia,
            'direccion' => $direccion_det,
            'estatus' => $estatus
        ];
        
        if ($id_cliente) {
            if ($this->modeloCliente->actualizar($id_cliente, $datos)) {
                // Si el cliente pasa a Inactivo, desactivamos sus puestos
                if ($estatus === 'Inactivo') {
                    $modeloPuesto = new Puesto();
                    $modeloPuesto->actualizarEstatusPorCliente($id_cliente, 'Inactivo');
                    $_SESSION['exito'] = 'Cliente actualizado. Sus puestos asociados han sido marcados como Inactivos.';
                } else {
                    $_SESSION['exito'] = 'Cliente actualizado.';
                }
            } else {
                $_SESSION['error'] = 'Error al actualizar.';
            }
        } else {
            if ($this->modeloCliente->insertar($datos)) {
                $_SESSION['exito'] = 'Cliente registrado.';
            } else {
                $_SESSION['error'] = 'Error al registrar.';
            }
        }
        
        header('Location: ' . BASE_URL . '/views/listado_clientes.php');
        exit();
    }
    
    /**
     * Eliminar
     */
    public function eliminar() {
        if (!self::esAdministrador()) {
            header('Location: ' . BASE_URL . '/views/listado_clientes.php');
            exit();
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
             header('Location: ' . BASE_URL . '/views/listado_clientes.php');
             exit();
        }
        
        // Verificar puestos activos antes de eliminar
        $modeloPuesto = new Puesto();
        $activos = $modeloPuesto->contarActivosPorCliente($id);
        
        if ($activos > 0) {
            $_SESSION['error'] = "No se puede eliminar el cliente. Tiene $activos puestos de guardia activos asociados. Debe desactivarlos o reasignarlos primero.";
            header('Location: ' . BASE_URL . '/views/listado_clientes.php');
            exit();
        }
        
        try {
            if ($this->modeloCliente->eliminar($id)) {
                $_SESSION['exito'] = 'Cliente eliminado.';
            } else {
                $_SESSION['error'] = 'No se puede eliminar (registros vinculados, verifique histórico).';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al eliminar: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/views/listado_clientes.php');
        exit();
    }
}
