<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

// Obtener datos
$modeloVigilante = new Vigilante();
$modeloDocumento = new Documento();

$termino_busqueda = $_GET['buscar'] ?? '';

if (!empty($termino_busqueda)) {
    $vigilantes = $modeloVigilante->buscar($termino_busqueda);
} else {
    $vigilantes = $modeloVigilante->obtenerTodos();
}

$puede_modificar = ControladorPrincipal::esAdministrador() || ControladorPrincipal::esSecretario();

$titulo = 'Listado de Vigilantes';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Estilos estandarizados */
    .table-custom {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table-custom th {
        background-color: var(--color-primario);
        color: white;
        padding: 12px 15px;
        font-weight: 600;
        text-align: left;
    }
    
    .table-custom td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    
    .table-custom tr:hover td {
        background-color: #f8f9fa;
    }
    
    /* Columnas fijas */
    .col-status { width: 120px; text-align: center; }
    .col-actions { width: 150px; text-align: center; } /* 150px */
    
    /* Badge de Estatus */
    .col-status .badge {
        font-size: 0.75rem;
        padding: 4px 8px;
        min-width: 80px;
        display: inline-block;
        border-radius: 4px;
        font-weight: 500;
    }
    
    /* Contenedor de Acciones */
    .action-buttons {
        display: flex;
        gap: 8px; /* 8px */
        justify-content: center;
        align-items: center;
    }
    
    /* Botones de Icono */
    .btn-icon {
        width: 34px; height: 34px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 6px; /* 6px */
        border: none; transition: all 0.2s ease;
        text-decoration: none; font-size: 0.9rem; color: white !important;
    }
    
    .btn-view { background-color: #5d6d7e; } /* Azul suave */
    .btn-view:hover { background-color: #34495e; transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    
    .btn-edit { background-color: #f1c40f; } /* Amarillo suave */
    .btn-edit:hover { background-color: #d4ac0d; transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    
    .btn-delete { background-color: #e74c3c; } /* Rojo suave */
    .btn-delete:hover { background-color: #c0392b; transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    
    /* Search Box */
    .search-input {
        flex: 1;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1 style="margin-bottom: 0;"><i class="fas fa-user-shield"></i> Listado de Vigilantes</h1>

    <?php if ($puede_modificar): ?>
    <div>
        <a href="<?php echo BASE_URL; ?>/views/registro_vigilante.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Nuevo Vigilante
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Búsqueda -->
<div class="search-box mb-3">
    <form method="GET" action="<?php echo BASE_URL; ?>/views/listado_vigilantes.php" style="display: flex; gap: 10px; width: 100%;">
        <input type="text" name="buscar" class="search-input" placeholder="Buscar por cédula, nombres o apellidos..." 
               value="<?php echo htmlspecialchars($termino_busqueda ?? ''); ?>">
        <button type="submit" class="btn btn-primary" style="width: auto;"><i class="fas fa-search"></i> Buscar</button>
        <?php if (!empty($termino_busqueda)): ?>
            <a href="<?php echo BASE_URL; ?>/views/listado_vigilantes.php" class="btn btn-secondary" style="width: auto;">Limpiar</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-container">
    <table class="table-custom">
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Nombres</th>
                <th>Apellidos</th>
                <th>Teléfono</th>
                <th class="col-status">Estatus</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($vigilantes)): ?>
                <tr>
                    <td colspan="6" class="text-center" style="padding: 2rem; color: #777;">
                        <i class="fas fa-search fa-2x mb-2"></i><br>
                        No se encontraron vigilantes registrados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($vigilantes as $vigilante): ?>
                    <tr>
                        <td style="font-weight: 500; color: var(--color-primario);"><?php echo htmlspecialchars($vigilante['cedula']); ?></td>
                        <td><?php echo htmlspecialchars($vigilante['nombres']); ?></td>
                        <td><?php echo htmlspecialchars($vigilante['apellidos']); ?></td>
                        <td>
                            <?php if (!empty($vigilante['telefono'])): ?>
                                <i class="fas fa-phone" style="color: #999; margin-right: 3px; font-size: 0.8rem;"></i>
                                <?php echo htmlspecialchars($vigilante['telefono']); ?>
                            <?php else: ?>
                                <span style="color: #ccc;">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="col-status">
                            <span class="badge 
                                <?php 
                                echo ($vigilante['estatus'] == 'Activo') ? 'badge-success' : 
                                     (($vigilante['estatus'] == 'Aspirante') ? 'badge-warning' : 'badge-danger'); 
                                ?>">
                                <?php echo $vigilante['estatus']; ?>
                            </span>
                        </td>
                        <td class="col-actions">
                            <div class="action-buttons">
                                <a href="<?php echo BASE_URL; ?>/views/ficha_personal.php?cedula=<?php echo $vigilante['cedula']; ?>" 
                                   class="btn-icon btn-view" title="Ver Expediente">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($puede_modificar): ?>
                                    <?php if (ControladorPrincipal::esAdministrador()): ?>
                                        <a href="<?php echo BASE_URL; ?>/index.php?action=editar_vigilante&cedula=<?php echo $vigilante['cedula']; ?>" 
                                           class="btn-icon btn-edit" title="Editar Vigilante">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        
                                        <a href="<?php echo BASE_URL; ?>/views/acciones.php?modulo=expediente&accion=eliminar&cedula=<?php echo $vigilante['cedula']; ?>" 
                                           class="btn-icon btn-delete" title="Eliminar Vigilante"
                                           onclick="return confirm('¿Está seguro de eliminar este vigilante? Esta acción no se puede deshacer.');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
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
