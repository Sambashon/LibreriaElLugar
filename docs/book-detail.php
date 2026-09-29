<?php
require_once __DIR__ . "/php/clases/libreriaDb.php";

function bookDetailEscape(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function bookDetailCoverColor(int $id): string
{
    $colors = ['#2a6644', '#2a5c40', '#1e4a35', '#2f5e45', '#234d38', '#335c43', '#244d38', '#355f48'];

    return $colors[abs($id) % count($colors)];
}

function bookDetailCoverTitle(string $title): string
{
    $words = preg_split('/\s+/u', trim($title), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if (count($words) <= 3) {
        return bookDetailEscape($title);
    }

    $midpoint = (int) ceil(count($words) / 2);

    return bookDetailEscape(implode(' ', array_slice($words, 0, $midpoint)))
        . '<br>'
        . bookDetailEscape(implode(' ', array_slice($words, $midpoint)));
}

function bookDetailDescription(array $book): string
{
    $description = trim((string) ($book['descripcion'] ?? ''));
    if ($description !== '') {
        $paragraphs = preg_split('/\n{2,}/u', $description) ?: [];

        return implode('', array_map(
            static fn(string $paragraph): string => '<p>' . nl2br(bookDetailEscape($paragraph), false) . '</p>',
            $paragraphs
        ));
    }

    $parts = [];
    if (!empty($book['titulo']) && !empty($book['autor'])) {
        $parts[] = '<p><em>' . bookDetailEscape($book['titulo']) . '</em> es una obra de <strong>'
            . bookDetailEscape($book['autor']) . '</strong>.</p>';
    }
    if (!empty($book['editorial'])) {
        $parts[] = '<p>Publicado por ' . bookDetailEscape($book['editorial']) . '.</p>';
    }
    if (!empty($book['genero'])) {
        $parts[] = '<p>Género: ' . bookDetailEscape($book['genero']) . '.</p>';
    }
    if (!$parts) {
        $parts[] = '<p>Información disponible en el catálogo de El Lugar.</p>';
    }

    return implode('', $parts);
}

$db = new LibreriaDB();
$requestedId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$book = is_int($requestedId) && $requestedId > 0
    ? $db->fetch(
        "SELECT id_libro, uid, titulo, autor, editorial, genero, precio, stock, descripcion
         FROM libros
         WHERE id_libro = ?",
        [$requestedId]
    )
    : null;

if (!$book) {
    http_response_code(404);
}

$relatedBooks = [];
if ($book) {
    $book['id_libro'] = (int) $book['id_libro'];
    $book['precio'] = (float) $book['precio'];
    $book['stock'] = (int) $book['stock'];

    if (!empty($book['genero'])) {
        $relatedBooks = $db->fetchAll(
            "SELECT id_libro, uid, titulo, autor, precio
             FROM libros
             WHERE id_libro <> ? AND genero = ?
             ORDER BY titulo ASC
             LIMIT 6",
            [$book['id_libro'], $book['genero']]
        );
    }

    if (count($relatedBooks) < 6) {
        $excludedIds = array_merge(
            [$book['id_libro']],
            array_map(static fn(array $related): int => (int) $related['id_libro'], $relatedBooks)
        );
        $placeholders = implode(', ', array_fill(0, count($excludedIds), '?'));
        $relatedBooks = array_merge(
            $relatedBooks,
            $db->fetchAll(
                "SELECT id_libro, uid, titulo, autor, precio
                 FROM libros
                 WHERE id_libro NOT IN ($placeholders) AND autor <=> ?
                 ORDER BY titulo ASC
                 LIMIT " . (6 - count($relatedBooks)),
                [...$excludedIds, $book['autor']]
            )
        );
    }

    foreach ($relatedBooks as &$relatedBook) {
        $relatedBook['id_libro'] = (int) $relatedBook['id_libro'];
        $relatedBook['precio'] = (float) $relatedBook['precio'];
    }
    unset($relatedBook);
}

$bookJson = $book
    ? json_encode(
        $book,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    )
    : 'null';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $book ? bookDetailEscape($book['titulo']) . ' — El lugar' : 'Libro no encontrado | El lugar' ?></title>
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
    <div class="mobile-topbar">
        <button class="mobile-hamburger" id="mobileMenuBtn" aria-label="Abrir menú">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
        <div class="mobile-logo-wrap">
            <a href="index.html"><img src="Resources/logos/libreriaElLugar.png" alt="El Lugar"></a>
        </div>
    </div>

    <div class="mobile-overlay" id="mobileOverlay"></div>

    <div class="custom-row">
        <nav class="nav-menu" aria-label="Navegación principal">
            <button class="nav-drawer-close" id="navDrawerClose" aria-label="Cerrar menú">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
            <header class="nav-logo">
                <div class="logo-container">
                    <a href="index.html"><img src="Resources/logos/libreriaElLugar.png" alt="El Lugar"></a>
                </div>
                <hr style="margin: 10px 0; width: 100%;">
            </header>
            <a class="menu-item" href="index.html" style="color: inherit; text-decoration: none;">
                <svg class="icon" viewBox="0 0 14 14" fill="none" aria-hidden="true"><rect x="1" y="1" width="5" height="5" rx="1" fill="currentColor"/><rect x="8" y="1" width="5" height="5" rx="1" fill="currentColor"/><rect x="1" y="8" width="5" height="5" rx="1" fill="currentColor"/><rect x="8" y="8" width="5" height="5" rx="1" fill="currentColor"/></svg>
                <p>Inicio</p>
            </a>
            <a class="menu-item active" href="catalogue.php" style="color: inherit; text-decoration: none;">
                <svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M4 5.5v16M8 7h8M8 10h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <p>Catálogo</p>
            </a>
            <a class="menu-item" href="catalogue.php?view=cart" style="color: inherit; text-decoration: none;">
                <svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.29977 5H21L19 12H7.37671M20 16H8L6 3H3M9 20C9 20.5523 8.55228 21 8 21C7.44772 21 7 20.5523 7 20C7 19.4477 7.44772 19 8 19C8.55228 19 9 19.4477 9 20ZM20 20C20 20.5523 19.5523 21 19 21C18.4477 21 18 20.5523 18 20C18 19.4477 18.4477 19 19 19C19.5523 19 20 19.4477 20 20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <p>Carrito</p>
            </a>
            <a class="menu-item" href="catalogue.php?view=favorites" style="color: inherit; text-decoration: none;">
                <svg class="icon" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M7 2l1.4 2.8 3.1.45-2.25 2.2.53 3.1L7 9.1l-2.78 1.45.53-3.1L2.5 5.25l3.1-.45L7 2z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>
                <p>Favoritos</p>
            </a>
        </nav>

        <main class="product-shell detail-open">
            <div class="main-wrapper">
                <?php if ($book): ?>
                    <div class="book-detail-view active" id="bookDetailView" aria-hidden="false">
                        <nav class="breadcrumb">
                            <button type="button" id="backToCatalogue">Volver al catálogo</button>
                        </nav>

                        <div class="product-layout">
                            <div class="cover-col">
                                <div class="book-cover" id="detailCover" style="background-color: <?= bookDetailCoverColor($book['id_libro']) ?>;">
                                    <div class="cover-deco">
                                        <svg class="cover-ornament" viewBox="0 0 60 12" fill="none" aria-hidden="true">
                                            <line x1="0" y1="6" x2="22" y2="6" stroke="#F0C05F" stroke-width="0.8"/>
                                            <circle cx="30" cy="6" r="4" stroke="#F0C05F" stroke-width="0.8"/>
                                            <line x1="38" y1="6" x2="60" y2="6" stroke="#F0C05F" stroke-width="0.8"/>
                                        </svg>
                                        <p class="cover-title-text" id="detailCoverTitle"><?= bookDetailCoverTitle((string) $book['titulo']) ?></p>
                                        <div class="cover-line"></div>
                                        <p class="cover-author-text" id="detailCoverAuthor"><?= bookDetailEscape($book['autor'] ?: '—') ?></p>
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
                                    <h2 class="book-title" id="detailTitle"><?= bookDetailEscape($book['titulo'] ?: '—') ?></h2>
                                    <p class="book-author">por <span id="detailAuthor"><?= bookDetailEscape($book['autor'] ?: '—') ?></span></p>
                                </div>

                                <div class="divider"></div>

                                <div class="meta-grid">
                                    <div class="meta-item">
                                        <span class="meta-label">Editorial</span>
                                        <span class="meta-value" id="detailEditorial"><?= bookDetailEscape($book['editorial'] ?: '—') ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Género</span>
                                        <span class="meta-value" id="detailGenero"><?= bookDetailEscape($book['genero'] ?: '—') ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Disponibilidad</span>
                                        <span class="meta-value" id="detailStockMeta"><?= $book['stock'] > 0 ? $book['stock'] . ' unidad' . ($book['stock'] !== 1 ? 'es' : '') : 'Sin stock' ?></span>
                                    </div>
                                </div>

                                <div class="divider"></div>

                                <div>
                                    <div class="price-block">
                                        <span class="price-main" id="detailPrice">$<?= number_format($book['precio'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="stock-badge <?= $book['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>" id="detailStockBadge">
                                        <div class="stock-dot"></div>
                                        <span id="detailStockLabel"><?= $book['stock'] > 0 ? 'En stock (' . $book['stock'] . ' disponible' . ($book['stock'] !== 1 ? 's' : '') . ')' : 'Agotado' ?></span>
                                    </div>
                                </div>

                                <div class="action-row">
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn" id="detailQtyMinus" aria-label="Disminuir cantidad">−</button>
                                        <span class="qty-val" id="detailQty">1</span>
                                        <button type="button" class="qty-btn" id="detailQtyPlus" aria-label="Aumentar cantidad">+</button>
                                    </div>
                                    <button type="button" class="btn-cart" id="detailAddCart" <?= $book['stock'] < 1 ? 'disabled' : '' ?>><?= $book['stock'] > 0 ? 'Agregar al carrito' : 'Sin stock' ?></button>
                                    <button type="button" class="btn-wishlist" id="detailWishlist" title="Agregar a favoritos" aria-label="Agregar a favoritos" aria-pressed="false">
                                        <svg width="18" height="18" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M7 2l1.4 2.8 3.1.45-2.25 2.2.53 3.1L7 9.1l-2.78 1.45.53-3.1L2.5 5.25l3.1-.45L7 2z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>

                                <div class="divider"></div>

                                <div class="description-section">
                                    <p class="section-label">Sobre el libro</p>
                                    <div class="description-text" id="detailDescription"><?= bookDetailDescription($book) ?></div>
                                </div>
                            </div>
                        </div>

                        <?php if ($relatedBooks): ?>
                            <div class="related-section" id="detailRelatedSection">
                                <p class="section-label">También podría interesarte</p>
                                <div class="related-grid" id="detailRelatedGrid">
                                    <?php foreach ($relatedBooks as $relatedBook): ?>
                                        <a class="related-card" href="book-detail.php?id=<?= $relatedBook['id_libro'] ?>" aria-label="<?= bookDetailEscape($relatedBook['titulo']) ?>" style="color: inherit; text-decoration: none;">
                                            <div class="related-cover" style="background: <?= bookDetailCoverColor($relatedBook['id_libro']) ?>;" data-title="<?= bookDetailEscape($relatedBook['titulo']) ?>" data-author="<?= bookDetailEscape($relatedBook['autor']) ?>" data-uid="<?= bookDetailEscape($relatedBook['uid']) ?>"></div>
                                            <p class="related-title"><?= bookDetailEscape($relatedBook['titulo']) ?></p>
                                            <p class="related-price">$<?= number_format($relatedBook['precio'], 0, ',', '.') ?></p>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="book-detail-view active">
                        <nav class="breadcrumb"><a id="backToCatalogue" href="catalogue.php">Volver al catálogo</a></nav>
                        <h1>Libro no encontrado</h1>
                        <p>El libro solicitado no está disponible.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div class="catalogue-toast" id="catalogueToast" role="status" aria-live="polite"></div>
    <?php if ($book): ?>
        <script>window.BOOK_DETAIL = <?= $bookJson ?>;</script>
        <script src="src/js/portadas.js?v=2"></script>
        <script src="src/js/book-detail.js"></script>
    <?php endif; ?>
</body>
</html>
