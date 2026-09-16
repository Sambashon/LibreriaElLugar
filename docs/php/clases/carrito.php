<?php

namespace App\Classes;

require_once __DIR__ . "/libreriaDb.php";
require_once __DIR__ . "/../bootstrap.php";

use App\Helpers\Result;

class Carrito extends \LibreriaDB
{
    public function obtenerIdCarrito(int $idUsuario): int
    {
        $row = $this->fetch(
            "SELECT id_carrito FROM carritos WHERE id_usuario = :id_usuario LIMIT 1",
            ['id_usuario' => $idUsuario]
        );

        if ($row) {
            return (int) $row['id_carrito'];
        }

        $this->query(
            "INSERT INTO carritos (id_usuario) VALUES (:id_usuario)",
            ['id_usuario' => $idUsuario]
        );

        return (int) $this->lastInsertId();
    }

    public function agregarLibro(int $idUsuario, int $idLibro, int $cantidad = 1): array
    {
        if ($cantidad < 1) {
            return Result::error('Cantidad inválida')->toArray();
        }

        $libro = $this->fetch(
            "SELECT id_libro, stock, titulo FROM libros WHERE id_libro = :id",
            ['id' => $idLibro]
        );

        if (!$libro) {
            return Result::error('Libro no encontrado', 404)->toArray();
        }

        $stock = (int) $libro['stock'];
        if ($stock < 1) {
            return Result::error('El libro no tiene stock')->toArray();
        }

        $idCarrito = $this->obtenerIdCarrito($idUsuario);

        $item = $this->fetch(
            "SELECT cantidad
             FROM carrito_libros
             WHERE id_carrito = :id_carrito AND id_libro = :id_libro",
            [
                'id_carrito' => $idCarrito,
                'id_libro' => $idLibro
            ]
        );

        $nuevaCantidad = $item
            ? (int) $item['cantidad'] + $cantidad
            : $cantidad;

        if ($nuevaCantidad > $stock) {
            $nuevaCantidad = $stock;
        }

        if ($item) {
            $this->query(
                "UPDATE carrito_libros
                 SET cantidad = :cantidad
                 WHERE id_carrito = :id_carrito AND id_libro = :id_libro",
                [
                    'cantidad' => $nuevaCantidad,
                    'id_carrito' => $idCarrito,
                    'id_libro' => $idLibro
                ]
            );
        } else {
            $this->query(
                "INSERT INTO carrito_libros (id_carrito, id_libro, cantidad)
                 VALUES (:id_carrito, :id_libro, :cantidad)",
                [
                    'id_carrito' => $idCarrito,
                    'id_libro' => $idLibro,
                    'cantidad' => $nuevaCantidad
                ]
            );
        }

        return Result::success('Libro agregado al carrito', [
            'item' => [
                'id_libro' => $idLibro,
                'cantidad' => $nuevaCantidad
            ]
        ])->toArray();
    }

    public function actualizarCantidad(int $idUsuario, int $idLibro, int $cantidad): array
    {
        if ($cantidad < 1) {
            return Result::error('Cantidad inválida')->toArray();
        }

        $libro = $this->fetch(
            "SELECT id_libro, stock FROM libros WHERE id_libro = :id",
            ['id' => $idLibro]
        );

        if (!$libro) {
            return Result::error('Libro no encontrado', 404)->toArray();
        }

        $stock = (int) $libro['stock'];
        if ($stock < 1) {
            return Result::error('El libro no tiene stock')->toArray();
        }

        if ($cantidad > $stock) {
            $cantidad = $stock;
        }

        $idCarrito = $this->obtenerIdCarrito($idUsuario);

        $item = $this->fetch(
            "SELECT cantidad
             FROM carrito_libros
             WHERE id_carrito = :id_carrito AND id_libro = :id_libro",
            [
                'id_carrito' => $idCarrito,
                'id_libro' => $idLibro
            ]
        );

        if ($item) {
            $this->query(
                "UPDATE carrito_libros
                 SET cantidad = :cantidad
                 WHERE id_carrito = :id_carrito AND id_libro = :id_libro",
                [
                    'cantidad' => $cantidad,
                    'id_carrito' => $idCarrito,
                    'id_libro' => $idLibro
                ]
            );
        } else {
            $this->query(
                "INSERT INTO carrito_libros (id_carrito, id_libro, cantidad)
                 VALUES (:id_carrito, :id_libro, :cantidad)",
                [
                    'id_carrito' => $idCarrito,
                    'id_libro' => $idLibro,
                    'cantidad' => $cantidad
                ]
            );
        }

        return Result::success('Cantidad actualizada', [
            'id_libro' => $idLibro,
            'cantidad' => $cantidad
        ])->toArray();
    }

    public function listar(int $idUsuario): array
    {
        $idCarrito = $this->obtenerIdCarrito($idUsuario);

        $items = $this->fetchAll(
            "SELECT
                l.id_libro,
                l.titulo,
                l.autor,
                l.editorial,
                l.genero,
                l.precio,
                l.stock,
                cl.cantidad
             FROM carrito_libros cl
             INNER JOIN libros l ON l.id_libro = cl.id_libro
             WHERE cl.id_carrito = :id_carrito
             ORDER BY l.titulo ASC",
            ['id_carrito' => $idCarrito]
        );

        $total = 0.0;
        foreach ($items as &$item) {
            $item['id_libro'] = (int) $item['id_libro'];
            $item['precio'] = (float) $item['precio'];
            $item['stock'] = (int) $item['stock'];
            $item['cantidad'] = (int) $item['cantidad'];
            $item['subtotal'] = $item['precio'] * $item['cantidad'];
            $total += $item['subtotal'];
        }
        unset($item);

        return Result::success('Carrito obtenido', [
            'items' => $items,
            'total' => $total
        ])->toArray();
    }

    public function quitarLibro(int $idUsuario, int $idLibro): array
    {
        $idCarrito = $this->obtenerIdCarrito($idUsuario);

        $this->query(
            "DELETE FROM carrito_libros
             WHERE id_carrito = :id_carrito AND id_libro = :id_libro",
            [
                'id_carrito' => $idCarrito,
                'id_libro' => $idLibro
            ]
        );

        return Result::success('Libro quitado del carrito')->toArray();
    }
}
