<?php
/**
 * Controlador de Asignaciones
 */

require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

// Cargar FPDF antes de que se defina cualquier clase que lo extienda
$fpdfPath = __DIR__ . '/../libs/fpdf/fpdf.php';
if (file_exists($fpdfPath)) {
    require_once $fpdfPath;
}

class ControladorAsignacion extends ControladorPrincipal {
    private $modeloAsignacion;
    private $modeloVigilante;
    private $modeloPuesto;
    
    public function __construct() {
        self::verificarSesion();
        
        if (!self::esAdministrador() && !self::esSupervisor()) {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta sección.';
            header('Location: ' . BASE_URL . '/views/dashboard.php');
            exit();
        }
        
        $this->modeloAsignacion = new Asignacion();
        $this->modeloVigilante = new Vigilante();
        $this->modeloPuesto = new Puesto();
    }
    
    /**
     * Muestra el formulario de asignación
     */
    public function mostrarFormulario() {
        $vigilantes_activos = $this->modeloVigilante->obtenerPorEstatus('Activo');
        $puestos_activos = $this->modeloPuesto->obtenerActivos();
        
        // Obtener mapa detallado de disponibilidad
        $disponibilidad = $this->analizarDisponibilidad($vigilantes_activos);
        
        $datos = [
            'vigilantes' => $vigilantes_activos,
            'puestos' => $puestos_activos,
            'disponibilidad' => $disponibilidad // Mapa [cedula => ['status', 'mensaje', 'fin']]
        ];
        
        $this->renderizarVista('asignar_puesto.php', $datos);
    }

    /**
     * Muestra la vista de Calendario de Conflictos / Disponibilidad
     */
    public function mostrarDisponibilidad() {
        $vigilantes = $this->modeloVigilante->obtenerPorEstatus('Activo');
        $analisis = $this->analizarDisponibilidad($vigilantes);
        
        // Filtro de Quincena (para mostrar contexto si se requiere)
        $dia_actual = (int)date('j');
        $rango_texto = ($dia_actual <= 15) ? '1 Quincena (01-15)' : '2 Quincena (16-Fin)';
        
        $datos = [
            'vigilantes' => $vigilantes,
            'analisis' => $analisis,
            'rango_texto' => $rango_texto
        ];
        
        $this->renderizarVista('disponibilidad_vigilantes.php', $datos);
    }

    /**
     * Motor de Cálculo de Descansos
     * Retorna un array con el estado de cada vigilante
     */
    private function analizarDisponibilidad($vigilantes) {
        $resultados = [];
        $ahora = new DateTime();
        
        foreach ($vigilantes as $v) {
            $cedula = $v['cedula'];
            $ultima = $this->modeloAsignacion->obtenerUltimaAsignacion($cedula);
            
            if (!$ultima || $ultima['estatus'] == 'Eliminada') {
                $resultados[$cedula] = ['status' => 'DISPONIBLE', 'mensaje' => 'Sin asignaciones recientes', 'fin' => null];
                continue;
            }
            
            $fin = new DateTime($ultima['fecha_fin']);
            
            if ($fin > $ahora) {
                // Está dentro del periodo ocupado (trabajo o descanso)
                // Determinar si es "Ocupado" (turno) o "Bloqueado" (descanso)
                // Analizamos el rol para estimar el punto de quiebre
                $inicio = new DateTime($ultima['fecha_inicio']);
                $rol = $ultima['rol_guardia'];
                
                // Extraer horas laborales teóricas según rol
                $horas_trabajo = 0;
                if (strpos($rol, '24x48') !== false) $horas_trabajo = 24;
                elseif (strpos($rol, '2x2') !== false) $horas_trabajo = 48; // 2 días
                elseif (strpos($rol, '5x2') !== false) $horas_trabajo = 120; // 5 días
                elseif (strpos($rol, '4x2') !== false) $horas_trabajo = 96; // 4 días
                
                $fin_trabajo = clone $inicio;
                $fin_trabajo->modify("+{$horas_trabajo} hours");
                
                if ($ahora < $fin_trabajo) {
                    $resultados[$cedula] = [
                        'status' => 'OCUPADO',
                        'mensaje' => 'En turno activo',
                        'fin' => $fin_trabajo->format('Y-m-d H:i')
                    ];
                } else {
                    // Si ya pasó el trabajo pero no el fin total, está en DESCANSO
                    $interval = $ahora->diff($fin);
                    $horas_restantes = ($interval->days * 24) + $interval->h;
                    
                    $resultados[$cedula] = [
                        'status' => 'BLOQUEADO',
                        'mensaje' => "En descanso obligatorio (Libre en {$horas_restantes}h)",
                        'fin' => $fin->format('Y-m-d H:i')
                    ];
                }
            } else {
                $resultados[$cedula] = ['status' => 'DISPONIBLE', 'mensaje' => 'Listo para asignar', 'fin' => null];
            }
        }
        
        return $resultados;
    }
    
    /**
     * Procesa una nueva asignación con lógica de ciclos
     */
    public function procesarNuevaAsignacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/views/asignar_puesto.php');
            exit();
        }
        
        $cedula_vigilante = trim($_POST['cedula_vigilante'] ?? '');
        $id_puesto = intval($_POST['id_puesto'] ?? 0);
        
        // Fecha y Hora
        $fecha_date = $_POST['fecha_inicio_date'] ?? '';
        $fecha_time = $_POST['fecha_inicio_time'] ?? '';
        $fecha_inicio = ($fecha_date && $fecha_time) ? $fecha_date . 'T' . $fecha_time : '';
        
        $rol_guardia = $_POST['rol_guardia'] ?? ''; 
        $observaciones = trim($_POST['observaciones'] ?? '');
        
        // Validaciones básicas
        if (empty($cedula_vigilante) || $id_puesto <= 0 || empty($fecha_inicio) || empty($rol_guardia)) {
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            header('Location: ' . BASE_URL . '/views/asignar_puesto.php');
            exit();
        }
        
        $fecha_inicio_dt = DateTime::createFromFormat('Y-m-d\TH:i', $fecha_inicio);
        if (!$fecha_inicio_dt) {
            $_SESSION['error'] = 'Formato de fecha inválido.';
            header('Location: ' . BASE_URL . '/views/asignar_puesto.php');
            exit();
        }

        // Detección Automática de Turno
        $hora = (int)$fecha_inicio_dt->format('H');
        $minuto = (int)$fecha_inicio_dt->format('i');
        
        if ($minuto !== 0 || ($hora !== 6 && $hora !== 18)) {
            $_SESSION['error'] = 'La hora de inicio debe ser 06:00 AM o 06:00 PM.';
            header('Location: ' . BASE_URL . '/views/asignar_puesto.php');
            exit();
        }
        
        $turno = ($hora === 6) ? 'Diurno' : 'Nocturno';
        $rol_completo = $rol_guardia . ' (' . $turno . ')';

        // Validación de permisos y límites de fecha
        $ahora = new DateTime();
        $limite_futuro = new DateTime('tomorrow 23:59:59');
        
        if ($fecha_inicio_dt > $limite_futuro) {
            $_SESSION['error'] = 'No se pueden agendar turnos que inicien más allá del día de mañana.';
            header('Location: ' . BASE_URL . '/views/asignar_puesto.php');
            exit();
        }

        if ($fecha_inicio_dt < $ahora && !self::esAdministrador()) {
            $_SESSION['error'] = 'Solo los administradores pueden crear asignaciones en el pasado.';
            header('Location: ' . BASE_URL . '/views/asignar_puesto.php');
            exit();
        }

        // CÁLCULO DE CICLOS Y FECHA FIN
        $fecha_fin_dt = clone $fecha_inicio_dt;
        
        switch ($rol_guardia) {
            case '24x48':
                $fecha_fin_dt->modify('+3 days'); 
                break;
            case '2x2':
                $fecha_fin_dt->modify('+4 days');
                break;
            case '5x2':
                $fecha_fin_dt->modify('+7 days'); 
                break;
            case '4x2':
                $fecha_fin_dt->modify('+6 days'); 
                break;
            default:
                $_SESSION['error'] = 'Rol de guardia no reconocido.';
                header('Location: ' . BASE_URL . '/views/asignar_puesto.php');
                exit();
        }
        
        $fecha_fin_dt->modify('-1 second');
        $fecha_fin = $fecha_fin_dt->format('Y-m-d H:i:s');
        $fecha_inicio_sql = $fecha_inicio_dt->format('Y-m-d H:i:s');
        
        // VERIFICACIÓN DE DISPONIBILIDAD (Bloqueo Fuerte)
        if ($this->modeloAsignacion->verificarConflicto($cedula_vigilante, $fecha_inicio_sql, $fecha_fin)) {
            $_SESSION['error'] = "El vigilante no está disponible (Turno activo o Periodo de Descanso obligatorio pendiente). Asignación rechazada.";
            header('Location: ' . BASE_URL . '/views/asignar_puesto.php');
            exit();
        }
        
        // Crear asignación
        $datos = [
            'cedula_vigilante' => $cedula_vigilante,
            'id_puesto' => $id_puesto,
            'fecha_inicio' => $fecha_inicio_sql,
            'fecha_fin' => $fecha_fin,
            'rol_guardia' => $rol_completo,
            'observaciones' => $observaciones . " [Turno: $turno]",
            'estatus' => 'Activa'
        ];
        
        if ($this->modeloAsignacion->crearAsignacion($datos)) {
            $_SESSION['exito'] = "Asignación creada ($turno). Vigilante ocupado hasta " . $fecha_fin_dt->format('d/m/Y H:i');
            header('Location: ' . BASE_URL . '/views/cronograma_guardia.php');
            exit();
        } else {
            $_SESSION['error'] = 'Error al crear la asignación.';
            header('Location: ' . BASE_URL . '/views/asignar_puesto.php');
            exit();
        }
    }
    
    /**
     * Muestra el cronograma de guardia
     */
    public function mostrarCronograma() {
        if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_fin'])) {
            $fecha_inicio = $_GET['fecha_inicio'];
            $fecha_fin = $_GET['fecha_fin'];
        } else {
            // Filtro Inteligente de Quincena
            $dia_actual = (int)date('j');
            $mes_actual = date('m');
            $anio_actual = date('Y');
            
            if ($dia_actual <= 15) {
                $fecha_inicio = "$anio_actual-$mes_actual-01";
                $fecha_fin = "$anio_actual-$mes_actual-15";
            } else {
                $fecha_inicio = "$anio_actual-$mes_actual-16";
                $fecha_fin = date("Y-m-t"); 
            }
        }
        $id_cliente = $_GET['id_cliente'] ?? null;
        
        $cronograma = $this->modeloAsignacion->obtenerCronograma($fecha_inicio, $fecha_fin, $id_cliente);
        $this->verificarEstadoAsignaciones();
        
        $datos = [
            'cronograma' => $cronograma,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ];
        
        $this->renderizarVista('cronograma_guardia.php', $datos);
    }
    
    /**
     * Genera el reporte PDF del cronograma
     */
    public function generarCronogramaPDF() {
        $fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $fecha_fin = $_GET['fecha_fin'] ?? '';
        $id_cliente = $_GET['id_cliente'] ?? null;
        
        $cronograma = $this->modeloAsignacion->obtenerCronograma($fecha_inicio, $fecha_fin, $id_cliente);
        
        $cronograma = $this->modeloAsignacion->obtenerCronograma($fecha_inicio, $fecha_fin, $id_cliente);
        
        if (!class_exists('FPDF')) {
            $_SESSION['error'] = 'Librería FPDF no encontrada.';
            header('Location: ' . BASE_URL . '/views/cronograma_guardia.php');
            exit();
        }
        
        // Usar la clase personalizada (definida al final deste archivo)
        $pdf = new PDF_Cronograma('L', 'mm', 'A4'); // Horizontal para más espacio
        $pdf->SetTituloPeriodo($fecha_inicio, $fecha_fin);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        
        // Cabecera Tabla
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetTextColor(44, 53, 65);
        $pdf->SetDrawColor(200, 200, 200);
        
        // Anchos: Vigilante(60), Puesto(60), Inicio(35), Fin(35), Rol(35), Estatus(25)
        $w_vig = 60; $w_pue = 70; $w_ini = 35; $w_fin = 35; $w_rol = 40; $w_est = 25;
        
        $pdf->Cell($w_vig, 8, 'Vigilante', 1, 0, 'L', true);
        $pdf->Cell($w_pue, 8, 'Puesto', 1, 0, 'L', true);
        $pdf->Cell($w_ini, 8, 'Inicio', 1, 0, 'C', true);
        $pdf->Cell($w_fin, 8, 'Fin', 1, 0, 'C', true);
        $pdf->Cell($w_rol, 8, 'Rol', 1, 0, 'C', true);
        $pdf->Cell($w_est, 8, 'Estatus', 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0);
        
        foreach ($cronograma as $a) {
            // Ajuste para evitar conflictos de altura
            // Usamos Cell simple con truncado si es necesario, o MultiCell controlado
            // Para cronograma, mejor truncar nombres muy largos o reducir fuente si es necesario
            
            $nombre = mb_convert_encoding(substr($a['nombres'] . ' ' . $a['apellidos'], 0, 30), 'ISO-8859-1', 'UTF-8');
            $puesto = mb_convert_encoding(substr($a['nombre_cliente'] . ' - ' . ($a['direccion_puesto'] ?? ''), 0, 40), 'ISO-8859-1', 'UTF-8');
            $inicio = date('d/m H:i', strtotime($a['fecha_inicio']));
            $fin = date('d/m H:i', strtotime($a['fecha_fin']));
            $rol = mb_convert_encoding($a['rol_guardia'], 'ISO-8859-1', 'UTF-8');
            $estatus = mb_convert_encoding($a['estatus'], 'ISO-8859-1', 'UTF-8');
            
            $pdf->Cell($w_vig, 8, $nombre, 1);
            $pdf->Cell($w_pue, 8, $puesto, 1);
            $pdf->Cell($w_ini, 8, $inicio, 1, 0, 'C');
            $pdf->Cell($w_fin, 8, $fin, 1, 0, 'C');
            $pdf->Cell($w_rol, 8, $rol, 1, 0, 'C');
            
            // Color estatus
            if ($a['estatus'] == 'Activa' || $a['estatus'] == 'Activo') $pdf->SetTextColor(46, 204, 113); // Verde
            elseif ($a['estatus'] == 'Completada') $pdf->SetTextColor(52, 152, 219); // Azul
            elseif ($a['estatus'] == 'Cancelada') $pdf->SetTextColor(230, 126, 34); // Naranja
            elseif ($a['estatus'] == 'Eliminada') $pdf->SetTextColor(231, 76, 60); // Rojo
            else $pdf->SetTextColor(128); // Gris
            
            $pdf->Cell($w_est, 8, $estatus, 1, 1, 'C');
            $pdf->SetTextColor(0);
        }
        
        $pdf->Output('I', 'Cronograma.pdf'); // 'I' para ver en navegador, 'D' para descargar forzado
    }

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/views/cronograma_guardia.php');
            exit();
        }
        $id_asignacion = intval($_POST['id_asignacion'] ?? 0);
        if ($id_asignacion <= 0) {
            $_SESSION['error'] = 'Asignación inválida.';
            header('Location: ' . BASE_URL . '/views/cronograma_guardia.php');
            exit();
        }
        
        // Ejecutar Hard Delete (borrado permanente)
        $sql = "DELETE FROM asignaciones WHERE id_asignacion = :id";
        $stmt = $this->modeloAsignacion->getDB()->prepare($sql);
        
        if ($stmt->execute(['id' => $id_asignacion])) {
            $_SESSION['exito'] = 'Asignación eliminada permanentemente del historial.';
        } else {
            $_SESSION['error'] = 'Error al eliminar la asignación de la base de datos.';
        }
        
        header('Location: ' . BASE_URL . '/views/cronograma_guardia.php');
        exit();
    }

    public function anular() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/views/cronograma_guardia.php');
            exit();
        }
        $id_asignacion = intval($_POST['id_asignacion'] ?? 0);
        if ($id_asignacion <= 0) {
            $_SESSION['error'] = 'Asignación inválida.';
            header('Location: ' . BASE_URL . '/views/cronograma_guardia.php');
            exit();
        }
        
        if ($this->modeloAsignacion->cambiarEstatus($id_asignacion, 'Cancelada')) {
            $_SESSION['exito'] = 'Asignación anulada. Se mantendrá en el historial como Cancelada sin afectar otros cálculos.';
        } else {
            $_SESSION['error'] = 'Error al anular la asignación.';
        }
        
        header('Location: ' . BASE_URL . '/views/cronograma_guardia.php');
        exit();
    }

    private function verificarEstadoAsignaciones() {
        $this->modeloAsignacion->actualizarEstadosCaducados();
    }
}

/**
 * Clase PDF Personalizada para Cronograma
 */
class PDF_Cronograma extends FPDF {
    private $fecha_inicio;
    private $fecha_fin;

    public function SetTituloPeriodo($inicio, $fin) {
        $this->fecha_inicio = $inicio;
        $this->fecha_fin = $fin;
    }

    function Header() {
        // === CONFIGURACIÓN DE COLORES ===
        $headerBgColor = [44, 53, 65]; 
        $this->SetFillColor($headerBgColor[0], $headerBgColor[1], $headerBgColor[2]);
        $this->Rect(0, 0, 297, 40, 'F'); // A4 Horizontal width ~297mm

        // Logo
        $logoPath = __DIR__ . '/../assets/images/logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 12, 10, 16);
        }

        // Título Empresa/Sistema
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(35, 12); 
        $this->Cell(0, 10, 'SIRCVIG', 0, 1, 'L');
        
        $this->SetFont('Arial', '', 10);
        $this->SetXY(35, 20);
        $this->Cell(0, 10, mb_convert_encoding('Sistema de Gestión de Seguridad', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

        // Título Documento
        $this->SetFont('Arial', 'B', 16);
        $this->SetXY(0, 10);
        $this->Cell(280, 10, mb_convert_encoding('CRONOGRAMA DE GUARDIA', 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        
        $this->SetFont('Arial', 'I', 10);
        $this->SetXY(0, 18);
        
        $str_inicio = !empty($this->fecha_inicio) ? date('d/m/Y', strtotime($this->fecha_inicio)) : 'Histórico';
        $str_fin = !empty($this->fecha_fin) ? date('d/m/Y', strtotime($this->fecha_fin)) : 'Actual';
        $periodo = "Desde: $str_inicio Hasta: $str_fin";
        
        $this->Cell(280, 10, mb_convert_encoding($periodo, 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');

        $this->Ln(20);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, mb_convert_encoding('Generado por SIRCVIG el ' . date('d/m/Y H:i'), 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $this->Cell(0, 10, mb_convert_encoding('Página ', 'ISO-8859-1', 'UTF-8') . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }
}
