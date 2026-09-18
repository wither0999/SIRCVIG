<?php
// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

// Determinar qué menús mostrar según el rol
$id_rol = $_SESSION['id_rol'] ?? 0;
$puede_gestionar_usuarios = ($id_rol == ROL_ADMINISTRADOR);
$puede_gestionar_vigilantes = ($id_rol == ROL_ADMINISTRADOR || $id_rol == ROL_SECRETARIO || $id_rol == ROL_SUPERVISOR);
$puede_gestionar_asignaciones = ($id_rol == ROL_ADMINISTRADOR || $id_rol == ROL_SUPERVISOR);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRCVIG - <?php echo $titulo ?? 'Dashboard'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/estilos.css">
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/logo.png">
</head>
<body>
    <div class="main-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px;">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo" style="width: 70px; height: auto;">
                <div style="text-align: left;">
                    <h2 style="margin: 0; font-size: 1.5rem;">SIRCVIG</h2>
                    <p style="margin: 0; font-size: 0.8rem;">Sistema de Gestión</p>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="<?php echo BASE_URL; ?>/views/dashboard.php" 
                       class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                
                <?php if ($puede_gestionar_vigilantes): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>/views/listado_vigilantes.php"
                       class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['listado_vigilantes.php', 'registro_vigilante.php', 'ficha_personal.php'])) ? 'active' : ''; ?>">
                        <i class="fas fa-user-shield"></i> Vigilantes
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/views/listado_clientes.php"
                       class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['listado_clientes.php', 'formulario_cliente.php'])) ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Clientes
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($puede_gestionar_vigilantes && ($id_rol == ROL_ADMINISTRADOR || $id_rol == ROL_SECRETARIO)): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>/views/listado_puestos.php"
                       class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['listado_puestos.php', 'formulario_puesto.php', 'detalles_puesto.php'])) ? 'active' : ''; ?>">
                        <i class="fas fa-building-shield"></i> Puestos de Guardia
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($puede_gestionar_asignaciones): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>/views/cronograma_guardia.php"
                       class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['cronograma_guardia.php', 'asignar_puesto.php'])) ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i> Asignaciones
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($puede_gestionar_usuarios): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>/views/gestion_roles.php"
                       class="<?php echo (basename($_SERVER['PHP_SELF']) == 'gestion_roles.php') ? 'active' : ''; ?>">
                        <i class="fas fa-users-cog"></i> Gestión de Usuarios
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($id_rol == ROL_ADMINISTRADOR || $id_rol == ROL_SECRETARIO || $id_rol == ROL_SUPERVISOR): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>/views/reportes.php"
                       class="<?php echo (basename($_SERVER['PHP_SELF']) == 'reportes.php') ? 'active' : ''; ?>">
                        <i class="fas fa-file-invoice"></i> Reportes
                    </a>
                </li>
                <?php endif; ?>
                
                <li>
                    <a href="<?php echo BASE_URL; ?>/views/ayuda.php"
                       class="<?php echo (basename($_SERVER['PHP_SELF']) == 'ayuda.php') ? 'active' : ''; ?>">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </a>
                </li>
                
                <li>
                    <a href="<?php echo BASE_URL; ?>/views/acerca_de.php"
                       class="<?php echo (basename($_SERVER['PHP_SELF']) == 'acerca_de.php') ? 'active' : ''; ?>">
                        <i class="fas fa-info-circle"></i> Acerca de
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content" id="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="top-bar-left">
                    <button id="toggle-sidebar" class="toggle-sidebar-btn" title="Alternar Menú Lateral">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="company-info">
                        <span class="company-name">Corporacion J&J Siglo XXI</span>
                        <div class="user-info">
                            <span><strong>Rol:</strong> <?php echo htmlspecialchars($_SESSION['nombre_rol']); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="top-bar-right" style="display: flex; align-items: center; gap: 15px;">
                    <?php
                    $hora = date('G');
                    if ($hora < 12) {
                        $saludo_tiempo = "¡Buenos días!";
                    } elseif ($hora < 18) {
                        $saludo_tiempo = "¡Buenas tardes!";
                    } else {
                        $saludo_tiempo = "¡Buenas noches!";
                    }
                    
                    // Determinar nombre a mostrar
                    $nombre_mostrar = $_SESSION['usuario_nombre'];
                    if (!empty($_SESSION['usuario_nombres'])) {
                         $nombre_mostrar = $_SESSION['usuario_nombres'];
                         if (!empty($_SESSION['usuario_apellidos'])) {
                             $nombre_mostrar .= ' ' . $_SESSION['usuario_apellidos'];
                         }
                    }
                    ?>
                    <div class="user-greeting" style="text-align: right;">
                        <span style="display: block; font-weight: bold; color: #2c3e50;">Bienvenido <?php echo htmlspecialchars($nombre_mostrar); ?>,</span>
                        <span style="font-size: 0.9rem; color: #7f8c8d;"><?php echo $saludo_tiempo; ?></span>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/index.php?action=logout" class="logout-btn" id="btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </div>
            
            <!-- Messages -->
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

