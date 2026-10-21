<?php

require_once '../bootstrap.php';

use App\Classes\Pedido;
use App\Helpers\Response;

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    Response::error('Método no permitido', 405);
}

$rawBody = file_get_contents('php://input');
if (!is_string($rawBody)) {
    Response::error('No se pudo leer la notificación de pago');
}
try {
    $payload = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $error) {
    Response::error('El cuerpo de la notificación no es JSON válido');
}
if (!is_array($payload)) {
    Response::error('La notificación de pago contiene datos inválidos');
}

$transactionId = $payload['TransactionExternalId'] ?? null;
$purchase = $payload['PurchaseData'] ?? null;
$status = is_array($purchase) ? ($purchase['Status'] ?? null) : null;
$currency = is_array($purchase) ? ($purchase['Currency'] ?? null) : null;
$total = is_array($purchase) ? ($purchase['TotalAmount'] ?? null) : null;
$esEntero = static fn ($value): bool => is_int($value)
    || (is_string($value) && preg_match('/^\d+$/', $value) === 1);
if (
    !is_string($transactionId)
    || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $transactionId)
    || !$esEntero($status)
    || !in_array((int) $status, [0, 1, 2, 3], true)
    || !$esEntero($currency)
    || (int) $currency !== 858
    || (!is_string($total) && !is_int($total) && !is_float($total))
) {
    Response::error('La notificación de pago contiene datos inválidos');
}

$totalString = (string) $total;
if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $totalString)) {
    Response::error('El monto notificado no es válido');
}
[$unidades, $centavos] = array_pad(explode('.', $totalString, 2), 2, '0');
$totalCentavos = ((int) $unidades * 100) + (int) str_pad($centavos, 2, '0');

try {
    $resultado = (new Pedido())->actualizarPagoHandy(
        $transactionId,
        (int) $status,
        $totalCentavos
    );
    Response::json($resultado, $resultado['state'] === 'success' ? 200 : 400);
} catch (Throwable $error) {
    error_log('No se pudo procesar la notificación de Handy: ' . $error->getMessage());
    Response::error('No se pudo procesar la notificación de pago', 500);
}
