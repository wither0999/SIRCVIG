<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Modelo.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Rol.php';

$usuarioModel = new Usuario();
$usuarios = $usuarioModel->obtenerTodos();

echo "<h1>Usuarios en Base de Datos</h1>";
echo "<pre>";
foreach ($usuarios as $u) {
    print_r([
        'id' => $u['id_usuario'],
        'usuario' => $u['nombre_usuario'],
        'cedula' => $u['cedula'],
        'pregunta_1' => $u['pregunta_seguridad'],
        'respuesta_1_hash' => $u['respuesta_seguridad']
    ]);
}
echo "</pre>";
?>
