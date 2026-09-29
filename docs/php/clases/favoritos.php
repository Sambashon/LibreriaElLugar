<?php

namespace App\Classes;

require_once __DIR__ . "/libreriaDb.php";
require_once __DIR__ . "/../bootstrap.php";

use App\Helpers\Result;

class Favoritos extends \LibreriaDB
{
    public function obtenerIdFavorito(int $idUsuario): int
    {
        $row = $this->fetch(
            "SELECT id_favorito FROM favoritos WHERE id_usuario = :id_usuario LIMIT 1",
            ['id_usuario' => $idUsuario]
        );

        if ($row) {
            return (int) $row['id_favorito'];
        }

        $this->query(
            "INSERT INTO favoritos (id_usuario) VALUES (:id_usuario)",
            ['id_usuario' => $idUsuario]
        );

        return (int) $this->lastInsertId();
    }

    public function agregarLibro(int $idUsuario, int $idLibro): array
    {
        $libro = $this->fetch(
            "SELECT id_libro FROM libros WHERE id_libro = :id",
            ['id' => $idLibro]
        );

        if (!$libro) {
            return Result::error('Libro no encontrado', 404)->toArray();
        }

        $idFavorito = $this->obtenerIdFavorito($idUsuario);
        $existe = $this->fetch(
            "SELECT 1 FROM favorito_libros
             WHERE id_favorito = :id_favorito AND id_libro = :id_libro",
            [
                'id_favorito' => $idFavorito,
                'id_libro' => $idLibro
            ]
        );

        if (!$existe) {
            $this->query(
                "INSERT INTO favorito_libros (id_favorito, id_libro)
                 VALUES (:id_favorito, :id_libro)",
                [
                    'id_favorito' => $idFavorito,
                    'id_libro' => $idLibro
                ]
            );
        }

        return Result::success(
            $existe ? 'El libro ya está en favoritos' : 'Libro agregado a favoritos',
            ['id_libro' => $idLibro, 'already' => (bool) $existe]
        )->toArray();
    }

    public function quitarLibro(int $idUsuario, int $idLibro): array
    {
        $idFavorito = $this->obtenerIdFavorito($idUsuario);

        $this->query(
            "DELETE FROM favorito_libros
             WHERE id_favorito = :id_favorito AND id_libro = :id_libro",
            [
                'id_favorito' => $idFavorito,
                'id_libro' => $idLibro
            ]
        );

        return Result::success('Libro quitado de favoritos')->toArray();
    }

    public function listar(int $idUsuario): array
    {
        $idFavorito = $this->obtenerIdFavorito($idUsuario);

        $items = $this->fetchAll(
            "SELECT
                l.id_libro,
                l.titulo,
                l.autor,
                l.editorial,
                l.genero,
                l.precio,
                l.stock
             FROM favorito_libros fl
             INNER JOIN libros l ON l.id_libro = fl.id_libro
             WHERE fl.id_favorito = :id_favorito
             ORDER BY l.titulo ASC",
            ['id_favorito' => $idFavorito]
        );

        foreach ($items as &$item) {
            $item['id_libro'] = (int) $item['id_libro'];
            $item['precio'] = (float) $item['precio'];
            $item['stock'] = (int) $item['stock'];
        }
        unset($item);

        return Result::success('Favoritos obtenidos', [
            'items' => $items
        ])->toArray();
    }
}
