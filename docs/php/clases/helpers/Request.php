<?php

namespace App\Helpers;

/**
 * Request Helper - Valida y obtiene datos de request
 */
class Request
{
    /**
     * Verifica que el método HTTP sea el requerido
     * @throws Exception Si el método no coincide
     */
    public static function requireMethod(string $method): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            Response::error('Método no permitido', 405);
        }
    }

    /**
     * Requiere múltiples campos del POST
     * @param array $fields Array de nombres de campos requeridos
     * @return array Array con los valores de los campos
     * @throws Exception Si algún campo está faltando
     */
    public static function requireFields(array $fields): array
    {
        $result = [];

        foreach ($fields as $field) {
            $value = $_POST[$field] ?? null;

            if ($value === null || $value === '') {
                Response::error("Campo requerido: $field", 400);
            }

            $result[$field] = $value;
        }

        return $result;
    }

    /**
     * Obtiene un valor del POST con default opcional
     */
    public static function getPost(string $key, ?string $default = null): ?string
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtiene todos los datos del POST
     */
    public static function getAllPost(): array
    {
        return $_POST;
    }
}
