<?php

namespace App\Helpers;

use App\Managers\SessionManager;

class AdminAccess
{
    public static function esAdmin(): bool
    {
        $session = new SessionManager();

        return $session->obtener('id_usuario') !== null
            && (bool) $session->obtener('admin', false);
    }

    public static function requireAdmin(): void
    {
        if (!self::esAdmin()) {
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            exit('Forbidden');
        }
    }

    public static function requireAdminPage(): void
    {
        if (!self::esAdmin()) {
            header('Location: index.html', true, 302);
            exit;
        }
    }
}
