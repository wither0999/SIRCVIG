<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

$id_cliente = $_GET['id'] ?? null;

if (!$id_cliente) {
    header('Location: ' . BASE_URL . '/views/listado_clientes.php');
    exit();
}

$modeloCliente = new Cliente();
$cliente = $modeloCliente->obtenerPorId($id_cliente);

if (!$cliente) {
    $_SESSION['error'] = 'Cliente no encontrado.';
    header('Location: ' . BASE_URL . '/views/listado_clientes.php');
    exit();
}

$titulo = 'Ficha del Cliente';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-ficha">
    <div class="header-ficha">
        <h1 class="titulo-ficha"><i class="fas fa-building"></i> <?php echo htmlspecialchars($cliente['nombre_cliente']); ?></h1>
        <div class="acciones-ficha">
            <a href="<?php echo BASE_URL; ?>/views/pdf_ficha_cliente.php?id=<?php echo $cliente['id_cliente']; ?>" target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Imprimir Ficha
            </a>
            <a href="<?php echo BASE_URL; ?>/views/listado_clientes.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="card-ficha">
        <div class="ficha-section">
            <h3 class="section-title">Datos de Identificación</h3>
            <div class="ficha-grid">
                <div class="ficha-item">
                    <span class="label">RIF / Cédula:</span>
                    <span class="value"><?php echo htmlspecialchars($cliente['rif_cedula']); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Nombre / Razón Social:</span>
                    <span class="value"><?php echo htmlspecialchars($cliente['nombre_cliente']); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Estatus:</span>
                    <span class="value">
                        <span class="badge <?php echo ($cliente['estatus'] == 'Activo') ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $cliente['estatus']; ?>
                        </span>
                    </span>
                </div>
                <div class="ficha-item">
                    <span class="label">Fecha de Registro:</span>
                    <span class="value"><?php echo ($cliente['fecha_creacion']) ? date('d/m/Y h:i A', strtotime($cliente['fecha_creacion'])) : 'No registrada'; ?></span>
                </div>
            </div>
        </div>

        <div class="ficha-section">
            <h3 class="section-title">Información de Contacto</h3>
            <div class="ficha-grid">
                <div class="ficha-item">
                    <span class="label">Teléfono:</span>
                    <span class="value"><?php echo htmlspecialchars($cliente['telefono'] ?? 'No registrado'); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Correo Electrónico:</span>
                    <span class="value"><?php echo htmlspecialchars($cliente['email'] ?? 'No registrado'); ?></span>
                </div>
            </div>
        </div>

        <div class="ficha-section">
            <h3 class="section-title">Ubicación</h3>
            <div class="ficha-item full-width">
                <span class="label">Estado / Municipio / Parroquia:</span>
                <span class="value">
                    <?php 
                    $loc = [];
                    if (!empty($cliente['estado'])) $loc[] = $cliente['estado'];
                    if (!empty($cliente['municipio'])) $loc[] = $cliente['municipio'];
                    if (!empty($cliente['parroquia'])) $loc[] = $cliente['parroquia'];
                    echo implode(' / ', $loc);
                    ?>
                </span>
            </div>
            <div class="ficha-item full-width mt-2">
                <span class="label">Dirección Detallada:</span>
                <span class="value"><?php echo htmlspecialchars($cliente['direccion']); ?></span>
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
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
