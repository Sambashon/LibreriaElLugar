<?php

function requireAdmin(bool $jsonResponse = true): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['id_usuario']) && filter_var($_SESSION['admin'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
        return;
    }

    $status = isset($_SESSION['id_usuario']) ? 403 : 401;

    if (!$jsonResponse) {
        header('Location: index.html');
        exit;
    }

    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'state' => 'error',
        'message' => $status === 401
            ? 'Debes iniciar sesión para acceder a esta función'
            : 'No tienes permisos para acceder a esta función'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
