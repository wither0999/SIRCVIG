<?php require_once __DIR__ . '/../../config/autoload.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - SIRCVIG</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/logo.png">
    <style>
        body { background-color: #F0F4F8; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .recovery-card { background: white; width: 100%; max-width: 400px; padding: 2.5rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .recovery-header { text-align: center; margin-bottom: 2rem; }
        .recovery-header h2 { color: #21618C; font-size: 1.5rem; margin-bottom: 0.5rem; }
        .recovery-header p { color: #7f8c8d; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="recovery-card">
        <div class="recovery-header">
            <h2>Nueva Contraseña</h2>
            <p>Cree una nueva contraseña segura</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/index.php?controller=Recuperacion&action=actualizar" method="POST">
            <div class="form-group">
                <label for="contrasena"><i class="fas fa-lock"></i> Nueva Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required minlength="8" placeholder="Mínimo 8 caracteres" class="form-control" style="width: 100%; padding: 0.75rem; border: 2px solid #5DADE2; border-radius: 5px;">
                
                <div id="password-requirements" style="margin-top: 10px; font-size: 0.85rem; background: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                    <div id="req-length" class="req-item" style="color: #dc3545;"><i class="fas fa-times-circle"></i> Mínimo 8 caracteres</div>
                    <div id="req-upper" class="req-item" style="color: #dc3545;"><i class="fas fa-times-circle"></i> Al menos una mayúscula</div>
                    <div id="req-number" class="req-item" style="color: #dc3545;"><i class="fas fa-times-circle"></i> Al menos un número</div>
                    <div id="req-symbol" class="req-item" style="color: #dc3545;"><i class="fas fa-times-circle"></i> Al menos un símbolo</div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirmar_contrasena"><i class="fas fa-lock"></i> Confirmar Contraseña</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required minlength="6" placeholder="Repita la contraseña" class="form-control" style="width: 100%; padding: 0.75rem; border: 2px solid #5DADE2; border-radius: 5px;">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Actualizar Contraseña</button>
        </form>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
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
            el.style.color = '#27ae60';
            icon.className = 'fas fa-check-circle';
        } else {
            el.style.color = '#dc3545';
            icon.className = 'fas fa-times-circle';
        }
    }
});
</script>
</body>
</html>
