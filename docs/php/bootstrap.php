<?php

/**
 * Bootstrap - Autoloader y setup inicial
 * Carga automáticamente clases usando namespaces PSR-4
 */

require_once __DIR__ . '/config.php';

// PSR-4 Autoloader con soporte para clases legacy sin namespace
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/clases/';
    
    // Verificar si la clase está en nuestro namespace
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    
    // Remover el prefix
    $relative_class = substr($class, strlen($prefix));
    
    // Mapeos especiales para clases existentes
    $special_map = [
        'Classes\Usuario' => 'usuario.php'
    ];
    
    if (isset($special_map[$relative_class])) {
        $file = $base_dir . $special_map[$relative_class];
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Conversión estándar de namespace a ruta
    // App\Helpers\Response → helpers/Response.php
    // App\Managers\SessionManager → managers/SessionManager.php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Intentar primero con la ruta como se especifica
    if (file_exists($file)) {
        require_once $file;
        return;
    }
    
    // Fallback: convertir a minúsculas para compatibilidad
    $file_lower = strtolower($file);
    if ($file !== $file_lower && file_exists($file_lower)) {
        require_once $file_lower;
        return;
    }
});

// Cargar clases existentes sin namespace (compatibilidad)
require_once __DIR__ . '/clases/libreriaDb.php';
