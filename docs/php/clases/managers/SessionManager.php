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
            $lifetime = SESSION_LIFETIME_DAYS * 86400;
            ini_set('session.gc_maxlifetime', (string) $lifetime);
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_trans_sid', '0');
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => COOKIE_PATH,
                'secure' => COOKIE_SECURE,
                'httponly' => COOKIE_HTTPONLY,
                'samesite' => COOKIE_SAMESITE,
            ]);
            session_start();

            if (
                isset($_SESSION['id_usuario'])
                && (
                    !isset($_SESSION['auth_expires_at'])
                    || !is_numeric($_SESSION['auth_expires_at'])
                    || (int) $_SESSION['auth_expires_at'] <= time()
                )
            ) {
                $this->destruirSesionActual();
                session_start();
            }
        }
    }

    /**
     * Regenera el ID de sesión (para después de login)
     */
    public function regenerateId(): void
    {
        $this->iniciar();
        session_regenerate_id(true);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

        $this->destruirSesionActual();

        return [
            'state' => 'success',
            'message' => 'Sesión cerrada correctamente'
        ];
    }

    private function destruirSesionActual(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'],
                ]
            );
        }

        session_destroy();
    }

    /**
     * Obtiene el estado de la sesión actual
     */
    public function obtenerEstado(): array
    {
        $this->iniciar();
        $csrfToken = $this->obtenerCsrfToken();

        if (!$this->existe('id_usuario')) {
            return [
                'logged' => false,
                'csrf_token' => $csrfToken,
            ];
        }

        return [
            'logged' => true,
            'csrf_token' => $csrfToken,
            'user' => [
                'id_usuario' => $this->obtener('id_usuario'),
                'nombre' => $this->obtener('nombre'),
                'apellido' => $this->obtener('apellido'),
                'email' => $this->obtener('email'),
                'admin' => (bool) $this->obtener('admin', false)
            ]
        ];
    }

    public function obtenerCsrfToken(): string
    {
        $this->iniciar();
        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}
