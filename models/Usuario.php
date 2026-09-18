<?php
/**
 * Modelo de Usuario
 */

class Usuario extends Modelo {
    protected $tabla = 'usuarios';
    
    /**
     * Autentica un usuario
     */
    public function autenticarUsuario($nombre_usuario, $contrasena) {
        $sql = "SELECT u.*, r.nombre_rol 
                FROM usuarios u 
                INNER JOIN roles r ON u.id_rol = r.id_rol 
                WHERE u.nombre_usuario = :nombre_usuario";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nombre_usuario' => $nombre_usuario]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            // Verificar contraseña (SHA-256)
            $hash_ingresado = hash('sha256', $contrasena);
            
            if ($hash_ingresado === $usuario['contrasena_hash']) {
                // Verificar estado de la cuenta
                if ($usuario['estado_cuenta'] === 'Bloqueada') {
                    throw new Exception("Su usuario se encuentra bloqueado. Contacte al administrador");
                }
                
                // Contraseña correcta y cuenta activa - reiniciar intentos fallidos
                $this->reiniciarIntentosFallidos($usuario['id_usuario']);
                $this->actualizarUltimoAcceso($usuario['id_usuario']);
                return $usuario;
            } else {
                // Contraseña incorrecta - incrementar intentos
                $this->incrementarIntentosFallidos($usuario['id_usuario']);
                return false;
            }
        }
        
        // Usuario no encontrado
        return false;
    }
    
    /**
     * Incrementa el contador de intentos fallidos
     */
    public function incrementarIntentosFallidos($id_usuario) {
        $sql = "UPDATE usuarios 
                SET intentos_fallidos = intentos_fallidos + 1 
                WHERE id_usuario = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_usuario]);
        
        // Verificar si alcanzó 5 intentos
        $usuario = $this->obtenerPorId($id_usuario);
        if ($usuario && $usuario['intentos_fallidos'] >= 5) {
            $this->bloquearCuenta($id_usuario);
        }
    }
    
    /**
     * Reinicia los intentos fallidos
     */
    public function reiniciarIntentosFallidos($id_usuario) {
        $sql = "UPDATE usuarios 
                SET intentos_fallidos = 0 
                WHERE id_usuario = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_usuario]);
    }
    
    /**
     * Bloquea una cuenta
     */
    public function bloquearCuenta($id_usuario) {
        $sql = "UPDATE usuarios 
                SET estado_cuenta = 'Bloqueada' 
                WHERE id_usuario = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id_usuario]);
    }
    
    /**
     * Desbloquea una cuenta
     */
    public function desbloquearCuenta($id_usuario) {
        $sql = "UPDATE usuarios 
                SET estado_cuenta = 'Activa', intentos_fallidos = 0 
                WHERE id_usuario = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id_usuario]);
    }
    
    /**
     * Actualiza el último acceso
     */
    public function actualizarUltimoAcceso($id_usuario) {
        $sql = "UPDATE usuarios 
                SET ultimo_acceso = NOW() 
                WHERE id_usuario = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_usuario]);
    }
    
    /**
     * Crea un nuevo usuario
     */
    public function crearUsuario($datos) {
        // Hash de la contraseña
        if (isset($datos['contrasena'])) {
            $datos['contrasena_hash'] = hash('sha256', $datos['contrasena']);
            unset($datos['contrasena']);
        }
        
        // Hash de respuestas de seguridad
        if (isset($datos['respuesta_seguridad'])) {
            $datos['respuesta_seguridad'] = hash('sha256', strtolower(trim($datos['respuesta_seguridad'])));
        }
        
        if (isset($datos['respuesta_seguridad_2'])) {
            $datos['respuesta_seguridad_2'] = hash('sha256', strtolower(trim($datos['respuesta_seguridad_2'])));
        }
        
        return $this->insertar($datos);
    }
    
    /**
     * Obtiene todos los usuarios con información de roles
     */
    public function obtenerUsuariosConRoles() {
        $sql = "SELECT u.*, r.nombre_rol 
                FROM usuarios u 
                INNER JOIN roles r ON u.id_rol = r.id_rol 
                ORDER BY u.fecha_creacion DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Actualiza el rol de un usuario
     */
    public function asignarRol($id_usuario, $id_rol) {
        $sql = "UPDATE usuarios SET id_rol = :id_rol WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id_rol' => $id_rol, 'id_usuario' => $id_usuario]);
    }
    
    /**
     * Verifica si un nombre de usuario existe
     */
    public function existeUsuario($nombre_usuario) {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE nombre_usuario = :nombre_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nombre_usuario' => $nombre_usuario]);
        $resultado = $stmt->fetch();
        return $resultado['total'] > 0;
    }

    /**
     * Obtiene un usuario por su cédula
     */
    public function obtenerPorCedula($cedula) {
        $sql = "SELECT * FROM usuarios WHERE cedula = :cedula AND estado_cuenta = 'Activa'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cedula' => $cedula]);
        return $stmt->fetch();
    }

    /**
     * Verifica la respuesta de seguridad
     */
    public function verificarRespuestaSeguridad($id_usuario, $respuesta) {
        $usuario = $this->obtenerPorId($id_usuario);
        if (!$usuario) return false;

        $hash_respuesta = hash('sha256', strtolower(trim($respuesta)));
        return $hash_respuesta === $usuario['respuesta_seguridad'];
    }

    /**
     * Actualiza la contraseña de un usuario
     */
    public function actualizarContrasena($id_usuario, $nueva_contrasena) {
        $hash = hash('sha256', $nueva_contrasena);
        
        $sql = "UPDATE usuarios SET contrasena_hash = :hash WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['hash' => $hash, 'id' => $id_usuario]);
    }

    /**
     * Actualiza los datos personales de un usuario (Perfil)
     */
    public function actualizarDatosPersonales($id_usuario, $nombres, $apellidos, $telefono) {
        $sql = "UPDATE usuarios 
                SET nombres = :nombres, apellidos = :apellidos, telefono = :telefono 
                WHERE id_usuario = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => $telefono,
            'id' => $id_usuario
        ]);
    }

    /**
     * Actualiza las preguntas de seguridad (para perfil/registro)
     */
    public function actualizarPreguntasSeguridad($id_usuario, $pregunta, $respuesta) {
        $hash_respuesta = hash('sha256', strtolower(trim($respuesta)));
        
        $sql = "UPDATE usuarios SET pregunta_seguridad = :pregunta, respuesta_seguridad = :respuesta WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['pregunta' => $pregunta, 'respuesta' => $hash_respuesta, 'id' => $id_usuario]);
    }
    
    /**
     * Actualiza un usuario desde el panel de administración
     */
    public function actualizarUsuarioAdmin($id_usuario, $datos) {
        $updateFields = [];
        $params = ['id_usuario' => $id_usuario];
        
        $camposPermitidos = ['nombre_usuario', 'id_rol', 'nombres', 'apellidos', 'cedula', 'email', 'telefono'];
        
        foreach ($camposPermitidos as $campo) {
            if (isset($datos[$campo])) {
                $updateFields[] = "$campo = :$campo";
                $params[$campo] = $datos[$campo];
            }
        }
        
        if (isset($datos['contrasena']) && !empty($datos['contrasena'])) {
            $updateFields[] = "contrasena_hash = :contrasena_hash";
            $params['contrasena_hash'] = hash('sha256', $datos['contrasena']);
        }
        
        if (empty($updateFields)) return true;
        
        $sql = "UPDATE usuarios SET " . implode(', ', $updateFields) . " WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}

