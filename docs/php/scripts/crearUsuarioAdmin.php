<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Este script solo puede ejecutarse desde CLI.\n");
}

require_once __DIR__ . "/../bootstrap.php";

$nombre = getenv('APP_ADMIN_NAME');
$apellido = getenv('APP_ADMIN_LASTNAME');
$email = getenv('APP_ADMIN_EMAIL');
$password = getenv('APP_ADMIN_PASSWORD');
if (
    !$nombre
    || !$apellido
    || !$email
    || !$password
    || strlen($password) < 12
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    fwrite(STDERR, "Configura APP_ADMIN_NAME, APP_ADMIN_LASTNAME, APP_ADMIN_EMAIL y una APP_ADMIN_PASSWORD de al menos 12 caracteres.\n");
    exit(1);
}

$db = new LibreriaDB();
$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "
    INSERT INTO usuarios
    (nombre, apellido, email, password, admin, verificado)
    VALUES
    (:nombre, :apellido, :email, :password, 1, 1)
";

$db->execute($sql, [
    'nombre' => $nombre,
    'apellido' => $apellido,
    'email' => $email,
    'password' => $hash
]);

fwrite(STDOUT, "Usuario administrador creado correctamente.\n");