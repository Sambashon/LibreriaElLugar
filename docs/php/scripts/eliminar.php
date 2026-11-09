<?php

require_once __DIR__ . "/../bootstrap.php";
use App\Classes\Usuario;
use App\Helpers\{Request, Response};
use App\Managers\SessionManager;
header("Content-Type: application/json");

Request::requireMethod('POST');

$session = new SessionManager();
$session->iniciar();

$idUsuario = $session->obtener("id_usuario");
if ($idUsuario === null) {
    Response::error('Debes iniciar sesión', 401);
}

$password = $_POST["password"] ?? null;

if (!is_string($password) || $password === '') {
    Response::error('Debes ingresar tu contraseña', 400);
}

$usuario = new Usuario();

$resultado = $usuario->eliminarUsuario(
    (int) $idUsuario,
    $password
);

Response::json($resultado);