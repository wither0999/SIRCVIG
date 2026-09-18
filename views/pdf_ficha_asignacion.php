<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';
require_once __DIR__ . '/../libs/fpdf/fpdf.php';
require_once __DIR__ . '/../models/Asignacion.php';

ControladorPrincipal::verificarSesion();

$id_asignacion = $_GET['id'] ?? null;

if (empty($id_asignacion)) {
    die('ID de asignación no especificado.');
}

$modeloAsignacion = new Asignacion();
$asignacion = $modeloAsignacion->obtenerPorId($id_asignacion);

if (!$asignacion) {
    die('Asignación no encontrada.');
}

define('FPDF_FONTPATH', __DIR__ . '/../libs/fpdf/font/');

class PDF_Assignment extends FPDF
{
    function Header()
    {
        // === CONFIGURACIÓN DE COLORES ===
        $headerBgColor = [44, 53, 65]; // Azul grisaceo oscuro (Igual que CV)
        $this->SetFillColor($headerBgColor[0], $headerBgColor[1], $headerBgColor[2]);
        $this->Rect(0, 0, 210, 40, 'F'); // Altura 40mm

        // Logo (Más pequeño)
        $logoPath = __DIR__ . '/../assets/images/logo.png';
        if (file_exists($logoPath)) {
            // Antes era 25, reducimos a 16
            $this->Image($logoPath, 12, 10, 16);
        }

        // Título Empresa/Sistema
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(35, 12); // Ajustado X para acercarlo al logo más pequeño
        $this->Cell(0, 10, 'SIRCVIG', 0, 1, 'L');
        
        $this->SetFont('Arial', '', 10);
        $this->SetXY(35, 20);
        $this->Cell(0, 10, mb_convert_encoding('Sistema de Gestión de Seguridad', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

        // Título Documento (Derecha)
        $this->SetFont('Arial', 'B', 16);
        $this->SetXY(0, 15);
        $this->Cell(200, 10, mb_convert_encoding('FICHA DE ASIGNACIÓN', 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        
        $this->Ln(20);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, mb_convert_encoding('Generado por SIRCVIG el ' . date('d/m/Y H:i'), 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $this->Cell(0, 10, mb_convert_encoding('Página ', 'ISO-8859-1', 'UTF-8') . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    // Header de sección adaptable en ancho
    function SectionHeader($title, $width = 0) {
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(44, 53, 65); // Color oscuro
        $this->Cell(0, 8, mb_convert_encoding($title, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        
        // Línea debajo, ancho variable
        $lineEnd = ($width > 0) ? $this->GetX() + $width : 200;
        // Si usamos Cell(0...), el cursor va a la derecha. Reiniciamos X si es columna
        if ($width > 0) {
           // Hack para que la línea quede bien si estamos en columna
           // Cell(0) lleva al margen derecho. Debemos dibujar la línea desde el X actual (que depende de donde empezamos)
           // Mejor obtenemos X antes de Cell? No, Cell ya movió.
           // Simplificación: Dibujamos la línea manual basada en margenes actuales si es full width
        }
        
        // Corrección: Dibujar la línea explícitamente desde el inicio de la celda
        $x = $this->GetX();
        $y = $this->GetY();
        // Pero Cell salto de linea con el 1 al final param? No, no debemos saltar aun si queremos línea alineada?
        // El codigo anterior hacia Cell(..., 1, 'L') -> Salto de linea. Linea dibujada en Y actual.
        
        $this->SetDrawColor(180, 180, 180);
        // Si width especifico (columnas), usamos el margen actual como inicio
        if ($width > 0) {
             // Asumimos que el cursor X está al inicio de la columna ANTES de escribir el texto?
             // No, SectionHeader fue llamado DESPUES de SetXY.
             // La linea debe estar en Y que acabamos de escribir.
             // Como Cell hizo ln, Y bajó 8.
             // La línea debe estar en Y-0.5 aprox? O justo debajo.
             $currentX = $this->GetX(); // Esto es el margen izquierdo tras el salto de linea de Cell?
             
             // Vamos a simplificar: Dibujar la linea manualmente en el script principal para columnas para evitar lios,
             // o arreglar esta funcion para no usar Cell(0,...) que fuerza margen global.
        } else {
             $this->Line($this->GetX(), $this->GetY(), 200, $this->GetY());
        }
        $this->Ln(3);
    }
    
    // Función auxiliar para dibujar encabezado de columna
    function ColumnHeader($title, $x, $y, $w) {
        $this->SetXY($x, $y);
        $this->SetFont('Arial', 'B', 11); // Un pelin mas pequeño para columnas
        $this->SetTextColor(44, 53, 65);
        $this->Cell($w, 8, mb_convert_encoding($title, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        
        $this->SetDrawColor(180, 180, 180);
        $this->Line($x, $y + 8, $x + $w, $y + 8);
        $this->SetXY($x, $y + 10); // Posicionar para contenido
    }
}

$pdf = new PDF_Assignment();
$pdf->AliasNbPages();
$pdf->AddPage();

// === CONTENIDO ===

// 1. Información del Puesto / Cliente (Bloque Principal)
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(44, 53, 65);
$pdf->Cell(0, 8, mb_convert_encoding('UBICACIÓN Y CLIENTE', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
$pdf->SetDrawColor(180, 180, 180);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(25, 6, 'Cliente:', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, mb_convert_encoding($asignacion['nombre_cliente'], 'ISO-8859-1', 'UTF-8'), 0, 1);

$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(25, 6, mb_convert_encoding('Dirección:', 'ISO-8859-1', 'UTF-8'), 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 6, mb_convert_encoding($asignacion['direccion_puesto'], 'ISO-8859-1', 'UTF-8'));

$pdf->Ln(8);

// 2. Detalles de la Asignación y Vigilante (Dos Columnas Independientes)
$y_columns_start = $pdf->GetY();

// --- COLUMNA 1: DETALLES ---
$col1_x = 10;
$col_w = 90;
$pdf->ColumnHeader('DETALLES OPERATIVOS', $col1_x, $y_columns_start, $col_w);

$pdf->SetX($col1_x); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(35, 6, 'Rol de Guardia:', 0, 0);
$pdf->SetFont('Arial', '', 10); $pdf->Cell(55, 6, mb_convert_encoding($asignacion['rol_guardia'], 'ISO-8859-1', 'UTF-8'), 0, 1);

$pdf->SetX($col1_x); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(35, 6, 'Fecha Inicio:', 0, 0);
$pdf->SetFont('Arial', '', 10); $pdf->Cell(55, 6, date('d/m/Y H:i', strtotime($asignacion['fecha_inicio'])), 0, 1);

$pdf->SetX($col1_x); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(35, 6, 'Fecha Fin:', 0, 0);
$pdf->SetFont('Arial', '', 10); $pdf->Cell(55, 6, date('d/m/Y H:i', strtotime($asignacion['fecha_fin'])), 0, 1);

$pdf->SetX($col1_x); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(35, 6, 'Estatus:', 0, 0);
$estatus = $asignacion['estatus'];
$colorEstatus = ($estatus == 'Activa' || $estatus == 'Activo') ? [46, 204, 113] : [128, 128, 128];
$pdf->SetTextColor($colorEstatus[0], $colorEstatus[1], $colorEstatus[2]);
$pdf->SetFont('Arial', 'B', 10); 
$pdf->Cell(55, 6, mb_convert_encoding($estatus, 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->SetTextColor(0);

// --- COLUMNA 2: PERSONAL ---
$col2_x = 110;
$pdf->ColumnHeader('PERSONAL ASIGNADO', $col2_x, $y_columns_start, $col_w);

$pdf->SetX($col2_x); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(25, 6, 'Nombre:', 0, 0);
$pdf->SetFont('Arial', '', 10); $pdf->Cell(65, 6, mb_convert_encoding($asignacion['nombres'] . ' ' . $asignacion['apellidos'], 'ISO-8859-1', 'UTF-8'), 0, 1);

$pdf->SetX($col2_x); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(25, 6, mb_convert_encoding('Cédula:', 'ISO-8859-1', 'UTF-8'), 0, 0);
$pdf->SetFont('Arial', '', 10); $pdf->Cell(65, 6, $asignacion['cedula'], 0, 1);

$pdf->SetX($col2_x); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(25, 6, mb_convert_encoding('Teléfono:', 'ISO-8859-1', 'UTF-8'), 0, 0);
$pdf->SetFont('Arial', '', 10); $pdf->Cell(65, 6, $asignacion['telefono_vigilante'] ?? 'No disponible', 0, 1);

// === SECCIÓN DE FIRMAS ===
$pdf->SetY(220); // Posición fija abajo

$pdf->SetDrawColor(100);
$pdf->Line(20, 240, 90, 240); // Línea Firma 1
$pdf->Line(120, 240, 190, 240); // Línea Firma 2

$pdf->SetXY(20, 242);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(70, 5, 'VIGILANTE', 0, 1, 'C');
$pdf->SetX(20);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(70, 5, mb_convert_encoding($asignacion['nombres'] . ' ' . $asignacion['apellidos'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$pdf->SetX(20);
$pdf->Cell(70, 5, 'C.I.: ' . $asignacion['cedula'], 0, 1, 'C');

$pdf->SetXY(120, 242);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(70, 5, 'SUPERVISOR / AUTORIZADO', 0, 1, 'C');
$pdf->SetX(120);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(70, 5, 'POR LA EMPRESA', 0, 1, 'C');

$pdf->Output('I', 'Ficha_Asignacion_' . $asignacion['id_asignacion'] . '.pdf');
?>
