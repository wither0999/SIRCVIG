<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

$id_asignacion = $_GET['id'] ?? null;

if (!$id_asignacion) {
    $_SESSION['error'] = 'ID de asignación no especificado.';
    header('Location: ' . BASE_URL . '/views/cronograma_guardia.php');
    exit();
}

$modeloAsignacion = new Asignacion();
$asignacion = $modeloAsignacion->obtenerPorId($id_asignacion);

if (!$asignacion) {
    $_SESSION['error'] = 'Asignación no encontrada.';
    header('Location: ' . BASE_URL . '/views/cronograma_guardia.php');
    exit();
}

$titulo = 'Ficha de Asignación';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-ficha">
    <div class="header-ficha">
        <h1 class="titulo-ficha"><i class="fas fa-clipboard-list"></i> Ficha de Asignación</h1>
        <div class="acciones-ficha">
            <a href="pdf_ficha_asignacion.php?id=<?php echo $asignacion['id_asignacion']; ?>" target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Imprimir Ficha
            </a>
            <a href="<?php echo BASE_URL; ?>/views/cronograma_guardia.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="card-ficha">
        
        <!-- Detalle de Asignación -->
        <div class="ficha-section">
            <h3 class="section-title">Detalle de la Asignación</h3>
            <div class="ficha-grid">
                <div class="ficha-item">
                    <span class="label">Rol de Guardia:</span>
                    <span class="value"><?php echo htmlspecialchars($asignacion['rol_guardia']); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Fecha Inicio:</span>
                    <span class="value"><?php echo date('d/m/Y H:i', strtotime($asignacion['fecha_inicio'])); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Fecha Fin:</span>
                    <span class="value"><?php echo date('d/m/Y H:i', strtotime($asignacion['fecha_fin'])); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Estatus:</span>
                    <span class="value">
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
                    </span>
                </div>
            </div>
        </div>

        <!-- Personal Asignado -->
        <div class="ficha-section">
            <h3 class="section-title">Personal Asignado</h3>
            <div class="ficha-grid">
                <div class="ficha-item">
                    <span class="label">Vigilante:</span>
                    <span class="value"><?php echo htmlspecialchars($asignacion['nombres'] . ' ' . $asignacion['apellidos']); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Cédula:</span>
                    <span class="value"><?php echo htmlspecialchars($asignacion['cedula']); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Teléfono:</span>
                    <span class="value"><?php echo htmlspecialchars($asignacion['telefono_vigilante'] ?? 'No registrado'); ?></span>
                </div>
            </div>
        </div>

        <!-- Ubicación del Puesto -->
        <div class="ficha-section">
            <h3 class="section-title">Ubicación del Puesto</h3>
            <div class="ficha-item full-width">
                <span class="label">Cliente:</span>
                <span class="value"><?php echo htmlspecialchars($asignacion['nombre_cliente']); ?></span>
            </div>
            <div class="ficha-item full-width mt-2">
                <span class="label">Dirección / Puesto:</span>
                <span class="value"><?php echo htmlspecialchars($asignacion['direccion_puesto']); ?></span>
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
        height: 38px;
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
    .badge-secondary { background-color: #95a5a6; }
    .badge-danger { background-color: #e74c3c; }
    .mt-2 { margin-top: 10px; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
