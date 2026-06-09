<?php

require_once "../bootstrap.php";

use App\Helpers\Response;
use App\Classes\Usuario;

header('Content-Type: application/json');

try {
    $usuario = new Usuario();
    $resultado = $usuario->obtenerSesion();
    Response::json($resultado);
} catch (Exception $e) {
    Response::error($e->getMessage());
}