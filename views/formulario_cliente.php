<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

if (!ControladorPrincipal::esAdministrador() && !ControladorPrincipal::esSecretario()) {
    $_SESSION['error'] = 'No tiene permisos para gestionar clientes.';
    header('Location: ' . BASE_URL . '/views/listado_clientes.php');
    exit();
}

$id_cliente = $_GET['id'] ?? null;
$cliente = null;

// Obtener datos si es edición
if ($id_cliente) {
    if (!isset($modeloCliente)) $modeloCliente = new Cliente();
    $cliente = $modeloCliente->obtenerPorId($id_cliente);
    
    if (!$cliente) {
        $_SESSION['error'] = 'Cliente no encontrado.';
        header('Location: ' . BASE_URL . '/views/listado_clientes.php');
        exit();
    }
}

// Valores por defecto
$c = $cliente ?? [];
$rif_full = $c['rif_cedula'] ?? '';
$tipo_doc = 'J';
$num_doc = '';

if (!empty($rif_full)) {
    if (strpos($rif_full, '-') !== false) {
        list($tipo_doc, $num_doc) = explode('-', $rif_full, 2);
    } else {
        $num_doc = $rif_full;
    }
}

// Teléfono
$tlf_full = $c['telefono'] ?? '';
$cod_tlf = '';
$num_tlf = '';
if (!empty($tlf_full)) {
    $clean = preg_replace('/[^0-9]/', '', $tlf_full);
    // Intentar extraer prefijo 4 dígitos
    if (strlen($clean) >= 11) { // 04141234567
         $cod_tlf = substr($clean, 0, 4);
         $num_tlf = substr($clean, 4);
    }
}

// Dirección pre-carga
$estado_val = $c['estado'] ?? '';
$municipio_val = $c['municipio'] ?? '';
$parroquia_val = $c['parroquia'] ?? '';
$direccion_val = $c['direccion'] ?? '';

$titulo = $cliente ? 'Editar Cliente' : 'Nuevo Cliente';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Estilos para iconos de validación centrados */
    .validation-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.2em;
        display: none;
        z-index: 10;
        pointer-events: none;
    }
    .form-group {
        position: relative; 
    }
    .input-with-icon {
        position: relative; 
        flex: 1;
    }
    .valid-feedback-icon {
        color: #27ae60;
    }
    .invalid-feedback-icon {
        color: #e74c3c;
    }
    .form-control {
        padding-right: 35px;
    }
</style>

<h1><i class="fas fa-user-tie"></i> <?php echo $titulo; ?></h1>

<div class="form-container">
    <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=cliente&accion=procesar" method="POST">
        <?php if ($cliente): ?>
            <input type="hidden" name="id_cliente" value="<?php echo $c['id_cliente']; ?>">
        <?php endif; ?>
        
        <!-- Identificación -->
        <h3 class="mb-2" style="font-size: 1.1rem; color: var(--color-primario); border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Datos de Identificación</h3>
        
        <div class="form-row">
            <div class="form-group">
                <label for="num_doc">RIF / Cédula *</label>
                <div style="display: flex; gap: 10px;">
                    <select name="tipo_doc" class="form-control" style="width: 80px;" required>
                        <option value="J" <?php echo ($tipo_doc == 'J') ? 'selected' : ''; ?>>J</option>
                        <option value="V" <?php echo ($tipo_doc == 'V') ? 'selected' : ''; ?>>V</option>
                        <option value="E" <?php echo ($tipo_doc == 'E') ? 'selected' : ''; ?>>E</option>
                        <option value="G" <?php echo ($tipo_doc == 'G') ? 'selected' : ''; ?>>G</option>
                    </select>
                    <div class="input-with-icon">
                        <input type="text" id="num_doc" name="num_doc" class="form-control" 
                               value="<?php echo htmlspecialchars($num_doc); ?>" 
                               required placeholder="123456789"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <i class="fas fa-check-circle validation-icon valid-feedback-icon" id="icon-doc-ok"></i>
                        <i class="fas fa-times-circle validation-icon invalid-feedback-icon" id="icon-doc-error"></i>
                    </div>
                </div>
                <small class="text-danger" id="error-doc" style="display:none;"></small>
            </div>
            
            <div class="form-group">
                <label for="nombre_cliente">Nombre / Razón Social *</label>
                <div class="input-with-icon" style="width: 100%;">
                    <input type="text" id="nombre_cliente" name="nombre_cliente" 
                           value="<?php echo htmlspecialchars($c['nombre_cliente'] ?? ''); ?>" required
                           class="form-control"
                           placeholder="Ej: Inversiones 2026 C.A.">
                    <i class="fas fa-check-circle validation-icon valid-feedback-icon" id="icon-nombre-ok"></i>
                    <i class="fas fa-times-circle validation-icon invalid-feedback-icon" id="icon-nombre-error"></i>
                </div>
                <small class="text-danger" id="error-nombre" style="display:none;">Este campo es obligatorio.</small>
            </div>
        </div>
        
        <!-- Contacto -->
        <div class="form-row">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <div class="input-with-icon" style="width: 100%;">
                    <input type="email" id="email" name="email" 
                           class="form-control"
                           value="<?php echo htmlspecialchars($c['email'] ?? ''); ?>"
                           placeholder="contacto@empresa.com">
                    <i class="fas fa-check-circle validation-icon valid-feedback-icon" id="icon-email-ok"></i>
                    <i class="fas fa-times-circle validation-icon invalid-feedback-icon" id="icon-email-error"></i>
                </div>
                <small class="text-danger" id="error-email" style="display:none;">Correo electrónico inválido.</small>
            </div>
            
            <div class="form-group">
                <label>Teléfono</label>
                <div style="display: flex; gap: 10px;">
                    <select name="cod_tlf" class="form-control" style="width: 100px;">
                         <option value="">Cód</option>
                         <?php
                         $ops = ['0412', '0414', '0424', '0416', '0422', '0426', '0281', '0282', '0283']; // Celulares + Fijos Anzoátegui
                         foreach ($ops as $op) {
                             $sel = ($cod_tlf == $op) ? 'selected' : '';
                             echo "<option value='$op' $sel>$op</option>";
                         }
                         ?>
                    </select>
                    <div class="input-with-icon">
                        <input type="text" id="num_tlf" name="num_tlf" class="form-control"
                               value="<?php echo htmlspecialchars($num_tlf); ?>"
                               placeholder="1234567"
                               maxlength="7"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <i class="fas fa-check-circle validation-icon valid-feedback-icon" id="icon-tlf-ok"></i>
                        <i class="fas fa-times-circle validation-icon invalid-feedback-icon" id="icon-tlf-error"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Dirección -->
        <h3 class="mb-2 mt-2" style="font-size: 1.1rem; color: var(--color-primario); border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Dirección Fiscal/Física</h3>
        
        <div class="form-row">
            <div class="form-group">
                <label for="estado">Estado *</label>
                <select id="estado" name="estado" class="form-control" required data-selected="<?php echo htmlspecialchars($estado_val); ?>">
                    <option value="">Seleccione Estado</option>
                </select>
            </div>
            <div class="form-group">
                <label for="municipio">Municipio *</label>
                <select id="municipio" name="municipio" class="form-control" required disabled data-selected="<?php echo htmlspecialchars($municipio_val); ?>">
                    <option value="">Seleccione Municipio</option>
                </select>
            </div>
            <div class="form-group">
                <label for="parroquia">Parroquia *</label>
                <select id="parroquia" name="parroquia" class="form-control" required disabled data-selected="<?php echo htmlspecialchars($parroquia_val); ?>">
                    <option value="">Seleccione Parroquia</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="direccion">Detalle Dirección *</label>
            <textarea id="direccion" name="direccion" rows="2" required placeholder="Av. Principal, Edificio Torre A, Piso 1, Oficina 5" class="form-control"><?php echo htmlspecialchars($direccion_val); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="estatus">Estatus</label>
            <select id="estatus" name="estatus" class="form-control">
                <option value="Activo" <?php echo (!isset($c['estatus']) || $c['estatus'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                <option value="Inactivo" <?php echo (isset($c['estatus']) && $c['estatus'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>
        
        <div class="form-actions">
            <a href="<?php echo BASE_URL; ?>/views/listado_clientes.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary"><?php echo $cliente ? 'Actualizar Cliente' : 'Registrar Cliente'; ?></button>
        </div>
    </form>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- Utilidad ---
    function setStatus(fieldId, isValid, msgId = null, msg = '') {
        const iconOk = document.getElementById(`icon-${fieldId}-ok`);
        const iconError = document.getElementById(`icon-${fieldId}-error`);
        const errorMsg = msgId ? document.getElementById(msgId) : null;
        
        if (isValid) {
            if (iconOk) iconOk.style.display = 'block';
            if (iconError) iconError.style.display = 'none';
            if (errorMsg) errorMsg.style.display = 'none';
        } else {
            if (iconOk) iconOk.style.display = 'none';
            if (iconError) iconError.style.display = 'block';
            if (errorMsg) {
                errorMsg.style.display = 'block';
                if(msg) errorMsg.textContent = msg;
            }
        }
        
        // Caso especial para campos vacíos (limpiar iconos)
        const input = document.getElementById(getFieldIdFromIcon(fieldId));
        if (input && input.value.trim() === '') {
            if (iconOk) iconOk.style.display = 'none';
            if (iconError) iconError.style.display = 'none';
            if (errorMsg) errorMsg.style.display = 'none';
        }
    }
    
    function getFieldIdFromIcon(iconSuffix) {
        // Mapeo inverso simple
        if (iconSuffix === 'doc') return 'num_doc';
        if (iconSuffix === 'nombre') return 'nombre_cliente';
        if (iconSuffix === 'email') return 'email';
        if (iconSuffix === 'tlf') return 'num_tlf';
        return '';
    }

    // --- Validación Documento (RIF/Cédula) ---
    const tipoDoc = document.querySelector('select[name="tipo_doc"]');
    const numDoc = document.getElementById('num_doc');
    
    function validateDoc() {
        if (!numDoc) return;
        const val = numDoc.value;
        const tipo = tipoDoc ? tipoDoc.value : 'J';
        let isValid = false;
        
        if (tipo === 'V') {
            isValid = val.length >= 7 && val.length <= 8;
        } else if (tipo === 'E' || tipo === 'J' || tipo === 'G') {
            isValid = val.length >= 8 && val.length <= 10;
        }
        
        if (val.length > 0) {
            setStatus('doc', isValid, 'error-doc', 'Longitud incorrecta para el tipo seleccionado.');
        } else {
             setStatus('doc', true); // Reset visual state
        }
    }
    
    function updateDocLimit() {
        if (!tipoDoc || !numDoc) return;
        
        const tipo = tipoDoc.value;
        if (tipo === 'V') {
             numDoc.maxLength = 8;
             numDoc.placeholder = "12345678";
        } else {
             numDoc.maxLength = 10;
             numDoc.placeholder = "123456789";
        }
        
        if (numDoc.value.length > numDoc.maxLength) {
            numDoc.value = numDoc.value.slice(0, numDoc.maxLength);
        }
        validateDoc();
    }
    
    if(tipoDoc && numDoc) {
        tipoDoc.addEventListener('change', updateDocLimit);
        numDoc.addEventListener('input', validateDoc);
        updateDocLimit(); 
    }

    // --- Validación Nombre ---
    const nombreInput = document.getElementById('nombre_cliente');
    if (nombreInput) {
        nombreInput.addEventListener('input', function() {
            const val = this.value.trim();
            if (val.length > 0) {
                setStatus('nombre', true);
            } else {
                setStatus('nombre', true); // Reset visual state assuming empty is handled by required validation
            }
        });
    }

    // --- Validación Email ---
    const emailInput = document.getElementById('email');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const val = this.value.trim();
            if (val.length > 0) {
                const isValid = emailRegex.test(val);
                setStatus('email', isValid, 'error-email', 'Formato de correo inválido.');
            } else {
                setStatus('email', true); // Reset
            }
        });
    }

    // --- Validación Teléfono ---
    const tlfInput = document.getElementById('num_tlf');
    if (tlfInput) {
        tlfInput.addEventListener('input', function() {
            const val = this.value.replace(/[^0-9]/g, '');
            if (val.length > 0) {
                const isValid = val.length === 7;
                setStatus('tlf', isValid);
            } else {
                document.getElementById('icon-tlf-ok').style.display = 'none';
                document.getElementById('icon-tlf-error').style.display = 'none';
            }
        });
    }
    
    // Validar al inicio si hay valores
    if(nombreInput && nombreInput.value) nombreInput.dispatchEvent(new Event('input'));
    if(emailInput && emailInput.value) emailInput.dispatchEvent(new Event('input'));
    if(tlfInput && tlfInput.value) tlfInput.dispatchEvent(new Event('input'));
});
</script>
<script src="<?php echo BASE_URL; ?>/assets/js/venezuela.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
