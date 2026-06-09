<?php

namespace App\Classes;

require_once "libreriaDb.php";
require_once __DIR__ . "/../bootstrap.php";

use App\Managers\{SessionManager, TokenManager};
use App\Helpers\Result;

class Usuario extends \LibreriaDB
{
    private SessionManager $sessionManager;
    private TokenManager $tokenManager;

    public function __construct(
        ?SessionManager $sessionManager = null,
        ?TokenManager $tokenManager = null
    ) {
        parent::__construct();
        $this->sessionManager = $sessionManager ?? new SessionManager();
        $this->tokenManager = $tokenManager ?? new TokenManager();
    }
    /**
     * Verifica si un nombre de usuario y email están disponibles
     */
    public function verificarUsuario(
        string $nombre,
        string $email
    ): array {
        if ($this->nombreExiste($nombre)) {
            return Result::error("El usuario ya existe")->toArray();
        }

        if ($this->emailExiste($email)) {
            return Result::error("El email ya está registrado")->toArray();
        }

        return Result::success("Usuario y email disponibles")->toArray();
    }

    /**
     * Verifica si un email ya existe en la BD
     */
    private function emailExiste(string $email): bool
    {
        $resultado = $this->fetch(
            "SELECT 1 FROM usuarios WHERE email = :email",
            ['email' => $email]
        );
        return (bool) $resultado;
    }

    /**
     * Verifica si un nombre de usuario ya existe en la BD
     */
    private function nombreExiste(string $nombre): bool
    {
        $resultado = $this->fetch(
            "SELECT 1 FROM usuarios WHERE nombre = :nombre",
            ['nombre' => $nombre]
        );
        return (bool) $resultado;
    }

    /**
     * Registra un nuevo usuario
     */
    public function registrarUsuario(
        string $nombre,
        string $apellido,
        string $password,
        string $email
    ): array {
        // Verificar si email ya existe
        if ($this->emailExiste($email)) {
            return Result::error("El email ya está registrado")->toArray();
        }

        try {
            // Iniciar transacción
            $this->beginTransaction();

            // Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar usuario
            $this->execute(
                "INSERT INTO usuarios
                (nombre, apellido, password, email)
                VALUES (:nombre, :apellido, :password, :email)",
                [
                    "nombre" => $nombre,
                    "apellido" => $apellido,
                    "password" => $hash,
                    "email" => $email
                ]
            );

            // ID del usuario creado
            $idUsuario = $this->lastInsertId();

            // Crear carrito
            $this->execute(
                "INSERT INTO carritos(id_usuario)
                VALUES(:id_usuario)",
                ["id_usuario" => $idUsuario]
            );

            // Crear favoritos
            $this->execute(
                "INSERT INTO favoritos(id_usuario)
                VALUES(:id_usuario)",
                ["id_usuario" => $idUsuario]
            );

            // Commit
            $this->commit();

            return Result::success("Usuario registrado correctamente")->toArray();
        } catch (\Exception $e) {
            $this->rollback();
            return Result::error("Error al registrar usuario")->toArray();
        }
    }

    public function loguearUsuario(
        string $email,
        string $password
    ): array {

        $usuarioDB = $this->fetch(
            "SELECT *
             FROM usuarios
             WHERE email = :email",
            [
                "email" => $email
            ]
        );

        if (!$usuarioDB) {
            return Result::error("Credenciales inválidas")->toArray();
        }

        if (
            !password_verify(
                $password,
                $usuarioDB["password"]
            )
        ) {
            return Result::error("Credenciales inválidas")->toArray();
        }

        unset($usuarioDB["password"]);

        // Usar SessionManager para iniciar sesión
        $this->sessionManager->regenerateId();
        $this->sessionManager->establecer("id_usuario", $usuarioDB["id_usuario"]);
        $this->sessionManager->establecer("nombre", $usuarioDB["nombre"]);
        $this->sessionManager->establecer("apellido", $usuarioDB["apellido"]);
        $this->sessionManager->establecer("email", $usuarioDB["email"]);
        $this->sessionManager->establecer("admin", (bool) $usuarioDB["admin"]);

        return Result::success(
            "Login exitoso",
            ["user" => $usuarioDB]
        )->toArray();
    }

    public function eliminarUsuario(
        int $idUsuario,
        string $password
    ): array {
        $usuarioDB = $this->fetch(
            "SELECT password
            FROM usuarios
            WHERE id_usuario = :id",
            [
                "id" => $idUsuario
            ]
        );

        if (!$usuarioDB) {
            return Result::error("Usuario no encontrado")->toArray();
        }

        if (!password_verify($password, $usuarioDB["password"])) {
            return Result::error("Contraseña incorrecta")->toArray();
        }

        $this->execute(
            "DELETE FROM usuarios
            WHERE id_usuario = :id",
            [
                "id" => $idUsuario
            ]
        );

        // Eliminar token persistente si existe
        $this->tokenManager->eliminarTodosTokens($idUsuario);
        \App\Managers\TokenManager::borrarCookie();

        // Cerrar sesión si es la del usuario actual
        if ($this->sessionManager->obtener("id_usuario") === $idUsuario) {
            $this->sessionManager->destruir();
        }

        return Result::success("Usuario eliminado correctamente")->toArray();
    }

    public function obtenerUsuario(
        int $idUsuario
    ): ?array {

        $usuario = $this->fetch(
            "SELECT
                id_usuario,
                nombre,
                apellido,
                email,
                fecha_registro
             FROM usuarios
             WHERE id_usuario = :id",
            [
                "id" => $idUsuario
            ]
        );

        return $usuario ?: null;
    }

    public function obtenerSesion(): array
    {
        return $this->sessionManager->obtenerEstado();
    }

    public function cerrarSesion(): array
    {
        // Eliminar token persistente y su cookie
        $this->tokenManager->limpiarTokensExpirados();
        \App\Managers\TokenManager::borrarCookie();

        // Destruir sesión PHP
        return $this->sessionManager->destruir();
    }
}