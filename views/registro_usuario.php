<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

if (!ControladorPrincipal::esAdministrador()) {
    $_SESSION['error'] = 'Solo los administradores pueden gestionar usuarios.';
    header('Location: ' . BASE_URL . '/views/dashboard.php');
    exit();
}

// Obtener roles para el selector
$modeloRol = new Rol();
$roles = $modeloRol->obtenerTodos();

// Recuperar datos antiguos si hubo error (desde ControladorUsuario.php)
$old_data = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);

$titulo = 'Registro de Usuario';
require_once __DIR__ . '/includes/header.php';
?>

<h1><i class="fas fa-user-plus"></i> Registro de Nuevo Usuario</h1>

<div class="mb-3">
    <a href="<?php echo BASE_URL; ?>/views/gestion_roles.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
</div>

<!-- Formulario para crear nuevo usuario -->
<div class="form-container mb-3" id="form-crear-usuario">
    <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=usuario&accion=crear" method="POST">
        <!-- Información Personal -->
        <h3 class="mb-2" style="font-size: 1.1rem; color: var(--color-primario); border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Información Personal</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="nombres">Nombres</label>
                <input type="text" id="nombres" name="nombres" required placeholder="Ej: Juan Antonio" 
                       minlength="3" maxlength="25" oninput="this.value = this.value.replace(/[^a-zA-Z\u00C0-\u00FF\s]/g, '')"
                       value="<?php echo htmlspecialchars($old_data['nombres'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos" required placeholder="Ej: Pérez Lopez" 
                       minlength="3" maxlength="25" oninput="this.value = this.value.replace(/[^a-zA-Z\u00C0-\u00FF\s]/g, '')"
                       value="<?php echo htmlspecialchars($old_data['apellidos'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="cedula_numero">Cédula</label>
                <div style="display: flex; gap: 10px;">
                    <select id="tipo_cedula" name="tipo_cedula" style="width: 80px; padding: 0.75rem; border: 2px solid var(--color-secundario); border-radius: 5px;" required>
                        <option value="V" <?php echo (isset($old_data['tipo_cedula']) && $old_data['tipo_cedula'] == 'V') ? 'selected' : ''; ?>>V</option>
                        <option value="E" <?php echo (isset($old_data['tipo_cedula']) && $old_data['tipo_cedula'] == 'E') ? 'selected' : ''; ?>>E</option>
                    </select>
                    <input type="text" id="cedula_numero" name="cedula_numero" required placeholder="" 
                           value="<?php echo htmlspecialchars($old_data['cedula_numero'] ?? ''); ?>"
                           style="flex: 1;"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com" value="<?php echo htmlspecialchars($old_data['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <div style="display: flex; gap: 10px;">
                    <select name="codigo_operadora" class="form-control" style="width: 100px;" required>
                        <option value="">Cód</option>
                        <?php
                        $old_cod = $old_data['codigo_operadora'] ?? '';
                        $ops = ['0412', '0414', '0416', '0422', '0424', '0426'];
                        foreach ($ops as $op) {
                            $sel = ($old_cod == $op) ? 'selected' : '';
                            echo "<option value='$op' $sel>$op</option>";
                        }
                        ?>
                    </select>
                    <input type="tel" id="telefono_numero" name="telefono_numero" class="form-control" style="flex: 1;" 
                           required placeholder="1234567" 
                           maxlength="7"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                           value="<?php echo htmlspecialchars($old_data['telefono_numero'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <!-- Datos de Cuenta -->
        <h3 class="mb-2 mt-2" style="font-size: 1.1rem; color: var(--color-primario); border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Datos de Cuenta</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="nombre_usuario">Nombre de Usuario</label>
                <input type="text" id="nombre_usuario" name="nombre_usuario" required value="<?php echo htmlspecialchars($old_data['nombre_usuario'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required minlength="8">
                <!-- Checklist de Contraseña -->
                <div id="password-requirements" style="margin-top: 10px; font-size: 0.85rem; background: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                    <div id="req-length" class="req-item" style="color: #dc3545;"><i class="fas fa-times-circle"></i> Mínimo 8 caracteres</div>
                    <div id="req-upper" class="req-item" style="color: #dc3545;"><i class="fas fa-times-circle"></i> Al menos una mayúscula</div>
                    <div id="req-number" class="req-item" style="color: #dc3545;"><i class="fas fa-times-circle"></i> Al menos un número</div>
                    <div id="req-symbol" class="req-item" style="color: #dc3545;"><i class="fas fa-times-circle"></i> Al menos un símbolo</div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="id_rol">Rol del Usuario</label>
                <select id="id_rol" name="id_rol" required>
                    <option value="">Seleccione un rol</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?php echo $rol['id_rol']; ?>" <?php echo (isset($old_data['id_rol']) && $old_data['id_rol'] == $rol['id_rol']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($rol['nombre_rol']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Seguridad -->
        <h3 class="mb-2 mt-2" style="font-size: 1.1rem; color: var(--color-primario); border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Preguntas de Seguridad</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="pregunta_seguridad">Pregunta de Seguridad 1</label>
                <select id="pregunta_seguridad" name="pregunta_seguridad" required>
                    <option value="">Seleccione una pregunta</option>
                    <?php 
                    $preguntas1 = [
                        "¿Cuál es el nombre de tu primera mascota?",
                        "¿En qué ciudad naciste?",
                        "¿Cuál es el segundo nombre de tu padre?",
                        "¿Cuál es tu comida favorita?"
                    ];
                    foreach($preguntas1 as $p) {
                        $selected = (isset($old_data['pregunta_seguridad']) && $old_data['pregunta_seguridad'] == $p) ? 'selected' : '';
                        echo "<option value='$p' $selected>$p</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="respuesta_seguridad">Respuesta 1</label>
                <input type="text" id="respuesta_seguridad" name="respuesta_seguridad" required placeholder="Tu respuesta secreta" value="<?php echo htmlspecialchars($old_data['respuesta_seguridad'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="pregunta_seguridad_2">Pregunta de Seguridad 2</label>
                <select id="pregunta_seguridad_2" name="pregunta_seguridad_2" required>
                    <option value="">Seleccione una pregunta</option>
                    <?php 
                    $preguntas2 = [
                        "¿Cuál es el nombre de tu mejor amigo de la infancia?",
                        "¿Cuál es el nombre de tu colegio de primaria?",
                        "¿Cuál es tu película favorita?",
                        "¿Cómo se llamaba tu primer jefe?"
                    ];
                    foreach($preguntas2 as $p) {
                        $selected = (isset($old_data['pregunta_seguridad_2']) && $old_data['pregunta_seguridad_2'] == $p) ? 'selected' : '';
                        echo "<option value='$p' $selected>$p</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="respuesta_seguridad_2">Respuesta 2</label>
                <input type="text" id="respuesta_seguridad_2" name="respuesta_seguridad_2" required placeholder="Tu respuesta secreta" value="<?php echo htmlspecialchars($old_data['respuesta_seguridad_2'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Registrar Usuario</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll automático al error
    const errorAlert = document.querySelector('.alert-error');
    if (errorAlert) {
         // Ya no necesitamos scroll porque es una página dedicada, pero por si acaso
         const form = document.getElementById('form-crear-usuario');
         if (form) {
             // form.scrollIntoView({ behavior: 'smooth', block: 'center' }); // Opcional en pagina dedicada
         }
    }

    // Validación de Contraseña en Vivo
    const passwordInput = document.getElementById('contrasena');
    const requirementsBox = document.getElementById('password-requirements');
    
    passwordInput.addEventListener('input', function() {
        const val = this.value;
        const reqs = {
            length: val.length >= 8,
            upper: /[A-Z]/.test(val),
            number: /\d/.test(val),
            symbol: /[\W_]/.test(val)
        };
        
        updateReq('req-length', reqs.length);
        updateReq('req-upper', reqs.upper);
        updateReq('req-number', reqs.number);
        updateReq('req-symbol', reqs.symbol);
    });
    
    function updateReq(id, valid) {
        const el = document.getElementById(id);
        const icon = el.querySelector('i');
        if (valid) {
            el.style.color = '#27ae60'; // Verde
            icon.className = 'fas fa-check-circle';
        } else {
            el.style.color = '#dc3545'; // Rojo
            icon.className = 'fas fa-times-circle';
        }
    }

    // Validación de Cédula (V=8, E=10)
    const tipoCedula = document.getElementById('tipo_cedula');
    const cedulaNumero = document.getElementById('cedula_numero');
    
    if (tipoCedula && cedulaNumero) {
        function updateCedulaLimit() {
            if (tipoCedula.value === 'V') {
                cedulaNumero.maxLength = 8;
            } else {
                cedulaNumero.maxLength = 10;
            }
            // Truncate if needed
            if (cedulaNumero.value.length > cedulaNumero.maxLength) {
                cedulaNumero.value = cedulaNumero.value.slice(0, cedulaNumero.maxLength);
            }
        }
        
        tipoCedula.addEventListener('change', updateCedulaLimit);
        // Initial call
        updateCedulaLimit();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
