<?php

require_once __DIR__ . "/../clases/usuario.php";
use App\Classes\Usuario;
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "state" => "error",
        "message" => "Método no permitido"
    ]);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["id_usuario"])) {
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
    (int) $_SESSION["id_usuario"],
    $password
);

echo json_encode($resultado);
exit;