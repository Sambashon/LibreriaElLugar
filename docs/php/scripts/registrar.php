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

// 1. Registrar usuario
$resultado = $usuarioDB->registrarUsuario(
    trim($nombre),
    trim($apellido),
    $password,
    trim($email)
);

// 2. Si el registro fue exitoso, iniciar sesión automáticamente
if ($resultado["state"] === "success") {
    $loginResult = $usuarioDB->loguearUsuario(
        trim($email),
        $password
    );
    
    // Retornar mensaje personalizado pero con datos de login
    if ($loginResult["state"] === "success") {
        echo json_encode([
            "state" => "success",
            "message" => "¡Cuenta creada! Sesión iniciada automáticamente",
            "user" => $loginResult["user"]
        ]);
    } else {
        // Registro exitoso pero error en login (raro pero posible)
        echo json_encode($resultado);
    }
} else {
    // Error en el registro
    echo json_encode($resultado);
}

exit;