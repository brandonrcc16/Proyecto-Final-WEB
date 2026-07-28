-- =====================================================================
-- PROYECTO: PALACIO DE FESTIVALES
-- Modelo Entidad-Relación (MER) - Base de datos MySQL / MariaDB (XAMPP)
-- Entidades: SALA, ESPECTACULO, ACTUACION, ZONA, BUTACA, ENTRADA
-- =====================================================================

DROP DATABASE IF EXISTS palacio_festivales;
CREATE DATABASE palacio_festivales CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
USE palacio_festivales;

-- ---------------------------------------------------------------------
-- Tabla: USUARIO
-- Usuario administrador del sistema (control de acceso / login).
-- El usuario admin se crea ejecutando una sola vez crear_admin.php
-- (así la contraseña queda hasheada correctamente con password_hash de PHP).
-- ---------------------------------------------------------------------
CREATE TABLE usuario (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    usuario         VARCHAR(50) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: SALA
-- Un palacio de festivales tiene varias salas (auditorios) físicas.
-- ---------------------------------------------------------------------
CREATE TABLE sala (
    id_sala        INT AUTO_INCREMENT PRIMARY KEY,
    nombre         VARCHAR(100) NOT NULL,
    capacidad      INT NOT NULL,
    ubicacion      VARCHAR(150) NULL,
    descripcion    TEXT NULL,
    estado         ENUM('activa','inactiva') NOT NULL DEFAULT 'activa',
    creado_en      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: ESPECTACULO
-- Catálogo de espectáculos (conciertos, obras de teatro, danza, etc.)
-- ---------------------------------------------------------------------
CREATE TABLE espectaculo (
    id_espectaculo INT AUTO_INCREMENT PRIMARY KEY,
    nombre         VARCHAR(150) NOT NULL,
    tipo           VARCHAR(60) NOT NULL,
    descripcion    TEXT NULL,
    duracion_min   INT NOT NULL,
    creado_en      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: ACTUACION
-- Una "función": un espectáculo concreto programado en una sala,
-- fecha y hora determinadas, con un precio base.
-- ---------------------------------------------------------------------
CREATE TABLE actuacion (
    id_actuacion   INT AUTO_INCREMENT PRIMARY KEY,
    id_espectaculo INT NOT NULL,
    id_sala        INT NOT NULL,
    fecha          DATE NOT NULL,
    hora           TIME NOT NULL,
    precio_base    DECIMAL(8,2) NOT NULL,
    estado         ENUM('programada','cancelada','finalizada') NOT NULL DEFAULT 'programada',
    CONSTRAINT fk_actuacion_espectaculo FOREIGN KEY (id_espectaculo)
        REFERENCES espectaculo(id_espectaculo) ON DELETE CASCADE,
    CONSTRAINT fk_actuacion_sala FOREIGN KEY (id_sala)
        REFERENCES sala(id_sala) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: ZONA
-- Cada sala se divide en zonas (VIP, Preferencia, General...) con un
-- multiplicador de precio sobre el precio_base de la actuación.
-- ---------------------------------------------------------------------
CREATE TABLE zona (
    id_zona              INT AUTO_INCREMENT PRIMARY KEY,
    id_sala              INT NOT NULL,
    nombre               VARCHAR(60) NOT NULL,
    multiplicador_precio DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    CONSTRAINT fk_zona_sala FOREIGN KEY (id_sala)
        REFERENCES sala(id_sala) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: BUTACA
-- Asiento físico dentro de una zona (fila + número).
-- ---------------------------------------------------------------------
CREATE TABLE butaca (
    id_butaca  INT AUTO_INCREMENT PRIMARY KEY,
    id_zona    INT NOT NULL,
    fila       VARCHAR(5) NOT NULL,
    numero     INT NOT NULL,
    estado     ENUM('disponible','mantenimiento') NOT NULL DEFAULT 'disponible',
    CONSTRAINT fk_butaca_zona FOREIGN KEY (id_zona)
        REFERENCES zona(id_zona) ON DELETE CASCADE,
    CONSTRAINT uq_butaca UNIQUE (id_zona, fila, numero)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: ENTRADA
-- Venta de un boleto: liga una BUTACA concreta a una ACTUACION
-- concreta. La restricción única evita vender dos veces el mismo
-- asiento para la misma función.
-- ---------------------------------------------------------------------
CREATE TABLE entrada (
    id_entrada         INT AUTO_INCREMENT PRIMARY KEY,
    id_actuacion       INT NOT NULL,
    id_butaca          INT NOT NULL,
    cliente_nombre     VARCHAR(150) NOT NULL,
    cliente_documento  VARCHAR(20) NOT NULL,
    cliente_email      VARCHAR(150) NULL,
    precio_final       DECIMAL(8,2) NOT NULL,
    codigo             VARCHAR(20) NOT NULL UNIQUE,
    fecha_compra       DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado             ENUM('activa','anulada') NOT NULL DEFAULT 'activa',
    CONSTRAINT fk_entrada_actuacion FOREIGN KEY (id_actuacion)
        REFERENCES actuacion(id_actuacion) ON DELETE CASCADE,
    CONSTRAINT fk_entrada_butaca FOREIGN KEY (id_butaca)
        REFERENCES butaca(id_butaca) ON DELETE CASCADE,
    CONSTRAINT uq_entrada_actuacion_butaca UNIQUE (id_actuacion, id_butaca)
) ENGINE=InnoDB;

-- =====================================================================
-- DATOS DE PRUEBA
-- =====================================================================

INSERT INTO sala (nombre, capacidad, ubicacion, descripcion, estado) VALUES
('Auditorio Principal', 500, 'Planta baja', 'Sala principal del palacio, con excelente acústica', 'activa'),
('Sala Cámara', 150, 'Primer piso', 'Sala íntima para conciertos de cámara', 'activa'),
('Sala Multiusos', 300, 'Segundo piso', 'Sala flexible para teatro y danza', 'activa');

INSERT INTO espectaculo (nombre, tipo, descripcion, duracion_min) VALUES
('Sinfonía de Otoño', 'Concierto', 'Orquesta sinfónica interpretando obras clásicas', 110),
('Noches de Flamenco', 'Danza', 'Espectáculo de danza y música flamenca', 90),
('El Jardín de las Sombras', 'Teatro', 'Obra dramática contemporánea', 100);

INSERT INTO actuacion (id_espectaculo, id_sala, fecha, hora, precio_base, estado) VALUES
(1, 1, '2026-08-15', '19:30:00', 25.00, 'programada'),
(2, 2, '2026-08-20', '20:00:00', 18.00, 'programada'),
(3, 3, '2026-08-22', '19:00:00', 15.00, 'programada');

INSERT INTO zona (id_sala, nombre, multiplicador_precio) VALUES
(1, 'VIP', 2.00),
(1, 'Preferencia', 1.50),
(1, 'General', 1.00),
(2, 'Preferencia', 1.30),
(2, 'General', 1.00),
(3, 'General', 1.00);

-- Butacas de ejemplo para la zona VIP (id_zona = 1) de la Sala Principal
INSERT INTO butaca (id_zona, fila, numero) VALUES
(1,'A',1),(1,'A',2),(1,'A',3),(1,'A',4),
(2,'B',1),(2,'B',2),(2,'B',3),(2,'B',4),
(3,'C',1),(3,'C',2),(3,'C',3),(3,'C',4);
