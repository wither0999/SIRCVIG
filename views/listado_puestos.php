<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

if (!ControladorPrincipal::esAdministrador() && !ControladorPrincipal::esSecretario()) {
    $_SESSION['error'] = 'No tiene permisos para ver puestos.';
    header('Location: ' . BASE_URL . '/views/dashboard.php');
    exit();
}

$modeloPuesto = new Puesto();
$puestos = $modeloPuesto->obtenerTodos();

$puede_modificar = ControladorPrincipal::esAdministrador() || ControladorPrincipal::esSecretario();

$titulo = 'Gestión de Puestos de Guardia';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Estilos estandarizados */
    .table-puestos {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table-puestos th {
        background-color: var(--color-primario);
        color: white;
        padding: 12px 15px;
        font-weight: 600;
        text-align: left;
    }
    
    .table-puestos td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    
    .table-puestos tr:hover td {
        background-color: #f8f9fa;
    }
    
    /* Columnas fijas */
    .col-status { width: 120px; text-align: center; }
    .col-actions { width: 150px; text-align: center; } /* 150px */
    
    /* Dirección con ellipsis */
    .address-cell {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #555;
    }
    
    /* Badge de Estatus */
    .col-status .badge {
        font-size: 0.75rem;
        padding: 4px 8px;
        min-width: 80px;
        display: inline-block;
        border-radius: 4px;
        font-weight: 500;
    }
    
    /* Acciones */
    .action-buttons {
        display: flex;
        gap: 8px; /* 8px */
        justify-content: center;
        align-items: center;
    }
    
    .btn-icon {
        width: 34px; height: 34px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 6px; /* 6px */
        border: none; transition: all 0.2s ease;
        text-decoration: none; font-size: 0.9rem; color: white !important;
    }
    
    .btn-view { background-color: #5d6d7e; }
    .btn-view:hover { background-color: #34495e; transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    
    .btn-edit { background-color: #f1c40f; }
    .btn-edit:hover { background-color: #d4ac0d; transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    
    .btn-delete { background-color: #e74c3c; }
    .btn-delete:hover { background-color: #c0392b; transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1 style="margin-bottom: 0;"><i class="fas fa-building-shield"></i> Gestión de Puestos de Guardia</h1>

    <?php if ($puede_modificar): ?>
    <div>
        <a href="<?php echo BASE_URL; ?>/views/formulario_puesto.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Nuevo Puesto
        </a>
    </div>
    <?php endif; ?>
</div>

<div class="table-container">
    <table class="table-puestos">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th>Contacto</th>
                <th class="col-status">Estatus</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($puestos)): ?>
                <tr>
                    <td colspan="6" class="text-center" style="padding: 2rem; color: #777;">
                        <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                        No hay puestos registrados en el sistema.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($puestos as $puesto): ?>
                    <tr>
                        <td style="font-weight: 500; color: var(--color-primario);"><?php echo htmlspecialchars($puesto['nombre_cliente']); ?></td>
                        <td>
                            <div class="address-cell" title="<?php echo htmlspecialchars($puesto['direccion']); ?>">
                                <i class="fas fa-map-marker-alt" style="color: #999; margin-right: 5px; font-size: 0.8rem;"></i>
                                <?php echo htmlspecialchars($puesto['direccion']); ?>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($puesto['telefono'])): ?>
                                <i class="fas fa-phone" style="color: #999; margin-right: 3px; font-size: 0.8rem;"></i>
                                <?php echo htmlspecialchars($puesto['telefono']); ?>
                            <?php else: ?>
                                <span style="color: #ccc;">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($puesto['contacto'] ?? '-'); ?></td>
                        <td class="col-status">
                            <?php 
                            $statusClass = $puesto['estatus'] == 'Activo' ? 'badge-success' : 'badge-danger'; 
                            $statusIcon = $puesto['estatus'] == 'Activo' ? 'fa-check-circle' : 'fa-times-circle';
                            ?>
                            <span class="badge <?php echo $statusClass; ?>">
                                <i class="fas <?php echo $statusIcon; ?>"></i> <?php echo htmlspecialchars($puesto['estatus']); ?>
                            </span>
                        </td>
                        <td class="col-actions">
                            <div class="action-buttons">
                                <a href="<?php echo BASE_URL; ?>/views/detalles_puesto.php?id=<?php echo $puesto['id_puesto']; ?>" 
                                   class="btn-icon btn-view" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($puede_modificar): ?>
                                    <a href="<?php echo BASE_URL; ?>/views/formulario_puesto.php?id=<?php echo $puesto['id_puesto']; ?>" 
                                       class="btn-icon btn-edit" title="Editar Puesto">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (ControladorPrincipal::esAdministrador()): ?>
                                    <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=puesto&accion=eliminar" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este puesto? Esta acción no se puede deshacer.');">
                                        <input type="hidden" name="id_puesto" value="<?php echo $puesto['id_puesto']; ?>">
                                        <button type="submit" class="btn-icon btn-delete" title="Eliminar Puesto">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
