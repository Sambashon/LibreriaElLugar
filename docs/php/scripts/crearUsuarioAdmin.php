<?php
require_once "../clases/usuario.php";

$nombre = "Admin";
$apellido = "Usuario";
$email = "admin@ejemplo.com";
$password = "123";

$usuario = new Usuario();
$pdo = $usuario->getConnection();

$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "
    INSERT INTO usuarios
    (nombre, apellido, email, password, admin, verificado)
    VALUES
    (:nombre, :apellido, :email, :password, 1, 1)
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':nombre' => $nombre,
    ':apellido' => $apellido,
    ':email' => $email,
    ':password' => $hash
]);

echo "✅ Usuario admin creado correctamente";