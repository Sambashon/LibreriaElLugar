<?php
require_once __DIR__ . "/php/clases/libreriaDb.php";

$db     = new LibreriaDB();
$libros = $db->fetchAll(
    "SELECT id_libro, uid, titulo, autor, editorial, genero, precio, stock, descripcion
     FROM libros
     ORDER BY titulo ASC"
);

// Get search parameter from URL
$initialSearch = isset($_GET['search']) ? htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') : '';
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

    <link rel="stylesheet" href="src/css/components.css">
    <link rel="stylesheet" href="src/css/header.css">
    <link rel="stylesheet" href="src/css/catalogue.css">
    <link rel="stylesheet" href="src/css/book-detail.css">
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
                    <div class="column">
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
                <hr style="color: #F0C05F;">
            </header>

            <div class="main-wrapper">
                <div class="catalogue-view" id="catalogueView">
                    <div class="product-body"></div>
                </div>

                <div class="book-detail-view" id="bookDetailView" aria-hidden="true">
                    <nav class="breadcrumb">
                        <button type="button" id="backToCatalogue">Volver al catálogo</button>
                    </nav>

                    <div class="product-layout">
                        <div class="cover-col">
                            <div class="book-cover" id="detailCover">
                                <div class="cover-deco">
                                    <svg class="cover-ornament" viewBox="0 0 60 12" fill="none" aria-hidden="true">
                                        <line x1="0" y1="6" x2="22" y2="6" stroke="#F0C05F" stroke-width="0.8"/>
                                        <circle cx="30" cy="6" r="4" stroke="#F0C05F" stroke-width="0.8"/>
                                        <line x1="38" y1="6" x2="60" y2="6" stroke="#F0C05F" stroke-width="0.8"/>
                                    </svg>
                                    <p class="cover-title-text" id="detailCoverTitle"></p>
                                    <div class="cover-line"></div>
                                    <p class="cover-author-text" id="detailCoverAuthor"></p>
                                    <svg class="cover-ornament" viewBox="0 0 60 12" fill="none" aria-hidden="true">
                                        <line x1="0" y1="6" x2="22" y2="6" stroke="#F0C05F" stroke-width="0.8"/>
                                        <circle cx="30" cy="6" r="4" stroke="#F0C05F" stroke-width="0.8"/>
                                        <line x1="38" y1="6" x2="60" y2="6" stroke="#F0C05F" stroke-width="0.8"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="info-col">

                            <div>
                                <h2 class="book-title" id="detailTitle"></h2>
                                <p class="book-author">por <span id="detailAuthor"></span></p>
                            </div>

                            <div class="divider"></div>

                            <div class="meta-grid">
                                <div class="meta-item">
                                    <span class="meta-label">Editorial</span>
                                    <span class="meta-value" id="detailEditorial"></span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">Género</span>
                                    <span class="meta-value" id="detailGenero"></span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">Disponibilidad</span>
                                    <span class="meta-value" id="detailStockMeta"></span>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div>
                                <div class="price-block">
                                    <span class="price-main" id="detailPrice"></span>
                                </div>
                                <div class="stock-badge in-stock" id="detailStockBadge">
                                    <div class="stock-dot"></div>
                                    <span id="detailStockLabel"></span>
                                </div>
                            </div>

                            <div class="action-row">
                                <div class="qty-control">
                                    <button type="button" class="qty-btn" id="detailQtyMinus" aria-label="Disminuir cantidad">−</button>
                                    <span class="qty-val" id="detailQty">1</span>
                                    <button type="button" class="qty-btn" id="detailQtyPlus" aria-label="Aumentar cantidad">+</button>
                                </div>
                                <button type="button" class="btn-cart" id="detailAddCart">Agregar al carrito</button>
                                <button type="button" class="btn-wishlist" id="detailWishlist" title="Agregar a favoritos" aria-label="Agregar a favoritos">
                                    <svg width="18" height="18" viewBox="0 0 14 14" fill="none"><path d="M7 2l1.4 2.8 3.1.45-2.25 2.2.53 3.1L7 9.1l-2.78 1.45.53-3.1L2.5 5.25l3.1-.45L7 2z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>
                                </button>
                            </div>

                            <div class="divider"></div>

                            <div class="description-section">
                                <p class="section-label">Sobre el libro</p>
                                <div class="description-text" id="detailDescription"></div>
                            </div>
                        </div>
                    </div>

                    <div class="related-section" id="detailRelatedSection" hidden>
                        <p class="section-label">También podría interesarte</p>
                        <div class="related-grid" id="detailRelatedGrid"></div>
                    </div>
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

            <footer class="footer-pages" id="footerPages"></footer>

        </main>
    </div>

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
        let   detailQty      = 1;
        let   detailQtyReady = false;
        let   qtySyncSeq     = 0;

        const catalogueView   = document.getElementById('catalogueView');
        const bookDetailView  = document.getElementById('bookDetailView');
        const cartView        = document.getElementById('cartView');
        const favView         = document.getElementById('favView');
        const carritoBtn      = document.getElementById('carritoBtn');
        const favoritosBtn    = document.getElementById('favoritosBtn');
        const favoriteIds     = new Set();
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

        function formatCoverTitle(title) {
            const words = String(title ?? '').trim().split(/\s+/);
            if (words.length <= 3) return escapeHtml(title);
            const mid = Math.ceil(words.length / 2);
            return escapeHtml(words.slice(0, mid).join(' ')) + '<br>' + escapeHtml(words.slice(mid).join(' '));
        }

        function formatPrice(value) {
            return '$' + Number(value).toLocaleString('es-AR');
        }

        function findBook(id) {
            return librosDB.find(l => l.id === Number(id));
        }

        function getRelatedBooks(book) {
            const sameGenre = book.genero
                ? librosDB.filter(l => l.id !== book.id && l.genero === book.genero)
                : [];
            const sameAuthor = librosDB.filter(l =>
                l.id !== book.id &&
                l.autor === book.autor &&
                !sameGenre.some(g => g.id === l.id)
            );
            return [...sameGenre, ...sameAuthor].slice(0, 6);
        }

        function buildDescription(book) {
            const descripcion = String(book.descripcion ?? '').trim();
            if (descripcion) {
                return descripcion
                    .split(/\n{2,}/)
                    .map(paragraph => `<p>${escapeHtml(paragraph).replace(/\n/g, '<br>')}</p>`)
                    .join('');
            }

            const parts = [];
            if (book.titulo && book.autor) {
                parts.push(`<p><em>${escapeHtml(book.titulo)}</em> es una obra de <strong>${escapeHtml(book.autor)}</strong>.</p>`);
            }
            if (book.editorial) {
                parts.push(`<p>Publicado por ${escapeHtml(book.editorial)}.</p>`);
            }
            if (book.genero) {
                parts.push(`<p>Género: ${escapeHtml(book.genero)}.</p>`);
            }
            if (!parts.length) {
                parts.push('<p>Información disponible en el catálogo de El Lugar.</p>');
            }
            return parts.join('');
        }

        function renderRelatedBooks(book) {
            const related = getRelatedBooks(book);
            const section = document.getElementById('detailRelatedSection');
            const grid    = document.getElementById('detailRelatedGrid');

            if (!related.length) {
                section.hidden = true;
                grid.innerHTML = '';
                return;
            }

            section.hidden = false;
            grid.innerHTML = related.map(item => `
                <div class="related-card" data-id="${item.id}" role="button" tabindex="0" aria-label="${escapeHtml(item.titulo)}">
                    <div class="related-cover" style="background:${coverColor(item.id)};"></div>
                    <p class="related-title">${escapeHtml(item.titulo)}</p>
                    <p class="related-price">${formatPrice(item.precio)}</p>
                </div>
            `).join('');

            grid.querySelectorAll('.related-card').forEach(card => {
                const relatedBook = findBook(card.dataset.id);
                if (relatedBook) {
                    PortadasOL.aplicarPortada(card.querySelector('.related-cover'), relatedBook.titulo, relatedBook.autor, relatedBook.uid);
                }
                const open = () => openBookDetail(card.dataset.id);
                card.addEventListener('click', open);
                card.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        open();
                    }
                });
            });
        }

        function maxDetailQty(book) {
            if (!book) return 99;
            const stock = Number(book.stock) || 0;
            if (stock < 1) return 1;
            return Math.min(stock, 99);
        }

        function setDetailQty(value, persist = false) {
            const book = currentBookId ? findBook(currentBookId) : null;
            detailQty = Math.max(1, Math.min(Number(value) || 1, maxDetailQty(book)));
            document.getElementById('detailQty').textContent = detailQty;
            if (persist) persistDetailQty();
        }

        async function persistDetailQty() {
            if (!currentBookId || !detailQtyReady) return false;

            const seq = ++qtySyncSeq;
            const idLibro = currentBookId;
            const cantidad = detailQty;
            const body = new FormData();
            body.append('id_libro', String(idLibro));
            body.append('cantidad', String(cantidad));

            try {
                const res = await fetch('php/scripts/actualizarCantidadCarrito.php', {
                    method: 'POST',
                    body,
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (seq !== qtySyncSeq || currentBookId !== idLibro) return false;
                if (data.state !== 'success') {
                    if (res.status !== 401) {
                        showToast(data.message || 'No se pudo actualizar la cantidad');
                    }
                    return false;
                }
                if (typeof data.cantidad === 'number') {
                    setDetailQty(data.cantidad, false);
                }
                if (cartView.classList.contains('active')) {
                    await loadCart();
                }
                return true;
            } catch (err) {
                if (seq !== qtySyncSeq) return false;
                showToast('Error al actualizar la cantidad');
                return false;
            }
        }

        async function syncDetailQtyFromCart(book) {
            try {
                const res = await fetch('php/scripts/obtenerCarrito.php', { credentials: 'same-origin' });
                const data = await res.json();
                if (data.state !== 'success' || currentBookId !== book.id) return;
                const item = (data.items || []).find(i => i.id_libro === book.id);
                if (item) setDetailQty(item.cantidad, false);
            } catch (err) {
                // Sin sesión o carrito vacío: se mantiene la cantidad local
            }
        }

        let currentBookId = null;

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

        function updateWishlistButton() {
            const btn = document.getElementById('detailWishlist');
            const on = !!(currentBookId && favoriteIds.has(currentBookId));
            btn.classList.toggle('active', on);
            btn.title = on ? 'Quitar de favoritos' : 'Agregar a favoritos';
            btn.setAttribute('aria-label', btn.title);
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
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
            const totalEl = document.getElementById('cartTotal');

            if (!items.length) {
                count.textContent = 'No tenés libros en el carrito';
                footer.hidden = false;
                totalEl.textContent = formatPrice(total);
                list.innerHTML = '<p class="cart-empty">Agregá libros desde el catálogo para verlos acá.</p>';
                return;
            }

            count.textContent = items.length + ' libro' + (items.length !== 1 ? 's' : '');
            footer.hidden = false;
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
            favoriteIds.clear();
            items.forEach(item => favoriteIds.add(Number(item.id_libro)));
            updateWishlistButton();

            if (!items.length) {
                count.textContent = 'No tenés libros en favoritos';
                list.innerHTML = '<p class="cart-empty">Marcá libros desde la ficha de producto para verlos acá.</p>';
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
                updateWishlistButton();
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

        async function loadFavorites() {
            const res = await fetch('php/scripts/obtenerFavoritos.php', { credentials: 'same-origin' });
            const data = await res.json();
            if (data.state !== 'success') {
                throw new Error(data.message || 'No se pudo cargar favoritos');
            }
            renderFavorites(data.items || []);
        }

        async function refreshFavoriteIds() {
            try {
                const res = await fetch('php/scripts/obtenerFavoritos.php', { credentials: 'same-origin' });
                const data = await res.json();
                if (data.state !== 'success') return;
                favoriteIds.clear();
                (data.items || []).forEach(i => favoriteIds.add(Number(i.id_libro)));
                updateWishlistButton();
            } catch (err) {}
        }

        function closeListView(view, btn, keepHidden = false) {
            view.classList.remove('active');
            view.setAttribute('aria-hidden', 'true');
            btn.classList.remove('active');
            const otherOpen = cartView.classList.contains('active') || favView.classList.contains('active');
            if (!otherOpen) productShell.classList.remove('cart-open');
            if (!keepHidden && !otherOpen && !bookDetailView.classList.contains('active')) {
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
            closeBookDetail(true);
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

        async function openBookDetail(id) {
            const book = findBook(id);
            if (!book) return;

            closeCartView(true);
            closeFavView(true);
            currentBookId = book.id;
            updateWishlistButton();
            detailQtyReady = false;
            qtySyncSeq += 1;
            setDetailQty(1, false);

            document.title = `${book.titulo} — El lugar`;
            document.getElementById('detailCover').style.backgroundColor = coverColor(book.id);
            PortadasOL.aplicarPortada(document.getElementById('detailCover'), book.titulo, book.autor, book.uid);
            document.getElementById('detailCoverTitle').innerHTML = formatCoverTitle(book.titulo);
            document.getElementById('detailCoverAuthor').textContent = book.autor || '—';
            document.getElementById('detailTitle').textContent = book.titulo || '—';
            document.getElementById('detailAuthor').textContent = book.autor || '—';
            document.getElementById('detailEditorial').textContent = book.editorial || '—';
            document.getElementById('detailGenero').textContent = book.genero || '—';

            document.getElementById('detailPrice').textContent = formatPrice(book.precio);


            const inStock = book.stock > 0;
            const stockBadge = document.getElementById('detailStockBadge');
            stockBadge.classList.toggle('in-stock', inStock);
            stockBadge.classList.toggle('out-of-stock', !inStock);
            document.getElementById('detailStockLabel').textContent = inStock
                ? `En stock (${book.stock} disponible${book.stock !== 1 ? 's' : ''})`
                : 'Agotado';
            document.getElementById('detailStockMeta').textContent = inStock
                ? `${book.stock} unidad${book.stock !== 1 ? 'es' : ''}`
                : 'Sin stock';
            document.getElementById('detailDescription').innerHTML = buildDescription(book);

            const addCartBtn = document.getElementById('detailAddCart');
            addCartBtn.disabled = !inStock;
            addCartBtn.textContent = inStock ? 'Agregar al carrito' : 'Sin stock';

            renderRelatedBooks(book);

            catalogueView.classList.add('hidden');
            bookDetailView.classList.add('active');
            bookDetailView.setAttribute('aria-hidden', 'false');
            productShell.classList.add('detail-open');
            mainWrapper.scrollTo({ top: 0, behavior: 'smooth' });
            window.scrollTo({ top: 0, behavior: 'smooth' });

            await Promise.all([syncDetailQtyFromCart(book), refreshFavoriteIds()]);
            if (currentBookId === book.id) {
                detailQtyReady = true;
            }
        }

        function closeBookDetail(keepHidden = false) {
            bookDetailView.classList.remove('active');
            bookDetailView.setAttribute('aria-hidden', 'true');
            productShell.classList.remove('detail-open');
            currentBookId = null;
            if (!keepHidden && !cartView.classList.contains('active') && !favView.classList.contains('active')) {
                catalogueView.classList.remove('hidden');
                document.title = 'Catálogo | El lugar';
            }
        }

        document.getElementById('backToCatalogue').addEventListener('click', () => closeBookDetail());
        document.getElementById('backFromCart').addEventListener('click', () => closeCartView());
        document.getElementById('backFromFav').addEventListener('click', () => closeFavView());
        document.getElementById('detailQtyMinus').addEventListener('click', () => setDetailQty(detailQty - 1, true));
        document.getElementById('detailQtyPlus').addEventListener('click', () => setDetailQty(detailQty + 1, true));
        document.getElementById('detailAddCart').addEventListener('click', async () => {
            if (!currentBookId) return;
            if (!(await requireLogin('Iniciá sesión para agregar libros al carrito'))) return;
            detailQtyReady = true;
            const ok = await persistDetailQty();
            if (ok) showToast('Libro agregado al carrito');
        });
        document.getElementById('detailWishlist').addEventListener('click', async () => {
            if (!currentBookId) return;
            if (!(await requireLogin('Iniciá sesión para usar favoritos'))) return;
            const id = currentBookId;
            const on = favoriteIds.has(id);
            try {
                if (on) {
                    await postLibro('php/scripts/quitarDeFavoritos.php', id);
                    favoriteIds.delete(id);
                    showToast('Libro quitado de favoritos');
                } else {
                    await postLibro('php/scripts/agregarAFavoritos.php', id);
                    favoriteIds.add(id);
                    showToast('Libro agregado a favoritos');
                }
                updateWishlistButton();
                if (favView.classList.contains('active')) await loadFavorites();
            } catch (err) {
                showToast(err.message || 'No se pudo actualizar favoritos');
            }
        });

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
            closeBookDetail();

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
                    openBookDetail(book.id);
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