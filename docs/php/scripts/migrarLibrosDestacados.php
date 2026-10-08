<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Este script solo puede ejecutarse desde CLI.\n");
}

require_once __DIR__ . "/../bootstrap.php";

$db = new LibreriaDB();
$column = $db->fetch(
    "SELECT COLUMN_NAME
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'libros'
       AND COLUMN_NAME = 'destacado'"
);

if (!$column) {
    $db->query(
        "ALTER TABLE libros
         ADD COLUMN destacado BOOLEAN NOT NULL DEFAULT FALSE"
    );
}

printf("Migración completada: columna destacado disponible en libros.\n");
