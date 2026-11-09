<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Este script solo puede ejecutarse desde CLI.\n");
}

require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../clases/helpers/BookUid.php";

use App\Helpers\BookUid;

$db = new LibreriaDB();

$db->query(
    "CREATE TABLE IF NOT EXISTS libros_uid (
        uid CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
        titulo TEXT NOT NULL,
        autor TEXT NOT NULL,
        editorial TEXT NOT NULL,
        genero TEXT NOT NULL,
        descripcion TEXT NULL,
        destacado BOOLEAN NULL
    )"
);

$metadataColumns = [
    'descripcion' => 'TEXT NULL',
    'destacado' => 'BOOLEAN NULL',
];

foreach ($metadataColumns as $columnName => $columnDefinition) {
    $column = $db->fetch(
        "SELECT COLUMN_NAME
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'libros_uid'
           AND COLUMN_NAME = ?",
        [$columnName]
    );

    if (!$column) {
        $db->query("ALTER TABLE libros_uid ADD COLUMN {$columnName} {$columnDefinition}");
    }
}

$uidColumn = $db->fetch(
    "SELECT COLUMN_NAME
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'libros'
       AND COLUMN_NAME = 'uid'"
);

if (!$uidColumn) {
    $db->query(
        "ALTER TABLE libros
         ADD COLUMN uid CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL
         AFTER id_libro"
    );
}

$infoAdicionalColumn = $db->fetch(
    "SELECT COLUMN_NAME
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'libros'
       AND COLUMN_NAME = 'info_adicional'"
);

if (!$infoAdicionalColumn) {
    $db->query("ALTER TABLE libros ADD COLUMN info_adicional TEXT NULL");
}

$descriptionColumn = $db->fetch(
    "SELECT COLUMN_NAME
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'libros'
       AND COLUMN_NAME = 'descripcion'"
);

if (!$descriptionColumn) {
    $db->query("ALTER TABLE libros ADD COLUMN descripcion TEXT NULL");
}

$featuredColumn = $db->fetch(
    "SELECT COLUMN_NAME
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'libros'
       AND COLUMN_NAME = 'destacado'"
);

if (!$featuredColumn) {
    $db->query("ALTER TABLE libros ADD COLUMN destacado BOOLEAN NOT NULL DEFAULT FALSE");
}

$portadaColumn = $db->fetch(
    "SELECT COLUMN_NAME
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'libros'
       AND COLUMN_NAME = 'portada'"
);

if ($portadaColumn) {
    $legacyCovers = $db->fetch(
        "SELECT COUNT(*) AS total
         FROM libros
         WHERE portada IS NOT NULL AND TRIM(portada) <> ''"
    );

    if ((int) ($legacyCovers['total'] ?? 0) > 0) {
        throw new RuntimeException(
            'Hay portadas existentes en libros.portada. Migrá esas referencias a archivos asociados al UID antes de quitar la columna.'
        );
    }

    $db->query("ALTER TABLE libros DROP COLUMN portada");
}

$libros = $db->fetchAll(
    "SELECT id_libro, titulo, autor, editorial, genero
     FROM libros
     ORDER BY id_libro"
);

$db->beginTransaction();

try {
    foreach ($libros as $libro) {
        $uid = BookUid::ensureExists($db, $libro);
        $db->query(
            "UPDATE libros SET uid = :uid WHERE id_libro = :id_libro",
            ['uid' => $uid, 'id_libro' => $libro['id_libro']]
        );
    }

    $db->query(
        "UPDATE libros_uid u
         INNER JOIN libros l ON l.uid = u.uid
         SET u.descripcion = CASE WHEN u.descripcion IS NULL THEN l.descripcion ELSE u.descripcion END,
             u.destacado = CASE WHEN u.destacado IS NULL THEN l.destacado ELSE u.destacado END
         WHERE u.descripcion IS NULL OR u.destacado IS NULL"
    );

    $db->commit();
} catch (Throwable $e) {
    $db->rollback();
    throw $e;
}

$db->query(
    "ALTER TABLE libros
     MODIFY COLUMN uid CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL"
);

$uidForeignKey = $db->fetch(
    "SELECT CONSTRAINT_NAME
     FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'libros'
       AND COLUMN_NAME = 'uid'
       AND REFERENCED_TABLE_NAME = 'libros_uid'
       AND REFERENCED_COLUMN_NAME = 'uid'"
);

if (!$uidForeignKey) {
    $db->query(
        "ALTER TABLE libros
         ADD CONSTRAINT fk_libros_uid
         FOREIGN KEY (uid) REFERENCES libros_uid(uid)"
    );
}

printf("Migración completada: %d libros vinculados a libros_uid.\n", count($libros));
