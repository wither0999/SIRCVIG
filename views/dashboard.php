<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

// Obtener estadísticas
$modeloVigilante = new Vigilante();
$modeloAsignacion = new Asignacion();
$modeloPuesto = new Puesto();

$estadisticas_vigilantes = $modeloVigilante->obtenerEstadisticas();
$estadisticas_asignaciones = $modeloAsignacion->obtenerEstadisticas();
$estadisticas_puestos = $modeloPuesto->obtenerEstadisticas();

$total_vigilantes = $estadisticas_vigilantes['total'] ?? 0;
$vigilantes_activos = $estadisticas_vigilantes['activos'] ?? 0;
$puestos_cubiertos = $estadisticas_asignaciones['activas'] ?? 0;
$total_puestos = $estadisticas_puestos['total'] ?? 0;

// Obtener puestos activos con sus vigilantes actuales
$puestos_con_vigilante = $modeloAsignacion->obtenerPuestosConVigilanteActual();

// Obtener vigilantes activos para el resumen
$vigilantes_activos_lista = $modeloVigilante->obtenerPorEstatus('Activo');

$titulo = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 20px;"><i class="fas fa-building-shield"></i> Puestos de Guardia Activos</h2>
    
    <?php if (empty($puestos_con_vigilante)): ?>
        <p>No hay puestos de guardia activos.</p>
    <?php else: ?>
        <div class="puestos-grid">
            <?php foreach ($puestos_con_vigilante as $puesto): ?>
                <div class="puesto-card" style="position: relative;">
                    <div class="puesto-header">
                        <h3 style="flex: 1; padding-right: 15px;"><?php echo htmlspecialchars($puesto['nombre_cliente']); ?></h3>
                        <span class="badge badge-success">Activo</span>
                    </div>
                    
                    <div class="puesto-info">
                        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($puesto['direccion']); ?></p>
                        
                        <div class="vigilante-actual <?php echo !$puesto['vigilante_nombres'] ? 'sin-vigilante' : ''; ?>">
                            <small>Vigilante Actual:</small>
                            <?php if ($puesto['vigilante_nombres']): ?>
                                <p style="margin: 5px 0 0 0; color: #333; font-weight: 500; font-size: 0.95rem;"><?php echo htmlspecialchars($puesto['vigilante_nombres'] . ' ' . $puesto['vigilante_apellidos']); ?></p>
                                <p style="margin: 2px 0 0 0; color: #777; font-size: 0.85rem;">Cédula: <?php echo htmlspecialchars($puesto['vigilante_cedula']); ?></p>
                            <?php else: ?>
                                <p style="margin: 5px 0 0 0; color: #e74c3c; font-weight: 500;">Sin vigilante asignado</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2 style="margin-bottom: 20px;"><i class="fas fa-users"></i> Resumen de Vigilantes Activos</h2>
    
    <?php if (empty($vigilantes_activos_lista)): ?>
        <p>No hay vigilantes activos.</p>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vigilantes_activos_lista as $vigilante): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vigilante['cedula']); ?></td>
                            <td><?php echo htmlspecialchars($vigilante['nombres']); ?></td>
                            <td><?php echo htmlspecialchars($vigilante['apellidos']); ?></td>
                            <td><?php echo htmlspecialchars($vigilante['telefono'] ?? ''); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/views/ficha_personal.php?cedula=<?php echo $vigilante['cedula']; ?>" class="btn btn-info" style="background-color: #5DADE2; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px; font-size: 0.85rem;">
                                    <i class="fas fa-id-card"></i> Ver Ficha
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
