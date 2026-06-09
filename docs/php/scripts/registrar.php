<?php

require_once "../bootstrap.php";

use App\Helpers\{Request, Response};
use App\Classes\Usuario;

header('Content-Type: application/json');

try {
    Request::requireMethod('POST');

    $formtype = Request::getPost('formtype');
    if ($formtype !== 'register') {
        Response::error('Formulario inválido');
    }

    $fields = Request::requireFields(['nombre', 'apellido', 'password', 'email']);

    $usuario = new Usuario();

    // Registrar usuario
    $resultado = $usuario->registrarUsuario(
        trim($fields['nombre']),
        trim($fields['apellido']),
        $fields['password'],
        trim($fields['email'])
    );

    // Si el registro fue exitoso, iniciar sesión automáticamente
    if ($resultado['state'] === 'success') {
        $loginResult = $usuario->loguearUsuario(
            trim($fields['email']),
            $fields['password']
        );

        if ($loginResult['state'] === 'success') {
            Response::success(
                '¡Cuenta creada! Sesión iniciada automáticamente',
                ['user' => $loginResult['user']]
            );
        } else {
            Response::json($resultado);
        }
    } else {
        Response::json($resultado);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}