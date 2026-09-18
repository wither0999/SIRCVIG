<?php require_once __DIR__ . '/../../config/autoload.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pregunta de Seguridad - SIRCVIG</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/logo.png">
    <style>
        body { background-color: #F0F4F8; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .recovery-card { background: white; width: 100%; max-width: 400px; padding: 2.5rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .recovery-header { text-align: center; margin-bottom: 2rem; }
        .recovery-header h2 { color: #21618C; font-size: 1.5rem; margin-bottom: 0.5rem; }
        .recovery-header p { color: #7f8c8d; font-size: 0.9rem; }
        .question-box { background: #e8f4f8; padding: 1rem; border-radius: 5px; border-left: 4px solid #21618C; margin-bottom: 1.5rem; color: #2c3e50; font-weight: 600; }
    </style>
</head>
<body>
    <div class="recovery-card">
        <div class="recovery-header">
            <h2>Seguridad</h2>
            <p>Responda su pregunta de seguridad para continuar</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="question-box">
            <i class="fas fa-question-circle"></i> <?php echo $_SESSION['recuperacion_pregunta']; ?>
        </div>

        <form action="<?php echo BASE_URL; ?>/index.php?controller=Recuperacion&action=verificar" method="POST">
            <div class="form-group">
                <label for="respuesta"><i class="fas fa-key"></i> Respuesta</label>
                <input type="text" id="respuesta" name="respuesta" required placeholder="Su respuesta" class="form-control" style="width: 100%; padding: 0.75rem; border: 2px solid #5DADE2; border-radius: 5px;">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Verificar</button>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=Recuperacion" style="display: block; text-align: center; margin-top: 1rem; color: #7f8c8d; text-decoration: none; font-size: 0.9rem;">Volver</a>
        </form>
    </div>
</body>
</html>
