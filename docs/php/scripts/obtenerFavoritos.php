<?php

require_once "../bootstrap.php";

use App\Helpers\Response;
use App\Classes\Favoritos;
use App\Managers\SessionManager;

header('Content-Type: application/json');

try {
    $session = new SessionManager();
    $idUsuario = $session->obtener('id_usuario');
    if (!$idUsuario) {
        Response::error('Debes iniciar sesión para ver favoritos', 401);
    }

    $favoritos = new Favoritos();
    Response::json($favoritos->listar((int) $idUsuario));
} catch (Throwable $error) {
    Response::serverError($error, 'No se pudieron obtener los favoritos', 'No se pudieron cargar los favoritos.');
}
