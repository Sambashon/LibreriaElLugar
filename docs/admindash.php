<?php
require_once __DIR__ . '/php/admin_guard.php';
requireAdmin(false);

$adminNombre = htmlspecialchars($_SESSION['nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador | Librería El Lugar</title>
    <link rel="icon" href="Resources/logos/libreriasimple.jpg" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="src/css/components.css">
    <link rel="stylesheet" href="src/css/modal.css">
    <link rel="stylesheet" href="src/css/header.css">
    <link rel="stylesheet" href="src/css/index.css">
</head>
<body>
    <main class="admin-dashboard-page" style="background-image: url(Resources/fondos/flores.svg);">
        <a class="button admin-dashboard-back" href="index.html">Volver al inicio</a>
        <section class="modal-content dashboard-modal admin-dashboard-card">
            <div class="dashboard-header admin-header">
                <div class="dashboard-icon admin-icon">
                </div>
                <h2 id="adminGreeting">Panel de Administrador - <?= $adminNombre ?></h2>
            </div>
            <div class="dashboard-divider"></div>
            <div class="admin-panel-content">
                <div class="admin-actions">
                    <a class="dashboard-btn admin-btn" href="gestion.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5h16M6 12.5h12M6 5.5h12"></path>
                        </svg>
                        Gestionar Libros
                    </a>
                    <button class="dashboard-btn admin-btn" id="adminUsuariosBtn" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        Gestionar Usuarios
                    </button>
                    <a class="dashboard-btn admin-btn" href="pedidos.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        Ver Pedidos
                    </a>
                </div>
            </div>
            <div class="dashboard-divider"></div>
            <div class="dashboard-actions">
                <button class="dashboard-btn logout-btn" id="adminLogoutBtn" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Cerrar sesión
                </button>
            </div>
        </section>
    </main>
    <script src="src/js/admindash.js"></script>
</body>
</html>
