require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

ControladorPrincipal::verificarSesion();

$usuarioModel = new Usuario();
$rolModel = new Rol();

$id_usuario = $_SESSION['id_usuario'];
$usuario = $usuarioModel->obtenerPorId($id_usuario);

if (!$usuario) {
    session_destroy();
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

$rol = $rolModel->obtenerPorId($usuario['id_rol']);
$usuario['nombre_rol'] = $rol['nombre_rol'] ?? 'Usuario';

$titulo = 'Mi Perfil';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h2 class="text-center" style="color: var(--color-primario); font-weight: 700;">
                    <i class="fas fa-user-circle"></i> Mi Perfil
                </h2>
                <p class="text-center text-muted">Gestiona tu información personal y seguridad</p>
            </div>
            
            <div class="card-body p-4">
                <form action="<?php echo BASE_URL; ?>/views/acciones.php?modulo=usuario&accion=procesar_perfil" method="POST" id="formPerfil">
                    
                    <!-- Sección Datos Personales -->
                    <h5 class="text-primary mb-3 border-bottom pb-2"><i class="fas fa-id-card"></i> Datos Personales</h5>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Nombre de Usuario</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" disabled style="background-color: #f8f9fa;">
                            <small class="text-muted">No editable</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Rol</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre_rol']); ?>" disabled style="background-color: #f8f9fa;">
                            <small class="text-muted">Gestionado por el administrador</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="nombres">Nombres *</label>
                            <input type="text" id="nombres" name="nombres" class="form-control" 
                                   value="<?php echo htmlspecialchars($usuario['nombres']); ?>" required minlength="3" maxlength="25"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\u00C0-\u00FF\s]/g, '')">
                            <small class="text-muted">Solo letras (3-25 caracteres)</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="apellidos">Apellidos *</label>
                            <input type="text" id="apellidos" name="apellidos" class="form-control" 
                                   value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required minlength="3" maxlength="25"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\u00C0-\u00FF\s]/g, '')">
                            <small class="text-muted">Solo letras (3-25 caracteres)</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" class="form-control" 
                               value="<?php echo htmlspecialchars($usuario['telefono']); ?>"
                               placeholder="04241234567" digits>
                    </div>

                    <!-- Sección Seguridad -->
                    <h5 class="text-primary mt-4 mb-3 border-bottom pb-2"><i class="fas fa-lock"></i> Cambiar Contraseña</h5>
                    
                    <div class="alert alert-light border" role="alert">
                        <i class="fas fa-info-circle text-info"></i> Deje estos campos vacíos si no desea cambiar su contraseña.
                    </div>

                    <div class="form-group">
                        <label for="password_actual">Contraseña Actual</label>
                        <input type="password" id="password_actual" name="password_actual" class="form-control" placeholder="Ingrese su contraseña actual para verificar">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="password_nueva">Nueva Contraseña</label>
                            <input type="password" id="password_nueva" name="password_nueva" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="password_confirmar">Confirmar Nueva Contraseña</label>
                            <input type="password" id="password_confirmar" name="password_confirmar" class="form-control">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?php echo BASE_URL; ?>/views/dashboard.php" class="btn btn-secondary px-4">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
// Mostrar SweetAlert si hay éxito
if (isset($_SESSION['exito'])) {
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '" . $_SESSION['exito'] . "',
            confirmButtonColor: 'var(--color-primario)'
        });
    </script>";
    unset($_SESSION['exito']);
}

// Mostrar SweetAlert si hay error
if (isset($_SESSION['error'])) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '" . $_SESSION['error'] . "',
            confirmButtonColor: '#d33'
        });
    </script>";
    unset($_SESSION['error']);
}
?>

<!-- Validación Frontend Extra -->
<script>
document.getElementById('formPerfil').addEventListener('submit', function(e) {
    const passNueva = document.getElementById('password_nueva').value;
    const passConf = document.getElementById('password_confirmar').value;
    const passActual = document.getElementById('password_actual').value;

    if (passNueva || passConf) {
        if (!passActual) {
            e.preventDefault();
            Swal.fire('Error', 'Debe ingresar su contraseña actual para realizar cambios de seguridad.', 'warning');
            return;
        }
        if (passNueva !== passConf) {
            e.preventDefault();
            Swal.fire('Error', 'Las nuevas contraseñas no coinciden.', 'error');
            return;
        }
        if (passNueva.length < 8) {
            e.preventDefault();
            Swal.fire('Seguridad', 'La nueva contraseña debe tener al menos 8 caracteres.', 'warning');
            return;
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
