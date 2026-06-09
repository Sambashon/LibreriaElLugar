<?php

namespace App\Managers;

/**
 * TokenManager - Gestiona tokens persistentes (remember-me)
 * Patrón: Selector (96 bits) + Hash (SHA256)
 * Rotación: Al restaurar sesión, crear nuevo token y eliminar el viejo
 */
class TokenManager extends \LibreriaDB
{
    private const TOKEN_COOKIE_NAME = COOKIE_NAME;
    private const TOKEN_EXPIRATION_DAYS = TOKEN_EXPIRATION_DAYS;

    /**
     * Crea un nuevo token persistente para un usuario
     * Retorna ['selector' => '...', 'token' => '...'] para guardar en cliente
     */
    public function crearTokenPersistente(int $idUsuario): array
    {
        // Generar selector y verifier (random)
        $selector = bin2hex(random_bytes(TOKEN_SELECTOR_LENGTH));
        $verifier = bin2hex(random_bytes(TOKEN_VERIFIER_LENGTH));
        
        // Hash del verifier (lo que se guarda en BD)
        $hashToken = hash('sha256', $verifier);
        
        // Fecha de expiración
        $expiraEn = date('Y-m-d H:i:s', time() + (self::TOKEN_EXPIRATION_DAYS * 86400));

        try {
            // Eliminar token anterior del usuario si existe
            $this->execute(
                "DELETE FROM user_tokens WHERE id_usuario = :id_usuario",
                ['id_usuario' => $idUsuario]
            );

            // Insertar nuevo token
            $this->execute(
                "INSERT INTO user_tokens (id_usuario, selector, hash_token, expira_en)
                VALUES (:id_usuario, :selector, :hash_token, :expira_en)",
                [
                    'id_usuario' => $idUsuario,
                    'selector' => $selector,
                    'hash_token' => $hashToken,
                    'expira_en' => $expiraEn
                ]
            );

            return [
                'selector' => $selector,
                'token' => $verifier
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Restaura sesión desde cookie con token persistente
     * Valida selector + hash y rota el token
     */
    public function restaurarSesionDesdeCookie(string $cookieValue): ?array
    {
        if (empty($cookieValue)) {
            return null;
        }

        // Extraer selector y token de la cookie
        // Formato esperado: "selector:token"
        $partes = explode(':', $cookieValue, 2);
        if (count($partes) !== 2) {
            return null;
        }

        [$selector, $tokenVerifier] = $partes;
        $hashVerifier = hash('sha256', $tokenVerifier);

        try {
            // Buscar token en BD
            $tokenDB = $this->fetch(
                "SELECT id_usuario, hash_token, expira_en 
                FROM user_tokens 
                WHERE selector = :selector 
                AND expira_en > NOW()",
                ['selector' => $selector]
            );

            if (!$tokenDB) {
                // Token no encontrado o expirado
                $this->eliminarTokenPersistente($selector);
                return null;
            }

            // Verificar hash
            if (!hash_equals($tokenDB['hash_token'], $hashVerifier)) {
                // Intento de manipulación - eliminar todos los tokens del usuario
                $this->eliminarTodosTokens($tokenDB['id_usuario']);
                return null;
            }

            // Token válido - rotar (crear nuevo, eliminar viejo)
            $this->execute(
                "DELETE FROM user_tokens WHERE selector = :selector",
                ['selector' => $selector]
            );

            return [
                'id_usuario' => (int) $tokenDB['id_usuario']
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Elimina un token persistente específico
     */
    public function eliminarTokenPersistente(string $selector): bool
    {
        try {
            return $this->execute(
                "DELETE FROM user_tokens WHERE selector = :selector",
                ['selector' => $selector]
            );
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Elimina todos los tokens de un usuario
     */
    public function eliminarTodosTokens(int $idUsuario): bool
    {
        try {
            return $this->execute(
                "DELETE FROM user_tokens WHERE id_usuario = :id_usuario",
                ['id_usuario' => $idUsuario]
            );
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Limpia tokens expirados (operación de mantenimiento)
     * Llamar periodicamente (desde cron o al cerrar sesión)
     */
    public function limpiarTokensExpirados(): int
    {
        try {
            $stmt = $this->query(
                "DELETE FROM user_tokens WHERE expira_en < NOW()"
            );
            return $stmt->rowCount();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Borra la cookie de token persistente
     */
    public static function borrarCookie(): void
    {
        setcookie(
            self::TOKEN_COOKIE_NAME,
            '',
            time() - 42000,
            COOKIE_PATH,
            '',
            COOKIE_SECURE,
            COOKIE_HTTPONLY
        );
    }
}
