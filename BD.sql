-- Primero, crear la base de datos
CREATE DATABASE IF NOT EXISTS sistema_rifas;
USE sistema_rifas;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de rifas
CREATE TABLE IF NOT EXISTS rifas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio_boleto DECIMAL(10,2) NOT NULL,
    fecha_sorteo DATE NOT NULL,
    premio VARCHAR(255) NOT NULL,
    imagen1 VARCHAR(255),
    imagen2 VARCHAR(255),
    youtube_link VARCHAR(255),
    numeros INT NOT NULL DEFAULT 50,
    estado ENUM('activa', 'finalizada', 'cancelada') DEFAULT 'activa',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de boletos
CREATE TABLE IF NOT EXISTS boletos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rifa_id INT NOT NULL,
    numero INT NOT NULL,
    nombre_comprador VARCHAR(100),
    email_comprador VARCHAR(100),
    telefono_comprador VARCHAR(20),
    estado ENUM('disponible', 'reservado', 'vendido') DEFAULT 'disponible',
    fecha_compra DATETIME,
    FOREIGN KEY (rifa_id) REFERENCES rifas(id) ON DELETE CASCADE,
    UNIQUE KEY (rifa_id, numero)
);