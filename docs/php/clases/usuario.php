<?php
include "libreriaDb.php";
class Usuario extends LibreriaDB{
    public function __construct(){
        parent::__construct("libreriadb", "usuario", "ElLugarDB", "12345678", 3306);
    }
    
    public function verificarUsuario(string $usuario, string $email):array{
         $solicitud = $this->pdo->prepare(
            "SELECT nombre, email 
            FROM usuarios 
            WHERE nombre = :nombre 
            OR email = :email;"
        );

        $solicitud->execute([
            "nombre" => $usuario,
            "email" => $email
        ]);

        $existe = $solicitud->fetch();

        if ($existe !== false) {

            if ($existe["nombre"] === $usuario) {

                return [
                    "state" => "error",
                    "message" => "El usuario ya existe"
                ];
            }

            if ($existe["email"] === $email) {

                return [
                    "state" => "error",
                    "message" => "El email ya está registrado"
                ];
            }
        }

        return [
            "state" => "success",
            "message" => "Usuario y email disponibles"
        ];
    }
    public function registrarUsuario(string $usuario, string $password, string $email): array{
       
        $verificacion = $this->verificarUsuario($usuario, $email);

        if ($verificacion["state"] === "error") {
            return $verificacion;
        }

        // Hash seguro
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Insertar usuario
        $insertar = $this->pdo->prepare(
            "INSERT INTO usuarios(nombre, password, email)
             VALUES(:nombre, :password, :email);"
        );

        $insertar->execute([
            "nombre" => $usuario,
            "password" => $hash,
            "email" => $email
        ]);

        return [
            "state" => "success",
            "message" => "Usuario registrado correctamente"
        ];
    }
    public function loguearUsuario(string $usuario, string $password, string $email){
        $verificacion = $this->verificarUsuario($usuario, $email);

        if ($verificacion["state"] === "error") {
            return $verificacion;
        }

        $solicitud = $this->pdo->prepare(
            "SELECT * FROM usuarios
            WHERE nombre = :nombre;"
        );

        $solicitud->execute([
            "nombre" => $usuario
        ]);

        $usuarioDB = $solicitud->fetch();

        if ($usuarioDB === false) {

            return [
                "state" => "error",
                "message" => "Usuario no encontrado"
            ];
        }

        if (!password_verify($password, $usuarioDB["password"])) {
            return [
                "state" => "error",
                "message" => "Contraseña incorrecta"
            ];
        }

        return [
            "state" => "success",
            "message" => "Login exitoso",
            "user" => $usuarioDB
        ];
    }
    public function eliminarUsuario(){
        
    }
}