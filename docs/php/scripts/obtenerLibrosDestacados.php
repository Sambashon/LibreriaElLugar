<?php

require_once __DIR__ . "/../bootstrap.php";

use App\Helpers\Response;

try {
    $db = new LibreriaDB();
    $libros = $db->fetchAll(
        "SELECT id_libro, uid, titulo, autor
         FROM libros
         WHERE destacado = 1
         ORDER BY titulo ASC"
    );

    Response::success('Libros destacados obtenidos correctamente', [
        'libros' => array_map(static fn(array $libro): array => [
            'id' => (int) $libro['id_libro'],
            'uid' => $libro['uid'],
            'titulo' => $libro['titulo'],
            'autor' => $libro['autor'],
        ], $libros),
    ]);
} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}
