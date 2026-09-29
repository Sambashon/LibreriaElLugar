<?php

require_once __DIR__ . "/../bootstrap.php";

use App\Helpers\{AdminAccess, Request, Response};

header('Content-Type: application/json; charset=utf-8');

try {
    Request::requireMethod('POST');
    AdminAccess::requireAdmin();

    $fields = Request::requireFields(['titulo', 'autor']);

    $titulo = trim($fields['titulo']);
    $autor = trim($fields['autor']);
    $editorial = trim(Request::getPost('editorial', ''));
    $genero = trim(Request::getPost('genero', ''));
    $descripcion = trim(Request::getPost('descripcion', ''));
    $info_adicional = trim(Request::getPost('info_adicional', ''));

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

    $db->query(
        "INSERT INTO libros (titulo, autor, editorial, genero, precio, stock, descripcion, info_adicional)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [$titulo, $autor, $editorial, $genero, $precio, $stock, $descripcion, $info_adicional]
    );

    $idLibro = (int) $db->lastInsertId();

    Response::success('Libro creado correctamente', [
        'libro' => [
            'id'        => $idLibro,
            'titulo'    => $titulo,
            'autor'     => $autor,
            'editorial' => $editorial,
            'genero'    => $genero,
            'precio'      => $precio,
            'stock'       => $stock,
            'descripcion' => $descripcion,
            'info_adicional' => $info_adicional
        ],
    ]);
} catch (Exception $e) {
    Response::error($e->getMessage());
}
