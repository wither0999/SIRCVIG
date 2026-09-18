<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

if (!ControladorPrincipal::esAdministrador() && !ControladorPrincipal::esSupervisor()) {
    $_SESSION['error'] = 'No tiene permisos.';
    header('Location: ' . BASE_URL . '/views/dashboard.php');
    exit();
}

// Datos pasados por el controlador
// $analisis = [cedula => ['status', 'mensaje', 'fin']]
// $vigilantes
// $rango_texto

$titulo = 'Disponibilidad de Vigilantes';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    .grid-vigilantes {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .card-vigilante {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        padding: 20px;
        border-left: 5px solid #ccc;
        transition: transform 0.2s;
    }
    
    .card-vigilante:hover {
        transform: translateY(-3px);
    }
    
    .status-available { border-left-color: #27ae60; }
    .status-occupied { border-left-color: #f1c40f; }
    .status-blocked { border-left-color: #c0392b; }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .card-name {
        font-weight: 600;
        font-size: 1.1rem;
        color: #333;
    }
    
    .card-cedula {
        font-size: 0.85rem;
        color: #777;
    }
    
    .status-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        color: white;
    }
    
    .badge-available { background-color: #27ae60; }
    .badge-occupied { background-color: #f1c40f; color: #333; }
    .badge-blocked { background-color: #c0392b; }
    
    .info-text {
        font-size: 0.9rem;
        color: #555;
        margin-top: 10px;
    }
    
    .countdown {
        font-weight: bold;
        color: #c0392b;
    }
    
    .rules-info {
        background: #e8f6f3;
        border: 1px solid #a2d9ce;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        color: #148f77;
    }
</style>

<h1><i class="fas fa-users-cog"></i> Calendario de Conflictos</h1>

<div class="mb-3">
    <a href="<?php echo BASE_URL; ?>/views/cronograma_guardia.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver al Cronograma
    </a>
</div>

<div class="rules-info">
    <strong><i class="fas fa-info-circle"></i> Reglas de Descanso Activas:</strong>
    <ul style="margin-bottom: 0; margin-top: 5px; padding-left: 20px;">
        <li><strong>24x48:</strong> 1 día trabajo + 2 días descanso obligatorio.</li>
        <li><strong>2x2 / 5x2 / 4x2:</strong> Ciclos de trabajo consecutivos seguidos de descanso proporcional bloqueado.</li>
    </ul>
    <div style="margin-top: 5px; font-size: 0.9rem;">
        Periodo visualizado actualmente: <strong><?php echo htmlspecialchars($rango_texto ?? 'Automático'); ?></strong>
    </div>
</div>

<div class="grid-vigilantes">
    <?php foreach ($vigilantes as $v): ?>
        <?php 
        $cedula = $v['cedula'];
        $estado = $analisis[$cedula] ?? ['status' => 'DISPONIBLE', 'mensaje' => 'Sin datos', 'fin' => null];
        
        $cardClass = 'card-vigilante';
        $badgeClass = 'status-badge';
        
        switch ($estado['status']) {
            case 'DISPONIBLE':
                $cardClass .= ' status-available';
                $badgeClass .= ' badge-available';
                $icon = 'fa-check';
                break;
            case 'OCUPADO':
                $cardClass .= ' status-occupied';
                $badgeClass .= ' badge-occupied';
                $icon = 'fa-clock';
                break;
            case 'BLOQUEADO':
                $cardClass .= ' status-blocked';
                $badgeClass .= ' badge-blocked';
                $icon = 'fa-ban';
                break;
        }
        ?>
        
        <div class="<?php echo $cardClass; ?>">
            <div class="card-header">
                <div>
                    <div class="card-name"><?php echo htmlspecialchars($v['nombres'] . ' ' . $v['apellidos']); ?></div>
                    <div class="card-cedula">V-<?php echo htmlspecialchars($v['cedula']); ?></div>
                </div>
                <span class="<?php echo $badgeClass; ?>">
                    <i class="fas <?php echo $icon; ?>"></i> <?php echo $estado['status']; ?>
                </span>
            </div>
            
            <div class="info-text">
                <?php echo htmlspecialchars($estado['mensaje']); ?>
            </div>
            
            <?php if ($estado['fin']): ?>
                <div class="info-text" style="font-size: 0.8rem; color: #777; margin-top: 5px;">
                    <i class="fas fa-calendar-alt"></i> Hasta: <?php echo date('d/m H:i', strtotime($estado['fin'])); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($estado['status'] == 'DISPONIBLE'): ?>
                <div style="margin-top: 15px; text-align: right;">
                    <a href="<?php echo BASE_URL; ?>/views/asignar_puesto.php?cedula=<?php echo $cedula; ?>" class="btn btn-sm btn-primary">Asignar</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
