<?php

require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../clases/helpers/BookUid.php";

use App\Helpers\{AdminAccess, BookUid, Request, Response};

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
    $destacado = Request::getPost('destacado', '0') === '1' ? 1 : 0;

    $db = new LibreriaDB();
    $uid = BookUid::ensureExists($db, [
        'titulo' => $titulo,
        'autor' => $autor,
        'editorial' => $editorial,
        'genero' => $genero,
    ]);

    $db->query(
        "INSERT INTO libros (uid, titulo, autor, editorial, genero, precio, stock, descripcion, info_adicional, destacado)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$uid, $titulo, $autor, $editorial, $genero, $precio, $stock, $descripcion, $info_adicional, $destacado]
    );

    $idLibro = (int) $db->lastInsertId();

    Response::success('Libro creado correctamente', [
        'libro' => [
            'id'        => $idLibro,
            'uid'       => $uid,
            'titulo'    => $titulo,
            'autor'     => $autor,
            'editorial' => $editorial,
            'genero'    => $genero,
            'precio'      => $precio,
            'stock'       => $stock,
            'descripcion' => $descripcion,
            'info_adicional' => $info_adicional,
            'destacado' => (bool) $destacado,
        ],
    ]);
} catch (Exception $e) {
    Response::error($e->getMessage());
}
