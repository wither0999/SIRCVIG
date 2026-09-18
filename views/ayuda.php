<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

// Verificar sesión
ControladorPrincipal::verificarSesion();

$titulo = "Módulo de Ayuda";
require_once __DIR__ . '/includes/header.php';
?>

<div class="content-wrapper">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin-bottom: 0;"><i class="fas fa-question-circle"></i> Centro de Ayuda</h1>
        <a href="<?php echo BASE_URL; ?>/assets/docs/Manual_de_Usuario.pdf" download="Manual_SIRCVIG.pdf" class="btn btn-primary" style="padding: 10px 20px; font-weight: bold;">
            <i class="fas fa-download"></i> Descargar Manual
        </a>
    </div>

    <div class="card" style="box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
        <div class="card-header" style="background-color: #2c3e50; color: white; padding: 15px 20px;">
            <h4 class="mb-0" style="margin: 0;"><i class="fas fa-book"></i> Manual de Usuario Interactivo</h4>
        </div>
        <div class="card-body" style="padding: 0; background-color: #f8f9fa;">
            <!-- Contenedor del Visor de PDF -->
            <div style="width: 100%; height: 75vh; border: none;">
                <object data="<?php echo BASE_URL; ?>/assets/docs/Manual_de_Usuario.pdf#toolbar=1&navpanes=1&scrollbar=1" 
                        type="application/pdf" 
                        width="100%" 
                        height="100%" 
                        style="border: none; display: block;">
                    
                    <!-- Fallback para navegadores que no soportan incrustar PDF -->
                    <div style="padding: 40px; text-align: center; color: #555;">
                        <i class="fas fa-file-pdf fa-4x" style="color: #e74c3c; margin-bottom: 20px;"></i>
                        <h3>El visor de PDF no está disponible en este navegador</h3>
                        <p style="margin-bottom: 20px;">Puede descargar el documento completo para leerlo en su equipo.</p>
                        <a href="<?php echo BASE_URL; ?>/assets/docs/Manual_de_Usuario.pdf" download="Manual_SIRCVIG.pdf" class="btn btn-primary">
                            <i class="fas fa-download"></i> Descargar Archivo PDF
                        </a>
                    </div>
                </object>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
