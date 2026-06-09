<?php

/**
 * Configuración centralizada de la aplicación
 */

// Constantes de Cookie
define('COOKIE_NAME', getenv('COOKIE_NAME') ?: 'auth_token');
define('COOKIE_DAYS', (int) (getenv('COOKIE_DAYS') ?: 30));
define('COOKIE_PATH', getenv('COOKIE_PATH') ?: '/');
define('COOKIE_SECURE', getenv('APP_ENV') === 'production' ? true : false);
define('COOKIE_HTTPONLY', true);
define('COOKIE_SAMESITE', 'Lax');

// Constantes de Token Persistente
define('TOKEN_EXPIRATION_DAYS', (int) (getenv('TOKEN_EXPIRATION_DAYS') ?: 30));
define('TOKEN_SELECTOR_LENGTH', 12); // 96 bits = 12 bytes base64
define('TOKEN_VERIFIER_LENGTH', 32); // 256 bits = 32 bytes base64

// Constantes de API Response
define('RESPONSE_DEFAULT_CHARSET', 'utf-8');
define('RESPONSE_DEFAULT_CONTENT_TYPE', 'application/json');

// Ambiente
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');
