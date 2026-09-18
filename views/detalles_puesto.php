<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

$id_puesto = $_GET['id'] ?? null;

if (empty($id_puesto)) {
    $_SESSION['error'] = 'ID de puesto no especificado.';
    header('Location: ' . BASE_URL . '/views/listado_puestos.php');
    exit();
}

$modeloPuesto = new Puesto();
$puesto = $modeloPuesto->obtenerPorId($id_puesto);

if (!$puesto) {
    $_SESSION['error'] = 'Puesto no encontrado.';
    header('Location: ' . BASE_URL . '/views/listado_puestos.php');
    exit();
}

// Obtener asignaciones activas
$modeloAsignacion = new Asignacion();
$asignaciones = $modeloAsignacion->obtenerPorPuesto($id_puesto, true);

$puede_modificar = ControladorPrincipal::esAdministrador() || ControladorPrincipal::esSecretario();

$titulo = 'Ficha de Puesto de Guardia';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-ficha">
    <div class="header-ficha">
        <h1 class="titulo-ficha"><i class="fas fa-building-shield"></i> Detalles del Puesto</h1>
        <div class="acciones-ficha">
            <a href="pdf_ficha_puesto.php?id=<?php echo $puesto['id_puesto']; ?>" target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Imprimir Ficha
            </a>
            <?php if ($puede_modificar): ?>
            <a href="<?php echo BASE_URL; ?>/views/formulario_puesto.php?id=<?php echo $puesto['id_puesto']; ?>" class="btn btn-primary" style="background-color: #3498db; border-color: #3498db;">
                <i class="fas fa-pen"></i> Editar
            </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/views/listado_puestos.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="card-ficha">
        
        <!-- Información del Puesto -->
        <div class="ficha-section">
            <h3 class="section-title">Información del Puesto</h3>
            <div class="ficha-grid">
                <div class="ficha-item">
                    <span class="label">Cliente:</span>
                    <span class="value"><?php echo htmlspecialchars($puesto['nombre_cliente']); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Estatus:</span>
                    <span class="value">
                        <span class="badge <?php echo $puesto['estatus'] == 'Activo' ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo htmlspecialchars($puesto['estatus']); ?>
                        </span>
                    </span>
                </div>
                <div class="ficha-item">
                    <span class="label">Teléfono:</span>
                    <span class="value"><?php echo htmlspecialchars($puesto['telefono'] ?? 'No registrado'); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Persona Contacto:</span>
                    <span class="value"><?php echo htmlspecialchars($puesto['contacto'] ?? 'No registrado'); ?></span>
                </div>
            </div>
            
            <div class="ficha-item full-width mt-2">
                <span class="label">Dirección / Ubicación:</span>
                <span class="value"><?php echo htmlspecialchars($puesto['direccion']); ?></span>
            </div>
            
            <?php if (!empty($puesto['descripcion'])): ?>
            <div class="ficha-item full-width mt-2">
                <span class="label">Descripción Adicional:</span>
                <span class="value"><?php echo nl2br(htmlspecialchars($puesto['descripcion'])); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Asignaciones Activas -->
        <div class="ficha-section">
            <h3 class="section-title">Asignaciones Activas</h3>
            
            <div class="table-container" style="box-shadow: none; border: 1px solid #eee;">
                <table>
                    <thead>
                        <tr>
                            <th>Vigilante</th>
                            <th>Cédula</th>
                            <th>Desde</th>
                            <th>Hasta</th>
                            <th>Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($asignaciones)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay personal asignado actualmente.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($asignaciones as $asignacion): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($asignacion['nombres'] . ' ' . $asignacion['apellidos']); ?></td>
                                    <td><?php echo htmlspecialchars($asignacion['cedula']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($asignacion['fecha_inicio'])); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($asignacion['fecha_fin'])); ?></td>
                                    <td>
                                        <span class="badge" style="background-color: #3498db; font-weight: normal;">
                                            <?php echo htmlspecialchars($asignacion['rol_guardia']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<style>
    .container-ficha {
        max-width: 900px;
        margin: 0 auto;
    }
    .header-ficha {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .titulo-ficha {
        font-size: 1.8rem;
        color: var(--color-primario);
        margin: 0;
    }
    .acciones-ficha {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .acciones-ficha .btn {
        padding: 5px 15px;
        font-size: 0.9rem;
        height: 38px; /* Fixed height for consistency */
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }
    .card-ficha {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        padding: 30px;
        border-top: 5px solid var(--color-primario);
    }
    .ficha-section {
        margin-bottom: 30px;
        border-bottom: 1px solid #eee;
        padding-bottom: 20px;
    }
    .ficha-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .section-title {
        color: #555;
        font-size: 1.2rem;
        margin-bottom: 15px;
        padding-bottom: 5px;
        border-left: 4px solid #3498db;
        padding-left: 10px;
    }
    .ficha-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }
    .ficha-item {
        display: flex;
        flex-direction: column;
    }
    .ficha-item.full-width {
        grid-column: 1 / -1;
    }
    .label {
        font-weight: 600;
        font-size: 0.9rem;
        color: #7f8c8d;
        margin-bottom: 4px;
    }
    .value {
        font-size: 1.05rem;
        color: #2c3e50;
        font-weight: 500;
    }
    .badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.85rem;
        color: white;
    }
    .badge-success { background-color: #27ae60; }
    .badge-danger { background-color: #e74c3c; }
    .mt-2 { margin-top: 10px; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
