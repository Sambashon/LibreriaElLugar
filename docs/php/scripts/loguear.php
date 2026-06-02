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

$email = $_POST["email"] ?? null;
$password = $_POST["password"] ?? null;

if (!$email || !$password) {
    echo json_encode([
        "state" => "error",
        "message" => "Faltan datos"
    ]);
    exit;
}

$usuarioDB = new Usuario();

$resultado = $usuarioDB->loguearUsuario(
    trim($email),
    $password
);

echo json_encode($resultado);
exit;