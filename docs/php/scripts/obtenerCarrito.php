<?php

require_once "../bootstrap.php";

use App\Helpers\Response;
use App\Classes\Carrito;
use App\Managers\SessionManager;

header('Content-Type: application/json');

try {
    $session = new SessionManager();
    $idUsuario = $session->obtener('id_usuario');
    if (!$idUsuario) {
        Response::error('Debes iniciar sesión para ver el carrito', 401);
    }

    $carrito = new Carrito();
    Response::json($carrito->listar((int) $idUsuario));
} catch (Exception $e) {
    Response::error($e->getMessage());
}
