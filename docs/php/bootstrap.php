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
    
    // Fallback: convertir a minúsculas para compatibilidad con directorios en minúsculas
    $file_lower = strtolower($file);
    if ($file !== $file_lower && file_exists($file_lower)) {
        require_once $file_lower;
        return;
    }
    
    // Fallback: buscar con namespace en minúsculas pero class name sin cambios
    // App\Helpers\Response → helpers/Response.php
    $parts = explode('\\', $relative_class);
    if (count($parts) > 1) {
        // Hacer minúsculas el directorio (namespace) pero mantener el nombre de clase
        $dir_parts = array_slice($parts, 0, -1);
        $dir_parts = array_map('strtolower', $dir_parts);
        $class_name = $parts[count($parts) - 1];
        $file_alt = $base_dir . implode('/', $dir_parts) . '/' . $class_name . '.php';
        if (file_exists($file_alt)) {
            require_once $file_alt;
            return;
        }
    }
});

// Cargar clases existentes sin namespace (compatibilidad)
require_once __DIR__ . '/clases/libreriaDb.php';
