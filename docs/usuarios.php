<?php
require_once __DIR__ . '/php/bootstrap.php';

use App\Classes\Usuario;
use App\Helpers\AdminAccess;
use App\Managers\SessionManager;

AdminAccess::requireAdminPage();

$session = new SessionManager();
$csrfToken = $session->obtenerCsrfToken();
$usuarios = (new Usuario())->listarUsuariosAdmin();
$escapar = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios | Librería El Lugar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="src/css/components.css">
    <link rel="stylesheet" href="src/css/gestion.css">
    <link rel="stylesheet" href="src/css/pedidos.css">
    <link rel="stylesheet" href="src/css/usuarios.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Gestión de <em>Usuarios</em></h1>
            <span class="header-count"><?= count($usuarios) ?> usuarios</span>
        </div>
        <div class="header-container">
            <a class="button pedidos-back" href="admindash.php">Volver al panel</a>
        </div>
    </header>
    <main class="wrapper pedidos-wrapper usuarios-wrapper" id="usuariosApp" data-csrf-token="<?= $escapar($csrfToken) ?>">
        <div class="toolbar usuarios-toolbar">
            <label for="buscarUsuario">Buscar usuario</label>
            <input id="buscarUsuario" type="search" placeholder="Nombre o email...">
            <button class="button usuarios-create" id="crearUsuario" type="button">Crear usuario</button>
        </div>
        <p class="usuarios-feedback" id="usuariosFeedback" role="status" aria-live="polite"></p>
        <?php if (!$usuarios): ?>
            <div class="empty"><p>Todavía no hay usuarios.</p></div>
        <?php else: ?>
            <div class="usuarios-table-scroll">
                <table class="pedidos-table usuarios-table" id="usuariosTabla">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Registro</th>
                            <th>Rol</th>
                            <th>Pedidos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr data-search="<?= $escapar(mb_strtolower($usuario['nombre'] . ' ' . $usuario['apellido'] . ' ' . $usuario['email'])) ?>">
                                <td>#<?= (int) $usuario['id_usuario'] ?></td>
                                <td><?= $escapar($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
                                <td><?= $escapar($usuario['email']) ?></td>
                                <td><?= $escapar(date('d/m/Y', strtotime($usuario['fecha_registro']))) ?></td>
                                <td><?= (bool) $usuario['admin'] ? 'Administrador' : 'Usuario' ?></td>
                                <td>
                                    <button class="usuarios-action" type="button" data-action="orders" data-id="<?= (int) $usuario['id_usuario'] ?>" data-name="<?= $escapar($usuario['nombre'] . ' ' . $usuario['apellido']) ?>">
                                        Ver pedidos
                                    </button>
                                </td>
                                <td>
                                    <div class="usuarios-row-actions">
                                        <?php if (!(bool) $usuario['admin']): ?>
                                            <button class="usuarios-action" type="button" data-action="edit" data-id="<?= (int) $usuario['id_usuario'] ?>" data-name="<?= $escapar($usuario['nombre']) ?>" data-lastname="<?= $escapar($usuario['apellido']) ?>" data-email="<?= $escapar($usuario['email']) ?>">Editar</button>
                                            <button class="usuarios-action usuarios-delete" type="button" data-action="delete" data-id="<?= (int) $usuario['id_usuario'] ?>" data-name="<?= $escapar($usuario['nombre'] . ' ' . $usuario['apellido']) ?>">Eliminar</button>
                                        <?php else: ?>
                                            <span class="usuario-protegido">Cuenta de administrador protegida</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    <footer><?= count($usuarios) ?> usuarios · <?= date('d/m/Y H:i') ?></footer>

    <dialog class="usuarios-dialog" id="usuarioDialog">
        <form id="usuarioForm" class="usuarios-form">
            <div class="usuarios-dialog-heading">
                <h2 id="usuarioDialogTitle">Crear usuario</h2>
                <button class="usuarios-dialog-close" type="button" data-close="usuarioDialog" aria-label="Cerrar">×</button>
            </div>
            <input type="hidden" name="action">
            <input type="hidden" name="id_usuario">
            <label for="usuarioNombre">Nombre</label>
            <input id="usuarioNombre" name="nombre" maxlength="100" required>
            <label for="usuarioApellido">Apellido</label>
            <input id="usuarioApellido" name="apellido" maxlength="100" required>
            <label for="usuarioEmail">Email</label>
            <input id="usuarioEmail" name="email" type="email" maxlength="150" required>
            <label for="usuarioPassword">Contraseña <span id="passwordHint"></span></label>
            <input id="usuarioPassword" name="password" type="password" minlength="8" autocomplete="new-password">
            <div class="usuarios-dialog-actions">
                <button class="button" type="button" data-close="usuarioDialog">Cancelar</button>
                <button class="button usuarios-create" type="submit">Guardar</button>
            </div>
        </form>
    </dialog>

    <dialog class="usuarios-dialog usuarios-orders-dialog" id="pedidosDialog">
        <div class="usuarios-dialog-heading">
            <div>
                <h2>Pedidos de <span id="pedidosUsuarioNombre"></span></h2>
                <p id="pedidosResumen"></p>
            </div>
            <button class="usuarios-dialog-close" type="button" data-close="pedidosDialog" aria-label="Cerrar">×</button>
        </div>
        <div id="pedidosContenido" class="usuarios-orders-content"></div>
    </dialog>
    <script src="src/js/usuarios.js"></script>
</body>
</html>
