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
    foreach (['nombre', 'apellido', 'password', 'email'] as $field) {
        if (!is_string($fields[$field])) {
            Response::error('Los datos del formulario no son válidos.', 400);
        }
    }

    if (
        strlen($fields['password']) < 8
        || strlen($fields['password']) > 1024
        || !filter_var(trim($fields['email']), FILTER_VALIDATE_EMAIL)
    ) {
        Response::error('Revisá el email y la contraseña (entre 8 y 1024 caracteres).', 400);
    }

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
                [
                    'user' => $loginResult['user'],
                    'csrf_token' => $loginResult['csrf_token'],
                ]
            );
        } else {
            Response::json($resultado);
        }
    } else {
        Response::json($resultado);
    }
} catch (Throwable $error) {
    error_log('No se pudo registrar el usuario: ' . $error->getMessage());
    Response::error('No se pudo completar el registro. Intentá nuevamente.', 500);
}