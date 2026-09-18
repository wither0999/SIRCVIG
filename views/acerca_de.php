<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

// Verificar sesión
ControladorPrincipal::verificarSesion();

$titulo = "Acerca de SIRCVIG";
require_once __DIR__ . '/includes/header.php';
?>

<div class="content-wrapper">
    <div class="page-header">
        <h2><i class="fas fa-info-circle"></i> Acerca de SIRCVIG</h2>
    </div>

    <!-- Información del Sistema -->
    <div class="card mb-4">
        <div class="card-body" style="text-align: center;">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo SIRCVIG" style="width: 150px; height: auto; margin-bottom: 20px;">
            <h1 style="color: #2c3e50; font-weight: bold; margin-bottom: 10px;">SIRCVIG</h1>
            <h4 style="color: #7f8c8d; margin-bottom: 20px;">Sistema de Gestión de Seguridad</h4>
            
            <div style="max-width: 800px; margin: 0 auto; text-align: justify; padding: 20px; background-color: #f8f9fa; border-left: 5px solid #3498db; border-radius: 5px;">
                <h5 style="color: #2c3e50; margin-top: 0;"><i class="fas fa-bullseye"></i> Alcance del Sistema</h5>
                <p style="margin-bottom: 0; line-height: 1.6;">
                    Garantizar el registro, control y gestión eficiente de todas las operaciones relacionadas con el personal de vigilancia, asignación de puestos, control de disponibilidad y administración de clientes de la Corporación J&J Siglo XXI. SIRCVIG centraliza la información brindando acceso rápido, optimizando los tiempos de respuesta y facilitando la toma de decisiones estratégicas mediante la generación de reportes y supervisión activa.
                </p>
            </div>
        </div>
    </div>

    <!-- Equipo de Desarrollo -->
    <div class="card mb-4 mt-4">
        <div class="card-header bg-light">
            <h4 class="mb-0"><i class="fas fa-users"></i> Equipo de Desarrollo</h4>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;">
                
                <?php
                $desarrolladores = [
                    [
                        'nombre' => 'Franyelis Pirona',
                        'rol' => 'Analista de Requisitos',
                        'icon' => 'fa-clipboard-check',
                        'color' => '#e74c3c',
                        'foto' => 'dev_franyelis.jpg'
                    ],
                    [
                        'nombre' => 'Jhort Moreno',
                        'rol' => 'Diseñador de Software',
                        'icon' => 'fa-pencil-ruler',
                        'color' => '#8e44ad',
                        'foto' => 'dev_jhort.jpg'
                    ],
                    [
                        'nombre' => 'Jepherson Diaz',
                        'rol' => 'Diseñador de Software',
                        'icon' => 'fa-paint-brush',
                        'color' => '#2980b9',
                        'foto' => 'dev_jepherson.png'
                    ],
                    [
                        'nombre' => 'Mathias Mata',
                        'rol' => 'Programador/Implementador',
                        'icon' => 'fa-laptop-code',
                        'color' => '#27ae60',
                        'foto' => 'dev_mathias.jpg'
                    ],
                    [
                        'nombre' => 'Victor Diaz',
                        'rol' => 'Tester/Especialista en Pruebas',
                        'icon' => 'fa-bug',
                        'color' => '#f39c12',
                        'foto' => 'dev_victor.jpg'
                    ]
                ];

                foreach ($desarrolladores as $dev):
                ?>
                <div style="background: white; border: 1px solid #eee; border-radius: 10px; width: 280px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; text-align: center; transition: transform 0.3s;">
                    <div style="background-color: <?php echo $dev['color']; ?>; padding: 25px 0;">
                        <div style="width: 100px; height: 100px; background: white; border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 8px rgba(0,0,0,0.2); overflow: hidden;">
                            <?php if(!empty($dev['foto'])): ?>
                                <img src="<?php echo BASE_URL; ?>/assets/images/<?php echo $dev['foto']; ?>" alt="<?php echo $dev['nombre']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-user-circle" style="font-size: 105px; color: #ecf0f1;"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="padding: 20px;">
                        <h4 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 1.1rem;">Br. <?php echo $dev['nombre']; ?></h4>
                        <div style="background: #f8f9fa; padding: 8px; border-radius: 20px; font-size: 0.85rem; color: #34495e; border: 1px solid #e9ecef;">
                            <i class="fas <?php echo $dev['icon']; ?>" style="color: <?php echo $dev['color']; ?>;"></i> <?php echo $dev['rol']; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
            </div>
        </div>
    </div>

    <!-- Tecnologías -->
    <div class="card mt-4 mb-5">
        <div class="card-header bg-light">
            <h4 class="mb-0"><i class="fas fa-microchip"></i> Tecnologías Utilizadas</h4>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; align-items: center; padding: 20px 0;">
                <div style="text-align: center;">
                    <i class="fab fa-php" style="font-size: 50px; color: #777BB4;"></i>
                    <p style="margin-top: 10px; font-weight: bold; color: #2c3e50;">PHP</p>
                </div>
                <div style="text-align: center;">
                    <i class="fas fa-database" style="font-size: 50px; color: #00758F;"></i>
                    <p style="margin-top: 10px; font-weight: bold; color: #2c3e50;">MySQL</p>
                </div>
                <div style="text-align: center;">
                    <i class="fab fa-html5" style="font-size: 50px; color: #E34F26;"></i>
                    <p style="margin-top: 10px; font-weight: bold; color: #2c3e50;">HTML5</p>
                </div>
                <div style="text-align: center;">
                    <i class="fab fa-css3-alt" style="font-size: 50px; color: #1572B6;"></i>
                    <p style="margin-top: 10px; font-weight: bold; color: #2c3e50;">CSS3</p>
                </div>
                <div style="text-align: center;">
                    <i class="fab fa-js" style="font-size: 50px; color: #F7DF1E;"></i>
                    <p style="margin-top: 10px; font-weight: bold; color: #2c3e50;">JavaScript</p>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    // Pequeño efecto CSS para las tarjetas
    document.write('<style>');
    document.write('.card-body > div > div:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.1) !important; }');
    document.write('</style>');
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
