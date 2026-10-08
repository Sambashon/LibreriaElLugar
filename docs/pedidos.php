<?php
require_once __DIR__ . '/php/bootstrap.php';

use App\Classes\Pedido;
use App\Helpers\AdminAccess;

AdminAccess::requireAdminPage();

$pedidos = (new Pedido())->listar();
$estados = [
    'pendiente' => 'Pendiente',
    'confirmado' => 'Confirmado',
    'preparando' => 'Preparando',
    'listo_para_retirar' => 'Listo para retirar',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado',
];
$siguientesEstados = [
    'pendiente' => 'confirmado',
    'confirmado' => 'preparando',
    'preparando' => 'listo_para_retirar',
    'listo_para_retirar' => 'entregado',
];
$escapar = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos | Librería El Lugar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="src/css/components.css">
    <link rel="stylesheet" href="src/css/gestion.css">
    <link rel="stylesheet" href="src/css/pedidos.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Gestión de <em>Pedidos</em></h1>
            <span class="header-count"><?= count($pedidos) ?> pedidos</span>
        </div>
        <div class="header-container">
            <a class="button pedidos-back" href="admindash.php">Volver al panel</a>
        </div>
    </header>
    <main class="wrapper pedidos-wrapper">
        <?php if (!$pedidos): ?>
            <div class="empty"><p>Todavía no hay pedidos.</p></div>
        <?php else: ?>
            <table class="pedidos-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Productos</th>
                        <th>Entrega y pago</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <?php
                        $estado = $pedido['estado'];
                        $destino = $siguientesEstados[$estado] ?? null;
                        $puedeCancelar = !in_array($estado, ['entregado', 'cancelado'], true);
                        ?>
                        <tr>
                            <td>
                                <strong>#<?= (int) $pedido['id_pedido'] ?></strong>
                                <span class="pedido-date"><?= $escapar(date('d/m/Y H:i', strtotime($pedido['fecha_creacion']))) ?></span>
                            </td>
                            <td>
                                <strong><?= $escapar($pedido['nombre_cliente']) ?></strong>
                                <span><?= $escapar($pedido['telefono']) ?></span>
                                <span><?= $escapar($pedido['email']) ?></span>
                                <?php if ($pedido['comentarios'] !== null && $pedido['comentarios'] !== ''): ?>
                                    <span class="pedido-comments"><?= nl2br($escapar($pedido['comentarios'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <ul class="pedido-items">
                                    <?php foreach ($pedido['detalles'] as $detalle): ?>
                                        <li>
                                            <?= (int) $detalle['cantidad'] ?> × <?= $escapar($detalle['titulo_libro']) ?>
                                            <small>$<?= number_format((float) $detalle['precio_unitario'], 2, ',', '.') ?> c/u</small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td>
                                <span>Retiro en librería</span>
                                <span><?= $pedido['metodo_pago'] === 'transferencia_bancaria' ? 'Transferencia bancaria' : 'Pago en librería' ?></span>
                            </td>
                            <td class="precio">$<?= number_format((float) $pedido['total'], 2, ',', '.') ?></td>
                            <td>
                                <span class="pedido-status status-<?= $escapar($estado) ?>"><?= $escapar($estados[$estado] ?? $estado) ?></span>
                                <?php if ($destino !== null || $puedeCancelar): ?>
                                    <form class="pedido-status-form">
                                        <input type="hidden" name="id_pedido" value="<?= (int) $pedido['id_pedido'] ?>">
                                        <select name="estado" aria-label="Nuevo estado del pedido <?= (int) $pedido['id_pedido'] ?>">
                                            <?php if ($destino !== null): ?>
                                                <option value="<?= $escapar($destino) ?>"><?= $escapar($estados[$destino]) ?></option>
                                            <?php endif; ?>
                                            <?php if ($puedeCancelar): ?>
                                                <option value="cancelado">Cancelar y reponer stock</option>
                                            <?php endif; ?>
                                        </select>
                                        <button class="button button-primary" type="submit">Actualizar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
    <footer><?= count($pedidos) ?> pedidos · <?= date('d/m/Y H:i') ?></footer>
    <script>
        document.querySelectorAll('.pedido-status-form').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const button = form.querySelector('button');
                button.disabled = true;
                try {
                    const response = await fetch('php/scripts/actualizarEstadoPedido.php', {
                        method: 'POST',
                        body: new FormData(form),
                        credentials: 'same-origin'
                    });
                    const data = await response.json();
                    if (!response.ok || data.state !== 'success') {
                        throw new Error(data.message || 'No se pudo actualizar el pedido');
                    }
                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || 'No se pudo actualizar el pedido');
                    button.disabled = false;
                }
            });
        });
    </script>
</body>
</html>
