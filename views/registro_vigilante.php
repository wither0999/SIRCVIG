<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

if (!ControladorPrincipal::esAdministrador() && !ControladorPrincipal::esSecretario()) {
    $_SESSION['error'] = 'No tiene permisos para crear vigilantes.';
    header('Location: ' . BASE_URL . '/views/listado_vigilantes.php');
    exit();
}

$titulo = 'Registro de Vigilante';
require_once __DIR__ . '/includes/header.php';
?>

<?php
$es_edicion = isset($es_edicion) && $es_edicion;
// Recuperar datos de sesión si existen (para repoblar formulario en error)
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']); // Limpiar después de usar

// Prioridad de datos: 1. Old Input (Error), 2. Datos DB (Edición), 3. Vacío
$v = !empty($old_input) ? $old_input : ($vigilante ?? []);

// Mapear claves old_input a claves de DB si difieren, o usar las mismas
// En old_input (POST) claves son: nombres, apellidos, cedula_numero, fecha_nacimiento, etc.
// En DB: nombres, apellidos, cedula, fecha_nacimiento
// Ajuste para cedula:
if (isset($v['cedula'])) {
    $cedula_full = $v['cedula'];
} elseif (isset($v['tipo_cedula']) && isset($v['cedula_numero'])) {
    $cedula_full = $v['tipo_cedula'] . '-' . $v['cedula_numero'];
} else {
    $cedula_full = '';
}

// Ajuste para telefono
if (isset($v['telefono'])) {
    $telefono_full = $v['telefono'];
} elseif (isset($v['codigo_operadora']) && isset($v['telefono_numero'])) {
    $telefono_full = $v['codigo_operadora'] . $v['telefono_numero'];
} else {
    $telefono_full = '';
}

$nombre_val = $v['nombres'] ?? '';
$apellido_val = $v['apellidos'] ?? '';
$fecha_val = $v['fecha_nacimiento'] ?? '';

// Dirección
$estado_val = $v['estado'] ?? '';
$municipio_val = $v['municipio'] ?? '';
$parroquia_val = $v['parroquia'] ?? '';
$detalle_val = $v['direccion'] ?? '';
?>

<style>
    /* Estilos para iconos de validación centrados */
    .validation-icon {
        position: absolute;
        right: 15px; /* Un poco mas de margen */
        top: 50%; /* Centrar verticalmente */
        transform: translateY(-50%); /* Ajuste fino */
        font-size: 1.2em;
        display: none;
        z-index: 10;
        pointer-events: none;
    }
    .form-group {
        position: relative; 
    }
    /* El contenedor del input con prefijo (flex) necesita position relative también */
    .input-with-icon {
        position: relative; 
        flex: 1; /* Ocupa espacio restante en flex container */
    }
    .valid-feedback-icon {
        color: #27ae60;
    }
    .invalid-feedback-icon {
        color: #e74c3c;
    }
    
    /* Asegurar que el input tenga espacio para el icono */
    .form-control {
        padding-right: 35px;
    }
</style>

<h1><?php echo $es_edicion ? 'Editar Vigilante' : 'Registro de Nuevo Vigilante'; ?></h1>

<div class="form-container">
    <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=expediente&accion=<?php echo $es_edicion ? 'procesar_edicion' : 'procesar_registro'; ?>" method="POST" enctype="multipart/form-data" id="formVigilante">
        
        <?php if ($es_edicion): ?>
            <input type="hidden" name="cedula_original" value="<?php echo htmlspecialchars($v['cedula'] ?? ''); ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="nombres">Nombres *</label>
                <div class="input-with-icon" style="width: 100%;">
                    <input type="text" id="nombres" name="nombres" value="<?php echo htmlspecialchars($nombre_val); ?>" required
                        minlength="4" maxlength="20"
                        class="form-control"
                        placeholder="Ej. Juan Jose">
                    <i class="fas fa-check-circle validation-icon valid-feedback-icon" id="icon-nombres-ok"></i>
                    <i class="fas fa-times-circle validation-icon invalid-feedback-icon" id="icon-nombres-error"></i>
                </div>
                <small class="text-danger" id="error-nombres" style="display:none;">Mínimo 4 caracteres. Solo letras.</small>
            </div>
            
            <div class="form-group">
                <label for="apellidos">Apellidos *</label>
                <div class="input-with-icon" style="width: 100%;">
                    <input type="text" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($apellido_val); ?>" required
                        minlength="4" maxlength="20"
                        class="form-control"
                        placeholder="Ej. Perez Garcia">
                    <i class="fas fa-check-circle validation-icon valid-feedback-icon" id="icon-apellidos-ok"></i>
                    <i class="fas fa-times-circle validation-icon invalid-feedback-icon" id="icon-apellidos-error"></i>
                </div>
                <small class="text-danger" id="error-apellidos" style="display:none;">Mínimo 4 caracteres. Solo letras.</small>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="cedula_numero">Cédula *</label>
                <div style="display: flex; gap: 10px;">
                    <?php
                    $tipo_sel = 'V'; 
                    $num_val = $cedula_full;
                    if (!empty($cedula_full) && strpos($cedula_full, '-') !== false) {
                        list($tipo_sel, $num_val) = explode('-', $cedula_full, 2);
                    }
                    ?>
                    <select id="tipo_cedula" name="tipo_cedula" class="form-control" style="width: 80px;" <?php echo $es_edicion ? 'disabled' : 'required'; ?>>
                        <option value="V" <?php echo ($tipo_sel == 'V') ? 'selected' : ''; ?>>V</option>
                        <option value="E" <?php echo ($tipo_sel == 'E') ? 'selected' : ''; ?>>E</option>
                    </select>
                    <?php if($es_edicion): ?><input type="hidden" name="tipo_cedula" value="<?php echo $tipo_sel; ?>"><?php endif; ?>
                    
                    <div class="input-with-icon">
                         <input type="text" id="cedula_numero" name="cedula_numero" class="form-control"
                               value="<?php echo htmlspecialchars($num_val); ?>" 
                               <?php echo $es_edicion ? 'readonly' : 'required'; ?>
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <i class="fas fa-check-circle validation-icon valid-feedback-icon" id="icon-cedula-ok"></i>
                        <i class="fas fa-times-circle validation-icon invalid-feedback-icon" id="icon-cedula-error"></i>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" 
                       value="<?php echo htmlspecialchars($fecha_val); ?>" 
                       min="1950-01-01" max="<?php echo date('Y-m-d'); ?>"
                       class="form-control"
                       required>
                <small class="text-muted" id="edad-calc"></small>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="telefono_numero">Teléfono</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <?php
                    $pref_sel = '';
                    $num_tlf = '';
                    
                    if (!empty($telefono_full)) {
                        $clean = preg_replace('/[^0-9]/', '', $telefono_full); 
                        $prefixes = ['0412', '0414', '0416', '0422', '0424', '0426'];
                        foreach ($prefixes as $p) {
                            if (strpos($clean, $p) === 0) {
                                $pref_sel = $p;
                                $num_tlf = substr($clean, strlen($p));
                                break;
                            }
                        }
                        if (empty($pref_sel) && strlen($clean) >= 4) {
                             $pref_sel = substr($clean, 0, 4);
                             $num_tlf = substr($clean, 4);
                        } elseif (empty($pref_sel)) {
                             $num_tlf = $clean;
                        }
                    }
                    ?>
                    <select id="codigo_operadora" name="codigo_operadora" class="form-control" style="width: 100px;" required>
                        <option value="">Cód</option>
                        <?php 
                        $ops = ['0412', '0414', '0416', '0422', '0424', '0426'];
                        foreach($ops as $op) {
                            $sel = ($pref_sel == $op) ? 'selected' : '';
                            echo "<option value='$op' $sel>$op</option>";
                        }
                        ?>
                    </select>
                    
                    <div class="input-with-icon">
                        <input type="text" id="telefono_numero" name="telefono_numero" class="form-control"
                               value="<?php echo htmlspecialchars($num_tlf); ?>" 
                               maxlength="7"
                               placeholder="1234567"
                               required
                               oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        <i class="fas fa-check-circle validation-icon valid-feedback-icon" id="icon-tlf-ok"></i>
                        <i class="fas fa-times-circle validation-icon invalid-feedback-icon" id="icon-tlf-error"></i>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="estatus">Estatus</label>
                <select id="estatus" name="estatus" class="form-control">
                    <option value="Aspirante" <?php echo (isset($v['estatus']) && $v['estatus'] == 'Aspirante') ? 'selected' : ''; ?>>Aspirante</option>
                    <option value="Activo" <?php echo (isset($v['estatus']) && $v['estatus'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="Inactivo" <?php echo (isset($v['estatus']) && $v['estatus'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
        </div>
        
        <h3 class="mt-2 mb-2">Dirección de Habitación</h3>
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
            <label for="direccion_detalle">Detalle (Calle, Casa, Punto de Referencia) *</label>
            <textarea id="direccion_detalle" name="direccion" rows="2" class="form-control" required placeholder="Av. Principal, Casa Nro. 123..."><?php echo htmlspecialchars($detalle_val); ?></textarea>
        </div>
        
        <h3 class="mt-3 mb-2">Documentos *</h3>
        <?php if (!$es_edicion): ?>
        <div class="alert alert-info py-2">Todos los documentos son obligatorios para el registro.</div>
        <?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label for="foto">Fotografía (JPG/PNG) *</label>
                <input type="file" id="foto" name="foto" class="form-control-file" accept="image/jpeg,image/png,image/jpg" <?php echo $es_edicion ? '' : 'required'; ?>>
                <?php if ($es_edicion): ?>
                    <small class="text-muted">Deje en blanco para mantener la actual.</small>
                <?php endif; ?>
                <div id="error-foto" class="text-danger" style="display:none;">Este documento es obligatorio.</div>
            </div>
            
            <div class="form-group">
                <label for="cedula_escaneada">Cédula Escaneada (JPG/PNG/PDF) *</label>
                <input type="file" id="cedula_escaneada" name="cedula_escaneada" class="form-control-file" accept="image/jpeg,image/png,image/jpg,application/pdf" <?php echo $es_edicion ? '' : 'required'; ?>>
                <?php if ($es_edicion): ?>
                    <small class="text-muted">Deje en blanco para mantener la actual.</small>
                <?php endif; ?>
                <div id="error-cedula_escaneada" class="text-danger" style="display:none;">Este documento es obligatorio.</div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="antecedentes">Antecedentes Penales (PDF) *</label>
            <input type="file" id="antecedentes" name="antecedentes" class="form-control-file" accept="application/pdf" <?php echo $es_edicion ? '' : 'required'; ?>>
            <?php if ($es_edicion): ?>
                <small class="text-muted">Deje en blanco para mantener la actual.</small>
            <?php endif; ?>
            <div id="error-antecedentes" class="text-danger" style="display:none;">Este documento es obligatorio.</div>
        </div>
        
        <div class="form-actions">
            <a href="<?php echo BASE_URL; ?>/views/listado_vigilantes.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary" id="btn-submit"><?php echo $es_edicion ? 'Guardar Cambios' : 'Registrar Vigilante'; ?></button>
        </div>
    </form>
</div>
<script src="<?php echo BASE_URL; ?>/assets/js/venezuela.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- Funciones de Utilidad ---
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
    }

    // --- Validaciones de Nombre y Apellido ---
    const regexNombres = /^[a-zA-Z\u00C0-\u00FF\s]+$/; // Letras y espacios (incluye acentos)
    
    function validateNameField(inputId, min, max) {
        const input = document.getElementById(inputId);
        if (!input) return;

        function check() {
            const val = input.value; 
            const cleanVal = val; 
            const hasInvalidChars = !regexNombres.test(cleanVal) && cleanVal.length > 0;
            const isLengthValid = cleanVal.length >= min && cleanVal.length <= max;
            
            if (hasInvalidChars) {
                 setStatus(inputId, false, `error-${inputId}`, 'Solo se permiten letras.');
                 // No retornamos false inmediatamente para permitir que el usuario borre
            }
            
            if (cleanVal.length > 0 && cleanVal.length < min) {
                setStatus(inputId, false, `error-${inputId}`, `Mínimo ${min} caracteres.`);
                return false;
            }

            if (isLengthValid && !hasInvalidChars) {
                setStatus(inputId, true, `error-${inputId}`);
                return true;
            } else if (cleanVal.length === 0) {
                document.getElementById(`icon-${inputId}-ok`).style.display = 'none';
                document.getElementById(`icon-${inputId}-error`).style.display = 'none';
                document.getElementById(`error-${inputId}`).style.display = 'none';
                return false;
            }
            return false;
        }

        input.addEventListener('input', check);
        input.addEventListener('blur', check); 
        
        // Bloqueo de teclas estrictamente no letras (opcional)
        input.addEventListener('keypress', function(e) {
             // Dejamos pasar para que el usuario vea el error visual (o se puede descomentar para ser estricto)
             /* if (!/[a-zA-Z\u00C0-\u00FF\s]/.test(e.key)) {
                e.preventDefault();
             } */
        });
    }

    validateNameField('nombres', 4, 20);
    validateNameField('apellidos', 4, 20);

    // --- Validación de Cédula (V: 8 digitos max, E: 10 max) ---
    const tipoCedula = document.getElementById('tipo_cedula');
    const cedulaInput = document.getElementById('cedula_numero');
    
    function updateCedulaMaxLength() {
        if (!cedulaInput) return;
        const tipo = tipoCedula ? tipoCedula.value : 'V';
        
        if (tipo === 'V') {
            cedulaInput.maxLength = 8;
            cedulaInput.placeholder = ""; //Placeholder limpio
        } else {
            cedulaInput.maxLength = 10;
        }
        
        // Truncar si se cambió el tipo y excede
        if (cedulaInput.value.length > cedulaInput.maxLength) {
            cedulaInput.value = cedulaInput.value.slice(0, cedulaInput.maxLength);
        }
        validateCedula();
    }

    function validateCedula() {
        if (!cedulaInput) return;
        const val = cedulaInput.value;
        const tipo = tipoCedula ? tipoCedula.value : 'V';
        let isValid = false;
        
        // V: Mínimo 7, Máximo 8
        if (tipo === 'V') {
            isValid = val.length >= 7 && val.length <= 8;
        } else {
             isValid = val.length >= 1 && val.length <= 10;
        }

        if (val.length > 0) {
            setStatus('cedula', isValid);
        } else {
             document.getElementById('icon-cedula-ok').style.display = 'none';
             document.getElementById('icon-cedula-error').style.display = 'none';
        }
    }

    if (cedulaInput) {
        if (tipoCedula) {
            tipoCedula.addEventListener('change', updateCedulaMaxLength);
        }
        cedulaInput.addEventListener('input', function() {
            // Asegurar comportamiento maxLength extra
            if (this.value.length > this.maxLength) {
                this.value = this.value.slice(0, this.maxLength);
            }
            validateCedula();
        });
        
        // Inicializar
        updateCedulaMaxLength();
    }

    // --- Validación Fecha Nacimiento ---
    const fechaInput = document.getElementById('fecha_nacimiento');
    const edadMsg = document.getElementById('edad-calc');
    
    if (fechaInput) {
        fechaInput.addEventListener('change', function() {
            const fecha = new Date(this.value);
            const hoy = new Date();
            const minDate = new Date('1950-01-01');

            if (fecha < minDate) {
               alert('El año de nacimiento debe ser mayor a 1950');
               this.value = ''; // Limpiar o resetear
               edadMsg.textContent = '';
               return;
            }

            let edad = hoy.getFullYear() - fecha.getFullYear();
            const m = hoy.getMonth() - fecha.getMonth();
            if (m < 0 || (m === 0 && hoy.getDate() < fecha.getDate())) {
                edad--;
            }
            
            if (this.value) {
                edadMsg.textContent = `Edad: ${edad} años`;
                if (edad < 18) {
                    edadMsg.className = 'text-danger font-weight-bold';
                    edadMsg.textContent += ' (Menor de edad)';
                } else {
                    edadMsg.className = 'text-success font-weight-bold';
                }
            } else {
                edadMsg.textContent = '';
            }
        });
    }

    // --- Validación Teléfono ---
    const tlfInput = document.getElementById('telefono_numero');
    function validatePhone() {
        if(!tlfInput) return;
        const val = tlfInput.value;
        const isValid = val.length === 7;
         if (val.length > 0) {
            setStatus('tlf', isValid);
        } else {
             document.getElementById('icon-tlf-ok').style.display = 'none';
             document.getElementById('icon-tlf-error').style.display = 'none';
        }
    }
    if(tlfInput) tlfInput.addEventListener('input', validatePhone);

    // --- Validación Documentos al Enviar ---
    const form = document.getElementById('formVigilante');
    form.addEventListener('submit', function(e) {
        // Validar documentos requeridos (solo si no es edicion o si se requiere logica extra)
        // El atributo 'required' de HTML hace el trabajo pesado, pero podemos añadir feedback visual personalizado
        const docs = ['foto', 'cedula_escaneada', 'antecedentes'];
        let hasError = false;

        <?php if (!$es_edicion): ?>
        docs.forEach(id => {
            const input = document.getElementById(id);
            const errorDiv = document.getElementById(`error-${id}`);
            if (input && input.files.length === 0) {
                if(errorDiv) errorDiv.style.display = 'block';
                hasError = true;
            } else {
                if(errorDiv) errorDiv.style.display = 'none';
            }
        });
        <?php endif; ?>

        if (hasError) {
            e.preventDefault();
            alert('Faltan documentos obligatorios.');
        }
    });

    // Run validations on load if values exist
    validateNameField('nombres', 4, 20);
    validateNameField('apellidos', 4, 20);
    validateCedula();
    validatePhone();
    if(fechaInput && fechaInput.value) fechaInput.dispatchEvent(new Event('change'));

});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
