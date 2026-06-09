<?php

require_once "../bootstrap.php";

use App\Helpers\{Request, Response};
use App\Classes\Usuario;

header('Content-Type: application/json');

try {
    Request::requireMethod('POST');
    $fields = Request::requireFields(['email', 'password']);

    $usuario = new Usuario();
    $resultado = $usuario->loguearUsuario(
        trim($fields['email']),
        $fields['password']
    );

    Response::json($resultado);
} catch (Exception $e) {
    Response::error($e->getMessage());
}