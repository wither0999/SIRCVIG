<?php
require_once __DIR__ . '/../config/autoload.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRCVIG - Sistema de Gestión de Vigilancia</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/estilos.css">
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/logo.png">
</head>
<body>
    <div class="login-container">
        <!-- Lado Izquierdo: Visual -->
        <div class="login-left">
            <div class="logo-container">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo" class="login-logo">
                <h1>SIRCVIG</h1>
                <p>Sistema de Gestión de Vigilancia</p>
                <p class="company-name-login">Corporacion J&J Siglo XXI</p>
            </div>
        </div>
        
        <!-- Lado Derecho: Formulario -->
        <div class="login-right">
            <div class="login-form-container">
                <h2><i class="fas fa-user-circle"></i> Iniciar Sesión</h2>
                <p class="welcome-message">Bienvenido, por favor ingrese sus credenciales</p>
                
                <?php if (isset($_SESSION['exito'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['exito']; 
                        unset($_SESSION['exito']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo BASE_URL; ?>/index.php" method="POST">
                    <div class="form-group">
                        <label for="nombre_usuario"><i class="fas fa-user"></i> Usuario</label>
                        <input type="text" 
                               id="nombre_usuario" 
                               name="nombre_usuario" 
                               required 
                               autocomplete="username"
                               placeholder="Ingrese su usuario">
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasena"><i class="fas fa-lock"></i> Contraseña</label>
                        <input type="password" 
                               id="contrasena" 
                               name="contrasena" 
                               required 
                               autocomplete="current-password"
                               placeholder="Ingrese su contraseña">
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</button>
                    
                    <div class="text-center mt-3">
                        <a href="<?php echo BASE_URL; ?>/index.php?controller=Recuperacion&action=index" style="color: #34495e; font-size: 0.9rem; text-decoration: none;">
                            ¿Olvidó su contraseña?
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
