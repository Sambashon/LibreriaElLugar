<?php

require_once __DIR__ . "/../bootstrap.php";
use App\Classes\Usuario;
use App\Managers\SessionManager;
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "state" => "error",
        "message" => "Método no permitido"
    ]);
    exit;
}

$session = new SessionManager();
$session->iniciar();

$idUsuario = $session->obtener("id_usuario");
if ($idUsuario === null) {
    echo json_encode([
        "state" => "error",
        "message" => "Debes iniciar sesión"
    ]);
    exit;
}

$password = $_POST["password"] ?? null;

if (!$password) {
    echo json_encode([
        "state" => "error",
        "message" => "Debes ingresar tu contraseña"
    ]);
    exit;
}

$usuario = new Usuario();

$resultado = $usuario->eliminarUsuario(
    (int) $idUsuario,
    $password
);

echo json_encode($resultado);
exit;