<?php

require_once "../bootstrap.php";

use App\Helpers\Response;
use App\Classes\Usuario;

header('Content-Type: application/json');

try {
    $usuario = new Usuario();
    $resultado = $usuario->obtenerSesion();
    Response::json($resultado);
} catch (Throwable $error) {
    error_log('No se pudo obtener el estado de sesión: ' . $error->getMessage());
    Response::error('No se pudo consultar la sesión.', 500);
}