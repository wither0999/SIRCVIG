<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

if (!ControladorPrincipal::esAdministrador()) {
    $_SESSION['error'] = 'Solo los administradores pueden gestionar usuarios.';
    header('Location: ' . BASE_URL . '/views/dashboard.php');
    exit();
}

// Obtener datos
$modeloUsuario = new Usuario();
$modeloRol = new Rol();

$usuarios = $modeloUsuario->obtenerUsuariosConRoles();
$roles = $modeloRol->obtenerTodos();

// Recuperar datos antiguos si hubo error
$old_data = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);

$titulo = 'Gestión de Usuarios y Roles';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Contenedor de Acciones */
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: center;
        align-items: center;
    }
    
    /* Botones de Icono */
    .btn-icon {
        width: 34px; height: 34px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 6px;
        border: none; transition: all 0.2s ease;
        text-decoration: none; font-size: 0.9rem; color: white !important;
        cursor: pointer;
    }
    
    .btn-edit { background-color: #f1c40f; }
    .btn-edit:hover { background-color: #d4ac0d; transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    
    .btn-delete { background-color: #e74c3c; }
    .btn-delete:hover { background-color: #c0392b; transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    
    .btn-remove { background-color: #8e44ad; }
    .btn-remove:hover { background-color: #732d91; transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    
    .btn-success-icon { background-color: #2ecc71; }
    .btn-success-icon:hover { background-color: #27ae60; transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1 style="margin-bottom: 0;"><i class="fas fa-users-cog"></i> Gestión de Usuarios y Roles</h1>

    <div>
        <a href="<?php echo BASE_URL; ?>/views/registro_usuario.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Nuevo Usuario</a>
    </div>
</div>

<!-- Tabla de usuarios existentes -->
<div class="table-container">
    <h2>Usuarios Existentes</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Intentos Fallidos</th>
                <th>Último Acceso</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="7" class="text-center">No hay usuarios registrados</td>
                </tr>
            <?php else: ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['id_usuario']; ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></td>
                        <td>
                            <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=usuario&accion=modificar_rol" method="POST" style="display: inline;">
                                <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                                <select name="id_rol" onchange="this.form.submit()">
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo $rol['id_rol']; ?>" 
                                                <?php echo ($rol['id_rol'] == $usuario['id_rol']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($rol['nombre_rol']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <span class="badge <?php echo ($usuario['estado_cuenta'] == 'Activa') ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo $usuario['estado_cuenta']; ?>
                            </span>
                        </td>
                        <td><?php echo $usuario['intentos_fallidos']; ?></td>
                        <td><?php echo $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'Nunca'; ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?php echo BASE_URL; ?>/views/editar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>" 
                                   class="btn-icon btn-edit" title="Editar Usuario">
                                    <i class="fas fa-pen"></i>
                                </a>
                                
                                <?php if ($usuario['estado_cuenta'] == 'Activa'): ?>
                                    <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=usuario&accion=cambiar_estado" method="POST" style="display: inline;">
                                        <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                                        <input type="hidden" name="accion" value="bloquear">
                                        <button type="submit" class="btn-icon btn-delete" title="Bloquear Usuario">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=usuario&accion=cambiar_estado" method="POST" style="display: inline;">
                                        <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                                        <input type="hidden" name="accion" value="desbloquear">
                                        <button type="submit" class="btn-icon btn-success-icon" title="Desbloquear Usuario">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=usuario&accion=eliminar" method="POST" style="display: inline;" onsubmit="return confirm('¿Está seguro de que desea eliminar este usuario de forma permanente? Esta acción no se puede deshacer.');">
                                    <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                                    <button type="submit" class="btn-icon btn-remove" title="Eliminar Usuario">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll automático al error
    const errorAlert = document.querySelector('.alert-error');
    if (errorAlert) {
        // Scroll hacia el formulario para ver los campos que faltan, el error está arriba en el header
        const form = document.getElementById('form-crear-usuario');
        if (form) {
             form.scrollIntoView({ behavior: 'smooth', block: 'center' });
             // Pequeña animación para resaltar
             form.style.border = "2px solid #dc3545";
             setTimeout(() => { form.style.border = "none"; }, 1500);
        }
    }

    // Validación de Contraseña en Vivo
    const passwordInput = document.getElementById('contrasena');
    const requirementsBox = document.getElementById('password-requirements');
    
    // Mostrar requisitos al enfocar
    passwordInput.addEventListener('focus', () => {
        // requirementsBox.style.display = 'block'; // Ya está visible en layout, pero si queremos ocultarlo:
    });

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

