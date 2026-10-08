<?php
require_once __DIR__ . '/php/bootstrap.php';

use App\Managers\SessionManager;

require_once __DIR__ . "/php/clases/libreriaDb.php";

$session = new SessionManager();
$db     = new LibreriaDB();
$libros = $db->fetchAll(
    "SELECT id_libro, uid, titulo, autor, editorial, genero, precio, stock, descripcion
     FROM libros
     ORDER BY titulo ASC"
);

// Get search parameter from URL
$initialSearch = isset($_GET['search']) ? htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') : '';
$checkoutName = trim((string) $session->obtener('nombre', '') . ' ' . (string) $session->obtener('apellido', ''));
$checkoutEmail = (string) $session->obtener('email', '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo | El lugar</title>
    <link rel="icon" href="Resources/logos/libreriasimple.jpg" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

    <link rel="preconnect" href="https://openlibrary.org">
    <link rel="preconnect" href="https://covers.openlibrary.org">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="src/css/modal.css">
    <link rel="stylesheet" href="src/css/components.css">
    
    <link rel="stylesheet" href="src/css/header.css">
    <link rel="stylesheet" href="src/css/catalogue.css">
</head>
<body>

    <!-- ── MOBILE TOP BAR (visible only on mobile) ──────────────── -->
    <div class="mobile-topbar">
        <button class="mobile-hamburger" id="mobileMenuBtn" aria-label="Abrir menú">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                <line x1="3" y1="6"  x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
        <div class="mobile-logo-wrap">
            <a href="index.html">
                <img src="Resources/logos/libreriaElLugar.png" alt="El Lugar">
            </a>
        </div>
    </div>

    <!-- ── OVERLAY (dims content behind open drawer) ─────────────── -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <div class="custom-row">
        <nav class="nav-menu">

            <!-- Close button — only visible on mobile -->
            <button class="nav-drawer-close" id="navDrawerClose" aria-label="Cerrar menú">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <line x1="18" y1="6"  x2="6"  y2="18"/>
                    <line x1="6"  y1="6"  x2="18" y2="18"/>
                </svg>
            </button>

            <header class="nav-logo">
                <div class="logo-container">
                    <a href="index.html">
                        <img src="Resources/logos/libreriaElLugar.png">
                    </a>
                    
                </div>
                <hr style="margin: 10px 0px; width: 100%;">
            </header>

            <div class="menu-item" id="inicioBtn">
                <svg class="icon" viewBox="0 0 14 14" fill="none"><rect x="1" y="1" width="5" height="5" rx="1" fill="currentColor"/><rect x="8" y="1" width="5" height="5" rx="1" fill="currentColor"/><rect x="1" y="8" width="5" height="5" rx="1" fill="currentColor"/><rect x="8" y="8" width="5" height="5" rx="1" fill="currentColor"/></svg>
                <p>Inicio</p>
            </div>
            <div class="menu-item" id="carritoBtn">
                <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.29977 5H21L19 12H7.37671M20 16H8L6 3H3M9 20C9 20.5523 8.55228 21 8 21C7.44772 21 7 20.5523 7 20C7 19.4477 7.44772 19 8 19C8.55228 19 9 19.4477 9 20ZM20 20C20 20.5523 19.5523 21 19 21C18.4477 21 18 20.5523 18 20C18 19.4477 18.4477 19 19 19C19.5523 19 20 19.4477 20 20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <p>Carrito</p>
            </div>
            <div class="menu-item" id="favoritosBtn">
                <svg class="icon" viewBox="0 0 14 14" fill="none"><path d="M7 2l1.4 2.8 3.1.45-2.25 2.2.53 3.1L7 9.1l-2.78 1.45.53-3.1L2.5 5.25l3.1-.45L7 2z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>
                <p>Favoritos</p>
            </div>

            <header class="nav-header">
                <hr style="margin: 4px 0px; width: 100%;">
                <h3>Filtros</h3>
            </header>

            <div class="filters-wrapper">

                <!-- GÉNERO -->
                <div class="filter-item">
                    <div class="filter-header" onclick="toggleFilter(this)">
                        <p>Género</p>
                        <div class="arrow-container">
                            <svg class="icon" fill="currentColor" viewBox="0 0 30.727 30.727" xml:space="preserve"><path d="M29.994,10.183L15.363,24.812L0.733,10.184c-0.977-0.978-0.977-2.561,0-3.536c0.977-0.977,2.559-0.976,3.536,0l11.095,11.093L26.461,6.647c0.977-0.976,2.559-0.976,3.535,0C30.971,7.624,30.971,9.206,29.994,10.183z"/></svg>
                        </div>
                    </div>
                    <div class="filter-body">
                        <div class="filter-search">
                            <input type="text" placeholder="Buscar género…" oninput="filterGenres(this.value)">
                        </div>
                        <div class="genre-list" id="genreList"></div>
                    </div>
                </div>

                <!-- PRECIO -->
                <div class="filter-item">
                    <div class="filter-header" onclick="toggleFilter(this)">
                        <p>Precio</p>
                        <div class="arrow-container">
                            <svg class="icon" fill="currentColor" viewBox="0 0 30.727 30.727" xml:space="preserve"><path d="M29.994,10.183L15.363,24.812L0.733,10.184c-0.977-0.978-0.977-2.561,0-3.536c0.977-0.977,2.559-0.976,3.536,0l11.095,11.093L26.461,6.647c0.977-0.976,2.559-0.976,3.535,0C30.971,7.624,30.971,9.206,29.994,10.183z"/></svg>
                        </div>
                    </div>
                    <div class="filter-body">
                        <div class="price-inputs">
                            <div class="price-row">
                                <input type="number" id="priceMin" placeholder="Mín" min="0" step="100" oninput="applyFilters()">
                                <span>—</span>
                                <input type="number" id="priceMax" placeholder="Máx" min="0" step="100" oninput="applyFilters()">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DISPONIBILIDAD -->
                <div class="filter-item">
                    <div class="filter-header" onclick="toggleFilter(this)">
                        <p>Disponibilidad</p>
                        <div class="arrow-container">
                            <svg class="icon" fill="currentColor" viewBox="0 0 30.727 30.727" xml:space="preserve"><path d="M29.994,10.183L15.363,24.812L0.733,10.184c-0.977-0.978-0.977-2.561,0-3.536c0.977-0.977,2.559-0.976,3.536,0l11.095,11.093L26.461,6.647c0.977-0.976,2.559-0.976,3.535,0C30.971,7.624,30.971,9.206,29.994,10.183z"/></svg>
                        </div>
                    </div>
                    <div class="filter-body">
                        <div class="avail-chips">
                            <label class="avail-chip"><input type="checkbox" id="filterStock" checked onchange="applyFilters()"> En stock</label>
                            <label class="avail-chip"><input type="checkbox" id="filterAgotado" onchange="applyFilters()"> Agotado</label>
                        </div>
                    </div>
                </div>

            </div><!-- /filters-wrapper -->

        </nav>

        <main class="product-shell">
            <header class="product-header">
                <div class="row">
                    <div class="columnb">
                        <h1>Catálogo</h1>
                        <h3 id="resultCount"></h3>
                    </div>
                    <form class="searchbar-form catalogue-searchbar">
                        <button type="button">
                            <svg width="17" height="16" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="search">
                                <path d="M7.667 12.667A5.333 5.333 0 107.667 2a5.333 5.333 0 000 10.667zM14.334 14l-2.9-2.9" stroke="currentColor" stroke-width="1.333" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                        <input type="text" id="searchInput" class="search-input" placeholder="Buscar libros..." value="<?= $initialSearch ?>" oninput="applyFilters()">
                        <button class="reset" type="reset">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </form>
                    <select class="sort-select" id="sortSelect" onchange="applyFilters()">
                        <option value="alpha">Título A–Z</option>
                        <option value="price-asc">Precio: menor a mayor</option>
                        <option value="price-desc">Precio: mayor a menor</option>
                    </select>
                </div>
                <hr>
            </header>

            <div class="main-wrapper">
                <div class="catalogue-view" id="catalogueView">
                    <div class="product-body"></div>
                </div>

                <div class="cart-view" id="cartView" aria-hidden="true">
                    <nav class="breadcrumb">
                        <button type="button" id="backFromCart">Volver al catálogo</button>
                    </nav>
                    <header class="cart-header">
                        <h2>Tu carrito</h2>
                        <p id="cartCount"></p>
                    </header>
                    <div class="cart-list" id="cartList"></div>
                    <div class="cart-footer" id="cartFooter" hidden>
                        <p class="cart-total-label">Total</p>
                        <p class="cart-total-value" id="cartTotal"></p>
                    </div>
                    <button class="button checkout-open" id="openCheckoutModal" type="button" hidden>Confirmar pedido</button>
                </div>

                <div class="cart-view" id="favView" aria-hidden="true">
                    <nav class="breadcrumb">
                        <button type="button" id="backFromFav">Volver al catálogo</button>
                    </nav>
                    <header class="cart-header">
                        <h2>Tus favoritos</h2>
                        <p id="favCount"></p>
                    </header>
                    <div class="cart-list" id="favList"></div>
                </div>
            </div>

    <div class="catalogue-toast" id="catalogueToast" role="status" aria-live="polite"></div>

    <div class="modal fade form-modal-bg checkout-modal" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalTitle" aria-hidden="true">
        <div class="modal-dialog form-modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <nav class="modal-nav">
                        <button class="modal-close unsetter" type="button" data-bs-dismiss="modal" aria-label="Cerrar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </nav>
                    <section class="register active checkout-modal-content">
                        <header class="header">
                            <h1 id="checkoutModalTitle">Confirmar pedido</h1>
                            <p>Completá tus datos para reservar los libros y coordinar el retiro.</p>
                        </header>
                        <p class="checkout-pickup">Entrega: retiro en la librería, dentro de nuestros horarios de trabajo (Lunes a Viernes de 10:00 a 20:00, Sábados de 10:00 a 18:00). No realizamos envíos.</p>
                        <form id="checkoutForm" novalidate>
                            <div class="row">
                                <div class="column">
                                    <label for="checkoutName">Nombre completo</label>
                                    <input class="form-input" type="text" id="checkoutName" name="nombre" placeholder="Tu nombre..." maxlength="200" pattern=".*\S.*" required value="<?= htmlspecialchars($checkoutName, ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="column">
                                    <label for="checkoutPhone">Teléfono</label>
                                    <input class="form-input" type="tel" id="checkoutPhone" name="telefono" placeholder="Tu teléfono..." maxlength="40" pattern="[\d\+\(\)\.\s\-]{6,40}" autocomplete="tel" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="column">
                                    <label for="checkoutEmail">Correo electrónico</label>
                                    <input class="form-input" type="email" id="checkoutEmail" name="email" placeholder="Tu email..." maxlength="150" autocomplete="email" required value="<?= htmlspecialchars($checkoutEmail, ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="column">
                                    <label for="checkoutPayment">Forma de pago</label>
                                    <select class="form-input" id="checkoutPayment" name="metodo_pago" required>
                                        <option value="" selected disabled>Seleccioná una forma de pago</option>
                                        <option value="en_libreria">Pago en librería</option>
                                        <option value="transferencia_bancaria">Transferencia bancaria</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="column">
                                    <label for="checkoutComments">Comentarios (opcional)</label>
                                    <textarea class="form-input" id="checkoutComments" name="comentarios" maxlength="2000" rows="3" placeholder="Dejanos cualquier comentario..."></textarea>
                                </div>
                            </div>
                            <div class="checkout-modal-actions">
                                <button class="button2 checkout-submit" id="checkoutSubmit" type="submit">Confirmar pedido</button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>

            <footer class="footer-pages" id="footerPages"></footer>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="src/js/search-utils.js"></script>
    <script src="src/js/portadas.js?v=2"></script>
    <script>
        // ── DATOS DESDE PHP ──────────────────────────────────────────
        const librosDB = <?= json_encode(array_map(fn($l) => [
            'id'        => (int)  $l['id_libro'],
            'uid'       =>        $l['uid'],
            'titulo'    =>        $l['titulo'],
            'autor'     =>        $l['autor'],
            'editorial' =>        $l['editorial'] ?? '',
            'genero'    =>        $l['genero']    ?? '',
            'precio'      => (float)$l['precio'],
            'stock'       => (int)  $l['stock'],
            'descripcion' =>        $l['descripcion'] ?? '',
        ], $libros), JSON_UNESCAPED_UNICODE) ?>;

        // Géneros únicos extraídos de la DB
        const genres = [...new Set(
            librosDB.map(l => l.genero).filter(Boolean).sort()
        )];

        // ── PER_PAGE: 6 filas × columnas visibles ───────────────────
        function calcPerPage() {
            const bodyW = document.querySelector('.product-body').clientWidth;
            const cols  = Math.max(1, Math.floor((bodyW + 24) / (200 + 24)));
            return cols * 6;
        }
        let PER_PAGE    = calcPerPage();
        let currentPage = 1;

        window.addEventListener('resize', () => {
            const newPerPage = calcPerPage();
            if (newPerPage === PER_PAGE) return;

            const firstItemIndex = (currentPage - 1) * PER_PAGE;
            PER_PAGE = newPerPage;
            const total = Math.max(1, Math.ceil(filteredLibros.length / PER_PAGE));
            currentPage = Math.min(
                Math.max(1, Math.floor(firstItemIndex / PER_PAGE) + 1),
                total
            );
            renderPage();
        });
        let   activeGenres   = new Set();
        let   filteredLibros = [...librosDB];

        const catalogueView   = document.getElementById('catalogueView');
        const cartView        = document.getElementById('cartView');
        const favView         = document.getElementById('favView');
        const carritoBtn      = document.getElementById('carritoBtn');
        const favoritosBtn    = document.getElementById('favoritosBtn');
        const productShell    = document.querySelector('.product-shell');
        const mainWrapper     = document.querySelector('.main-wrapper');
        let toastTimer = null;

        const COVER_COLORS = ['#2a6644', '#2a5c40', '#1e4a35', '#2f5e45', '#234d38', '#335c43', '#244d38', '#355f48'];

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function coverColor(id) {
            return COVER_COLORS[Math.abs(Number(id) || 0) % COVER_COLORS.length];
        }

        function formatPrice(value) {
            return '$' + Number(value).toLocaleString('es-AR');
        }

        function findBook(id) {
            return librosDB.find(l => l.id === Number(id));
        }

        function showToast(message) {
            const toast = document.getElementById('catalogueToast');
            toast.textContent = message;
            toast.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
        }

        async function getSession() {
            const res = await fetch('php/scripts/sesion.php', { credentials: 'same-origin' });
            return res.json();
        }

        async function requireLogin(message) {
            const session = await getSession();
            if (session && session.logged) return true;
            showToast(message || 'Iniciá sesión para usar el carrito');
            return false;
        }

        async function postLibro(url, idLibro, extra = {}) {
            const body = new FormData();
            body.append('id_libro', String(idLibro));
            Object.entries(extra).forEach(([k, v]) => body.append(k, String(v)));
            const res = await fetch(url, { method: 'POST', body, credentials: 'same-origin' });
            const data = await res.json();
            if (data.state !== 'success') throw new Error(data.message || 'No se pudo completar la acción');
            return data;
        }

        async function addToCart(idLibro, cantidad = 1) {
            if (!(await requireLogin('Iniciá sesión para agregar libros al carrito'))) return false;
            try {
                await postLibro('php/scripts/agregarAlCarrito.php', idLibro, { cantidad });
                showToast('Libro agregado al carrito');
                return true;
            } catch (err) {
                showToast(err.message || 'Error al agregar al carrito');
                return false;
            }
        }

        function renderCart(items, total) {
            const list = document.getElementById('cartList');
            const count = document.getElementById('cartCount');
            const footer = document.getElementById('cartFooter');
            const checkoutButton = document.getElementById('openCheckoutModal');
            const totalEl = document.getElementById('cartTotal');

            if (!items.length) {
                count.textContent = 'No tenés libros en el carrito';
                footer.hidden = true;
                checkoutButton.hidden = true;
                totalEl.textContent = formatPrice(total);
                list.innerHTML = '<p class="cart-empty">Agregá libros desde el catálogo para verlos acá.</p>';
                return;
            }

            count.textContent = items.length + ' libro' + (items.length !== 1 ? 's' : '');
            footer.hidden = false;
            checkoutButton.hidden = false;
            totalEl.textContent = formatPrice(total);
            list.innerHTML = items.map(item => `
                <article class="cart-item" data-id="${item.id_libro}" data-uid="${escapeHtml(item.uid || '')}">
                    <div class="cart-item-cover" style="background:${coverColor(item.id_libro)};"></div>
                    <div class="cart-item-info">
                        <p class="cart-item-title">${escapeHtml(item.titulo)}</p>
                        <p class="cart-item-author">${escapeHtml(item.autor || '')}</p>
                        <p class="cart-item-qty">Cantidad: ${item.cantidad}</p>
                    </div>
                    <div class="cart-item-side">
                        <span class="cart-item-price">${formatPrice(item.subtotal)}</span>
                        <button type="button" class="cart-remove">Quitar</button>
                    </div>
                </article>
            `).join('');

            bindCartItemCovers(list);
            bindRemoveButtons(list, 'php/scripts/quitarDelCarrito.php', loadCart);
        }

        function bindCartItemCovers(list) {
            list.querySelectorAll('.cart-item').forEach(row => {
                const book = findBook(row.dataset.id);
                if (row.dataset.uid || book) {
                    const title = row.querySelector('.cart-item-title')?.textContent || book?.titulo || '';
                    const author = row.querySelector('.cart-item-author')?.textContent || book?.autor || '';
                    PortadasOL.aplicarPortada(
                        row.querySelector('.cart-item-cover'),
                        title,
                        author,
                        row.dataset.uid || book?.uid || ''
                    );
                }
            });
        }

        function bindRemoveButtons(list, url, reload) {
            list.querySelectorAll('.cart-remove').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = Number(btn.closest('.cart-item').dataset.id);
                    try {
                        await postLibro(url, id);
                        await reload();
                    } catch (err) {
                        showToast(err.message || 'Error al quitar el libro');
                    }
                });
            });
        }

        function renderFavorites(items) {
            const list = document.getElementById('favList');
            const count = document.getElementById('favCount');

            if (!items.length) {
                count.textContent = 'No tenés libros en favoritos';
                list.innerHTML = '<p class="cart-empty">Marcá libros desde el detalle para verlos acá.</p>';
                return;
            }

            count.textContent = items.length + ' libro' + (items.length !== 1 ? 's' : '');
            list.innerHTML = items.map(item => `
                <article class="cart-item" data-id="${item.id_libro}" data-uid="${escapeHtml(item.uid || '')}">
                    <div class="cart-item-cover" style="background:${coverColor(item.id_libro)};"></div>
                    <div class="cart-item-info">
                        <p class="cart-item-title">${escapeHtml(item.titulo)}</p>
                        <p class="cart-item-author">${escapeHtml(item.autor || '')}</p>
                    </div>
                    <div class="cart-item-side">
                        <span class="cart-item-price">${formatPrice(item.precio)}</span>
                        <button type="button" class="cart-remove">Quitar</button>
                    </div>
                </article>
            `).join('');

            bindCartItemCovers(list);
            bindRemoveButtons(list, 'php/scripts/quitarDeFavoritos.php', async () => {
                await loadFavorites();
            });
        }

        async function loadCart() {
            const res = await fetch('php/scripts/obtenerCarrito.php', { credentials: 'same-origin' });
            const data = await res.json();
            if (data.state !== 'success') {
                throw new Error(data.message || 'No se pudo cargar el carrito');
            }
            renderCart(data.items || [], data.total ?? 0);
        }

        const checkoutForm = document.getElementById('checkoutForm');
        const checkoutModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('checkoutModal'));
        const checkoutSubmit = document.getElementById('checkoutSubmit');

        document.getElementById('openCheckoutModal').addEventListener('click', () => {
            checkoutModal.show();
        });

        checkoutForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!checkoutForm.checkValidity()) {
                checkoutForm.reportValidity();
                return;
            }
            checkoutSubmit.disabled = true;
            try {
                const response = await fetch('php/scripts/crearPedido.php', {
                    method: 'POST',
                    body: new FormData(checkoutForm),
                    credentials: 'same-origin'
                });
                const data = await response.json();
                if (!response.ok || data.state !== 'success') {
                    throw new Error(data.message || 'No se pudo confirmar el pedido');
                }
                showToast(`Pedido confirmado. Te contactaremos para coordinar.`);
                checkoutForm.reset();
                checkoutModal.hide();
                renderCart([], 0);
                try {
                    await loadCart();
                } catch (error) {
                    showToast(`Pedido confirmado, pero no se pudo actualizar el carrito.`);
                }
            } catch (error) {
                showToast(error.message || 'No se pudo confirmar el pedido');
            } finally {
                checkoutSubmit.disabled = false;
            }
        });

        async function loadFavorites() {
            const res = await fetch('php/scripts/obtenerFavoritos.php', { credentials: 'same-origin' });
            const data = await res.json();
            if (data.state !== 'success') {
                throw new Error(data.message || 'No se pudo cargar favoritos');
            }
            renderFavorites(data.items || []);
        }

        function closeListView(view, btn, keepHidden = false) {
            view.classList.remove('active');
            view.setAttribute('aria-hidden', 'true');
            btn.classList.remove('active');
            const otherOpen = cartView.classList.contains('active') || favView.classList.contains('active');
            if (!otherOpen) productShell.classList.remove('cart-open');
            if (!keepHidden && !otherOpen) {
                catalogueView.classList.remove('hidden');
                document.title = 'Catálogo | El lugar';
            }
        }

        function closeCartView(keepHidden = false) {
            closeListView(cartView, carritoBtn, keepHidden);
        }

        function closeFavView(keepHidden = false) {
            closeListView(favView, favoritosBtn, keepHidden);
        }

        async function openListView({ requireMsg, load, otherClose, view, btn, title }) {
            if (!(await requireLogin(requireMsg))) return;
            try {
                await load();
            } catch (err) {
                showToast(err.message);
                return;
            }
            otherClose();
            catalogueView.classList.add('hidden');
            view.classList.add('active');
            view.setAttribute('aria-hidden', 'false');
            productShell.classList.add('cart-open');
            btn.classList.add('active');
            document.title = title;
            mainWrapper.scrollTo({ top: 0, behavior: 'smooth' });
        }

        async function openCartView() {
            await openListView({
                requireMsg: 'Iniciá sesión para ver tu carrito',
                load: loadCart,
                otherClose: () => closeFavView(true),
                view: cartView,
                btn: carritoBtn,
                title: 'Carrito | El lugar'
            });
        }

        async function openFavView() {
            await openListView({
                requireMsg: 'Iniciá sesión para ver tus favoritos',
                load: loadFavorites,
                otherClose: () => closeCartView(true),
                view: favView,
                btn: favoritosBtn,
                title: 'Favoritos | El lugar'
            });
        }

        document.getElementById('backFromCart').addEventListener('click', () => closeCartView());
        document.getElementById('backFromFav').addEventListener('click', () => closeFavView());

        // ── PRECIO — sin inicialización necesaria

        // ── FILTROS ─────────────────────────────────────────────────
        function applyFilters() {
            const sort       = document.getElementById('sortSelect').value;
            const stockOn    = document.getElementById('filterStock').checked;
            const agotadoOn  = document.getElementById('filterAgotado').checked;
            const minVal     = parseFloat(document.getElementById('priceMin').value) || 0;
            const maxVal     = parseFloat(document.getElementById('priceMax').value) || Infinity;
            const searchTerm = document.getElementById('searchInput').value.trim();

            let list = librosDB.filter(l => {
                if (activeGenres.size && !activeGenres.has(l.genero)) return false;
                if (l.precio < minVal)                                 return false;
                if (l.precio > maxVal)                                 return false;
                if (l.stock > 0  && !stockOn)                         return false;
                if (l.stock === 0 && !agotadoOn)                      return false;
                if (searchTerm && !textIncludesSearch(l.titulo, searchTerm) &&
                                  !textIncludesSearch(l.autor, searchTerm)) return false;
                return true;
            });

            if (sort === 'alpha')      list.sort((a,b) => a.titulo.localeCompare(b.titulo));
            if (sort === 'price-asc')  list.sort((a,b) => a.precio - b.precio);
            if (sort === 'price-desc') list.sort((a,b) => b.precio - a.precio);

            filteredLibros = list;
            currentPage    = 1;
            closeCartView();
            closeFavView();

            const body = document.querySelector('.product-body');
            body.style.transition = 'opacity 0.2s ease';
            body.style.opacity = '0';
            setTimeout(() => {
                renderPage();
                document.querySelector('.main-wrapper').scrollTo({ top: 0 });
                body.style.opacity = '1';
            }, 200);
        }


        // ── GÉNEROS ─────────────────────────────────────────────────
        function renderGenres(list) {
            document.getElementById('genreList').innerHTML = list.map(g => `
                <label class="genre-item">
                    <input type="checkbox" ${activeGenres.has(g) ? 'checked' : ''}
                           onchange="toggleGenre('${g.replace(/'/g,"\\'")}', this.checked)">
                    ${g}
                </label>
            `).join('');
        }

        function filterGenres(q) {
            renderGenres(genres.filter(g => textIncludesSearch(g, q)));
        }

        function toggleGenre(g, checked) {
            checked ? activeGenres.add(g) : activeGenres.delete(g);
            applyFilters();
        }

        renderGenres(genres);

        // ── RENDER CARDS ────────────────────────────────────────────
        function renderPage() {
            const start = (currentPage - 1) * PER_PAGE;
            const page  = filteredLibros.slice(start, start + PER_PAGE);
            const body  = document.querySelector('.product-body');

            document.getElementById('resultCount').textContent =
                filteredLibros.length + ' resultado' + (filteredLibros.length !== 1 ? 's' : '');

            body.innerHTML = '';

            if (page.length === 0) {
                body.innerHTML = '<p style="color:#EBE9DA;grid-column:1/-1;padding:2rem;font-family:\'Fraunces\',serif;font-style:italic;">No se encontraron libros.</p>';
                renderPagination();
                return;
            }

            page.forEach(book => {
                const card = document.createElement('div');
                card.classList.add('product-card');
                card.dataset.id = book.id;
                card.innerHTML = `
                    <div class="card-cover"></div>
                    <div class="card-info">
                        <div>
                            <p class="card-title">${escapeHtml(book.titulo)}</p>
                            <p class="card-author">${escapeHtml(book.autor)}</p>
                        </div>
                        <p class="card-price">${formatPrice(book.precio)}</p>
                    </div>
                    <div class="card-btn">Agregar al carrito</div>
                `;
                card.addEventListener('click', (e) => {
                    if (e.target.closest('.card-btn')) {
                        e.stopPropagation();
                        addToCart(book.id, 1);
                        return;
                    }
                    window.location.href = `book-detail.php?id=${encodeURIComponent(book.id)}`;
                });
                body.appendChild(card);
                PortadasOL.aplicarPortada(card.querySelector('.card-cover'), book.titulo, book.autor, book.uid);
            });

            renderPagination();
        }

        // ── PAGINACIÓN ───────────────────────────────────────────────
        function renderPagination() {
            const total   = Math.ceil(filteredLibros.length / PER_PAGE);
            const footer  = document.getElementById('footerPages');
            footer.innerHTML = '';

            if (total <= 1) return;

            const mkBtn = (label, page, active = false, disabled = false) => {
                const b = document.createElement('button');
                b.className  = 'page-btn' + (active ? ' active' : '');
                b.textContent = label;
                b.disabled   = disabled;
                if (!disabled) b.onclick = () => {
                    const body = document.querySelector('.product-body');
                    body.style.transition = 'opacity 0.2s ease';
                    body.style.opacity = '0';
                    setTimeout(() => {
                        currentPage = page;
                        renderPage();
                        document.querySelector('.main-wrapper').scrollTo({ top: 0 });
                        body.style.opacity = '1';
                    }, 200);
                };
                return b;
            };

            footer.appendChild(mkBtn('‹', currentPage - 1, false, currentPage === 1));

            // ventana de páginas
            let start = Math.max(1, currentPage - 2);
            let end   = Math.min(total, start + 4);
            if (end - start < 4) start = Math.max(1, end - 4);

            for (let i = start; i <= end; i++) {
                footer.appendChild(mkBtn(i, i, i === currentPage));
            }

            footer.appendChild(mkBtn('›', currentPage + 1, false, currentPage === total));
        }

        // ── NAV ─────────────────────────────────────────────────────
        document.getElementById('inicioBtn').addEventListener('click', () => {
            window.location.href = '/';
        });

        carritoBtn.addEventListener('click', () => {
            if (typeof closeDrawer === 'function') closeDrawer();
            openCartView();
        });

        favoritosBtn.addEventListener('click', () => {
            if (typeof closeDrawer === 'function') closeDrawer();
            openFavView();
        });

        document.querySelectorAll('.uC').forEach(el => {
            el.addEventListener('click', () => alert('Función en construcción'));
        });

        /* Acordeón */
        function toggleFilter(header) {
            const arrow  = header.querySelector('.arrow-container');
            const body   = header.nextElementSibling;
            const isOpen = body.classList.contains('open');
            document.querySelectorAll('.filter-body').forEach(b => b.classList.remove('open'));
            document.querySelectorAll('.arrow-container').forEach(a => a.classList.remove('active'));
            if (!isOpen) { body.classList.add('open'); arrow.classList.add('active'); }
        }

        // ── ARRANCAR ────────────────────────────────────────────────
        const catalogueSearch = sessionStorage.getItem('catalogueSearch');
        if (catalogueSearch) {
            document.getElementById('searchInput').value = catalogueSearch;
            sessionStorage.removeItem('catalogueSearch');
        }
        applyFilters();
        
        // Apply initial search if provided
        if ('<?= $initialSearch ?>') {
            document.getElementById('searchInput').value = '<?= $initialSearch ?>';
            applyFilters();
        }

        //para hacer funcionar mi reset
        document.querySelector('.catalogue-searchbar').addEventListener('reset', () => {
            setTimeout(() => applyFilters(), 0);
        });

        const viewParam = new URLSearchParams(window.location.search).get('view');
        if (viewParam === 'cart') {
            openCartView();
        } else if (viewParam === 'favorites') {
            openFavView();
        }

        // ── MOBILE DRAWER ────────────────────────────────────────────
        const mobileMenuBtn   = document.getElementById('mobileMenuBtn');
        const navDrawerClose  = document.getElementById('navDrawerClose');
        const mobileOverlay   = document.getElementById('mobileOverlay');
        const navMenu         = document.querySelector('.nav-menu');

        function openDrawer() {
            navMenu.classList.add('open');
            mobileOverlay.classList.add('visible');
            document.body.classList.add('drawer-open');
        }
        function closeDrawer() {
            navMenu.classList.remove('open');
            mobileOverlay.classList.remove('visible');
            document.body.classList.remove('drawer-open');
        }

        if (mobileMenuBtn)   mobileMenuBtn.addEventListener('click', openDrawer);
        if (navDrawerClose)  navDrawerClose.addEventListener('click', closeDrawer);
        if (mobileOverlay)   mobileOverlay.addEventListener('click', closeDrawer);
    </script>

</body>
</html>