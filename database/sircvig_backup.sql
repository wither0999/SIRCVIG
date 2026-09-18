-- ============================================
-- SISTEMA SIRCVIG - Base de Datos
-- ============================================

CREATE DATABASE IF NOT EXISTS sircvig CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sircvig;

-- ============================================
-- Tabla: roles
-- ============================================
CREATE TABLE IF NOT EXISTS roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar roles iniciales
INSERT INTO roles (nombre_rol, descripcion) VALUES
('Administrador', 'Acceso completo al sistema'),
('Secretario', 'Gestión de expedientes y asignaciones'),
('Supervisor', 'Supervisión y consulta de información');

-- ============================================
-- Tabla: usuarios
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
    cedula VARCHAR(20) UNIQUE,
    nombres VARCHAR(100),
    apellidos VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    telefono VARCHAR(20),
    contrasena_hash VARCHAR(255) NOT NULL,
    pregunta_seguridad VARCHAR(255),
    respuesta_seguridad VARCHAR(255),
    pregunta_seguridad_2 VARCHAR(255),
    respuesta_seguridad_2 VARCHAR(255),
    id_rol INT NOT NULL,
    intentos_fallidos INT DEFAULT 0,
    estado_cuenta ENUM('Activa', 'Bloqueada') DEFAULT 'Activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuario administrador por defecto (contraseña: admin123)
-- Hash SHA-256 de 'admin123' = 240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9
-- Usando password_hash() sería más seguro, pero para compatibilidad usamos SHA-256
INSERT INTO usuarios (nombre_usuario, cedula, nombres, apellidos, email, telefono, contrasena_hash, pregunta_seguridad, respuesta_seguridad, pregunta_seguridad_2, respuesta_seguridad_2, id_rol) VALUES
('admin', '00000000', 'Administrador', 'Sistema', 'admin@sircvig.com', '555-5555', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', '¿Cual es el nombre de tu primera mascota?', '986429f4f1a2384a6c6e7f80db7227d86f78bb8f0375a0684f85e3343336531d', '¿Ciudad de nacimiento?', '986429f4f1a2384a6c6e7f80db7227d86f78bb8f0375a0684f85e3343336531d', 1);

-- ============================================
-- Tabla: vigilantes
-- ============================================
CREATE TABLE IF NOT EXISTS vigilantes (
    id_vigilante INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    direccion TEXT,
    telefono VARCHAR(20),
    email VARCHAR(100),
    estatus ENUM('Aspirante', 'Activo', 'Inactivo') DEFAULT 'Aspirante',
    fecha_ingreso DATE,
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: documentos
-- ============================================
CREATE TABLE IF NOT EXISTS documentos (
    id_documento INT AUTO_INCREMENT PRIMARY KEY,
    cedula_vigilante VARCHAR(20) NOT NULL,
    tipo_documento ENUM('Foto', 'CedulaEscaneada', 'Antecedentes') NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cedula_vigilante) REFERENCES vigilantes(cedula) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: puestos
-- ============================================
CREATE TABLE IF NOT EXISTS puestos (
    id_puesto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cliente VARCHAR(200) NOT NULL,
    direccion TEXT NOT NULL,
    telefono VARCHAR(20),
    contacto VARCHAR(100),
    descripcion TEXT,
    estatus ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: asignaciones
-- ============================================
CREATE TABLE IF NOT EXISTS asignaciones (
    id_asignacion INT AUTO_INCREMENT PRIMARY KEY,
    cedula_vigilante VARCHAR(20) NOT NULL,
    id_puesto INT NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    rol_guardia ENUM('24x48', '24x24', '12x12') NOT NULL,
    observaciones TEXT,
    estatus ENUM('Activa', 'Completada', 'Cancelada') DEFAULT 'Activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cedula_vigilante) REFERENCES vigilantes(cedula) ON DELETE RESTRICT,
    FOREIGN KEY (id_puesto) REFERENCES puestos(id_puesto) ON DELETE RESTRICT,
    INDEX idx_vigilante_fechas (cedula_vigilante, fecha_inicio, fecha_fin),
    INDEX idx_puesto_fechas (id_puesto, fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Índices adicionales para optimización
-- ============================================
CREATE INDEX idx_vigilante_estatus ON vigilantes(estatus);
CREATE INDEX idx_asignacion_estatus ON asignaciones(estatus);
CREATE INDEX idx_documento_tipo ON documentos(tipo_documento);

