
CREATE DATABASE IF NOT EXISTS ElLugarDB;
USE ElLugarDB;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    admin BOOLEAN DEFAULT FALSE NOT NULL,
    verificado BOOLEAN DEFAULT FALSE NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_tokens (
    token_id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE libros_uid (
    uid CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
    titulo TEXT NOT NULL,
    autor TEXT NOT NULL,
    editorial TEXT NOT NULL,
    genero TEXT NOT NULL
);

CREATE TABLE libros (
    id_libro INT AUTO_INCREMENT PRIMARY KEY,
    uid CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(150),
    editorial VARCHAR(150),
    genero VARCHAR(150),
    precio DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    descripcion TEXT,
    info_adicional TEXT,
    destacado BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_libros_uid FOREIGN KEY (uid) REFERENCES libros_uid(uid)
);

-- =========================
-- TABLA CARRITOS
-- Un carrito pertenece a un usuario
-- =========================
CREATE TABLE carritos (
    id_carrito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

-- =========================
-- TABLA CARRITO_LIBROS
-- Relación muchos a muchos
-- entre carritos y libros
-- =========================
CREATE TABLE carrito_libros (
    id_carrito INT NOT NULL,
    id_libro INT NOT NULL,
    cantidad INT DEFAULT 1,

    PRIMARY KEY (id_carrito, id_libro),

    FOREIGN KEY (id_carrito)
        REFERENCES carritos(id_carrito)
        ON DELETE CASCADE,

    FOREIGN KEY (id_libro)
        REFERENCES libros(id_libro)
        ON DELETE CASCADE
);

CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    nombre_cliente VARCHAR(200) NOT NULL,
    telefono VARCHAR(40) NOT NULL,
    email VARCHAR(150) NOT NULL,
    comentarios TEXT NULL,
    tipo_entrega ENUM('retiro_en_libreria') NOT NULL DEFAULT 'retiro_en_libreria',
    metodo_pago ENUM('en_libreria', 'transferencia_bancaria') NOT NULL,
    estado ENUM(
        'pendiente',
        'confirmado',
        'preparando',
        'listo_para_retirar',
        'entregado',
        'cancelado'
    ) NOT NULL DEFAULT 'pendiente',
    total DECIMAL(12,2) NOT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pedidos_estado_fecha (estado, fecha_creacion),
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

CREATE TABLE detalle_pedido (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_libro INT NULL,
    titulo_libro VARCHAR(200) NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalle_pedido FOREIGN KEY (id_pedido)
        REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_libro FOREIGN KEY (id_libro)
        REFERENCES libros(id_libro) ON DELETE SET NULL
);

CREATE TABLE favoritos (
    id_favorito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,

    FOREIGN KEY(id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE favorito_libros (
    id_favorito INT NOT NULL,
    id_libro INT NOT NULL,

    PRIMARY KEY (id_favorito, id_libro),

    FOREIGN KEY (id_favorito)
        REFERENCES favoritos(id_favorito)
        ON DELETE CASCADE,

    FOREIGN KEY (id_libro)
        REFERENCES libros(id_libro)
        ON DELETE CASCADE
);
