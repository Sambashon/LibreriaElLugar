<?php

require_once __DIR__ . "/../bootstrap.php";

use App\Helpers\{AdminAccess, Request, Response};

header('Content-Type: application/json; charset=utf-8');

try {
    Request::requireMethod('POST');
    if (!AdminAccess::esAdmin()) {
        Response::error('Tu sesión de administrador venció. Iniciá sesión nuevamente.', 403);
    }

    $idLibro = filter_input(INPUT_POST, 'id_libro', FILTER_VALIDATE_INT);
    if (!$idLibro || $idLibro <= 0) {
        Response::error('ID de libro inválido', 400);
    }

    if (!isset($_FILES['portada']) || $_FILES['portada']['error'] !== UPLOAD_ERR_OK) {
        Response::error('No se pudo recibir la portada', 400);
    }

    $upload = $_FILES['portada'];
    if ($upload['size'] > 8 * 1024 * 1024) {
        Response::error('La portada no puede superar los 8 MB', 400);
    }

    $imageInfo = getimagesize($upload['tmp_name']);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($upload['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (
        $imageInfo === false
        || !isset($extensions[$mime])
        || $imageInfo['mime'] !== $mime
    ) {
        Response::error('Formato de imagen inválido. Usá JPG, PNG o WebP.', 400);
    }

    $db = new LibreriaDB();
    $book = $db->fetch(
        "SELECT uid FROM libros WHERE id_libro = ?",
        [$idLibro]
    );
    if (!$book) {
        Response::error('Libro no encontrado', 404);
    }

    $directory = getenv('COVER_UPLOAD_DIR') ?: '/var/lib/el-lugar/portadas';
    if (!is_dir($directory) || !is_writable($directory)) {
        Response::error(
            'El almacenamiento de portadas no está disponible. Revisá el volumen Docker y sus permisos.',
            500
        );
    }

    $uid = $book['uid'];
    $extension = $extensions[$mime];
    $destination = $directory . '/' . $uid . '.' . $extension;
    if (!move_uploaded_file($upload['tmp_name'], $destination)) {
        throw new RuntimeException('No se pudo guardar la portada en el volumen persistente');
    }

    foreach (['jpg', 'png', 'webp'] as $otherExtension) {
        if ($otherExtension === $extension) {
            continue;
        }

        $oldCover = $directory . '/' . $uid . '.' . $otherExtension;
        if (is_file($oldCover) && !unlink($oldCover)) {
            error_log('No se pudo eliminar una versión anterior de la portada: ' . $oldCover);
        }
    }

    Response::success('Portada subida correctamente', [
        'uid' => $uid,
        'url' => 'php/scripts/portadaLibro.php?uid=' . rawurlencode($uid),
    ]);
} catch (Throwable $e) {
    Response::error($e->getMessage(), 500);
}
