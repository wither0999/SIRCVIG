<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

if (!ControladorPrincipal::esAdministrador() && !ControladorPrincipal::esSupervisor()) {
    $_SESSION['error'] = 'No tiene permisos para asignar puestos.';
    header('Location: ' . BASE_URL . '/views/dashboard.php');
    exit();
}

$modeloVigilante = new Vigilante();
$modeloPuesto = new Puesto();

$vigilantes = $modeloVigilante->obtenerPorEstatus('Activo');
$puestos = $modeloPuesto->obtenerActivos();

$titulo = 'Asignar Puesto';
require_once __DIR__ . '/includes/header.php';
?>

<h1>Asignar Puesto a Vigilante</h1>

<div class="form-container">
    <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=asignacion&accion=procesar" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="cedula_vigilante">Vigilante *</label>
                <select id="cedula_vigilante" name="cedula_vigilante" required>
                    <option value="">Seleccione un vigilante</option>
                    <?php 
                    $mapa = $disponibilidad ?? [];
                    foreach ($vigilantes as $vigilante): 
                        $cedula = $vigilante['cedula'];
                        $info = $mapa[$cedula] ?? ['status' => 'DISPONIBLE', 'mensaje' => ''];
                        $isBusy = ($info['status'] != 'DISPONIBLE');
                        
                        $label = htmlspecialchars($vigilante['nombres'] . ' ' . $vigilante['apellidos']);
                        
                        if ($isBusy) {
                            // Mostrar mensaje corto de estado
                            if ($info['status'] == 'BLOQUEADO') $label .= ' (EN DESCANSO)';
                            elseif ($info['status'] == 'OCUPADO') $label .= ' (EN TURNO)';
                        }
                    ?>
                        <option value="<?php echo $cedula; ?>" <?php echo $isBusy ? 'disabled style="color: #999; background: #f2f2f2;"' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="id_puesto">Puesto *</label>
                <select id="id_puesto" name="id_puesto" required>
                    <option value="">Seleccione un puesto</option>
                    <?php foreach ($puestos as $puesto): ?>
                        <option value="<?php echo $puesto['id_puesto']; ?>">
                            <?php echo htmlspecialchars($puesto['nombre_cliente']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Fecha y Hora de Inicio *</label>
                <div style="display: flex; gap: 10px;">
                    <?php $max_date = date('Y-m-d', strtotime('+1 day')); ?>
                    <input type="date" id="fecha_inicio_date" name="fecha_inicio_date" required class="form-control" style="flex: 1;" max="<?php echo $max_date; ?>">
                    <select id="fecha_inicio_time" name="fecha_inicio_time" required class="form-control" style="width: 140px;">
                        <option value="06:00">06:00 AM (Diurno)</option>
                        <option value="18:00">06:00 PM (Nocturno)</option>
                    </select>
                </div>
                <small class="form-text text-muted">Hora fijada obligatoriamente a inicio de turno.</small>
            </div>
            
            <div class="form-group">
                <label for="rol_guardia">Rol de Guardia *</label>
                <select id="rol_guardia" name="rol_guardia" required>
                    <option value="">Seleccione un rol</option>
                    <option value="24x48">24x48 (1 día 24h + 2 días descanso)</option>
                    <option value="2x2">2x2 (2 días 12h + 2 días descanso)</option>
                    <option value="5x2">5x2 (5 días 12h + 2 días descanso)</option>
                    <option value="4x2">4x2 (2D + 2N + 2R - Ciclo Especial 6 días)</option>
                </select>
                <small class="form-text text-muted">La fecha de fin y descanso se calculará automáticamente.</small>
            </div>
        </div>
        
        <div class="form-group">
            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones" rows="3"></textarea>
        </div>
        
        <div class="form-actions">
            <a href="<?php echo BASE_URL; ?>/views/cronograma_guardia.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Asignar Puesto</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

