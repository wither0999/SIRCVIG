<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

$cedula = $_GET['cedula'] ?? '';

if (empty($cedula)) {
    $_SESSION['error'] = 'Cédula no especificada.';
    header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
    exit();
}

$modeloVigilante = new Vigilante();
$modeloDocumento = new Documento();

$vigilante = $modeloVigilante->obtenerPorCedula($cedula);

if (!$vigilante) {
    $_SESSION['error'] = 'Vigilante no encontrado.';
    header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
    exit();
}

$documentos = $modeloDocumento->obtenerPorVigilante($cedula);
$puede_ver_antecedentes = ControladorPrincipal::esAdministrador();

// Calcular Edad
$fecha_nac = new DateTime($vigilante['fecha_nacimiento']);
$hoy = new DateTime();
$edad = $hoy->diff($fecha_nac)->y;

$titulo = 'Ficha Personal del Vigilante';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-ficha">
    <div class="header-ficha">
        <h1 class="titulo-ficha"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($vigilante['nombres'] . ' ' . $vigilante['apellidos']); ?></h1>
        <div class="acciones-ficha">
            <a href="pdf_ficha_vigilante.php?cedula=<?php echo $vigilante['cedula']; ?>" target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Imprimir Ficha
            </a>
            <a href="<?php echo BASE_URL; ?>/views/listado_vigilantes.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="card-ficha">
        
        <!-- Datos de Identificación -->
        <div class="ficha-section">
            <h3 class="section-title">Datos de Identificación</h3>
            <div class="ficha-grid">
                <div class="ficha-item">
                    <span class="label">Nombres:</span>
                    <span class="value"><?php echo htmlspecialchars($vigilante['nombres']); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Apellidos:</span>
                    <span class="value"><?php echo htmlspecialchars($vigilante['apellidos']); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Cédula:</span>
                    <span class="value"><?php echo htmlspecialchars($vigilante['cedula']); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Fecha de Nacimiento:</span>
                    <span class="value"><?php echo date('d/m/Y', strtotime($vigilante['fecha_nacimiento'])); ?></span>
                </div>
                <div class="ficha-item">
                    <span class="label">Edad:</span>
                    <span class="value"><?php echo $edad; ?> años</span>
                </div>
                <div class="ficha-item">
                    <span class="label">Estatus:</span>
                    <span class="value">
                        <span class="badge 
                            <?php 
                            echo ($vigilante['estatus'] == 'Activo') ? 'badge-success' : 
                                 (($vigilante['estatus'] == 'Aspirante') ? 'badge-warning' : 'badge-danger'); 
                            ?>">
                            <?php echo $vigilante['estatus']; ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Información de Contacto -->
        <div class="ficha-section">
            <h3 class="section-title">Información de Contacto</h3>
            <div class="ficha-grid">
                <div class="ficha-item">
                    <span class="label">Teléfono:</span>
                    <span class="value"><?php echo htmlspecialchars($vigilante['telefono'] ?? '-'); ?></span>
                </div>
            </div>
        </div>

        <!-- Ubicación -->
        <div class="ficha-section">
            <h3 class="section-title">Ubicación</h3>
            <div class="ficha-item full-width mt-2">
                <span class="label">Dirección / Estado:</span>
                <span class="value"><?php echo htmlspecialchars($vigilante['direccion'] ?? 'No registrada'); ?>  <?php echo !empty($vigilante['estado']) ? '(' . $vigilante['estado'] . ')' : ''; ?></span>
            </div>
        </div>

        <!-- Documentos -->
        <div class="ficha-section">
            <h3 class="section-title">Documentos</h3>
            <div class="table-container" style="box-shadow: none; border: 1px solid #eee;">
                <table>
                    <thead>
                        <tr>
                            <th>Tipo de Documento</th>
                            <th>Nombre del Archivo</th>
                            <th>Fecha de Subida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($documentos)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay documentos registrados</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($documentos as $doc): ?>
                                <?php 
                                // Ocultar Antecedentes si no es Administrador
                                if ($doc['tipo_documento'] == 'Antecedentes' && !$puede_ver_antecedentes) {
                                    continue;
                                }
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($doc['tipo_documento']); ?></td>
                                    <td><?php echo htmlspecialchars($doc['nombre_archivo']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($doc['fecha_subida'])); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL . $doc['ruta_archivo']; ?>" 
                                           target="_blank" 
                                           class="btn btn-secondary" 
                                           style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
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
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
    .badge-warning { background-color: #f39c12; }
    .badge-danger { background-color: #e74c3c; }
    .mt-2 { margin-top: 10px; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
