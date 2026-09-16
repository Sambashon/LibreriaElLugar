<?php

require_once "../bootstrap.php";

use App\Helpers\{Request, Response};
use App\Classes\Carrito;
use App\Managers\SessionManager;

header('Content-Type: application/json');

try {
    Request::requireMethod('POST');

    $session = new SessionManager();
    $idUsuario = $session->obtener('id_usuario');
    if (!$idUsuario) {
        Response::error('Debes iniciar sesión para usar el carrito', 401);
    }

    $fields = Request::requireFields(['id_libro']);
    $idLibro = (int) $fields['id_libro'];
    if ($idLibro < 1) {
        Response::error('Libro inválido', 400);
    }

    $cantidadRaw = Request::getPost('cantidad', '1');
    $cantidad = is_numeric($cantidadRaw) ? (int) $cantidadRaw : 1;

    $carrito = new Carrito();
    Response::json($carrito->agregarLibro((int) $idUsuario, $idLibro, $cantidad));
} catch (Exception $e) {
    Response::error($e->getMessage());
}
