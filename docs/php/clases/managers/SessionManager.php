<?php

namespace App\Managers;

/**
 * SessionManager - Gestiona sesiones PHP
 */
class SessionManager
{
    /**
     * Inicia la sesión PHP si no está iniciada
     */
    public function iniciar(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Regenera el ID de sesión (para después de login)
     */
    public function regenerateId(): void
    {
        $this->iniciar();
        session_regenerate_id(true);
    }

    /**
     * Establece un valor en la sesión
     */
    public function establecer(string $key, mixed $value): void
    {
        $this->iniciar();
        $_SESSION[$key] = $value;
    }

    /**
     * Obtiene un valor de la sesión
     */
    public function obtener(string $key, mixed $default = null): mixed
    {
        $this->iniciar();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verifica si existe una clave en la sesión
     */
    public function existe(string $key): bool
    {
        $this->iniciar();
        return isset($_SESSION[$key]);
    }

    /**
     * Obtiene toda la sesión
     */
    public function obtenerTodo(): array
    {
        $this->iniciar();
        return $_SESSION;
    }

    /**
     * Destruye la sesión y borra la cookie
     */
    public function destruir(): array
    {
        $this->iniciar();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        return [
            'state' => 'success',
            'message' => 'Sesión cerrada correctamente'
        ];
    }

    /**
     * Obtiene el estado de la sesión actual
     */
    public function obtenerEstado(): array
    {
        $this->iniciar();

        if (!$this->existe('id_usuario')) {
            return [
                'logged' => false
            ];
        }

        return [
            'logged' => true,
            'user' => [
                'id_usuario' => $this->obtener('id_usuario'),
                'nombre' => $this->obtener('nombre'),
                'apellido' => $this->obtener('apellido'),
                'email' => $this->obtener('email'),
                'admin' => (bool) $this->obtener('admin', false)
            ]
        ];
    }
}
