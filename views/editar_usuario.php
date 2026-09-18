<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

if (!ControladorPrincipal::esAdministrador()) {
    $_SESSION['error'] = 'Solo los administradores pueden gestionar usuarios.';
    header('Location: ' . BASE_URL . '/views/dashboard.php');
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . '/views/gestion_roles.php');
    exit();
}

$id_usuario = intval($_GET['id']);

$modeloUsuario = new Usuario();
$usuario_editar = $modeloUsuario->obtenerPorId($id_usuario);

if (!$usuario_editar) {
    $_SESSION['error'] = 'El usuario no existe.';
    header('Location: ' . BASE_URL . '/views/gestion_roles.php');
    exit();
}

$modeloRol = new Rol();
$roles = $modeloRol->obtenerTodos();

$old_data = $_SESSION['old_data'] ?? $usuario_editar;
unset($_SESSION['old_data']);

// Separar cédula
$tipo_cedula = isset($old_data['cedula']) ? substr($old_data['cedula'], 0, 1) : 'V';
$cedula_numero = isset($old_data['cedula']) ? substr($old_data['cedula'], 2) : '';

// Separar teléfono
$codigo_operadora = isset($old_data['telefono']) ? substr($old_data['telefono'], 0, 4) : '';
$telefono_numero = isset($old_data['telefono']) ? substr($old_data['telefono'], 4) : '';

$titulo = 'Editar Usuario';
require_once __DIR__ . '/includes/header.php';
?>

<h1><i class="fas fa-user-edit"></i> Editar Usuario</h1>

<div class="mb-3">
    <a href="<?php echo BASE_URL; ?>/views/gestion_roles.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
</div>

<div class="form-container mb-3" id="form-editar-usuario">
    <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=usuario&accion=editar" method="POST">
        <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
        
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
                        <option value="V" <?php echo ($tipo_cedula == 'V') ? 'selected' : ''; ?>>V</option>
                        <option value="E" <?php echo ($tipo_cedula == 'E') ? 'selected' : ''; ?>>E</option>
                    </select>
                    <input type="text" id="cedula_numero" name="cedula_numero" required 
                           value="<?php echo htmlspecialchars($cedula_numero); ?>"
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
                        $ops = ['0412', '0414', '0416', '0422', '0424', '0426'];
                        foreach ($ops as $op) {
                            $sel = ($codigo_operadora == $op) ? 'selected' : '';
                            echo "<option value='$op' $sel>$op</option>";
                        }
                        ?>
                    </select>
                    <input type="tel" id="telefono_numero" name="telefono_numero" class="form-control" style="flex: 1;" 
                           required placeholder="1234567" 
                           maxlength="7"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                           value="<?php echo htmlspecialchars($telefono_numero); ?>">
                </div>
            </div>
        </div>

        <h3 class="mb-2 mt-2" style="font-size: 1.1rem; color: var(--color-primario); border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Datos de Cuenta</h3>
        <div class="form-row">
            <div class="form-group" style="grid-column: span 2;">
                <label for="nombre_usuario">Nombre de Usuario</label>
                <input type="text" id="nombre_usuario" name="nombre_usuario" required value="<?php echo htmlspecialchars($old_data['nombre_usuario'] ?? ''); ?>">
            </div>
            
            <div class="form-group" style="grid-column: span 2;">
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
            
            <div class="form-group" style="grid-column: span 2;">
                <label for="contrasena">Nueva Contraseña (Dejar en blanco para no cambiar)</label>
                <input type="password" id="contrasena" name="contrasena" minlength="8">
                <div id="password-requirements" style="margin-top: 10px; font-size: 0.85rem; background: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #ddd; display: none;">
                    <div id="req-length" class="req-item" style="color: #dc3545;"><i class="fas fa-times-circle"></i> Mínimo 8 caracteres</div>
                    <div id="req-upper" class="req-item" style="color: #dc3545;"><i class="fas fa-times-circle"></i> Al menos una mayúscula</div>
                    <div id="req-number" class="req-item" style="color: #dc3545;"><i class="fas fa-times-circle"></i> Al menos un número</div>
                    <div id="req-symbol" class="req-item" style="color: #dc3545;"><i class="fas fa-times-circle"></i> Al menos un símbolo</div>
                </div>
            </div>
        </div>
        
        <div class="form-actions mt-3">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Guadar Cambios</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('contrasena');
    const requirementsBox = document.getElementById('password-requirements');
    
    passwordInput.addEventListener('input', function() {
        if(this.value.length > 0) {
            requirementsBox.style.display = 'block';
        } else {
            requirementsBox.style.display = 'none';
        }
        
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
            el.style.color = '#27ae60';
            icon.className = 'fas fa-check-circle';
        } else {
            el.style.color = '#dc3545';
            icon.className = 'fas fa-times-circle';
        }
    }

    const tipoCedula = document.getElementById('tipo_cedula');
    const cedulaNumero = document.getElementById('cedula_numero');
    
    if (tipoCedula && cedulaNumero) {
        function updateCedulaLimit() {
            if (tipoCedula.value === 'V') {
                cedulaNumero.maxLength = 8;
            } else {
                cedulaNumero.maxLength = 10;
            }
            if (cedulaNumero.value.length > cedulaNumero.maxLength) {
                cedulaNumero.value = cedulaNumero.value.slice(0, cedulaNumero.maxLength);
            }
        }
        
        tipoCedula.addEventListener('change', updateCedulaLimit);
        updateCedulaLimit();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
