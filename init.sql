CREATE DATABASE IF NOT EXISTS sistema_activos;
USE sistema_activos;

-- Tabla de Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'usuario') NOT NULL DEFAULT 'usuario',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Activos Tecnológicos
CREATE TABLE activos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_activo VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL, -- Sensor, Placa, Herramienta, etc.
    estado ENUM('disponible', 'prestado', 'mantenimiento') NOT NULL DEFAULT 'disponible',
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Préstamos / Devoluciones
CREATE TABLE movimientos_prestamos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    activo_id INT NOT NULL,
    fecha_salida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_limite DATETIME NOT NULL,
    fecha_devolucion DATETIME DEFAULT NULL,
    estado_prestamo ENUM('activo', 'devuelto', 'vencido') NOT NULL DEFAULT 'activo',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (activo_id) REFERENCES activos(id)
);

-- Bitácora o Mecanismo de Trazabilidad
CREATE TABLE bitacora_trazabilidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_accion_id INT NOT NULL,
    accion VARCHAR(255) NOT NULL, -- Ej: 'PRESTAMO_REGISTRADO', 'ACTIVO_MODIFICADO'
    descripcion TEXT,
    fecha_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar un usuario administrador por defecto (Contraseña encriptada: 'admin123')
INSERT INTO usuarios (nombre, correo, password, rol) 
VALUES ('Administrador Principal', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador');