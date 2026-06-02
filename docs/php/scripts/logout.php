<?php

require_once "../clases/usuario.php";

header('Content-Type: application/json');

$usuario = new Usuario();

echo json_encode(
    $usuario->cerrarSesion()
);

exit;