
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

CREATE TABLE libros (
    id_libro INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(150),
    editorial VARCHAR(150),
    genero VARCHAR(150),
    precio DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    portada VARCHAR(255)
);

-- =========================
-- TABLA CARRITOS
-- Un carrito pertenece a un usuario
-- =========================
CREATE TABLE carritos (
    id_carrito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
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
