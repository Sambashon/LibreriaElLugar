<?php
require_once "../clases/importador.php";
require_once "../clases/managers/SessionManager.php";


$sessionManager = new SessionManager();
if (!$sessionManager->obtener("admin", false)) {
    Response::error("No autorizado", 403); // or similar
    exit;
}

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "state" => "error",
        "message" => "Método no permitido"
    ]);
    exit;
}


try {
    $importador = new Importador();

    $importador->verificarArchivos($_FILES['archivo']);

    $resultado = $importador->importarLibros($_FILES['archivo']['tmp_name']);
        echo json_encode([
            "state"      => "success",
            "insertados" => $resultado["insertados"],
            "errores"    => $resultado["errores"]
        ]);

    /*
    echo json_encode([
        "state" => "ok",
        "message" => "Importación completada"
    ]);
    */
} catch (RuntimeException $e) {
    echo json_encode([
        "state" => "error",
        "message" => $e->getMessage()
    ]);
}