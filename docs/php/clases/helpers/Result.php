<?php

namespace App\Helpers;

/**
 * Result Helper - Clase tipada para respuestas consistentes
 */
class Result
{
    public readonly string $state;
    public readonly string $message;
    public readonly ?array $data;
    public readonly ?int $httpCode;

    private function __construct(
        string $state,
        string $message,
        ?array $data = null,
        ?int $httpCode = null
    ) {
        $this->state = $state;
        $this->message = $message;
        $this->data = $data;
        $this->httpCode = $httpCode ?? ($state === 'success' ? 200 : 400);
    }

    /**
     * Crea un resultado exitoso
     */
    public static function success(string $message, ?array $data = null): self
    {
        return new self('success', $message, $data, 200);
    }

    /**
     * Crea un resultado de error
     */
    public static function error(string $message, int $httpCode = 400): self
    {
        return new self('error', $message, null, $httpCode);
    }

    /**
     * Convierte el resultado a array para Response::json()
     */
    public function toArray(): array
    {
        $result = [
            'state' => $this->state,
            'message' => $this->message
        ];

        if ($this->data !== null) {
            $result = array_merge($result, $this->data);
        }

        return $result;
    }

    /**
     * Alias para toArray() para mantener compatibilidad
     */
    public function toJSON(): array
    {
        return $this->toArray();
    }
}
