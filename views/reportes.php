<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

// Verificar sesión
ControladorPrincipal::verificarSesion();

// Verificar permisos
$id_rol = $_SESSION['id_rol'] ?? 0;
if (!($id_rol == ROL_ADMINISTRADOR || $id_rol == ROL_SECRETARIO || $id_rol == ROL_SUPERVISOR)) {
    $_SESSION['error'] = 'No tiene permisos para acceder a esta sección.';
    header('Location: ' . BASE_URL . '/views/dashboard.php');
    exit();
}

$modulo = $_GET['modulo'] ?? '';
$estatus = $_GET['estatus'] ?? 'Todos';
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

$datos = [];
$columnas = [];
$titulo_reporte = "Reporte General";

if ($modulo) {
    // Formatear periodo para el título
    $periodo_str = " (Del " . date('d/m/Y', strtotime($fecha_inicio)) . " al " . date('d/m/Y', strtotime($fecha_fin)) . ")";
    
    if ($modulo == 'Vigilantes') {
        $modelo = new Vigilante();
        $condicion = ($estatus != 'Todos') ? "estatus = '$estatus'" : "1=1";
        $condicion .= " AND DATE(fecha_creacion) >= '$fecha_inicio' AND DATE(fecha_creacion) <= '$fecha_fin'";
        $datos = $modelo->obtenerTodos($condicion);
        $columnas = [
            'Cédula' => 'cedula', 
            'Nombre Completo' => function($row) { return $row['nombres'] . ' ' . $row['apellidos']; }, 
            'Teléfono' => 'telefono', 
            'Estatus' => 'estatus',
            'Fecha Registro' => function($row) { return date('d/m/Y', strtotime($row['fecha_creacion'])); }
        ];
        $titulo_reporte = "Reporte de Vigilantes" . ($estatus != 'Todos' ? " ($estatus)" : "") . $periodo_str;
    } 
    elseif ($modulo == 'Clientes') {
        $modelo = new Cliente();
        $condicion = ($estatus != 'Todos') ? "estatus = '$estatus'" : "1=1";
        $condicion .= " AND DATE(fecha_creacion) >= '$fecha_inicio' AND DATE(fecha_creacion) <= '$fecha_fin'";
        $datos = $modelo->obtenerTodos($condicion);
        $columnas = [
            'RIF / C.I.' => 'rif_cedula', 
            'Razón Social' => 'nombre_cliente', 
            'Teléfono' => 'telefono', 
            'Estatus' => 'estatus',
            'Fecha Registro' => function($row) { return date('d/m/Y', strtotime($row['fecha_creacion'])); }
        ];
        $titulo_reporte = "Reporte de Clientes" . ($estatus != 'Todos' ? " ($estatus)" : "") . $periodo_str;
    } 
    elseif ($modulo == 'Puestos') {
        $modelo = new Puesto();
        $condicion = ($estatus != 'Todos') ? "estatus = '$estatus'" : "1=1";
        $condicion .= " AND DATE(fecha_creacion) >= '$fecha_inicio' AND DATE(fecha_creacion) <= '$fecha_fin'";
        $datos = $modelo->obtenerTodos($condicion);
        $columnas = [
            'Cliente' => 'nombre_cliente', 
            'Dirección' => 'direccion', 
            'Contacto' => 'contacto', 
            'Estatus' => 'estatus',
            'Fecha Registro' => function($row) { return date('d/m/Y', strtotime($row['fecha_creacion'])); }
        ];
        $titulo_reporte = "Reporte de Puestos" . ($estatus != 'Todos' ? " ($estatus)" : "") . $periodo_str;
    }
    elseif ($modulo == 'Asignaciones') {
        $modelo = new Asignacion();
        $datos_raw = $modelo->obtenerCronograma(); 
        $datos = [];
        foreach ($datos_raw as $item) {
            $fecha_creacion_item = date('Y-m-d', strtotime($item['fecha_creacion']));
            if ($fecha_creacion_item >= $fecha_inicio && $fecha_creacion_item <= $fecha_fin) {
                if ($estatus == 'Todos' || $item['estatus'] == $estatus) {
                    $datos[] = $item;
                }
            }
        }
        $columnas = [
            'Vigilante' => function($row) { return $row['nombres'] . ' ' . $row['apellidos']; }, 
            'Puesto' => 'nombre_cliente', 
            'Inicio' => function($row) { return date('d/m/Y H:i', strtotime($row['fecha_inicio'])); }, 
            'Fin' => function($row) { return date('d/m/Y H:i', strtotime($row['fecha_fin'])); }, 
            'Estatus' => 'estatus',
            'F. Registro' => function($row) { return date('d/m/Y', strtotime($row['fecha_creacion'])); }
        ];
        $titulo_reporte = "Reporte de Asignaciones" . ($estatus != 'Todos' ? " ($estatus)" : "") . $periodo_str;
    }
    elseif ($modulo == 'Usuarios') {
        $modelo = new Usuario();
        $datos_raw = $modelo->obtenerUsuariosConRoles();
        $datos = [];
        foreach ($datos_raw as $item) {
            $fecha_creacion_item = date('Y-m-d', strtotime($item['fecha_creacion']));
            if ($fecha_creacion_item >= $fecha_inicio && $fecha_creacion_item <= $fecha_fin) {
                if ($estatus == 'Todos' || $item['estado_cuenta'] == $estatus) {
                    $datos[] = $item;
                }
            }
        }
        $columnas = [
            'Usuario' => 'nombre_usuario', 
            'Rol' => 'nombre_rol', 
            'Nombre Completo' => function($row) { return $row['nombres'] . ' ' . $row['apellidos']; }, 
            'Estado' => 'estado_cuenta',
            'F. Registro' => function($row) { return date('d/m/Y', strtotime($row['fecha_creacion'])); }
        ];
        $titulo_reporte = "Reporte de Usuarios" . ($estatus != 'Todos' ? " ($estatus)" : "") . $periodo_str;
    }
}

$titulo = "Generación de Reportes";
require_once __DIR__ . '/includes/header.php';
?>

<div class="content-wrapper">
    <!-- Panel Controlador para Generar Reporte -->
    <div class="card mb-4 no-print">
        <div class="card-header bg-light" style="display: flex; justify-content: space-between; align-items: center;">
            <h4 class="mb-0"><i class="fas fa-filter"></i> Generador de Reportes</h4>
            <?php if ($modulo && count($datos) > 0): ?>
            <button onclick="window.print()" class="btn btn-primary" style="background-color: #2c3e50; border: none;">
                <i class="fas fa-print"></i> Imprimir Este Reporte
            </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="GET" action="reportes.php" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 1; min-width: 180px;">
                    <label for="modulo" style="font-weight: bold; display: block; margin-bottom: 5px;">Módulo a Consultar:</label>
                    <select name="modulo" id="modulo" class="form-control" onchange="actualizarEstatus()" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">-- Seleccione un módulo --</option>
                        <option value="Vigilantes" <?php echo ($modulo == 'Vigilantes') ? 'selected' : ''; ?>>Vigilantes</option>
                        <option value="Clientes" <?php echo ($modulo == 'Clientes') ? 'selected' : ''; ?>>Clientes</option>
                        <option value="Puestos" <?php echo ($modulo == 'Puestos') ? 'selected' : ''; ?>>Puestos de Guardia</option>
                        <option value="Asignaciones" <?php echo ($modulo == 'Asignaciones') ? 'selected' : ''; ?>>Asignaciones</option>
                        <?php if ($id_rol == ROL_ADMINISTRADOR): ?>
                        <option value="Usuarios" <?php echo ($modulo == 'Usuarios') ? 'selected' : ''; ?>>Usuarios del Sistema</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div style="flex: 1; min-width: 150px;">
                    <label for="estatus" style="font-weight: bold; display: block; margin-bottom: 5px;">Estatus:</label>
                    <select name="estatus" id="estatus" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="Todos" <?php echo ($estatus == 'Todos') ? 'selected' : ''; ?>>Todos</option>
                        <!-- Options generated by JS -->
                    </select>
                </div>
                
                <div style="flex: 1; min-width: 130px;">
                    <label for="fecha_inicio" style="font-weight: bold; display: block; margin-bottom: 5px;">Desde:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="flex: 1; min-width: 130px;">
                    <label for="fecha_fin" style="font-weight: bold; display: block; margin-bottom: 5px;">Hasta:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                
                <div>
                    <button type="submit" class="btn btn-success" style="padding: 9px 20px;">
                        <i class="fas fa-search"></i> Generar
                    </button>
                    <?php if ($modulo): ?>
                    <a href="reportes.php" class="btn btn-secondary" style="padding: 9px 20px; background-color: #95a5a6; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Contenedor del Repote (Membrete y Contenido a Imprimir) -->
    <div class="card reporte-container" style="background: white; border: 1px solid #ddd; padding: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-radius: 8px;">
        
        <!-- MEMBRETE -->
        <div class="membrete" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #1a252f; padding-bottom: 20px; margin-bottom: 30px;">
            <div style="width: 25%; text-align: left;">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo_corporacion.png" alt="Logo de la Empresa" style="max-width: 140px; height: auto;">
            </div>
            <div style="width: 75%; text-align: right; color: #2c3e50;">
                <h2 style="margin: 0 0 10px 0; font-size: 1.4rem; text-transform: uppercase; font-weight: bold;">Corporación J&J Siglo XXI</h2>
                <div style="font-size: 0.9rem; line-height: 1.6;">
                    <p style="margin: 0;"><strong>RIF:</strong> J-00000000-0</p>
                    <p style="margin: 0;"><strong>Dirección:</strong> Sector Pueblo Nuevo Sur El Tigre, Municipio Simón Rodríguez,</p>
                    <p style="margin: 0;">Estado Anzoátegui, en las inmediaciones de la 9na Carrera Sur y la Calle 27 Sur.</p>
                    <p style="margin: 0;"><strong>Teléfono:</strong> 0424-8438299</p>
                    <p style="margin: 0;"><strong>Correo:</strong> CorporacionSiglo21JJ@gmail.com</p>
                </div>
            </div>
        </div>

        <!-- CONTENIDO DEL REPORTE -->
        <div class="contenido-reporte">
            <h3 style="text-align: center; margin-bottom: 25px; text-transform: uppercase; color: #34495e; letter-spacing: 1px;">
                <?php echo htmlspecialchars($titulo_reporte); ?>
            </h3>
            
            <?php if (!$modulo): ?>
                <div style="text-align: center; padding: 40px; color: #7f8c8d; background-color: #f8f9fa; border-radius: 5px;">
                    <i class="fas fa-file-alt fa-3x" style="margin-bottom: 15px; color: #bdc3c7;"></i>
                    <p>Seleccione un módulo y estatus en el panel superior para generar un reporte.</p>
                </div>
            <?php elseif (empty($datos)): ?>
                <div style="text-align: center; padding: 40px; color: #e74c3c; background-color: #fdf2f2; border-radius: 5px; border: 1px solid #fadbd8;">
                    <i class="fas fa-exclamation-triangle fa-2x" style="margin-bottom: 15px;"></i>
                    <p>No se encontraron registros para la combinación seleccionada.</p>
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 40px;" border="1">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: center; width: 40px;">N°</th>
                            <?php foreach ($columnas as $titulo_col => $llave): ?>
                                <th style="padding: 10px; border: 1px solid #ddd; text-align: left; color: #2c3e50; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($titulo_col); ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $contador = 1; foreach ($datos as $fila): ?>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd; text-align: center; font-size: 0.9rem;">
                                <?php echo $contador++; ?>
                            </td>
                            <?php foreach ($columnas as $llave): ?>
                                <td style="padding: 8px; border: 1px solid #ddd; font-size: 0.9rem; color: #333;">
                                    <?php 
                                        if (is_callable($llave)) {
                                            echo htmlspecialchars($llave($fila));
                                        } else {
                                            echo htmlspecialchars($fila[$llave] ?? '-');
                                        }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p style="text-align: right; color: #7f8c8d; font-size: 0.85rem; margin-top: -20px; margin-bottom: 30px;">
                    Total de registros: <strong><?php echo count($datos); ?></strong>
                </p>
            <?php endif; ?>

            <div style="margin-top: 60px; text-align: center;">
                <p style="margin-bottom: 5px;">_________________________________________</p>
                <p style="font-weight: bold; margin: 0; color: #2c3e50;">Firma de Conformidad</p>
                <p style="font-size: 0.85rem; color: #7f8c8d; margin-top: 2px;">Dirección / Gerencia General</p>
                <p style="font-size: 0.75rem; color: #bdc3c7; margin-top: 15px;">Generado el <?php echo date('d/m/Y H:i'); ?></p>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para manejar la impresión adecuadamente */
    @media print {
        body { background-color: white !important; margin: 0; padding: 0; }
        .sidebar, .top-bar, .no-print, footer, .page-header { display: none !important; }
        .main-container { display: block !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
        .reporte-container { border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; }
        table { page-break-inside:auto; width: 100% !important; }
        tr { page-break-inside:avoid; page-break-after:auto; }
        thead { display:table-header-group; }
        tfoot { display:table-footer-group; }
    }
</style>

<script>
    const estatusPorModulo = {
        'Vigilantes': ['Todos', 'Activo', 'Inactivo', 'Aspirante'],
        'Clientes': ['Todos', 'Activo', 'Inactivo'],
        'Puestos': ['Todos', 'Activo', 'Inactivo'],
        'Asignaciones': ['Todos', 'Activa', 'Completada', 'Cancelada'],
        'Usuarios': ['Todos', 'Activa', 'Bloqueada']
    };

    const estatusSeleccionado = '<?php echo $estatus; ?>';

    function actualizarEstatus() {
        const moduloSelect = document.getElementById('modulo');
        const estatusSelect = document.getElementById('estatus');
        const moduloSeleccionado = moduloSelect.value;
        
        estatusSelect.innerHTML = '';
        
        if (moduloSeleccionado && estatusPorModulo[moduloSeleccionado]) {
            const opciones = estatusPorModulo[moduloSeleccionado];
            opciones.forEach(function(opcion) {
                const opt = document.createElement('option');
                opt.value = opcion;
                opt.textContent = opcion;
                if (opcion === estatusSeleccionado) {
                    opt.selected = true;
                }
                estatusSelect.appendChild(opt);
            });
        } else {
            const opt = document.createElement('option');
            opt.value = 'Todos';
            opt.textContent = 'Todos';
            estatusSelect.appendChild(opt);
        }
    }

    // Inicializar al cargar
    document.addEventListener('DOMContentLoaded', actualizarEstatus);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
