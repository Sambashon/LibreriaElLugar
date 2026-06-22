<?php

namespace App\Helpers;

/**
 * Response Helper - Unifica respuestas JSON
 */
class Response
{
    /**
     * Envía respuesta JSON y termina la ejecución
     */
    public static function json(array $data, int $httpCode = 200): never
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Envía respuesta de error y termina la ejecución
     */
    public static function error(string $message, int $httpCode = 400): never
    {
        self::json([
            'state' => 'error',
            'message' => $message
        ], $httpCode);
    }

    /**
     * Envía respuesta de éxito y termina la ejecución
     */
    public static function success(string $message, ?array $data = null, int $httpCode = 200): never
    {
        $response = [
            'state' => 'success',
            'message' => $message
        ];

        if ($data !== null) {
            $response = array_merge($response, $data);
        }

        self::json($response, $httpCode);
    }
}
