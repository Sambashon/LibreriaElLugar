<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Classes\{Pedido, Usuario};
use App\Helpers\{AdminAccess, Response};
use App\Managers\SessionManager;

AdminAccess::requireAdmin();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'orders') {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        Response::error('Método no permitido', 405);
    }

    $idUsuario = filter_var($_GET['id_usuario'] ?? null, FILTER_VALIDATE_INT);
    if (!$idUsuario || $idUsuario < 1) {
        Response::error('Usuario inválido', 400);
    }

    try {
        $usuario = (new Usuario())->obtenerUsuario($idUsuario);
        if ($usuario === null) {
            Response::error('Usuario no encontrado', 404);
        }
        $pedidos = (new Pedido())->listarPorUsuario($idUsuario);
        Response::success('Pedidos cargados correctamente', [
            'usuario' => $usuario,
            'pedidos' => $pedidos,
        ]);
    } catch (Throwable $error) {
        error_log('No se pudieron cargar los pedidos del usuario: ' . $error->getMessage());
        Response::error('No se pudieron cargar los pedidos', 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Método no permitido', 405);
}

$session = new SessionManager();
$csrfToken = $_POST['csrf_token'] ?? '';
$storedCsrfToken = $session->obtener('csrf_token');
if (
    !is_string($storedCsrfToken)
    || $storedCsrfToken === ''
    || !is_string($csrfToken)
    || !hash_equals($storedCsrfToken, $csrfToken)
) {
    Response::error('La solicitud expiró. Recarga la página e inténtalo nuevamente.', 403);
}

$idUsuario = filter_var($_POST['id_usuario'] ?? null, FILTER_VALIDATE_INT);
$nombre = $_POST['nombre'] ?? null;
$apellido = $_POST['apellido'] ?? null;
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? '';

if ($action === 'create' || $action === 'update') {
    if (!$idUsuario && $action === 'update') {
        Response::error('Usuario inválido', 400);
    }
    if (!is_string($nombre) || !is_string($apellido) || !is_string($email)) {
        Response::error('Los datos del usuario no son válidos.', 400);
    }
    $nombre = trim($nombre);
    $apellido = trim($apellido);
    $email = trim($email);
    if (
        $nombre === ''
        || $apellido === ''
        || mb_strlen($nombre, 'UTF-8') > 100
        || mb_strlen($apellido, 'UTF-8') > 100
    ) {
        Response::error('Ingresa un nombre y apellido válidos (máximo 100 caracteres).', 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        Response::error('Ingresa un email válido (máximo 150 caracteres).', 400);
    }
    if ($action === 'create' && (!is_string($password) || strlen($password) < 8)) {
        Response::error('La contraseña debe tener al menos 8 caracteres.', 400);
    }
    if ($action === 'update' && $password !== '' && (!is_string($password) || strlen($password) < 8)) {
        Response::error('La nueva contraseña debe tener al menos 8 caracteres.', 400);
    }

    try {
        $usuario = new Usuario();
        if ($action === 'create') {
            $resultado = $usuario->registrarUsuario($nombre, $apellido, $password, $email);
            if ($resultado['state'] !== 'success') {
                $status = $resultado['message'] === 'Error al registrar usuario' ? 500 : 400;
                Response::json($resultado, $status);
            }
            Response::success('Usuario creado correctamente');
        }

        $resultado = $usuario->actualizarUsuarioAdmin(
            $idUsuario,
            $nombre,
            $apellido,
            $email,
            $password === '' ? null : $password
        );
        if ($resultado['state'] !== 'success') {
            Response::json($resultado, $resultado['message'] === 'Usuario no encontrado' ? 404 : 400);
        }
        if ((int) $session->obtener('id_usuario') === $idUsuario) {
            $session->establecer('nombre', $nombre);
            $session->establecer('apellido', $apellido);
            $session->establecer('email', $email);
        }
        Response::json($resultado);
    } catch (Throwable $error) {
        error_log('No se pudo guardar el usuario: ' . $error->getMessage());
        Response::error('No se pudo guardar el usuario', 500);
    }
}

if ($action === 'delete') {
    if (!$idUsuario || $idUsuario < 1) {
        Response::error('Usuario inválido', 400);
    }
    if ((int) $session->obtener('id_usuario') === $idUsuario) {
        Response::error('No puedes eliminar tu propia cuenta desde este panel.', 400);
    }

    try {
        $resultado = (new Usuario())->eliminarUsuarioAdmin($idUsuario);
        if ($resultado['state'] !== 'success') {
            $status = $resultado['message'] === 'Usuario no encontrado' ? 404 : 400;
            Response::json($resultado, $status);
        }
        Response::json($resultado);
    } catch (Throwable $error) {
        error_log('No se pudo eliminar el usuario: ' . $error->getMessage());
        Response::error('No se pudo eliminar el usuario', 500);
    }
}

Response::error('Acción inválida', 400);
