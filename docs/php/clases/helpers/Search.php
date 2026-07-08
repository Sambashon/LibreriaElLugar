<?php

namespace App\Helpers;

class Search
{
    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');

        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($value, \Normalizer::FORM_D);
            if ($normalized !== false) {
                $value = preg_replace('/\p{M}/u', '', $normalized) ?? $value;
            }
        } else {
            $value = strtr($value, [
                'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
                'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
                'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
                'ã' => 'a', 'õ' => 'o', 'ñ' => 'n',
            ]);
        }

        $value = preg_replace('/[!¡?,]/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    public static function includes(string $haystack, string $needle): bool
    {
        $normalizedNeedle = self::normalize($needle);
        if ($normalizedNeedle === '') {
            return true;
        }

        return str_contains(self::normalize($haystack), $normalizedNeedle);
    }
}
