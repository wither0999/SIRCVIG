<?php
/**
 * Controlador de Dashboard
 */

require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../core/ControladorPrincipal.php';

class ControladorDashboard extends ControladorPrincipal {
    private $modeloVigilante;
    private $modeloAsignacion;
    private $modeloPuesto;
    
    public function __construct() {
        self::verificarSesion();
        $this->modeloVigilante = new Vigilante();
        $this->modeloAsignacion = new Asignacion();
        $this->modeloPuesto = new Puesto();
    }
    
    /**
     * Muestra el dashboard principal
     */
    public function mostrarDashboard() {
        // Obtener estadísticas
        $estadisticas_vigilantes = $this->modeloVigilante->obtenerEstadisticas();
        $estadisticas_asignaciones = $this->modeloAsignacion->obtenerEstadisticas();
        $estadisticas_puestos = $this->modeloPuesto->obtenerEstadisticas();
        
        // Calcular puestos cubiertos (asignaciones activas)
        $puestos_cubiertos = $estadisticas_asignaciones['activas'] ?? 0;
        
        $datos = [
            'total_vigilantes' => $estadisticas_vigilantes['total'] ?? 0,
            'vigilantes_activos' => $estadisticas_vigilantes['activos'] ?? 0,
            'puestos_cubiertos' => $puestos_cubiertos,
            'total_puestos' => $estadisticas_puestos['total'] ?? 0,
            'asignaciones_activas' => $estadisticas_asignaciones['activas'] ?? 0
        ];
        
        $this->renderizarVista('dashboard.php', $datos);
    }
}

