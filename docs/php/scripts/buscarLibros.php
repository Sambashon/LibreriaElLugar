<?php

require_once __DIR__ . "/../clases/libreriaDb.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if (!$query || strlen($query) < 2) {
        echo json_encode([
            'state' => 'success',
            'suggestions' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Search in database
    $searchTerm = '%' . $query . '%';
    $db = new LibreriaDB();
    
    $libros = $db->fetchAll(
        "SELECT id_libro, titulo, autor, precio, stock
         FROM libros
         WHERE titulo LIKE ? OR autor LIKE ?
         LIMIT 8",
        [$searchTerm, $searchTerm]
    );
    
    echo json_encode([
        'state' => 'success',
        'suggestions' => array_map(fn($l) => [
            'id' => (int)$l['id_libro'],
            'titulo' => $l['titulo'],
            'autor' => $l['autor'],
            'precio' => (float)$l['precio'],
            'stock' => (int)$l['stock']
        ], $libros)
    ], JSON_UNESCAPED_UNICODE);
    exit;
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'state' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
