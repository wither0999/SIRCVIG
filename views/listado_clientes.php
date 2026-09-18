<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

$puede_modificar = ControladorPrincipal::esAdministrador() || ControladorPrincipal::esSecretario();

if (!$puede_modificar && !ControladorPrincipal::esSupervisor()) {
    $_SESSION['error'] = 'No tiene permisos para acceder a esta sección.';
    header('Location: ' . BASE_URL . '/views/dashboard.php');
    exit();
}

$modeloCliente = new Cliente();

$termino_busqueda = $_GET['buscar'] ?? '';

if (!empty($termino_busqueda)) {
    $clientes = $modeloCliente->buscar($termino_busqueda);
} else {
    $clientes = $modeloCliente->obtenerTodos();
}

$titulo = 'Listado de Clientes';
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
    .col-actions { width: 150px; text-align: center; }
    
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
        gap: 8px;
        justify-content: center;
        align-items: center;
    }
    
    /* Botones de Icono */
    .btn-icon {
        width: 34px; height: 34px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 6px;
        border: none; transition: all 0.2s ease;
        text-decoration: none; font-size: 0.9rem; color: white !important;
    }
    
    .btn-view { background-color: #5d6d7e; }
    .btn-view:hover { background-color: #34495e; transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    
    .btn-edit { background-color: #f1c40f; }
    .btn-edit:hover { background-color: #d4ac0d; transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    
    .btn-delete { background-color: #e74c3c; }
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
    <h1 style="margin-bottom: 0;"><i class="fas fa-users"></i> Listado de Clientes</h1>

    <?php if ($puede_modificar): ?>
    <div>
        <a href="<?php echo BASE_URL; ?>/views/formulario_cliente.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Nuevo Cliente
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Búsqueda -->
<div class="search-box mb-3">
    <form method="GET" action="<?php echo BASE_URL; ?>/views/listado_clientes.php" style="display: flex; gap: 10px; width: 100%;">
        <input type="text" name="buscar" class="search-input" placeholder="Buscar clientes por nombre, RIF o ubicación..." 
               value="<?php echo htmlspecialchars($termino_busqueda ?? ''); ?>">
        <button type="submit" class="btn btn-primary" style="width: auto;"><i class="fas fa-search"></i> Buscar</button>
        <?php if (!empty($termino_busqueda)): ?>
            <a href="<?php echo BASE_URL; ?>/views/listado_clientes.php" class="btn btn-secondary" style="width: auto;">Limpiar</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-container">
    <table class="table-custom">
        <thead>
            <tr>
                <th>Razón Social</th>
                <th>RIF</th>
                <th>Ubicación</th>
                <th>Teléfono</th>
                <th class="col-status">Estatus</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clientes)): ?>
                <tr>
                    <td colspan="6" class="text-center" style="padding: 2rem; color: #777;">
                        <i class="fas fa-search fa-2x mb-2"></i><br>
                        No se encontraron clientes registrados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td style="font-weight: 500; color: var(--color-primario);"><?php echo htmlspecialchars($cliente['nombre_cliente']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['rif_cedula']); ?></td>
                        <td>
                            <?php 
                            $loc = [];
                            if (!empty($cliente['municipio'])) $loc[] = $cliente['municipio'];
                            if (!empty($cliente['parroquia'])) $loc[] = $cliente['parroquia'];
                            echo !empty($loc) ? htmlspecialchars(implode(' / ', $loc)) : '<span style="color: #ccc;">-</span>';
                            ?>
                        </td>
                        <td>
                            <?php if (!empty($cliente['telefono'])): ?>
                                <i class="fas fa-phone" style="color: #999; margin-right: 3px; font-size: 0.8rem;"></i>
                                <?php echo htmlspecialchars($cliente['telefono']); ?>
                            <?php else: ?>
                                <span style="color: #ccc;">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="col-status">
                            <span class="badge <?php echo ($cliente['estatus'] == 'Activo') ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo htmlspecialchars($cliente['estatus']); ?>
                            </span>
                        </td>
                        <td class="col-actions">
                            <div class="action-buttons">
                                <a href="<?php echo BASE_URL; ?>/views/ficha_cliente.php?id=<?php echo $cliente['id_cliente']; ?>" 
                                   class="btn-icon btn-view" title="Ver Ficha">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($puede_modificar): ?>
                                    <a href="<?php echo BASE_URL; ?>/views/formulario_cliente.php?id=<?php echo $cliente['id_cliente']; ?>" 
                                       class="btn-icon btn-edit" title="Editar Cliente">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (ControladorPrincipal::esAdministrador()): ?>
                                    <a href="<?php echo BASE_URL; ?>/views/acciones.php?modulo=cliente&accion=eliminar&id=<?php echo $cliente['id_cliente']; ?>" 
                                       class="btn-icon btn-delete" title="Eliminar Cliente"
                                       onclick="return confirm('¿Está seguro de eliminar este cliente? Se borrarán sus datos.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
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
