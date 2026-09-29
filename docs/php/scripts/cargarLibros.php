<?php
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../clases/importador.php";

use App\Helpers\{AdminAccess, Request, Response};

try {
    Request::requireMethod('POST');
    AdminAccess::requireAdmin();

    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        Response::error('No se pudo recibir el archivo', 400);
    }

    $importador = new Importador();
    $importador->verificarArchivos($_FILES['archivo']);
    $resultado = $importador->importarLibros($_FILES['archivo']['tmp_name']);

    Response::json([
        'state' => 'success',
        'insertados' => $resultado['insertados'],
        'errores' => $resultado['errores']
    ]);
} catch (RuntimeException $e) {
    Response::error($e->getMessage());
}
