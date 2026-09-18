<?php require_once __DIR__ . '/../../config/autoload.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - SIRCVIG</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/logo.png">
    <style>
        /* Estilos inline para asegurar consistencia con el login "box" style */
        body { background-color: #F0F4F8; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .recovery-card { background: white; width: 100%; max-width: 400px; padding: 2.5rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .recovery-header { text-align: center; margin-bottom: 2rem; }
        .recovery-header h2 { color: #21618C; font-size: 1.5rem; margin-bottom: 0.5rem; }
        .recovery-header p { color: #7f8c8d; font-size: 0.9rem; }
        .btn-back { display: block; text-align: center; margin-top: 1rem; color: #5DADE2; text-decoration: none; font-size: 0.9rem; }
        .btn-back:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="recovery-card">
        <div class="recovery-header">
            <h2>Recuperar Contraseña</h2>
            <p>Ingrese su número de cédula para buscar su cuenta</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/index.php?controller=Recuperacion&action=buscar" method="POST">
            <div class="form-group">
                <label for="cedula_numero"><i class="fas fa-id-card"></i> Cédula</label>
                <div style="display: flex; gap: 10px;">
                    <select id="tipo_cedula" name="tipo_cedula" style="width: 80px; padding: 0.75rem; border: 2px solid #5DADE2; border-radius: 5px;" required>
                        <option value="V">V</option>
                        <option value="E">E</option>
                    </select>
                    <input type="text" id="cedula_numero" name="cedula_numero" required placeholder="" class="form-control" 
                           style="flex: 1; padding: 0.75rem; border: 2px solid #5DADE2; border-radius: 5px;"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Buscar Cuenta</button>
            <a href="<?php echo BASE_URL; ?>" class="btn-back"> <i class="fas fa-arrow-left"></i> Volver al Login</a>
        </form>
    </div>
<script>
    const tipoCedula = document.getElementById('tipo_cedula');
    const cedulaNumero = document.getElementById('cedula_numero');
    
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
</script>
</body>
</html>
