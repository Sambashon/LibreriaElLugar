<?php
    include "../clases/usuario.php";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($_POST["formtype"] === "register") {

        $usuarioDB = new Usuario();

        $resultado = $usuarioDB->registrarUsuario(
            $_POST["usuario"],
            $_POST["password"],
            $_POST["email"]
        );

        print_r($resultado);
    }
}
?>