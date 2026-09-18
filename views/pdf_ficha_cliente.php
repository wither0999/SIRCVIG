<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';
require_once __DIR__ . '/../libs/fpdf/fpdf.php';

ControladorPrincipal::verificarSesion();

$id_cliente = $_GET['id'] ?? null;

if (!$id_cliente) {
    die('ID de cliente no especificado.');
}

$modeloCliente = new Cliente();
$cliente = $modeloCliente->obtenerPorId($id_cliente);

if (!$cliente) {
    die('Cliente no encontrado.');
}

define('FPDF_FONTPATH', __DIR__ . '/../libs/fpdf/font/');

class PDF_FichaCliente extends FPDF
{
    function Header()
    {
        // === CONFIGURACIÓN DE COLORES ===
        $headerBgColor = [44, 53, 65]; // Azul grisaceo oscuro
        $this->SetFillColor($headerBgColor[0], $headerBgColor[1], $headerBgColor[2]);
        $this->Rect(0, 0, 210, 40, 'F'); // Altura 40mm

        // Logo (Más pequeño)
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

        // Título Documento (Derecha)
        $this->SetFont('Arial', 'B', 16);
        $this->SetXY(0, 15);
        $this->Cell(200, 10, mb_convert_encoding('FICHA DE CLIENTE', 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        
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
        $this->SetTextColor(44, 53, 65); // Color oscuro
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

$pdf = new PDF_FichaCliente();
$pdf->AliasNbPages();
$pdf->AddPage();

// === CONTENIDO ===

// 1. Datos de Identificación (Bloque Principal)
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(44, 53, 65);
$pdf->Cell(0, 8, mb_convert_encoding('CLIENTE', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
$pdf->SetDrawColor(180, 180, 180);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0);
$pdf->Cell(0, 8, mb_convert_encoding($cliente['nombre_cliente'], 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(80);
$pdf->Cell(25, 6, 'RIF / C.I.:', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0);
$pdf->Cell(50, 6, $cliente['rif_cedula'], 0, 0);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(80);
$pdf->Cell(25, 6, 'Estatus:', 0, 0);

$estatus = $cliente['estatus'];
$colorEstatus = ($estatus == 'Activo') ? [46, 204, 113] : [128, 128, 128];
$pdf->SetTextColor($colorEstatus[0], $colorEstatus[1], $colorEstatus[2]);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 6, mb_convert_encoding($estatus, 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->SetTextColor(0);

$pdf->Ln(10);

// 2. Información Detallada (Dos Columnas)
$y_columns_start = $pdf->GetY();

// --- COLUMNA 1: CONTACTO ---
$col1_x = 10;
$col_w = 90;
$pdf->ColumnHeader('CONTACTO', $col1_x, $y_columns_start, $col_w);

$pdf->SetX($col1_x); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(25, 6, mb_convert_encoding('Teléfono:', 'ISO-8859-1', 'UTF-8'), 0, 0);
$pdf->SetFont('Arial', '', 10); $pdf->Cell(60, 6, $cliente['telefono'] ?? 'No registrado', 0, 1);

$pdf->SetX($col1_x); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(25, 6, mb_convert_encoding('Correo:', 'ISO-8859-1', 'UTF-8'), 0, 0);
$pdf->SetFont('Arial', '', 10); $pdf->Cell(60, 6, $cliente['email'] ?? 'No registrado', 0, 1);

$pdf->SetX($col1_x); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(25, 6, mb_convert_encoding('Registro:', 'ISO-8859-1', 'UTF-8'), 0, 0);
$fecha_reg = ($cliente['fecha_creacion']) ? date('d/m/Y', strtotime($cliente['fecha_creacion'])) : '-';
$pdf->SetFont('Arial', '', 10); $pdf->Cell(60, 6, $fecha_reg, 0, 1);


// --- COLUMNA 2: UBICACIÓN ---
$col2_x = 110;
$pdf->ColumnHeader('UBICACIÓN', $col2_x, $y_columns_start, $col_w);

$loc = [];
if (!empty($cliente['estado'])) $loc[] = $cliente['estado'];
if (!empty($cliente['municipio'])) $loc[] = $cliente['municipio'];
if (!empty($cliente['parroquia'])) $loc[] = $cliente['parroquia'];
$ubicacion_str = implode(' / ', $loc);

$pdf->SetX($col2_x); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(25, 6, mb_convert_encoding('Zona:', 'ISO-8859-1', 'UTF-8'), 0, 0);
$pdf->SetFont('Arial', '', 9); 
$pdf->MultiCell($col_w - 25, 6, mb_convert_encoding($ubicacion_str, 'ISO-8859-1', 'UTF-8'));

$pdf->SetX($col2_x); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(25, 6, mb_convert_encoding('Dirección:', 'ISO-8859-1', 'UTF-8'), 0, 0); // Label en línea aparte si es necesario
$pdf->Ln(6);
$pdf->SetX($col2_x);
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell($col_w, 6, mb_convert_encoding($cliente['direccion'], 'ISO-8859-1', 'UTF-8'));


$pdf->Output('I', 'Ficha_Cliente_' . $cliente['rif_cedula'] . '.pdf');
?>
