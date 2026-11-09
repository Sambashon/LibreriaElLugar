<?php

require_once "../bootstrap.php";

use App\Helpers\{Request, Response};
use App\Classes\Usuario;

header('Content-Type: application/json');

try {
    Request::requireMethod('POST');
    $usuario = new Usuario();
    $resultado = $usuario->cerrarSesion();
    Response::json($resultado);
} catch (Throwable $error) {
    error_log('No se pudo cerrar sesión: ' . $error->getMessage());
    Response::error('No se pudo cerrar sesión.', 500);
}