<?php

require_once __DIR__ . "/../bootstrap.php";

use App\Helpers\{AdminAccess, Request, Response};

header('Content-Type: application/json; charset=utf-8');

try {
    Request::requireMethod('POST');
    AdminAccess::requireAdmin();

    $fields = Request::requireFields(['id_libro', 'titulo']);

    $idLibro = (int) $fields['id_libro'];
    if ($idLibro <= 0) {
        Response::error('ID de libro inválido', 400);
    }

    $titulo = trim($fields['titulo']);
    $autor = trim(Request::getPost('autor', ''));
    $editorial = trim(Request::getPost('editorial', ''));
    $genero = trim(Request::getPost('genero', ''));
    $descripcion = trim(Request::getPost('descripcion', ''));

    $precioRaw = Request::getPost('precio');
    if ($precioRaw === null || $precioRaw === '' || !is_numeric($precioRaw) || (float) $precioRaw < 0) {
        Response::error('Precio inválido', 400);
    }
    $precio = (float) $precioRaw;

    $stockRaw = Request::getPost('stock', '0');
    if (!is_numeric($stockRaw) || (int) $stockRaw < 0) {
        Response::error('Stock inválido', 400);
    }
    $stock = (int) $stockRaw;

    $db = new LibreriaDB();

    $existing = $db->fetch(
        "SELECT id_libro FROM libros WHERE id_libro = ?",
        [$idLibro]
    );
    if (!$existing) {
        Response::error('Libro no encontrado', 404);
    }

    $db->query(
        "UPDATE libros
         SET titulo = ?, autor = ?, editorial = ?, genero = ?, precio = ?, stock = ?, descripcion = ?
         WHERE id_libro = ?",
        [$titulo, $autor, $editorial, $genero, $precio, $stock, $descripcion, $idLibro]
    );

    Response::success('Libro actualizado correctamente', [
        'libro' => [
            'id'        => $idLibro,
            'titulo'    => $titulo,
            'autor'     => $autor,
            'editorial' => $editorial,
            'genero'    => $genero,
            'precio'      => $precio,
            'stock'       => $stock,
            'descripcion' => $descripcion,
        ],
    ]);
} catch (Exception $e) {
    Response::error($e->getMessage());
}
