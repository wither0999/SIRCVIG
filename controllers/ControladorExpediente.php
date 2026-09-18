<?php
/**
 * Controlador de Expedientes de Vigilantes
 */

require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

class ControladorExpediente extends ControladorPrincipal {
    private $modeloVigilante;
    private $modeloDocumento;
    
    public function __construct() {
        self::verificarSesion();
        
        // Administrador y Secretario pueden modificar, Supervisor solo lectura
        if (!self::esAdministrador() && !self::esSecretario() && !self::esSupervisor()) {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta sección.';
            header('Location: ' . BASE_URL . '/views/dashboard.php');
            exit();
        }
        
        $this->modeloVigilante = new Vigilante();
        $this->modeloDocumento = new Documento();
    }
    
    /**
     * Muestra el formulario de registro
     */
    public function mostrarFormularioRegistro() {
        // Solo Administrador y Secretario pueden crear
        if (!self::esAdministrador() && !self::esSecretario()) {
            $_SESSION['error'] = 'No tiene permisos para crear vigilantes.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        $this->renderizarVista('registro_vigilante.php');
    }
    
    /**
     * Procesa el registro de un nuevo vigilante
     */
    public function procesarRegistro() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
            exit();
        }
        
        // Solo Administrador y Secretario
        if (!self::esAdministrador() && !self::esSecretario()) {
            $_SESSION['error'] = 'No tiene permisos para crear vigilantes.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        // --- Recolección de Datos ---
        $tipo_cedula = $_POST['tipo_cedula'] ?? '';
        $cedula_numero = trim($_POST['cedula_numero'] ?? '');
        $cedula = $tipo_cedula . '-' . $cedula_numero;
        
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
        
        $estado = trim($_POST['estado'] ?? '');
        $municipio = trim($_POST['municipio'] ?? '');
        $parroquia = trim($_POST['parroquia'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        
        $codigo_operadora = $_POST['codigo_operadora'] ?? '';
        $telefono_numero = trim($_POST['telefono_numero'] ?? '');
        $telefono = $codigo_operadora . $telefono_numero;
        
        $estatus = $_POST['estatus'] ?? 'Aspirante';

        // Guardar input en sesión para repoblar formulario en caso de error
        $_SESSION['old_input'] = $_POST;
        
        // --- Validaciones ---

        // 1. Campos Obligatorios Básicos
        if (empty($cedula) || empty($nombres) || empty($apellidos) || empty($fecha_nacimiento) || empty($direccion) || empty($telefono) ||
            empty($estado) || empty($municipio) || empty($parroquia)) {
            $_SESSION['error'] = 'Todos los campos marcados con * son obligatorios.';
            header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
            exit();
        }
        
        // 2. Nombres y Apellidos (Min 4, Max 20, Solo Letras)
        // Regex: Inicio y fin, letras mayus/minus, acentos, espacios. 
        // No numeros ni simbolos.
        if (!preg_match("/^[a-zA-Z\x{00C0}-\x{00FF}\s]+$/u", $nombres) || strlen($nombres) < 4 || strlen($nombres) > 20) {
            $_SESSION['error'] = 'El nombre debe tener entre 4 y 20 caracteres y solo contener letras.';
            header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
            exit();
        }
        if (!preg_match("/^[a-zA-Z\x{00C0}-\x{00FF}\s]+$/u", $apellidos) || strlen($apellidos) < 4 || strlen($apellidos) > 20) {
            $_SESSION['error'] = 'El apellido debe tener entre 4 y 20 caracteres y solo contener letras.';
            header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
            exit();
        }
        
        // 3. Cédula
        if (!is_numeric($cedula_numero)) {
            $_SESSION['error'] = 'La cédula debe contener solo números.';
            header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
            exit();
        }
        if ($tipo_cedula === 'V' && (strlen($cedula_numero) < 7 || strlen($cedula_numero) > 8)) {
            $_SESSION['error'] = 'La cédula venezolana debe tener entre 7 y 8 dígitos.';
            header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
            exit();
        }
        // Verificar existencia cédula
        if ($this->modeloVigilante->obtenerPorCedula($cedula)) {
            $_SESSION['error'] = 'Ya existe un vigilante con esta cédula.';
            header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
            exit();
        }
        
        // 4. Teléfono
        if (!is_numeric($telefono_numero) || strlen($telefono_numero) != 7 || empty($codigo_operadora)) {
             $_SESSION['error'] = 'El teléfono debe tener un código de operadora y 7 dígitos numéricos.';
             header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
             exit();
        }
        // Verificar existencia teléfono
        if ($this->modeloVigilante->obtenerPorTelefono($telefono)) {
            $_SESSION['error'] = 'El número de teléfono ya está registrado por otro vigilante.';
            header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
            exit();
        }
        
        // 5. Fecha de Nacimiento (Min Año 1950, Max Hoy - 18 años)
        $fecha_valida = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
        if (!$fecha_valida) {
            $_SESSION['error'] = 'Fecha de nacimiento inválida.';
            header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
            exit();
        }
        
        // Validar año mínimo 1950
        if ((int)$fecha_valida->format('Y') < 1950) {
            $_SESSION['error'] = 'El año de nacimiento debe ser mayor a 1950.';
            header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
            exit();
        }
        
        // Validar mayoría de edad
        $fecha_actual = new DateTime();
        $edad = $fecha_actual->diff($fecha_valida)->y;
        if ($edad < 18) {
            $_SESSION['error'] = 'El vigilante debe ser mayor de edad (18+).';
            header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
            exit();
        }
        
        // 6. Validar Documentos Obligatorios (Solo en Registro)
        // Foto, CedulaEscaneada, Antecedentes
        $docs_req = ['foto', 'cedula_escaneada', 'antecedentes'];
        foreach ($docs_req as $doc) {
            if (!isset($_FILES[$doc]) || $_FILES[$doc]['error'] === UPLOAD_ERR_NO_FILE) {
                $_SESSION['error'] = 'Debe subir todos los documentos obligatorios (Foto, Cédula, Antecedentes).';
                header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
                exit();
            }
        }

        // --- Fin Validaciones ---
        
        // Preparar datos
        $datos = [
            'cedula' => $cedula,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'fecha_nacimiento' => $fecha_nacimiento,
            'estado' => $estado,
            'municipio' => $municipio,
            'parroquia' => $parroquia,
            'direccion' => $direccion,
            'telefono' => $telefono,
            'estatus' => $estatus
        ];
        
        // Registrar vigilante
        if ($this->modeloVigilante->registrar($datos)) {
            // Procesar archivos
            $this->procesarArchivos($cedula);
            
            // Limpiar old_input al tener éxito
            unset($_SESSION['old_input']);
            
            $_SESSION['exito'] = 'Vigilante registrado exitosamente.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        } else {
            $_SESSION['error'] = 'Error al registrar el vigilante en base de datos.';
            header('Location: ' . BASE_URL . '/views/registro_vigilante.php');
            exit();
        }
    }
    
    /**
     * Procesa la subida de archivos
     */
    private function procesarArchivos($cedula) {
        $tipos_documentos = [
            'foto' => 'Foto',
            'cedula_escaneada' => 'CedulaEscaneada',
            'antecedentes' => 'Antecedentes'
        ];
        
        // Crear directorio si no existe
        $directorio = UPLOADS_PATH . '/vigilantes/' . $cedula . '/';
        if (!file_exists($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        foreach ($tipos_documentos as $campo => $tipo) {
            if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
                $archivo = $_FILES[$campo];
                
                // Validar tipo de archivo
                $tipos_permitidos = ($tipo === 'Antecedentes') ? ALLOWED_DOC_TYPES : ALLOWED_IMAGE_TYPES;
                
                if (!in_array($archivo['type'], $tipos_permitidos)) {
                    continue; // Saltar este archivo
                }
                
                // Validar tamaño
                if ($archivo['size'] > MAX_FILE_SIZE) {
                    continue; // Saltar este archivo
                }
                
                // Generar nombre único
                $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
                $nombre_archivo = $tipo . '_' . time() . '.' . $extension;
                $ruta_completa = $directorio . $nombre_archivo;
                
                // Mover archivo
                if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
                    // Guardar en base de datos
                    $ruta_relativa = '/uploads/vigilantes/' . $cedula . '/' . $nombre_archivo;
                    $this->modeloDocumento->guardarDocumento($cedula, $tipo, $ruta_relativa, $archivo['name']);
                }
            }
        }
    }
    
    /**
     * Lista todos los vigilantes
     */
    public function listarVigilantes() {
        $termino_busqueda = $_GET['buscar'] ?? '';
        
        if (!empty($termino_busqueda)) {
            $vigilantes = $this->modeloVigilante->buscar($termino_busqueda);
        } else {
            $vigilantes = $this->modeloVigilante->obtenerTodos();
        }
        
        // Obtener documentos para cada vigilante (solo si es Administrador)
        foreach ($vigilantes as &$vigilante) {
            $documentos = $this->modeloDocumento->obtenerPorVigilante($vigilante['cedula']);
            $vigilante['documentos'] = $documentos;
        }
        
        $datos = [
            'vigilantes' => $vigilantes,
            'termino_busqueda' => $termino_busqueda,
            'puede_modificar' => self::esAdministrador() || self::esSecretario(),
            'puede_ver_antecedentes' => self::esAdministrador()
        ];
        
        $this->renderizarVista('listado_vigilantes.php', $datos);
    }
    
    /**
     * Modifica el estatus de un vigilante
     */
    public function modificarEstatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        // Solo Administrador y Secretario
        if (!self::esAdministrador() && !self::esSecretario()) {
            $_SESSION['error'] = 'No tiene permisos para modificar vigilantes.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        $cedula = trim($_POST['cedula'] ?? '');
        $estatus = $_POST['estatus'] ?? '';
        
        if (empty($cedula) || empty($estatus)) {
            $_SESSION['error'] = 'Datos inválidos.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        if ($this->modeloVigilante->cambiarEstatus($cedula, $estatus)) {
            $_SESSION['exito'] = 'Estatus actualizado exitosamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar el estatus.';
        }
        
        header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
        exit();
    }
    
    /**
     * Muestra la ficha personal de un vigilante
     */
    public function mostrarFicha() {
        $cedula = $_GET['cedula'] ?? '';
        
        if (empty($cedula)) {
            $_SESSION['error'] = 'Cédula no especificada.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        $vigilante = $this->modeloVigilante->obtenerPorCedula($cedula);
        
        if (!$vigilante) {
            $_SESSION['error'] = 'Vigilante no encontrado.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        $documentos = $this->modeloDocumento->obtenerPorVigilante($cedula);
        
        $datos = [
            'vigilante' => $vigilante,
            'documentos' => $documentos,
            'puede_ver_antecedentes' => self::esAdministrador()
        ];
        
        $this->renderizarVista('ficha_personal.php', $datos);
    }
    /**
     * Muestra el formulario de edición
     */
    public function mostrarFormularioEdicion() {
        if (!self::esAdministrador()) {
            $_SESSION['error'] = 'No tiene permisos para editar vigilantes.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        $cedula = $_GET['cedula'] ?? '';
        
        if (empty($cedula)) {
            $_SESSION['error'] = 'Cédula no especificada.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        $vigilante = $this->modeloVigilante->obtenerPorCedula($cedula);
        
        if (!$vigilante) {
            $_SESSION['error'] = 'Vigilante no encontrado.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        // Renderizar vista con datos
        $datos = [
            'vigilante' => $vigilante,
            'documentos' => $this->modeloDocumento->obtenerPorVigilante($cedula),
            'es_edicion' => true
        ];
        
        $this->renderizarVista('registro_vigilante.php', $datos);
    }

    /**
     * Procesa la edición de un vigilante
     */
    public function procesarEdicion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        if (!self::esAdministrador()) {
            $_SESSION['error'] = 'No tiene permisos para editar vigilantes.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        // Reconstruir cédula
        $tipo_cedula = $_POST['tipo_cedula'] ?? '';
        $cedula_numero = trim($_POST['cedula_numero'] ?? '');
        $cedula = $tipo_cedula . '-' . $cedula_numero;
        
        $cedula_original = trim($_POST['cedula_original'] ?? '');
        
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
        
        $estado = trim($_POST['estado'] ?? '');
        $municipio = trim($_POST['municipio'] ?? '');
        $parroquia = trim($_POST['parroquia'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        
        $codigo_operadora = $_POST['codigo_operadora'] ?? '';
        $telefono_numero = trim($_POST['telefono_numero'] ?? '');
        $telefono = $codigo_operadora . $telefono_numero;
        
        $estatus = $_POST['estatus'] ?? 'Aspirante';
        
        // Validaciones básicas
        if (empty($cedula) || empty($nombres) || empty($apellidos) || 
            empty($estado) || empty($municipio) || empty($parroquia) || empty($direccion)) {
            $_SESSION['error'] = 'Campos obligatorios faltantes.';
            // Redirigir usando cédula original si existe, sino al listado
            $redir_cedula = !empty($cedula_original) ? $cedula_original : $cedula;
            header('Location: ' . BASE_URL . '/index.php?action=editar_vigilante&cedula=' . $redir_cedula);
            exit();
        }
        
        // Validación de Nombres y Apellidos
        if (!preg_match("/^[a-zA-Z\x{00C0}-\x{00FF}\s]{3,25}$/u", $nombres) || !preg_match("/^[a-zA-Z\x{00C0}-\x{00FF}\s]{3,25}$/u", $apellidos)) {
             $_SESSION['error'] = 'Nombres y Apellidos deben tener entre 3 y 25 letras y solo letras.';
             $redir_cedula = !empty($cedula_original) ? $cedula_original : $cedula;
             header('Location: ' . BASE_URL . '/index.php?action=editar_vigilante&cedula=' . $redir_cedula);
             exit();
        }
        
        // Validar año mínimo 1950
        $fecha_val = new DateTime($fecha_nacimiento);
         if ((int)$fecha_val->format('Y') < 1950) {
            $_SESSION['error'] = 'El año de nacimiento debe ser mayor a 1950.';
            $redir_cedula = !empty($cedula_original) ? $cedula_original : $cedula;
            header('Location: ' . BASE_URL . '/index.php?action=editar_vigilante&cedula=' . $redir_cedula);
            exit();
        }

        // Validar edad (Edición)
        $hoy = new DateTime();
        if ($hoy->diff($fecha_val)->y < 18) {
             $_SESSION['error'] = 'El vigilante debe ser mayor de edad (18+).';
             $redir_cedula = !empty($cedula_original) ? $cedula_original : $cedula;
             header('Location: ' . BASE_URL . '/index.php?action=editar_vigilante&cedula=' . $redir_cedula);
             exit();
        }
        
        // Verificar si el teléfono ya existe (excluyendo al vigilante actual)
        $vigilanteConTelefono = $this->modeloVigilante->obtenerPorTelefono($telefono);
        if ($vigilanteConTelefono && $vigilanteConTelefono['cedula'] != $cedula) {
            $_SESSION['error'] = 'El número de teléfono ya está registrado por otro vigilante.';
            header('Location: ' . BASE_URL . '/index.php?action=editar_vigilante&cedula=' . $cedula);
            exit();
        }
        
        // Nota: En edición básica no permitimos cambiar la cédula para simplificar (PK)
        // O si se permite, hay que manejar la integridad referencial.
        // Asumiremos que la cédula NO cambia por ahora.
        
        $datos = [
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'fecha_nacimiento' => $fecha_nacimiento,
            'estado' => $estado,
            'municipio' => $municipio,
            'parroquia' => $parroquia,
            'direccion' => $direccion,
            'telefono' => $telefono,
            'estatus' => $estatus
        ];
        
        if ($this->modeloVigilante->modificar($cedula, $datos)) {
            // Procesar nuevos archivos si los hay
            $this->procesarArchivos($cedula);
            
            $_SESSION['exito'] = 'Vigilante actualizado exitosamente.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        } else {
            $_SESSION['error'] = 'Error al actualizar el vigilante.';
            header('Location: ' . BASE_URL . '/index.php?action=editar_vigilante&cedula=' . $cedula);
            exit();
        }
    }

    /**
     * Elimina un vigilante y todos sus datos relacionados (asignaciones, documentos)
     */
    public function eliminarVigilante() {
        if (!self::esAdministrador()) {
            $_SESSION['error'] = 'No tiene permisos para eliminar vigilantes.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        $cedula = $_GET['cedula'] ?? '';
        
        if (empty($cedula)) {
            $_SESSION['error'] = 'Cédula no especificada.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        $vigilante = $this->modeloVigilante->obtenerPorCedula($cedula);
        if (!$vigilante) {
            $_SESSION['error'] = 'Vigilante no encontrado.';
            header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
            exit();
        }
        
        try {
            // Iniciar transacción manualmente accediendo a la conexión PDO
            $db = $this->modeloVigilante->getDB();
            $db->beginTransaction();
            
            // 1. Eliminar Asignaciones relacionadas
            $sql_asignaciones = "DELETE FROM asignaciones WHERE cedula_vigilante = :cedula";
            $stmt_a = $db->prepare($sql_asignaciones);
            $stmt_a->execute(['cedula' => $cedula]);
            
            // 2. Eliminar Documentos relacionados
            // (Opcional: aquí se podría iterar para borrar archivos físicos, 
            // pero por simplicidad y robustez SQL solo borramos registros DB primero)
            $sql_documentos = "DELETE FROM documentos WHERE cedula_vigilante = :cedula";
            $stmt_d = $db->prepare($sql_documentos);
            $stmt_d->execute(['cedula' => $cedula]);
            
            // 3. Eliminar Vigilante
            $sql_vigilante = "DELETE FROM vigilantes WHERE cedula = :cedula";
            $stmt_v = $db->prepare($sql_vigilante);
            $stmt_v->execute(['cedula' => $cedula]);
            
            $db->commit();
            
            // Intentar limpiar carpetas de archivos (No crítico si falla)
            // Esto se hace post-commit para no bloquear la BD si el sistema de archivos es lento
            $directorio = UPLOADS_PATH . '/vigilantes/' . $cedula . '/';
            if (is_dir($directorio)) {
                // Borrar archivos
                $files = glob($directorio . '*');
                foreach($files as $file) {
                    if(is_file($file)) unlink($file);
                }
                // Borrar carpeta
                rmdir($directorio);
            }
            
            $_SESSION['exito'] = 'Vigilante y todos sus datos asociados eliminados exitosamente.';
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error eliminando vigilante: " . $e->getMessage());
            $_SESSION['error'] = 'Error crítico al eliminar: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
        exit();
    }
}
