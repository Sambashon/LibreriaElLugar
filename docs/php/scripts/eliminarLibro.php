<?php

require_once __DIR__ . "/../bootstrap.php";

use App\Helpers\{AdminAccess, Request, Response};

header('Content-Type: application/json; charset=utf-8');

try {
    Request::requireMethod('POST');
    AdminAccess::requireAdmin();

    $fields = Request::requireFields(['id_libro']);
    $idLibro = filter_var($fields['id_libro'], FILTER_VALIDATE_INT);
    if (!$idLibro || $idLibro <= 0) {
        Response::error('ID de libro inválido', 400);
    }

    $db = new LibreriaDB();
    if (!$db->execute("DELETE FROM libros WHERE id_libro = ?", [$idLibro])) {
        Response::error('Libro no encontrado', 404);
    }

    Response::success('Libro eliminado correctamente');
} catch (Exception $e) {
    Response::error($e->getMessage());
}
