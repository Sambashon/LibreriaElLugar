const book = window.BOOK_DETAIL;
const favoriteIds = new Set();
const favoriteButton = document.getElementById('detailWishlist');
const quantityValue = document.getElementById('detailQty');
const toast = document.getElementById('catalogueToast');

let detailQuantity = Number(quantityValue.textContent) || 1;
let quantityReady = false;
let quantitySyncSequence = 0;
let toastTimer = null;

function formatPrice(value) {
    return '$' + Number(value).toLocaleString('es-AR');
}

function showToast(message) {
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
}

function updateWishlistButton() {
    const isFavorite = favoriteIds.has(Number(book.id_libro));
    favoriteButton.classList.toggle('active', isFavorite);
    favoriteButton.title = isFavorite ? 'Quitar de favoritos' : 'Agregar a favoritos';
    favoriteButton.setAttribute('aria-label', favoriteButton.title);
    favoriteButton.setAttribute('aria-pressed', isFavorite ? 'true' : 'false');
}

async function getSession() {
    const response = await fetch('php/scripts/sesion.php', { credentials: 'same-origin' });
    return response.json();
}

async function requireLogin(message) {
    const session = await getSession();
    if (session && session.logged) return true;
    showToast(message);
    return false;
}

async function postLibro(url, idLibro, extra = {}) {
    const body = new FormData();
    body.append('id_libro', String(idLibro));
    Object.entries(extra).forEach(([key, value]) => body.append(key, String(value)));

    const response = await fetch(url, {
        method: 'POST',
        body,
        credentials: 'same-origin'
    });
    const data = await response.json();
    if (data.state !== 'success') {
        const error = new Error(data.message || 'No se pudo completar la acción');
        error.status = response.status;
        throw error;
    }

    return data;
}

function maxDetailQuantity() {
    const stock = Number(book.stock) || 0;
    return stock < 1 ? 1 : Math.min(stock, 99);
}

function setDetailQuantity(value, persist = false) {
    detailQuantity = Math.max(1, Math.min(Number(value) || 1, maxDetailQuantity()));
    quantityValue.textContent = String(detailQuantity);
    if (persist && quantityReady) persistDetailQuantity();
}

async function persistDetailQuantity() {
    const sequence = ++quantitySyncSequence;
    try {
        const data = await postLibro('php/scripts/actualizarCantidadCarrito.php', book.id_libro, {
            cantidad: detailQuantity
        });
        if (sequence !== quantitySyncSequence) return false;
        if (typeof data.cantidad === 'number') {
            setDetailQuantity(data.cantidad);
        }
        return true;
    } catch (error) {
        if (sequence !== quantitySyncSequence) return false;
        if (error?.status !== 401 && error?.message) showToast(error.message);
        return false;
    }
}

async function syncDetailQuantityFromCart() {
    const response = await fetch('php/scripts/obtenerCarrito.php', { credentials: 'same-origin' });
    const data = await response.json();
    if (response.status === 401) return;
    if (data.state !== 'success') {
        throw new Error(data.message || 'No se pudo cargar el carrito');
    }

    const item = (data.items || []).find(item => Number(item.id_libro) === Number(book.id_libro));
    if (item) setDetailQuantity(item.cantidad);
}

async function syncFavoriteState() {
    const response = await fetch('php/scripts/obtenerFavoritos.php', { credentials: 'same-origin' });
    const data = await response.json();
    if (response.status === 401) return;
    if (data.state !== 'success') {
        throw new Error(data.message || 'No se pudieron cargar los favoritos');
    }

    favoriteIds.clear();
    (data.items || []).forEach(item => favoriteIds.add(Number(item.id_libro)));
    updateWishlistButton();
}

function navigateBack() {
    if (document.referrer) {
        const referrer = new URL(document.referrer);
        const fromDetailOrCatalogue = referrer.origin === window.location.origin
            && /\/(?:catalogue|book-detail)\.php$/.test(referrer.pathname);
        if (fromDetailOrCatalogue) {
            window.history.back();
            return;
        }
    }
    window.location.href = 'catalogue.php';
}

document.getElementById('backToCatalogue').addEventListener('click', navigateBack);
document.getElementById('detailQtyMinus').addEventListener('click', () => {
    setDetailQuantity(detailQuantity - 1, true);
});
document.getElementById('detailQtyPlus').addEventListener('click', () => {
    setDetailQuantity(detailQuantity + 1, true);
});

document.getElementById('detailAddCart').addEventListener('click', async () => {
    try {
        if (!(await requireLogin('Iniciá sesión para agregar libros al carrito'))) return;
        quantityReady = true;
        if (await persistDetailQuantity()) showToast('Libro agregado al carrito');
    } catch (error) {
        showToast(error.message || 'No se pudo agregar el libro al carrito');
    }
});

favoriteButton.addEventListener('click', async () => {
    try {
        if (!(await requireLogin('Iniciá sesión para usar favoritos'))) return;

        const isFavorite = favoriteIds.has(Number(book.id_libro));
        if (isFavorite) {
            await postLibro('php/scripts/quitarDeFavoritos.php', book.id_libro);
            favoriteIds.delete(Number(book.id_libro));
            showToast('Libro quitado de favoritos');
        } else {
            await postLibro('php/scripts/agregarAFavoritos.php', book.id_libro);
            favoriteIds.add(Number(book.id_libro));
            showToast('Libro agregado a favoritos');
        }
        updateWishlistButton();
    } catch (error) {
        showToast(error.message || 'No se pudo actualizar favoritos');
    }
});

document.querySelectorAll('.related-cover').forEach(cover => {
    PortadasOL.aplicarPortada(
        cover,
        cover.dataset.title,
        cover.dataset.author,
        cover.dataset.uid
    );
});
PortadasOL.aplicarPortada(
    document.getElementById('detailCover'),
    book.titulo,
    book.autor,
    book.uid
);

const mobileMenuButton = document.getElementById('mobileMenuBtn');
const drawerCloseButton = document.getElementById('navDrawerClose');
const mobileOverlay = document.getElementById('mobileOverlay');
const navMenu = document.querySelector('.nav-menu');

function closeDrawer() {
    navMenu.classList.remove('open');
    mobileOverlay.classList.remove('visible');
    document.body.classList.remove('drawer-open');
}

mobileMenuButton.addEventListener('click', () => {
    navMenu.classList.add('open');
    mobileOverlay.classList.add('visible');
    document.body.classList.add('drawer-open');
});
drawerCloseButton.addEventListener('click', closeDrawer);
mobileOverlay.addEventListener('click', closeDrawer);

Promise.all([
    syncDetailQuantityFromCart()
        .catch(error => showToast(error.message || 'No se pudo cargar el carrito'))
        .finally(() => { quantityReady = true; }),
    syncFavoriteState().catch(error => showToast(error.message || 'No se pudieron cargar los favoritos'))
]);
