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

    $status = $resultado['message'] === 'Demasiados intentos. Esperá 15 minutos e intentá nuevamente.'
        ? 429
        : 200;
    Response::json($resultado, $status);
} catch (Throwable $error) {
    error_log('No se pudo iniciar sesión: ' . $error->getMessage());
    Response::error('No se pudo iniciar sesión. Intentá nuevamente.', 500);
}