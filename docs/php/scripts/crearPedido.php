<?php

require_once '../bootstrap.php';

use App\Classes\Pedido;
use App\Helpers\{Request, Response};
use App\Managers\SessionManager;

Request::requireMethod('POST');

$idUsuario = (new SessionManager())->obtener('id_usuario');
if (!$idUsuario) {
    Response::error('Debes iniciar sesión para confirmar un pedido', 401);
}

$nombreRaw = $_POST['nombre'] ?? null;
$telefonoRaw = $_POST['telefono'] ?? null;
$emailRaw = $_POST['email'] ?? null;
$comentariosRaw = $_POST['comentarios'] ?? '';
$metodoPago = $_POST['metodo_pago'] ?? null;
if (
    !is_string($nombreRaw)
    || !is_string($telefonoRaw)
    || !is_string($emailRaw)
    || !is_string($comentariosRaw)
    || !is_string($metodoPago)
) {
    Response::error('Los datos del cliente no son válidos');
}
$nombre = trim($nombreRaw);
$telefono = trim($telefonoRaw);
$email = trim($emailRaw);
$comentarios = trim($comentariosRaw);
$longitud = static function (string $value): int {
    $length = preg_match_all('/./us', $value);

    return $length === false ? PHP_INT_MAX : $length;
};

if ($nombre === '' || $longitud($nombre) > 200) {
    Response::error('Ingresá un nombre válido (máximo 200 caracteres)');
}
if ($telefono === '' || $longitud($telefono) > 40 || !preg_match('/^[0-9+().\-\s]{6,40}$/', $telefono)) {
    Response::error('Ingresá un teléfono válido');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $longitud($email) > 150) {
    Response::error('Ingresá un email válido');
}
if ($longitud($comentarios) > 2000) {
    Response::error('Los comentarios no pueden superar los 2000 caracteres');
}
if (!in_array($metodoPago, ['en_libreria', 'transferencia_bancaria'], true)) {
    Response::error('Método de pago inválido');
}

try {
    $pedido = new Pedido();
    $resultado = $pedido->crear((int) $idUsuario, [
        'nombre' => $nombre,
        'telefono' => $telefono,
        'email' => $email,
        'comentarios' => $comentarios,
        'metodo_pago' => $metodoPago,
    ]);
    Response::json($resultado, $resultado['state'] === 'success' ? 201 : 400);
} catch (Throwable $error) {
    error_log('No se pudo registrar el pedido: ' . $error->getMessage());
    Response::error('No se pudo registrar el pedido. Intentá nuevamente.', 500);
}
