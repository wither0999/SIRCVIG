<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';
require_once __DIR__ . '/../libs/fpdf/fpdf.php';

ControladorPrincipal::verificarSesion();

$id_puesto = $_GET['id'] ?? null;

if (!$id_puesto) {
    die('ID de puesto no especificado.');
}

$modeloPuesto = new Puesto();
$puesto = $modeloPuesto->obtenerPorId($id_puesto);

if (!$puesto) {
    die('Puesto no encontrado.');
}

// Obtener asignaciones activas para el reporte
$modeloAsignacion = new Asignacion();
$asignaciones = $modeloAsignacion->obtenerPorPuesto($id_puesto, true);

define('FPDF_FONTPATH', __DIR__ . '/../libs/fpdf/font/');

class PDF_FichaPuesto extends FPDF
{
    function Header()
    {
        // === CONFIGURACIÓN DE COLORES ===
        $headerBgColor = [44, 53, 65]; 
        $this->SetFillColor($headerBgColor[0], $headerBgColor[1], $headerBgColor[2]);
        $this->Rect(0, 0, 210, 40, 'F'); 

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
        $this->SetXY(0, 15);
        $this->Cell(200, 10, mb_convert_encoding('FICHA DE PUESTO', 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        
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
    
    function SectionHeader($title) {
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(44, 53, 65);
        $this->Cell(0, 8, mb_convert_encoding($title, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->SetDrawColor(180, 180, 180);
        $this->Line($this->GetX(), $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }

    // Función auxiliar para dibujar encabezado de columna
    function ColumnHeader($title, $x, $y, $w) {
        $this->SetXY($x, $y);
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(44, 53, 65);
        $this->Cell($w, 8, mb_convert_encoding($title, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        
        $this->SetDrawColor(180, 180, 180);
        $this->Line($x, $y + 8, $x + $w, $y + 8);
        $this->SetXY($x, $y + 10); // Posicionar para contenido
    }
}

$pdf = new PDF_FichaPuesto();
$pdf->AliasNbPages();
$pdf->AddPage();

// === CONTENIDO ===

// 1. Información del Puesto (Bloque Principal)
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(44, 53, 65);
$pdf->Cell(0, 8, mb_convert_encoding('INFORMACIÓN DEL PUESTO', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
$pdf->SetDrawColor(180, 180, 180);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 11); $pdf->SetTextColor(80);
$pdf->Cell(30, 6, 'Cliente:', 0, 0);
$pdf->SetFont('Arial', 'B', 12); $pdf->SetTextColor(0);
$pdf->Cell(0, 6, mb_convert_encoding($puesto['nombre_cliente'], 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->Ln(2);

// Info en columnas
$y_cols = $pdf->GetY();

// Col 1
$pdf->SetXY(10, $y_cols);
$pdf->SetFont('Arial', 'B', 10); $pdf->SetTextColor(80);
$pdf->Cell(25, 6, mb_convert_encoding('Dirección:', 'ISO-8859-1', 'UTF-8'), 0, 0);
$pdf->SetFont('Arial', '', 10); $pdf->SetTextColor(0);
// Reducir ancho de MultiCell para evitar solapamiento con Col 2 (Empieza en 110)
// 10 (Margen) + 25 (Label) = 35. 110 - 35 = 75 Max. Usamos 65 para margen seguro.
$pdf->MultiCell(65, 6, mb_convert_encoding($puesto['direccion'], 'ISO-8859-1', 'UTF-8'));
$y_end_col1 = $pdf->GetY();

// Col 2
$pdf->SetXY(110, $y_cols);
$pdf->SetFont('Arial', 'B', 10); $pdf->SetTextColor(80);
$pdf->Cell(25, 6, mb_convert_encoding('Contacto:', 'ISO-8859-1', 'UTF-8'), 0, 0);
$pdf->SetFont('Arial', '', 10); $pdf->SetTextColor(0);
// Usar Cell fijo en lugar de 0
$pdf->Cell(60, 6, mb_convert_encoding($puesto['contacto'] ?? 'No registrado', 'ISO-8859-1', 'UTF-8'), 0, 1);

$pdf->SetXY(110, $y_cols + 8);
$pdf->SetFont('Arial', 'B', 10); $pdf->SetTextColor(80);
$pdf->Cell(25, 6, mb_convert_encoding('Teléfono:', 'ISO-8859-1', 'UTF-8'), 0, 0);
$pdf->SetFont('Arial', '', 10); $pdf->SetTextColor(0);
$pdf->Cell(60, 6, $puesto['telefono'] ?? 'No registrado', 0, 1);

$pdf->SetXY(110, $y_cols + 16);
$pdf->SetFont('Arial', 'B', 10); $pdf->SetTextColor(80);
$pdf->Cell(25, 6, mb_convert_encoding('Estatus:', 'ISO-8859-1', 'UTF-8'), 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$est = $puesto['estatus'];
$colE = ($est == 'Activo') ? [46, 204, 113] : [128, 128, 128];
$pdf->SetTextColor($colE[0], $colE[1], $colE[2]);
$pdf->Cell(60, 6, mb_convert_encoding($est, 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->SetTextColor(0);

$pdf->SetY(max($y_end_col1, $y_cols + 25));
$pdf->Ln(5);


// 2. Asignaciones Activas
$pdf->SectionHeader('ASIGNACIONES ACTIVAS');

if (empty($asignaciones)) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, mb_convert_encoding('No hay personal asignado actualmente.', 'ISO-8859-1', 'UTF-8'), 0, 1);
} else {
    // Cabecera Tabla
    $pdf->SetFont('Arial', 'B', 9); // Un poco mas pequeño para caber
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetTextColor(44, 53, 65);
    $pdf->SetDrawColor(200, 200, 200);
    
    // Anchos: Vigilante(60), Cedula(25), Inicio(35), Fin(35), Rol(35)
    $w_vig = 60; $w_ced = 25; $w_ini = 35; $w_fin = 35; $w_rol = 35;
    
    $pdf->Cell($w_vig, 8, mb_convert_encoding('Vigilante', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
    $pdf->Cell($w_ced, 8, 'Cedula', 1, 0, 'C', true);
    $pdf->Cell($w_ini, 8, 'Inicio', 1, 0, 'C', true);
    $pdf->Cell($w_fin, 8, 'Fin', 1, 0, 'C', true);
    $pdf->Cell($w_rol, 8, 'Rol', 1, 1, 'C', true);
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0);
    
    foreach ($asignaciones as $asig) {
        $pdf->Cell($w_vig, 8, mb_convert_encoding($asig['nombres'] . ' ' . $asig['apellidos'], 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->Cell($w_ced, 8, $asig['cedula'], 1, 0, 'C');
        $pdf->Cell($w_ini, 8, date('d/m/Y H:i', strtotime($asig['fecha_inicio'])), 1, 0, 'C');
        $pdf->Cell($w_fin, 8, date('d/m/Y H:i', strtotime($asig['fecha_fin'])), 1, 0, 'C');
        $pdf->Cell($w_rol, 8, mb_convert_encoding($asig['rol_guardia'], 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
    }
}

$pdf->Output('I', 'Ficha_Puesto_' . $puesto['id_puesto'] . '.pdf');
?>
