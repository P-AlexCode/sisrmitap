-- ==============================================================================
-- SISTEMA DE RECURSOS MATERIALES Y SERVICIOS V3
-- Motor: MariaDB 10 | Codificación: utf8mb4
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS sistema_recursos_v3 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_recursos_v3;

-- ------------------------------------------------------------------------------
-- 1. SEGURIDAD Y CONFIGURACIÓN (El Cerebro)
-- ------------------------------------------------------------------------------

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255),
    estado TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    tema_preferido ENUM('tecnm', 'oscuro', 'pastel') DEFAULT 'tecnm',
    estado TINYINT(1) DEFAULT 1,
    ultimo_acceso DATETIME NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE configuracion_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    descripcion VARCHAR(255)
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 2. CATÁLOGOS DE GESTIÓN E INFRAESTRUCTURA
-- ------------------------------------------------------------------------------

CREATE TABLE edificios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edificio_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    estado TINYINT(1) DEFAULT 1,
    FOREIGN KEY (edificio_id) REFERENCES edificios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE personal_directorio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departamento_id INT NOT NULL,
    numero_empleado VARCHAR(50) UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cargo VARCHAR(100),
    email VARCHAR(100),
    telefono VARCHAR(20),
    estado TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    estado TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rfc VARCHAR(20) UNIQUE,
    razon_social VARCHAR(150) NOT NULL,
    nombre_contacto VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    estado TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 3. INVENTARIO CENTRAL (Productos)
-- ------------------------------------------------------------------------------

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    codigo_barras VARCHAR(100) UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    tipo_material ENUM('consumible', 'devolutivo') NOT NULL COMMENT 'Consumible = Salida Directa, Devolutivo = Préstamo',
    unidad_medida VARCHAR(30) NOT NULL COMMENT 'Ej: Pieza, Caja, Litro, Paquete',
    stock_actual DECIMAL(10,2) DEFAULT 0,
    stock_minimo DECIMAL(10,2) DEFAULT 0,
    imagen_url VARCHAR(255) NULL,
    estado TINYINT(1) DEFAULT 1,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 4. OPERACIONES: ENTRADAS (Compras)
-- ------------------------------------------------------------------------------

CREATE TABLE entradas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    proveedor_id INT NOT NULL,
    folio_factura VARCHAR(50) NOT NULL,
    fecha_compra DATE NOT NULL,
    monto_total DECIMAL(12,2),
    archivo_pdf VARCHAR(255) COMMENT 'Ruta del PDF de la factura',
    observaciones TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE entrada_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entrada_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (entrada_id) REFERENCES entradas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 5. OPERACIONES: SALIDAS Y PRÉSTAMOS
-- ------------------------------------------------------------------------------

CREATE TABLE operaciones_salida (
    id INT AUTO_INCREMENT PRIMARY KEY,
    folio VARCHAR(20) NOT NULL UNIQUE,
    usuario_id INT NOT NULL COMMENT 'Quien autoriza/registra',
    personal_id INT NOT NULL COMMENT 'Quien recibe el material',
    tipo_operacion ENUM('salida_directa', 'prestamo') NOT NULL,
    estado ENUM('entregado', 'pendiente_devolucion', 'devuelto_parcial', 'devuelto_total', 'con_danos') NOT NULL,
    fecha_salida DATETIME NOT NULL,
    fecha_limite_devolucion DATETIME NULL COMMENT 'Aplica solo para préstamos',
    archivo_firma_pdf VARCHAR(255) COMMENT 'Ruta del Vale generado y firmado',
    observaciones TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (personal_id) REFERENCES personal_directorio(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE operacion_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operacion_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad_entregada DECIMAL(10,2) NOT NULL,
    cantidad_devuelta DECIMAL(10,2) DEFAULT 0 COMMENT 'Aplica solo para préstamos',
    fecha_devolucion DATETIME NULL,
    FOREIGN KEY (operacion_id) REFERENCES operaciones_salida(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 6. SOLICITUDES WEB (Módulo en línea)
-- ------------------------------------------------------------------------------

CREATE TABLE solicitudes_web (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personal_id INT NOT NULL COMMENT 'Quien lo pide desde la web',
    usuario_revisor_id INT NULL COMMENT 'Quien aprueba o rechaza',
    estado ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    motivo_rechazo TEXT NULL,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_revision DATETIME NULL,
    FOREIGN KEY (personal_id) REFERENCES personal_directorio(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_revisor_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE solicitud_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad_solicitada DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes_web(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 7. AUDITORÍA (El Gran Ojo)
-- ------------------------------------------------------------------------------

CREATE TABLE auditoria_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    modulo VARCHAR(50) NOT NULL,
    accion ENUM('CREAR', 'EDITAR', 'ELIMINAR', 'LOGIN', 'LOGOUT', 'APROBAR', 'RECHAZAR') NOT NULL,
    descripcion_evento TEXT NOT NULL,
    direccion_ip VARCHAR(45) NOT NULL,
    fecha_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ==============================================================================
-- ACTUALIZACIÓN: JERARQUÍA Y USUARIOS POR DEPARTAMENTO
-- ==============================================================================

-- 1. Asignar un usuario del sistema a un departamento específico.
-- Lo hacemos 'NULL' por defecto porque el Súper Administrador podría no 
-- pertenecer a un departamento en particular, sino verlos todos.
ALTER TABLE usuarios
ADD COLUMN departamento_id INT NULL AFTER rol_id,
ADD CONSTRAINT fk_usuario_departamento 
FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL;

-- 2. Definir quién es el encargado (Jefe) del departamento.
-- Lo vinculamos a la tabla 'personal_directorio' porque el encargado es 
-- un empleado físico de la institución.
ALTER TABLE departamentos
ADD COLUMN encargado_id INT NULL AFTER nombre,
ADD CONSTRAINT fk_departamento_encargado 
FOREIGN KEY (encargado_id) REFERENCES personal_directorio(id) ON DELETE SET NULL;

-- 3. (Opcional pero muy recomendado) Vincular la cuenta de acceso (usuario)
-- directamente con su perfil físico en el directorio.
ALTER TABLE usuarios
ADD COLUMN personal_id INT NULL AFTER departamento_id,
ADD CONSTRAINT fk_usuario_personal 
FOREIGN KEY (personal_id) REFERENCES personal_directorio(id) ON DELETE SET NULL;

-- Agregar la columna 'username' y hacerla única
ALTER TABLE usuarios
ADD COLUMN username VARCHAR(50) UNIQUE AFTER nombre;

-- Actualizar al Super Administrador para que su usuario sea 'admin'
UPDATE usuarios SET username = 'admin' WHERE email = 'admin@tecnm.mx';