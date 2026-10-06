<?php

namespace App\Classes;

require_once __DIR__ . '/libreriaDb.php';
require_once __DIR__ . '/../bootstrap.php';

use App\Helpers\Result;

class Pedido extends \LibreriaDB
{
    private const ESTADOS = [
        'pendiente',
        'confirmado',
        'preparando',
        'listo_para_retirar',
        'entregado',
        'cancelado',
    ];

    public function crear(int $idUsuario, array $cliente): array
    {
        $this->beginTransaction();

        try {
            $carrito = $this->fetch(
                "SELECT c.id_carrito
                 FROM carritos c
                 WHERE c.id_usuario = :id_usuario
                 FOR UPDATE",
                ['id_usuario' => $idUsuario]
            );

            if (!$carrito) {
                $this->rollback();
                return Result::error('No se encontró el carrito')->toArray();
            }

            $items = $this->fetchAll(
                "SELECT l.id_libro, l.titulo, l.precio, l.stock, cl.cantidad
                 FROM carrito_libros cl
                 INNER JOIN libros l ON l.id_libro = cl.id_libro
                 WHERE cl.id_carrito = :id_carrito
                 ORDER BY l.id_libro
                 FOR UPDATE",
                ['id_carrito' => $carrito['id_carrito']]
            );

            if (!$items) {
                $this->rollback();
                return Result::error('El carrito está vacío')->toArray();
            }

            $totalCentavos = 0;
            foreach ($items as $item) {
                $cantidad = (int) $item['cantidad'];
                $stock = (int) $item['stock'];
                $precioCentavos = $this->aCentavos((string) $item['precio']);
                if ($precioCentavos < 0) {
                    $this->rollback();
                    return Result::error(
                        'El precio de "' . $item['titulo'] . '" no es válido'
                    )->toArray();
                }
                if ($cantidad < 1) {
                    $this->rollback();
                    return Result::error('La cantidad de "' . $item['titulo'] . '" no es válida')->toArray();
                }
                if ($stock < $cantidad) {
                    $this->rollback();
                    return Result::error(
                        'El stock de "' . $item['titulo'] . '" no alcanza para completar el pedido'
                    )->toArray();
                }
                $totalCentavos += $precioCentavos * $cantidad;
            }

            $this->query(
                "INSERT INTO pedidos
                    (id_usuario, nombre_cliente, telefono, email, comentarios, metodo_pago, total)
                 VALUES
                    (:id_usuario, :nombre_cliente, :telefono, :email, :comentarios, :metodo_pago, :total)",
                [
                    'id_usuario' => $idUsuario,
                    'nombre_cliente' => $cliente['nombre'],
                    'telefono' => $cliente['telefono'],
                    'email' => $cliente['email'],
                    'comentarios' => $cliente['comentarios'] !== '' ? $cliente['comentarios'] : null,
                    'metodo_pago' => $cliente['metodo_pago'],
                    'total' => $this->aDecimal($totalCentavos),
                ]
            );
            $idPedido = (int) $this->lastInsertId();

            foreach ($items as $item) {
                $cantidad = (int) $item['cantidad'];
                $this->query(
                    "INSERT INTO detalle_pedido
                        (id_pedido, id_libro, titulo_libro, cantidad, precio_unitario)
                     VALUES (:id_pedido, :id_libro, :titulo, :cantidad, :precio)",
                    [
                        'id_pedido' => $idPedido,
                        'id_libro' => $item['id_libro'],
                        'titulo' => $item['titulo'],
                        'cantidad' => $cantidad,
                        'precio' => $item['precio'],
                    ]
                );
                $actualizacion = $this->query(
                    "UPDATE libros SET stock = stock - :cantidad_restar
                     WHERE id_libro = :id_libro AND stock >= :cantidad_requerida",
                    [
                        'cantidad_restar' => $cantidad,
                        'id_libro' => $item['id_libro'],
                        'cantidad_requerida' => $cantidad,
                    ]
                );
                if ($actualizacion->rowCount() !== 1) {
                    throw new \RuntimeException('No se pudo reservar el stock del libro');
                }
            }

            $this->query(
                'DELETE FROM carrito_libros WHERE id_carrito = :id_carrito',
                ['id_carrito' => $carrito['id_carrito']]
            );
            $this->commit();

            return Result::success('Pedido registrado correctamente', [
                'id_pedido' => $idPedido,
                'total' => $this->aDecimal($totalCentavos),
            ])->toArray();
        } catch (\Throwable $error) {
            $this->rollback();
            throw $error;
        }
    }

    public function listar(): array
    {
        $pedidos = $this->fetchAll(
            "SELECT id_pedido, nombre_cliente, telefono, email, comentarios,
                    tipo_entrega, metodo_pago, estado, total, fecha_creacion
             FROM pedidos
             ORDER BY fecha_creacion DESC, id_pedido DESC"
        );
        if (!$pedidos) {
            return [];
        }

        $detalles = $this->fetchAll(
            "SELECT id_pedido, titulo_libro, cantidad, precio_unitario
             FROM detalle_pedido
             ORDER BY id_detalle"
        );
        $porPedido = [];
        foreach ($detalles as $detalle) {
            $porPedido[(int) $detalle['id_pedido']][] = $detalle;
        }
        foreach ($pedidos as &$pedido) {
            $pedido['detalles'] = $porPedido[(int) $pedido['id_pedido']] ?? [];
        }
        unset($pedido);

        return $pedidos;
    }

    public function actualizarEstado(int $idPedido, string $nuevoEstado): array
    {
        if (!in_array($nuevoEstado, self::ESTADOS, true)) {
            return Result::error('Estado de pedido inválido')->toArray();
        }

        $this->beginTransaction();
        try {
            $pedido = $this->fetch(
                'SELECT estado FROM pedidos WHERE id_pedido = :id_pedido FOR UPDATE',
                ['id_pedido' => $idPedido]
            );
            if (!$pedido) {
                $this->rollback();
                return Result::error('Pedido no encontrado', 404)->toArray();
            }

            $estadoActual = $pedido['estado'];
            if ($estadoActual === $nuevoEstado) {
                $this->commit();
                return Result::success('El pedido ya tenía ese estado')->toArray();
            }
            if ($estadoActual === 'entregado' || $estadoActual === 'cancelado') {
                $this->rollback();
                return Result::error('No se puede modificar un pedido finalizado')->toArray();
            }
            $siguiente = [
                'pendiente' => 'confirmado',
                'confirmado' => 'preparando',
                'preparando' => 'listo_para_retirar',
                'listo_para_retirar' => 'entregado',
            ];
            if ($nuevoEstado !== 'cancelado' && ($siguiente[$estadoActual] ?? null) !== $nuevoEstado) {
                $this->rollback();
                return Result::error('El estado debe avanzar en el orden del ciclo de vida')->toArray();
            }

            if ($nuevoEstado === 'cancelado') {
                $this->query(
                    "UPDATE libros l
                     INNER JOIN detalle_pedido d ON d.id_libro = l.id_libro
                     SET l.stock = l.stock + d.cantidad
                     WHERE d.id_pedido = :id_pedido",
                    ['id_pedido' => $idPedido]
                );
            }
            $this->query(
                'UPDATE pedidos SET estado = :estado WHERE id_pedido = :id_pedido',
                ['estado' => $nuevoEstado, 'id_pedido' => $idPedido]
            );
            $this->commit();

            return Result::success('Estado del pedido actualizado')->toArray();
        } catch (\Throwable $error) {
            $this->rollback();
            throw $error;
        }
    }

    private function aCentavos(string $precio): int
    {
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $precio)) {
            return -1;
        }
        [$unidades, $centavos] = array_pad(explode('.', $precio, 2), 2, '0');

        return ((int) $unidades * 100) + (int) str_pad($centavos, 2, '0');
    }

    private function aDecimal(int $centavos): string
    {
        return intdiv($centavos, 100) . '.' . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }
}
