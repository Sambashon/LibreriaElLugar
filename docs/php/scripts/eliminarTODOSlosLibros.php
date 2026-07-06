<?php

require_once __DIR__ . "/../bootstrap.php";

use App\Helpers\{Request, Response};

header('Content-Type: application/json; charset=utf-8');

try {
    Request::requireMethod('POST');

    $db = new LibreriaDB();

    $db->execute("SET FOREIGN_KEY_CHECKS = 0");
    $db->execute("TRUNCATE TABLE libros");
    $db->execute("SET FOREIGN_KEY_CHECKS = 1");

    Response::success('Todos los libros fueron eliminados');
} catch (Exception $e) {
    Response::error($e->getMessage());
}
