<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

if (!ControladorPrincipal::esAdministrador() && !ControladorPrincipal::esSupervisor()) {
    $_SESSION['error'] = 'No tiene permisos para ver asignaciones.';
    header('Location: ' . BASE_URL . '/views/dashboard.php');
    exit();
}

$modeloAsignacion = new Asignacion();
$modeloCliente = new Cliente();

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$id_cliente = $_GET['id_cliente'] ?? '';

$cronograma = $modeloAsignacion->obtenerCronograma($fecha_inicio, $fecha_fin, $id_cliente);
$clientes = $modeloCliente->obtenerActivos();

$titulo = 'Cronograma de Guardia';
require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
    <h1 style="margin-bottom: 0;"><i class="fas fa-calendar-alt"></i> Cronograma de Guardia</h1>

    <div style="display: flex; flex-direction: column; gap: 10px; min-width: 200px;">
        <a href="<?php echo BASE_URL; ?>/views/asignar_puesto.php" class="btn btn-primary" style="display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 500;">
            <i class="fas fa-plus-circle"></i> Nueva Asignación
        </a>
        <a href="<?php echo BASE_URL; ?>/views/acciones.php?modulo=asignacion&accion=generar_pdf&fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>&id_cliente=<?php echo $id_cliente; ?>" 
           target="_blank"
           class="btn btn-success" style="display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 500;">
           <i class="fas fa-file-pdf"></i> Exportar a PDF
        </a>
    </div>
</div>

<!-- Filtro de fechas -->
<div class="form-container mb-3" style="max-width: 600px;">
    <form method="GET" action="<?php echo BASE_URL; ?>/views/cronograma_guardia.php" style="max-width: 600px;">
        <div class="form-row">
            <div class="form-group">
                <label for="fecha_inicio">Fecha Inicio</label>
                <?php $max_date = date('Y-m-d', strtotime('+1 day')); ?>
                <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" max="<?php echo $max_date; ?>">
            </div>
            
            <div class="form-group">
                <label for="fecha_fin">Fecha Fin</label>
                <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>" max="<?php echo $max_date; ?>">
            </div>
            
            <div class="form-group">
                <label for="id_cliente">Cliente</label>
                <select id="id_cliente" name="id_cliente" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?php echo $c['id_cliente']; ?>" <?php echo ($id_cliente == $c['id_cliente']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['nombre_cliente']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Vigilante</th>
                <th>Cédula</th>
                <th>Puesto</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Rol de Guardia</th>
                <th>Estatus</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($cronograma)): ?>
                <tr>
                    <td colspan="8" class="text-center">No hay asignaciones para el período seleccionado</td>
                </tr>
            <?php else: ?>
                <?php foreach ($cronograma as $asignacion): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($asignacion['nombres'] . ' ' . $asignacion['apellidos']); ?></td>
                        <td><?php echo htmlspecialchars($asignacion['cedula']); ?></td>
                        <td><?php echo htmlspecialchars($asignacion['nombre_cliente'] . ' - ' . ($asignacion['direccion_puesto'] ?? '')); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($asignacion['fecha_inicio'])); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($asignacion['fecha_fin'])); ?></td>
                        <td><?php echo htmlspecialchars($asignacion['rol_guardia']); ?></td>
                        <td>
                            <?php 
                            $bgColor = '#95a5a6'; // Gris
                            switch($asignacion['estatus']) {
                                case 'Activa': $bgColor = '#27ae60'; break; // Verde
                                case 'Completada': $bgColor = '#3498db'; break; // Azul
                                case 'Cancelada': $bgColor = '#f39c12'; break; // Naranja
                                case 'Eliminada': $bgColor = '#e74c3c'; break; // Rojo
                            }
                            ?>
                            <span style="background-color: <?php echo $bgColor; ?>; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.85rem; font-weight: 600; display: inline-block; text-align: center; min-width: 80px;">
                                <?php echo $asignacion['estatus']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>/views/ficha_asignacion.php?id=<?php echo $asignacion['id_asignacion']; ?>" class="btn btn-sm btn-info" style="background-color: #3498db; border-color: #3498db; color: white; margin-right: 5px;" title="Ver Ficha">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($asignacion['estatus'] === 'Activa' || $asignacion['estatus'] === 'Completada' || $asignacion['estatus'] === 'Cancelada'): ?>
                                <?php if ($asignacion['estatus'] === 'Activa'): ?>
                                <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=asignacion&accion=anular" method="POST" onsubmit="return confirm('¿Está seguro de anular esta asignación? Quedará en su historial de canceladas.');" style="display:inline;">
                                    <input type="hidden" name="id_asignacion" value="<?php echo $asignacion['id_asignacion']; ?>">
                                    <button type="submit" class="btn btn-sm btn-warning" title="Anular Turno"><i class="fas fa-ban"></i></button>
                                </form>
                                <?php endif; ?>
                                
                                <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=asignacion&accion=eliminar" method="POST" onsubmit="return confirm('ATENCIÓN: ¿Está seguro de ELIMINAR PERMANENTEMENTE esta asignación? Se borrará de toda la base de datos.');" style="display:inline;">
                                    <input type="hidden" name="id_asignacion" value="<?php echo $asignacion['id_asignacion']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Definitivamente"><i class="fas fa-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

