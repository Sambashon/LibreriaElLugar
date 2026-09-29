<?php

namespace App\Helpers;

class BookUid
{
    private const IDENTITY_FIELDS = ['titulo', 'autor', 'editorial', 'genero'];

    public static function normalizeBook(array $book): array
    {
        $normalized = [];

        foreach (self::IDENTITY_FIELDS as $field) {
            $value = mb_strtolower((string) ($book[$field] ?? ''), 'UTF-8');
            $value = preg_replace('/\s+/u', ' ', trim($value));

            if ($value === null) {
                throw new \InvalidArgumentException("El campo {$field} no contiene texto UTF-8 válido");
            }

            $normalized[$field] = trim($value);
        }

        return $normalized;
    }

    public static function forBook(array $book): string
    {
        return hash('sha256', implode('|', array_values(self::normalizeBook($book))));
    }

    public static function ensureExists(\LibreriaDB $db, array $book): string
    {
        $normalized = self::normalizeBook($book);
        $uid = hash('sha256', implode('|', array_values($normalized)));

        $db->query(
            "INSERT INTO libros_uid (uid, titulo, autor, editorial, genero)
             VALUES (:uid, :titulo, :autor, :editorial, :genero)
             ON DUPLICATE KEY UPDATE uid = VALUES(uid)",
            [
                'uid' => $uid,
                'titulo' => $normalized['titulo'],
                'autor' => $normalized['autor'],
                'editorial' => $normalized['editorial'],
                'genero' => $normalized['genero'],
            ]
        );

        return $uid;
    }
}
