-- ========================================================
-- CREACIÓN Y CONFIGURACIÓN DE LA BASE DE DATOS
-- ========================================================
CREATE DATABASE IF NOT EXISTS sistema_activos;
USE sistema_activos;

-- 1. Tabla de Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'usuario') NOT NULL DEFAULT 'usuario',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabla de Activos Tecnológicos (Actualizada con Ubicación y nuevos estados)
CREATE TABLE activos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_activo VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL, -- Sensor, Placa, Herramienta, etc.
    estado ENUM('disponible', 'prestado', 'mantenimiento', 'dado_de_baja') NOT NULL DEFAULT 'disponible',
    descripcion TEXT,
    ubicacion VARCHAR(100) DEFAULT NULL, -- Campo agregado para ubicación física
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabla de Préstamos / Devoluciones
CREATE TABLE movimientos_prestamos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    activo_id INT NOT NULL,
    descripcion VARCHAR(255) NULL,
    fecha_salida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_limite DATETIME NOT NULL,
    fecha_devolucion DATETIME DEFAULT NULL,
    estado_prestamo ENUM('activo', 'devuelto', 'vencido') NOT NULL DEFAULT 'activo',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (activo_id) REFERENCES activos(id) ON DELETE CASCADE
);

-- 4. Bitácora o Mecanismo de Trazabilidad
CREATE TABLE bitacora_trazabilidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_accion_id INT NOT NULL,
    accion VARCHAR(255) NOT NULL, -- Ej: 'PRESTAMO_REGISTRADO', 'ACTIVO_MODIFICADO'
    detalles TEXT, 
    fecha_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_inicio DATETIME NULL,  -- Nueva columna para fecha y hora de inicio de préstamo
    fecha_limite DATETIME NULL,  -- Nueva columna para fecha y hora límite de devolución
    FOREIGN KEY (usuario_accion_id) REFERENCES usuarios(id)
);

-- ========================================================
-- DATOS INICIALES (SEEDS)
-- ========================================================

-- Insertar un usuario administrador por defecto (Contraseña encriptada: 'admin123')
INSERT INTO usuarios (nombre, usuario, password, rol) 
VALUES ('Administrador Principal', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador');