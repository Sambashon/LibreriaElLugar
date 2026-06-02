<?php

require_once "libreriaDb.php";

class Usuario extends LibreriaDB
{
    public function verificarUsuario(
        string $nombre,
        string $email
    ): array {

        $resultado = $this->fetch(
            "SELECT
                EXISTS(
                    SELECT 1
                    FROM usuarios
                    WHERE nombre = :nombre
                ) AS nombreExiste,

                EXISTS(
                    SELECT 1
                    FROM usuarios
                    WHERE email = :email
                ) AS emailExiste",
            [
                "nombre" => $nombre,
                "email" => $email
            ]
        );

        if ($resultado["nombreExiste"]) {
            return [
                "state" => "error",
                "message" => "El usuario ya existe"
            ];
        }

        if ($resultado["emailExiste"]) {
            return [
                "state" => "error",
                "message" => "El email ya está registrado"
            ];
        }

        return [
            "state" => "success",
            "message" => "Usuario y email disponibles"
        ];
    }

    public function registrarUsuario(
        string $nombre,
        string $apellido,
        string $password,
        string $email
    ): array {

        // 1. Verificar solo email
        $verificacion = $this->fetch(
            "SELECT id_usuario
            FROM usuarios
            WHERE email = :email",
            [
                "email" => $email
            ]
        );

        if ($verificacion) {
            return [
                "state" => "error",
                "message" => "El email ya está registrado"
            ];
        }

        try {

            // 2. Iniciar transacción
            $this->beginTransaction();

            // 3. Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // 4. Insertar usuario
            $this->execute(
                "INSERT INTO usuarios
                (
                    nombre,
                    apellido,
                    password,
                    email
                )
                VALUES
                (
                    :nombre,
                    :apellido,
                    :password,
                    :email
                )",
                [
                    "nombre" => $nombre,
                    "apellido" => $apellido,
                    "password" => $hash,
                    "email" => $email
                ]
            );

            // 5. ID del usuario creado
            $idUsuario = $this->lastInsertId();

            // 6. Crear carrito
            $this->execute(
                "INSERT INTO carritos(id_usuario)
                VALUES(:id_usuario)",
                [
                    "id_usuario" => $idUsuario
                ]
            );

            // 7. Crear favoritos
            $this->execute(
                "INSERT INTO favoritos(id_usuario)
                VALUES(:id_usuario)",
                [
                    "id_usuario" => $idUsuario
                ]
            );

            // 8. Commit
            $this->commit();

            return [
                "state" => "success",
                "message" => "Usuario registrado correctamente"
            ];

        } catch (Exception $e) {

            $this->rollback();

            return [
                "state" => "error",
                "message" => "Error al registrar usuario"
            ];
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
            return [
                "state" => "error",
                "message" => "Credenciales inválidas"
            ];
        }

        if (
            !password_verify(
                $password,
                $usuarioDB["password"]
            )
        ) {
            return [
                "state" => "error",
                "message" => "Credenciales inválidas"
            ];
        }

        unset($usuarioDB["password"]);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_regenerate_id(true);

        $_SESSION["id_usuario"] = $usuarioDB["id_usuario"];
        $_SESSION["nombre"] = $usuarioDB["nombre"];
        $_SESSION["apellido"] = $usuarioDB["apellido"];
        $_SESSION["email"] = $usuarioDB["email"];

        return [
            "state" => "success",
            "message" => "Login exitoso",
            "user" => $usuarioDB
        ];
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
            return [
                "state" => "error",
                "message" => "Usuario no encontrado"
            ];
        }

        if (!password_verify(
            $password,
            $usuarioDB["password"]
        )) {
            return [
                "state" => "error",
                "message" => "Contraseña incorrecta"
            ];
        }

        $this->execute(
            "DELETE FROM usuarios
            WHERE id_usuario = :id",
            [
                "id" => $idUsuario
            ]
        );

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (
            isset($_SESSION["id_usuario"]) &&
            (int) $_SESSION["id_usuario"] === $idUsuario
        ) {
            $this->cerrarSesion();
        }

        return [
            "state" => "success",
            "message" => "Usuario eliminado correctamente"
        ];
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["id_usuario"])) {
            return [
                "logged" => false
            ];
        }

        return [
            "logged" => true,
            "user" => [
                "id_usuario" => $_SESSION["id_usuario"],
                "nombre" => $_SESSION["nombre"],
                "apellido" => $_SESSION["apellido"],
                "email" => $_SESSION["email"]
            ]
        ];
    }

    public function cerrarSesion(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        return [
            "state" => "success",
            "message" => "Sesión cerrada correctamente"
        ];
    }
}