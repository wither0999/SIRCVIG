<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';
require_once __DIR__ . '/../libs/fpdf/fpdf.php';
require_once __DIR__ . '/../models/Asignacion.php';
require_once __DIR__ . '/../models/Documento.php'; // Incluir modelo de documento

ControladorPrincipal::verificarSesion();

$cedula = $_GET['cedula'] ?? '';

if (empty($cedula)) {
    die('Cédula no especificada.');
}

$modeloVigilante = new Vigilante();
$vigilante = $modeloVigilante->obtenerPorCedula($cedula);

if (!$vigilante) {
    die('Vigilante no encontrado.');
}

// Obtener historial de asignaciones
$modeloAsignacion = new Asignacion();
$asignaciones = $modeloAsignacion->obtenerPorVigilante($cedula, false);

// Obtener Documentos (Foto y Cédula)
$modeloDocumento = new Documento();
$fotoDoc = $modeloDocumento->obtenerPorTipo($cedula, 'Foto');
$cedulaDoc = $modeloDocumento->obtenerPorTipo($cedula, 'CedulaEscaneada');

define('FPDF_FONTPATH', __DIR__ . '/../libs/fpdf/font/');

class PDF_CV extends FPDF
{
    function Footer() {
        // No footer needed specifically based on image, but keeping page number is good practice
        // $this->SetY(-15);
        // $this->SetFont('Arial', 'I', 8);
        // $this->SetTextColor(128);
        // $this->Cell(0, 10, mb_convert_encoding('Página ', 'ISO-8859-1', 'UTF-8') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF_CV();
$pdf->AliasNbPages();
$pdf->AddPage();

// === CONFIGURACIÓN DE COLORES ===
$headerBgColor = [44, 53, 65]; // Azul grisaceo oscuro (según imagen)
$sectionTitleColor = [80, 80, 80]; // Gris oscuro para títulos secciones
$textColor = [50, 50, 50]; // Gris texto normal
$lineColor = [180, 180, 180]; // Gris linea
$blueMarker = [52, 152, 219]; // Azul marcador

// === HEADER ===
// Fondo Header
$pdf->SetFillColor($headerBgColor[0], $headerBgColor[1], $headerBgColor[2]);
$pdf->Rect(0, 0, 210, 50, 'F'); // Altura 50mm

// Logo Sistema (Nuevo)
$logoPath = __DIR__ . '/../assets/images/logo.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 10, 10, 16);
}

// Nombre (Moved slightly right to accommodate logo)
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 22);
$pdf->SetXY(30, 12); // Moved from 15 to 30
$nombre_completo = mb_strtoupper($vigilante['nombres'] . ' ' . $vigilante['apellidos']);
$pdf->Cell(0, 10, mb_convert_encoding($nombre_completo, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

// Rol ("Vigilante")
$pdf->SetFont('Arial', '', 12);
$pdf->SetXY(30, 24); // Moved from 15 to 30
$pdf->Cell(0, 6, 'Vigilante', 0, 1, 'L');

// Info Cédula y Estatus
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(30, 32); // Moved from 15 to 30
$pdf->Cell(35, 6, 'C.I.: ' . $vigilante['cedula'], 0, 0, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(5, 6, '|', 0, 0, 'C');

$estatus = $vigilante['estatus'];
$pdf->Cell(0, 6, mb_convert_encoding('Estatus - ' . $estatus, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

// Foto (Cuadrado Blanco a la derecha, dentro del header)
$photoSize = 35; // mm (Un poco más grande para que se vea bien)
$photoX = 160;
$photoY = 7.5; // Centrado verticalmente en el header de 50mm aprox

// Dibujar marco blanco si se quiere o directo la foto
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect($photoX, $photoY, $photoSize, $photoSize, 'F');

// Intentar cargar la foto real
$fotoPath = null;
if ($fotoDoc && !empty($fotoDoc['ruta_archivo'])) {
    // La ruta en BD es relativa: /uploads/... 
    // Necesitamos ruta absoluta de sistema de archivos
    $rutaAbsoluta = __DIR__ . '/..' . $fotoDoc['ruta_archivo'];
    if (file_exists($rutaAbsoluta)) {
        $fotoPath = $rutaAbsoluta;
    }
}

if ($fotoPath) {
    // Image(file, x, y, w, h)
    $pdf->Image($fotoPath, $photoX, $photoY, $photoSize, $photoSize);
} else {
    // Placeholder si no hay foto
    $pdf->SetXY($photoX, $photoY + 12);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(200); 
    $pdf->Cell($photoSize, 6, 'FOTO', 0, 0, 'C');
}


// === LAYOUT CUERPO ===
$marginTop = 65; // Margen superior para el contenido
$col1_x = 15;
$col1_w = 60; // Columna izquierda (Contacto)

$col2_x = 80;
$col2_w = 115; // Columna derecha (Historial)

// === BARRA LATERAL (CONTACTO) ===
$cursorY = $marginTop;

// Título CONTACTO con Línea
$pdf->SetXY($col1_x, $cursorY);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor($headerBgColor[0], $headerBgColor[1], $headerBgColor[2]); // Usamos color oscuro del header para títulos o gris oscuro
$pdf->Cell($col1_w, 6, 'CONTACTO', 0, 1, 'L');

// Línea gris debajo del título
$pdf->SetDrawColor($lineColor[0], $lineColor[1], $lineColor[2]);
$pdf->Line($col1_x, $cursorY + 7, $col1_x + $col1_w, $cursorY + 7);

$cursorY += 10;

// Datos Contacto
$pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

// Teléfono
$pdf->SetXY($col1_x, $cursorY);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell($col1_w, 5, mb_convert_encoding('TELÉFONO', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
$cursorY += 4;
$pdf->SetXY($col1_x, $cursorY);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell($col1_w, 5, $vigilante['telefono'] ?? '-', 0, 1, 'L');
$cursorY += 8;

// Dirección
$pdf->SetXY($col1_x, $cursorY);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell($col1_w, 5, mb_convert_encoding('DIRECCIÓN', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
$cursorY += 4;
$pdf->SetXY($col1_x, $cursorY);
$pdf->SetFont('Arial', '', 9);

$dir_parts = [];
if (!empty($vigilante['direccion'])) $dir_parts[] = $vigilante['direccion'];
if (!empty($vigilante['parroquia'])) $dir_parts[] = $vigilante['parroquia'];
if (!empty($vigilante['municipio'])) $dir_parts[] = $vigilante['municipio'];
if (!empty($vigilante['estado'])) $dir_parts[] = $vigilante['estado'];
$full_address = !empty($dir_parts) ? implode(', ', $dir_parts) : 'No registrada';

$pdf->MultiCell($col1_w, 5, mb_convert_encoding($full_address, 'ISO-8859-1', 'UTF-8'));


// === COLUMNA PRINCIPAL (HISTORIAL) ===
$cursorY2 = $marginTop;

// Título HISTORIAL con Línea
$pdf->SetXY($col2_x, $cursorY2);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor($headerBgColor[0], $headerBgColor[1], $headerBgColor[2]);
$pdf->Cell($col2_w, 6, 'HISTORIAL DE ASIGNACIONES', 0, 1, 'L');

// Línea gris
$pdf->SetDrawColor($lineColor[0], $lineColor[1], $lineColor[2]);
$pdf->Line($col2_x, $cursorY2 + 7, $col2_x + $col2_w, $cursorY2 + 7);

$cursorY2 += 10;

if (empty($asignaciones)) {
    $pdf->SetXY($col2_x, $cursorY2);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
    $pdf->Cell(0, 8, mb_convert_encoding('Sin asignaciones registradas.', 'ISO-8859-1', 'UTF-8'), 0, 1);
} else {
    foreach ($asignaciones as $asig) {
        if ($cursorY2 > 250) {
            $pdf->AddPage();
            $cursorY2 = 40;
        }

        // Marcador Azul (Cuadradito)
        $pdf->SetFillColor($blueMarker[0], $blueMarker[1], $blueMarker[2]);
        $pdf->Rect($col2_x - 4, $cursorY2 + 2, 2, 2, 'F');

        // Nombre del Cliente
        $pdf->SetXY($col2_x, $cursorY2);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(0);
        $cliente = $asig['nombre_cliente'] ?? 'Cliente Desconocido';
        $pdf->Cell(70, 6, mb_convert_encoding($cliente, 'ISO-8859-1', 'UTF-8'), 0, 0);

        // Fechas (Abonadas a la derecha)
        $fecha_inicio = date('d/m/Y', strtotime($asig['fecha_inicio']));
        $fecha_fin = date('d/m/Y', strtotime($asig['fecha_fin']));
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(150); // Gris claro
        $pdf->Cell(45, 6, "$fecha_inicio - $fecha_fin", 0, 1, 'R');

        $cursorY2 += 6;

        // Rol / Detalle (Ej. "24x48 (Diurno)")
        // Usaremos el Rol y el tipo si lo tuviéramos, o solo Rol
        $rol = $asig['rol_guardia'] ?? 'Vigilante';
        
        $pdf->SetXY($col2_x, $cursorY2);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(100); // Gris medio
        $pdf->Cell(0, 5, mb_convert_encoding($rol, 'ISO-8859-1', 'UTF-8'), 0, 1);
        $cursorY2 += 5;

        // Estatus (Verde si activa)
        $estatus_asig = $asig['estatus'];
        $pdf->SetXY($col2_x, $cursorY2);
        $pdf->SetFont('Arial', 'B', 9);
        
        $colorEstatus = ($estatus_asig == 'Activa' || $estatus_asig == 'Activo') ? [46, 204, 113] : [128, 128, 128]; // Verde o Gris
        $pdf->SetTextColor($colorEstatus[0], $colorEstatus[1], $colorEstatus[2]);
        
        $pdf->Cell(0, 5, mb_convert_encoding("Estatus: " . $estatus_asig, 'ISO-8859-1', 'UTF-8'), 0, 1);
        
        $cursorY2 += 8; // Espacio entre items
    }
}


// === PÁGINA ANEXO: CÉDULA AMPLIADA (ID CARD) ===
$pdf->AddPage();

// Título Página
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor($headerBgColor[0], $headerBgColor[1], $headerBgColor[2]);
$pdf->Cell(0, 10, mb_convert_encoding('DOCUMENTO DE IDENTIDAD', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$pdf->Ln(20);

// ID Card lógica
$w_card = 160;
$h_card = 100;
$x_card = (210 - $w_card) / 2;
$y_card = $pdf->GetY();

$cedulaPath = null;
if ($cedulaDoc && !empty($cedulaDoc['ruta_archivo'])) {
    $rutaAbsolutaC = __DIR__ . '/..' . $cedulaDoc['ruta_archivo'];
    if (file_exists($rutaAbsolutaC)) {
        $cedulaPath = $rutaAbsolutaC;
    }
}

if ($cedulaPath) {
    // Si hay imagen real, mostrarla limpia y centrada
    // Calcular dimensiones proporcionales (ancho fijo, alto auto)
    $imgW = 150; 
    $x_img = (210 - $imgW) / 2;
    
    $ext = strtolower(pathinfo($cedulaPath, PATHINFO_EXTENSION));
    
    if ($ext === 'pdf') {
         $pdf->SetFont('Arial', 'I', 10);
         $pdf->Cell(0, 10, 'El documento está en formato PDF. Ver archivo adjunto original.', 0, 0, 'C');
    } else {
        $pdf->Image($cedulaPath, $x_img, $y_card, $imgW);
    }
} else {
    // Si NO hay imagen, mostrar diseño simulado (Placeholder)
    $pdf->SetDrawColor(100);
    $pdf->Rect($x_card, $y_card, $w_card, $h_card);
    $pdf->SetFillColor(250, 250, 255);
    $pdf->Rect($x_card, $y_card, $w_card, $h_card, 'F');

    $pdf->SetXY($x_card, $y_card + 5);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(0);
    $pdf->Cell($w_card, 10, mb_convert_encoding('REPÚBLICA BOLIVARIANA DE VENEZUELA', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

    $pdf->SetXY($x_card, $y_card + 12);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($w_card, 10, mb_convert_encoding('CÉDULA DE IDENTIDAD', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

    // Foto ID Placeholder
    $pdf->Rect($x_card + 10, $y_card + 35, 35, 45);
    $pdf->SetXY($x_card + 10, $y_card + 50);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(35, 10, 'FOTO', 0, 0, 'C');

    // Datos ID
    $data_x = $x_card + 55;
    $data_y = $y_card + 40;

    $pdf->SetXY($data_x, $data_y);
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->Cell(0, 10, 'V-' . $vigilante['cedula'], 0, 1, 'L');

    $data_y += 15;
    $pdf->SetXY($data_x, $data_y);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, 'APELLIDOS:', 0, 1, 'L');
    $pdf->SetXY($data_x, $data_y + 6);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 10, mb_convert_encoding(mb_strtoupper($vigilante['apellidos']), 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

    $data_y += 20;
    $pdf->SetXY($data_x, $data_y);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, 'NOMBRES:', 0, 1, 'L');
    $pdf->SetXY($data_x, $data_y + 6);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 10, mb_convert_encoding(mb_strtoupper($vigilante['nombres']), 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

    $pdf->SetXY($x_card, $y_card + $h_card - 15);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell($w_card, 10, 'FIRMA DEL TITULAR', 0, 0, 'C');
}

$pdf->Output('I', 'CV_Vigilante_' . $vigilante['cedula'] . '.pdf');
?>
