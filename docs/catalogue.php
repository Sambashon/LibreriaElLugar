<?php
require_once __DIR__ . "/php/clases/libreriaDb.php";

$db     = new LibreriaDB();
$libros = $db->fetchAll(
    "SELECT id_libro, titulo, autor, editorial, genero, precio, stock
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="src/css/components.css">
    <link rel="stylesheet" href="src/css/header.css">
    <link rel="stylesheet" href="src/css/catalogue.css">
</head>
<body>
    <div class="custom-row">
        <nav class="nav-menu">

            <header class="nav-logo">
                <div class="logo-container">
                    <img src="Resources/logos/libreriaElLugar.png">
                </div>
                <hr style="margin: 10px 0px; width: 100%;">
            </header>

            <div class="menu-item" id="inicioBtn">
                <svg class="icon" viewBox="0 0 14 14" fill="none"><rect x="1" y="1" width="5" height="5" rx="1" fill="currentColor"/><rect x="8" y="1" width="5" height="5" rx="1" fill="currentColor"/><rect x="1" y="8" width="5" height="5" rx="1" fill="currentColor"/><rect x="8" y="8" width="5" height="5" rx="1" fill="currentColor"/></svg>
                <p>Inicio</p>
            </div>
            <div class="menu-item uC">
                <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.29977 5H21L19 12H7.37671M20 16H8L6 3H3M9 20C9 20.5523 8.55228 21 8 21C7.44772 21 7 20.5523 7 20C7 19.4477 7.44772 19 8 19C8.55228 19 9 19.4477 9 20ZM20 20C20 20.5523 19.5523 21 19 21C18.4477 21 18 20.5523 18 20C18 19.4477 18.4477 19 19 19C19.5523 19 20 19.4477 20 20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <p>Carrito</p>
            </div>
            <div class="menu-item uC">
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
                <div class="product-body"></div>
                <aside class="bookMenu"></aside>
            </div>

            <footer class="footer-pages" id="footerPages"></footer>

        </main>
    </div>

    <script>
        // ── DATOS DESDE PHP ──────────────────────────────────────────
        const librosDB = <?= json_encode(array_map(fn($l) => [
            'id'        => (int)  $l['id_libro'],
            'titulo'    =>        $l['titulo'],
            'autor'     =>        $l['autor'],
            'editorial' =>        $l['editorial'] ?? '',
            'genero'    =>        $l['genero']    ?? '',
            'precio'    => (float)$l['precio'],
            'stock'     => (int)  $l['stock'],
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
            PER_PAGE = calcPerPage();
            currentPage = 1;
            renderPage();
        });
        let   activeGenres   = new Set();
        let   filteredLibros = [...librosDB];

        // ── PRECIO — sin inicialización necesaria

        // ── FILTROS ─────────────────────────────────────────────────
        function applyFilters() {
            const sort       = document.getElementById('sortSelect').value;
            const stockOn    = document.getElementById('filterStock').checked;
            const agotadoOn  = document.getElementById('filterAgotado').checked;
            const minVal     = parseFloat(document.getElementById('priceMin').value) || 0;
            const maxVal     = parseFloat(document.getElementById('priceMax').value) || Infinity;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();

            let list = librosDB.filter(l => {
                if (activeGenres.size && !activeGenres.has(l.genero)) return false;
                if (l.precio < minVal)                                 return false;
                if (l.precio > maxVal)                                 return false;
                if (l.stock > 0  && !stockOn)                         return false;
                if (l.stock === 0 && !agotadoOn)                      return false;
                if (searchTerm && !l.titulo.toLowerCase().includes(searchTerm) && 
                                  !l.autor.toLowerCase().includes(searchTerm)) return false;
                return true;
            });

            if (sort === 'alpha')      list.sort((a,b) => a.titulo.localeCompare(b.titulo));
            if (sort === 'price-asc')  list.sort((a,b) => a.precio - b.precio);
            if (sort === 'price-desc') list.sort((a,b) => b.precio - a.precio);

            filteredLibros = list;
            currentPage    = 1;

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
            renderGenres(genres.filter(g => g.toLowerCase().includes(q.toLowerCase())));
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
                card.innerHTML = `
                    <div class="card-cover"></div>
                    <div class="card-info">
                        <div>
                            <p class="card-title">${book.titulo}</p>
                            <p class="card-author">${book.autor}</p>
                        </div>
                        <p class="card-price">$${book.precio.toLocaleString('es-AR')}</p>
                    </div>
                    <div class="card-btn">Agregar al carrito</div>
                `;
                body.appendChild(card);
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
        applyFilters();
        
        // Apply initial search if provided
        if ('<?= $initialSearch ?>') {
            document.getElementById('searchInput').value = '<?= $initialSearch ?>';
            applyFilters();
        }
    </script>

</body>
</html>