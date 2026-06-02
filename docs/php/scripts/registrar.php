<?php

require_once "../clases/usuario.php";

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "state" => "error",
        "message" => "Método no permitido"
    ]);
    exit;
}

if (!isset($_POST["formtype"]) || $_POST["formtype"] !== "register") {
    echo json_encode([
        "state" => "error",
        "message" => "Formulario inválido"
    ]);
    exit;
}

// Validación básica para evitar undefined array key
$nombre   = $_POST["nombre"] ?? null;
$apellido = $_POST["apellido"] ?? null;
$password = $_POST["password"] ?? null;
$email    = $_POST["email"] ?? null;

if (!$nombre || !$apellido || !$password || !$email) {
    echo json_encode([
        "state" => "error",
        "message" => "Faltan datos en el formulario"
    ]);
    exit;
}

$usuarioDB = new Usuario();

$resultado = $usuarioDB->registrarUsuario(
    trim($nombre),
    trim($apellido),
    $password,
    trim($email)
);

echo json_encode($resultado);
exit;