<?php

require_once '../bootstrap.php';

use App\Classes\Pedido;
use App\Helpers\{AdminAccess, Request, Response};

AdminAccess::requireAdmin();
Request::requireMethod('POST');

$idPedido = filter_var($_POST['id_pedido'] ?? null, FILTER_VALIDATE_INT);
$estado = $_POST['estado'] ?? null;
if (!$idPedido || !is_string($estado)) {
    Response::error('Pedido o estado inválido');
}

try {
    $pedido = new Pedido();
    $resultado = $pedido->actualizarEstado($idPedido, $estado);
    Response::json($resultado, $resultado['state'] === 'success' ? 200 : ($resultado['message'] === 'Pedido no encontrado' ? 404 : 400));
} catch (Throwable $error) {
    error_log('No se pudo actualizar el pedido: ' . $error->getMessage());
    Response::error('No se pudo actualizar el pedido', 500);
}
