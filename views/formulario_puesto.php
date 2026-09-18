<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

if (!ControladorPrincipal::esAdministrador() && !ControladorPrincipal::esSecretario()) {
    $_SESSION['error'] = 'No tiene permisos para gestionar puestos.';
    header('Location: ' . BASE_URL . '/views/listado_puestos.php');
    exit();
}

$id_puesto = $_GET['id'] ?? null;
$puesto = null;

if ($id_puesto) {
    $modeloPuesto = new Puesto();
    $puesto = $modeloPuesto->obtenerPorId($id_puesto);
    
    if (!$puesto) {
        $_SESSION['error'] = 'Puesto no encontrado.';
        header('Location: ' . BASE_URL . '/views/listado_puestos.php');
        exit();
    }
}

$titulo = $puesto ? 'Editar Puesto' : 'Nuevo Puesto';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Estilos específicos para este formulario */
    .input-group {
        display: flex;
        gap: 5px;
    }
    .btn-plus {
        background-color: var(--color-primario);
        color: white;
        border: none;
        border-radius: 5px;
        width: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.3s;
    }
    .btn-plus:hover {
        background-color: var(--color-secundario);
    }
    
    /* Switch Toggle */
    .switch-container {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        margin-bottom: 0;
    }
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    .switch input { 
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: var(--color-primario);
    }
    input:focus + .slider {
        box-shadow: 0 0 1px var(--color-primario);
    }
    input:checked + .slider:before {
        transform: translateX(26px);
    }
    .switch-label {
        font-weight: 600;
        color: var(--color-texto);
        font-size: 0.95rem;
    }
</style>

<h1><?php echo $puesto ? 'Editar Puesto' : 'Nuevo Puesto'; ?></h1>

<div class="form-container">
    <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=puesto&accion=procesar" method="POST">
        <?php if ($puesto): ?>
            <input type="hidden" name="id_puesto" value="<?php echo $puesto['id_puesto']; ?>">
        <?php endif; ?>
        
        <!-- Fila 1: Cliente y Estatus -->
        <h3 class="mb-2" style="font-size: 1.1rem; color: var(--color-primario); border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Información General</h3>
        <div class="form-row">
            <?php
            $modeloCliente = new Cliente();
            $clientes = $modeloCliente->obtenerActivos();
            ?>
            <div class="form-group" style="flex: 2;">
                <label for="id_cliente">Cliente Asociado *</label>
                <div class="input-group">
                    <select id="id_cliente" name="id_cliente" required class="form-control" style="flex: 1;">
                        <option value="" data-estado="" data-municipio="" data-parroquia="" data-direccion="">Seleccione Cliente</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?php echo $c['id_cliente']; ?>" 
                                    data-estado="<?php echo htmlspecialchars($c['estado'] ?? ''); ?>"
                                    data-municipio="<?php echo htmlspecialchars($c['municipio'] ?? ''); ?>"
                                    data-parroquia="<?php echo htmlspecialchars($c['parroquia'] ?? ''); ?>"
                                    data-direccion="<?php echo htmlspecialchars($c['direccion'] ?? ''); ?>"
                                    <?php echo (isset($puesto['id_cliente']) && $puesto['id_cliente'] == $c['id_cliente']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['nombre_cliente']); ?> (<?php echo htmlspecialchars($c['rif_cedula']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="<?php echo BASE_URL; ?>/views/formulario_cliente.php" class="btn-plus" title="Registrar nuevo cliente" target="_blank">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
            </div>
            
            <div class="form-group" style="flex: 1;">
                <label for="estatus">Estatus Operativo</label>
                <select id="estatus" name="estatus" class="form-control">
                    <option value="Activo" <?php echo (!isset($puesto) || $puesto['estatus'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="Inactivo" <?php echo (isset($puesto) && $puesto['estatus'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
        </div>
        
        <!-- Fila 2: Autollenado -->
        <div class="form-row align-items-center mb-2" style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
            <label class="switch-container">
                <div class="switch">
                    <input type="checkbox" id="use_client_address">
                    <span class="slider"></span>
                </div>
                <span class="switch-label">¿Usar misma ubicación del cliente?</span>
            </label>
        </div>
        
        <!-- Fila 3: Contacto -->
        <h3 class="mb-2 mt-2" style="font-size: 1.1rem; color: var(--color-primario); border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Contacto en Sitio</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="contacto">Persona de Enlace / Responsable</label>
                <input type="text" id="contacto" name="contacto" 
                       value="<?php echo htmlspecialchars($puesto['contacto'] ?? ''); ?>"
                       minlength="3" maxlength="25"
                       oninput="this.value = this.value.replace(/[^a-zA-Z\u00C0-\u00FF\s]/g, '')"
                       placeholder="Ej: Jefe de Seguridad o Gerente">
            </div>
            
            <div class="form-group">
                <label>Teléfono Directo</label>
                <div style="display: flex; gap: 10px;">
                    <?php
                    // Parsear teléfono actual si existe
                    $tlf_puesto = $puesto['telefono'] ?? '';
                    $cod_p = ''; $num_p = '';
                    if (!empty($tlf_puesto)) {
                        $clean = preg_replace('/[^0-9]/', '', $tlf_puesto);
                        if (strlen($clean) >= 11) {
                            $cod_p = substr($clean, 0, 4);
                            $num_p = substr($clean, 4);
                        }
                    }
                    ?>
                    <select name="codigo_operadora" class="form-control" style="width: 100px;" required>
                        <option value="">Cód</option>
                         <?php
                         $ops = ['0412', '0414', '0416', '0422', '0424', '0426', '0281', '0282', '0283'];
                         foreach ($ops as $op) {
                             $sel = ($cod_p == $op) ? 'selected' : '';
                             echo "<option value='$op' $sel>$op</option>";
                         }
                         ?>
                    </select>
                    <input type="text" name="telefono_numero" class="form-control" style="flex: 1;"
                           value="<?php echo htmlspecialchars($num_p); ?>"
                           required placeholder="1234567" maxlength="7"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
            </div>
        </div>
        
        <!-- Fila 4: Dirección -->
        <h3 class="mb-2 mt-2" style="font-size: 1.1rem; color: var(--color-primario); border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Ubicación Geográfica</h3>
        
        <div class="form-row">
            <div class="form-group">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" class="form-control">
                    <option value="">Seleccione Estado</option>
                </select>
            </div>
            <div class="form-group">
                <label for="municipio">Municipio</label>
                <select id="municipio" name="municipio" class="form-control" disabled>
                    <option value="">Seleccione Municipio</option>
                </select>
            </div>
            <div class="form-group">
                <label for="parroquia">Parroquia</label>
                <select id="parroquia" name="parroquia" class="form-control" disabled>
                    <option value="">Seleccione Parroquia</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="direccion_detalle">Punto de Referencia / Detalle Exacto *</label>
            <textarea id="direccion_detalle" name="direccion_detalle" rows="2" required placeholder="Av. Principal, Edificio Torre A..." class="form-control"><?php echo htmlspecialchars($puesto['direccion'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="descripcion">Notas Adicionales</label>
            <textarea id="descripcion" name="descripcion" rows="2" placeholder="Instrucciones especiales para el puesto..."><?php echo htmlspecialchars($puesto['descripcion'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-actions">
            <a href="<?php echo BASE_URL; ?>/views/listado_puestos.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary"><?php echo $puesto ? 'Actualizar Puesto' : 'Crear Puesto'; ?></button>
        </div>
    </form>
</div>

<script src="<?php echo BASE_URL; ?>/assets/js/venezuela.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const clientSelect = document.getElementById('id_cliente');
    const addressCheck = document.getElementById('use_client_address');
    
    // Campos
    const estadoSel = document.getElementById('estado');
    const muniSel = document.getElementById('municipio');
    const parrSel = document.getElementById('parroquia');
    const dirDetail = document.getElementById('direccion_detalle');
    
    function autofillAddress() {
        if (!addressCheck.checked) return;
        
        const option = clientSelect.options[clientSelect.selectedIndex];
        if (!option.value) return;
        
        const est = option.getAttribute('data-estado');
        const mun = option.getAttribute('data-municipio');
        const par = option.getAttribute('data-parroquia');
        const dir = option.getAttribute('data-direccion');
        
        if (est) {
            estadoSel.value = est;
            estadoSel.dispatchEvent(new Event('change'));
            
            setTimeout(() => {
                muniSel.value = mun;
                muniSel.dispatchEvent(new Event('change'));
                
                setTimeout(() => {
                    parrSel.value = par;
                    lockFields(true); // Re-enforce lock after venezuela.js potentially unlocks it
                }, 100);
            }, 100);
        }
        
        if (dir) dirDetail.value = dir;
        
        lockFields(true);
    }
    
    function lockFields(lock) {
        estadoSel.disabled = lock;
        if(lock) {
            muniSel.disabled = true;
            parrSel.disabled = true;
            dirDetail.readOnly = true;
        } else {
            estadoSel.disabled = false;
            muniSel.disabled = false;
            parrSel.disabled = false;
            dirDetail.readOnly = false;
        }
    }
    
    addressCheck.addEventListener('change', function() {
        if (this.checked) {
            autofillAddress();
        } else {
            lockFields(false);
        }
    });
    
    clientSelect.addEventListener('change', function() {
        if (addressCheck.checked) autofillAddress();
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
