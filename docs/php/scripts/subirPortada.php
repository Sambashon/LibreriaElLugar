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
    if (
        !is_uploaded_file($upload['tmp_name'])
        || (int) $upload['size'] < 1
        || (int) $upload['size'] > 8 * 1024 * 1024
    ) {
        Response::error('La portada no puede superar los 8 MB', 400);
    }
    $actualSize = filesize($upload['tmp_name']);
    if ($actualSize === false || $actualSize < 1 || $actualSize > 8 * 1024 * 1024) {
        Response::error('La portada no puede superar los 8 MB', 400);
    }

    $extension = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
    $imageInfo = getimagesize($upload['tmp_name']);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($upload['tmp_name']);
    $extensions = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp'],
    ];

    if (
        $imageInfo === false
        || !isset($extensions[$mime])
        || !in_array($extension, $extensions[$mime], true)
        || $imageInfo['mime'] !== $mime
    ) {
        Response::error('Formato de imagen inválido. Usá JPG, PNG o WebP.', 400);
    }
    if (($imageInfo[0] * $imageInfo[1]) > 20000000) {
        Response::error('La portada supera el tamaño máximo de imagen permitido.', 400);
    }
    if (!function_exists('imagewebp')) {
        Response::error('El servidor no tiene habilitada la conversión de imágenes WebP.', 500);
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
    $source = match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($upload['tmp_name']),
        'image/png' => imagecreatefrompng($upload['tmp_name']),
        'image/webp' => imagecreatefromwebp($upload['tmp_name']),
    };
    if ($source === false) {
        Response::error('No se pudo procesar la imagen subida.', 400);
    }

    imagepalettetotruecolor($source);
    imagealphablending($source, false);
    imagesavealpha($source, true);

    $temporaryPath = tempnam($directory, $uid . '.');
    if ($temporaryPath === false) {
        imagedestroy($source);
        throw new RuntimeException('No se pudo preparar el archivo WebP');
    }

    $converted = imagewebp($source, $temporaryPath, 82);
    imagedestroy($source);
    if (!$converted) {
        unlink($temporaryPath);
        throw new RuntimeException('No se pudo convertir la portada a WebP');
    }

    $destination = $directory . '/' . $uid . '.webp';
    if (!rename($temporaryPath, $destination)) {
        unlink($temporaryPath);
        throw new RuntimeException('No se pudo guardar la portada WebP en el volumen persistente');
    }

    foreach (['jpg', 'png'] as $oldExtension) {
        $oldCover = $directory . '/' . $uid . '.' . $oldExtension;
        if (is_file($oldCover) && !unlink($oldCover)) {
            error_log('No se pudo eliminar una versión anterior de la portada: ' . $oldCover);
        }
    }

    Response::success('Portada subida correctamente', [
        'uid' => $uid,
        'url' => 'php/scripts/portadaLibro.php?uid=' . rawurlencode($uid),
    ]);
} catch (Throwable $e) {
    error_log('No se pudo subir la portada: ' . $e->getMessage());
    Response::error('No se pudo procesar la portada. Intentá nuevamente.', 500);
}
