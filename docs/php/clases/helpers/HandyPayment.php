<?php

namespace App\Helpers;

use RuntimeException;

class HandyPayment
{
    public function crearLink(int $idPedido, int $totalCentavos, array $items): array
    {
        if (HANDY_MERCHANT_SECRET_KEY === '') {
            throw new RuntimeException('Falta configurar HANDY_MERCHANT_SECRET_KEY');
        }
        if (APP_BASE_URL === '' || !$this->esUrlHttps(APP_BASE_URL)) {
            throw new RuntimeException('APP_BASE_URL debe ser una URL pública HTTPS');
        }
        if (!$this->esUrlHttps(HANDY_API_BASE_URL)) {
            throw new RuntimeException('HANDY_API_BASE_URL debe ser una URL HTTPS');
        }
        if (!is_numeric(HANDY_TAX_RATE) || !is_finite((float) HANDY_TAX_RATE)) {
            throw new RuntimeException('HANDY_TAX_RATE debe ser un número válido');
        }

        $tasaPorcentual = (float) HANDY_TAX_RATE;
        if ($tasaPorcentual < 0 || $tasaPorcentual > 100) {
            throw new RuntimeException('HANDY_TAX_RATE debe estar entre 0 y 100');
        }
        $tasaBase = (int) round($tasaPorcentual * 100);
        $totalGravadoCentavos = 0;
        $productos = [];
        foreach ($items as $item) {
            $cantidad = (int) $item['cantidad'];
            $montoCentavos = $this->aCentavos((string) $item['precio']);
            if ($cantidad < 1 || $montoCentavos < 1) {
                throw new RuntimeException('El carrito contiene un producto con precio o cantidad inválidos');
            }

            $gravadoUnitarioCentavos = $tasaBase === 0
                ? 0
                : (int) round(($montoCentavos * 10000) / (10000 + $tasaBase));
            $totalGravadoCentavos += $gravadoUnitarioCentavos * $cantidad;
            $productos[] = [
                'Name' => (string) $item['titulo'],
                'Quantity' => $cantidad,
                'Amount' => $this->aDecimal($montoCentavos),
                'TaxedAmount' => $this->aDecimal($gravadoUnitarioCentavos),
            ];
        }
        if (!$productos || $totalCentavos < 1) {
            throw new RuntimeException('No se puede crear un pago Handy para un carrito vacío');
        }

        $transactionId = $this->crearUuid();
        $payload = [
            'Cart' => [
                'InvoiceNumber' => $idPedido,
                'Currency' => 858,
                'TotalAmount' => $this->aDecimal($totalCentavos),
                'TaxedAmount' => $this->aDecimal($totalGravadoCentavos),
                'TransactionExternalId' => $transactionId,
                'Products' => $productos,
            ],
            'Client' => [
                'CommerceName' => 'Librería El Lugar',
                'SiteUrl' => APP_BASE_URL . '/catalogue.php?handy_return=1',
            ],
            'CallbackURL' => APP_BASE_URL . '/php/scripts/handyWebhook.php',
            'ResponseType' => 'Json',
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $handle = curl_init(rtrim(HANDY_API_BASE_URL, '/') . '/payments');
        if ($handle === false) {
            throw new RuntimeException('No se pudo iniciar la solicitud a Handy');
        }
        try {
            curl_setopt_array($handle, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'merchant-secret-key: ' . HANDY_MERCHANT_SECRET_KEY,
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            $response = curl_exec($handle);
            if ($response === false) {
                throw new RuntimeException('Falló la conexión con Handy: ' . curl_error($handle));
            }
            $httpStatus = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        } finally {
            curl_close($handle);
        }

        if ($httpStatus < 200 || $httpStatus >= 300) {
            throw new RuntimeException(
                'Handy rechazó la creación del pago (HTTP ' . $httpStatus . '): '
                . $this->resumenRespuesta($response)
            );
        }
        try {
            $respuesta = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $error) {
            throw new RuntimeException(
                'Handy devolvió una respuesta JSON inválida (HTTP ' . $httpStatus . ', '
                . $this->resumenRespuesta($response) . ')',
                0,
                $error
            );
        }
        if (is_string($respuesta)) {
            try {
                $respuesta = json_decode($respuesta, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $error) {
                throw new RuntimeException(
                    'Handy devolvió una respuesta JSON anidada inválida (HTTP ' . $httpStatus . ')',
                    0,
                    $error
                );
            }
        }
        $urlPago = is_array($respuesta) ? ($respuesta['url'] ?? $respuesta['Url'] ?? null) : null;
        if (!is_string($urlPago) || $urlPago === '') {
            throw new RuntimeException(
                'Handy respondió sin URL de pago (HTTP ' . $httpStatus . ', '
                . $this->resumenRespuesta($response) . ')'
            );
        }
        if (!$this->esUrlHttps($urlPago)) {
            throw new RuntimeException('Handy devolvió una URL de pago inválida');
        }

        return [
            'transaction_external_id' => $transactionId,
            'url' => $urlPago,
        ];
    }

    private function resumenRespuesta(string $response): string
    {
        try {
            $respuesta = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return 'respuesta no JSON, ' . strlen($response) . ' bytes';
        }
        if (!is_array($respuesta)) {
            $tipo = match (true) {
                is_string($respuesta) => 'cadena',
                is_int($respuesta), is_float($respuesta) => 'número',
                is_bool($respuesta) => 'booleano',
                $respuesta === null => 'null',
                default => get_debug_type($respuesta),
            };
            $detalle = is_string($respuesta)
                ? $this->resumirTextoRespuesta($respuesta)
                : '';

            return 'respuesta JSON escalar (' . $tipo . ')'
                . ($detalle !== '' ? ', valor=' . $detalle : '');
        }

        $campos = array_slice(array_map('strval', array_keys($respuesta)), 0, 20);
        $mensaje = $respuesta['Message'] ?? $respuesta['message'] ?? null;
        if (!is_string($mensaje)) {
            $mensaje = '';
        }
        $mensaje = $this->resumirTextoRespuesta($mensaje);

        return 'campos=' . json_encode($campos, JSON_UNESCAPED_UNICODE)
            . ($mensaje !== '' ? '; mensaje=' . $mensaje : '');
    }

    private function resumirTextoRespuesta(string $texto): string
    {
        $texto = str_replace(HANDY_MERCHANT_SECRET_KEY, '[redactado]', $texto);
        $texto = preg_replace('/https?:\/\/[^\s"\']+/i', '[URL omitida]', $texto) ?? '';
        $texto = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $texto) ?? '';
        if (strlen($texto) > 300) {
            $texto = substr($texto, 0, 300);
        }

        return json_encode($texto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

    private function esUrlHttps(string $url): bool
    {
        $partes = parse_url($url);
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && is_array($partes)
            && strtolower($partes['scheme'] ?? '') === 'https'
            && isset($partes['host']);
    }

    private function crearUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20)
        );
    }

    private function aCentavos(string $precio): int
    {
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $precio)) {
            return -1;
        }
        [$unidades, $centavos] = array_pad(explode('.', $precio, 2), 2, '0');

        return ((int) $unidades * 100) + (int) str_pad($centavos, 2, '0');
    }

    private function aDecimal(int $centavos): string
    {
        return intdiv($centavos, 100) . '.' . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }
}
