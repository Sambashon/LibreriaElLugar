(function (global) {
    const CACHE_KEY = 'ol-spa-covers-v1';
    const memory = new Map();
    const inflight = new Map();
    let stored = {};

    try {
        stored = JSON.parse(sessionStorage.getItem(CACHE_KEY) || '{}');
    } catch (err) {
        stored = {};
    }

    function persist(key, value) {
        stored[key] = value;
        try {
            sessionStorage.setItem(CACHE_KEY, JSON.stringify(stored));
        } catch (err) {
            // quota / private mode
        }
    }

    function cacheKey(titulo, autor) {
        return String(titulo || '').trim().toLowerCase() + '|' + String(autor || '').trim().toLowerCase();
    }

    function esperar(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async function fetchConReintento(url, intento = 1) {
        const res = await fetch(url);
        if (res.status === 429 && intento <= 3) {
            await esperar(800 * intento);
            return fetchConReintento(url, intento + 1);
        }
        return res;
    }

    const MAX_PARALELO = 4;
    let activos = 0;
    const esperaCola = [];

    function encolar(fn) {
        return new Promise((resolve, reject) => {
            esperaCola.push({ fn, resolve, reject });
            drenarCola();
        });
    }

    function drenarCola() {
        while (activos < MAX_PARALELO && esperaCola.length) {
            const { fn, resolve, reject } = esperaCola.shift();
            activos += 1;
            Promise.resolve()
                .then(fn)
                .then(resolve, reject)
                .finally(() => {
                    activos -= 1;
                    drenarCola();
                });
        }
    }

    function autorCoincide(obra, autor) {
        if (!autor) return true;
        const buscado = autor.toLowerCase();
        return (obra.author_name || []).some(nombre => {
            const actual = nombre.toLowerCase();
            return actual.includes(buscado) || buscado.includes(actual);
        });
    }

    async function buscarObra(titulo, autor) {
        const query = [titulo, autor].filter(Boolean).join(' ');
        const searchUrl = `https://openlibrary.org/search.json?q=${encodeURIComponent(query)}&fields=key,title,author_name&limit=5`;
        const searchRes = await fetchConReintento(searchUrl);
        if (!searchRes.ok) throw new Error(`Error HTTP ${searchRes.status} buscando el libro`);

        const searchData = await searchRes.json();
        const docs = searchData.docs || [];
        return docs.find(doc => autorCoincide(doc, autor)) || docs[0] || null;
    }

    async function primeraPortadaSpa(obra) {
        const edicionesUrl = `https://openlibrary.org${obra.key}/editions.json?limit=50`;
        const edicionesRes = await fetchConReintento(edicionesUrl);
        if (!edicionesRes.ok) throw new Error(`Error HTTP ${edicionesRes.status} buscando ediciones`);

        const edicionesData = await edicionesRes.json();
        const ediciones = edicionesData.entries || [];

        for (const edicion of ediciones) {
            const idiomas = Array.isArray(edicion.languages)
                ? edicion.languages.map(l => l.key.replace('/languages/', ''))
                : [];
            if (!idiomas.includes('spa')) continue;

            const covers = Array.isArray(edicion.covers)
                ? edicion.covers.filter(id => id > 0)
                : [];
            if (!covers.length) continue;

            return `https://covers.openlibrary.org/b/id/${covers[0]}-M.jpg?default=false`;
        }

        return null;
    }

    async function obtenerPortada(titulo, autor) {
        const key = cacheKey(titulo, autor);
        if (Object.prototype.hasOwnProperty.call(stored, key)) return stored[key];
        if (memory.has(key)) return memory.get(key);
        if (inflight.has(key)) return inflight.get(key);

        const job = encolar(async () => {
            try {
                const obra = await buscarObra(titulo, autor);
                if (!obra) {
                    persist(key, null);
                    return null;
                }
                const url = await primeraPortadaSpa(obra);
                persist(key, url);
                return url;
            } catch (err) {
                console.error(err);
                return null;
            }
        }).then(url => {
            memory.set(key, url);
            inflight.delete(key);
            return url;
        });

        inflight.set(key, job);
        return job;
    }

    function cargarImagen(el, titulo, url, deco, token, onError) {
        const img = document.createElement('img');
        img.className = 'book-cover-img';
        img.alt = titulo || '';
        img.loading = 'lazy';
        img.onload = () => {
            if (token !== el._coverToken) {
                img.remove();
                return;
            }
            el.classList.add('has-cover');
            if (deco) deco.hidden = true;
        };
        img.onerror = () => {
            img.remove();
            if (token !== el._coverToken) return;
            el.classList.remove('has-cover');
            if (deco) deco.hidden = false;
            onError();
        };
        img.src = url;
        el.appendChild(img);
    }

    function aplicarPortada(el, titulo, autor, uid = '') {
        if (!el) return;

        el.querySelectorAll(':scope > img.book-cover-img').forEach(img => img.remove());
        el.classList.remove('has-cover');
        const deco = el.querySelector('.cover-deco');
        if (deco) deco.hidden = false;

        const token = (el._coverToken = (el._coverToken || 0) + 1);

        if (uid) {
            cargarImagen(
                el,
                titulo,
                `php/scripts/portadaLibro.php?uid=${encodeURIComponent(uid)}`,
                deco,
                token,
                () => cargarPortadaOpenLibrary(el, titulo, autor, deco, token)
            );
            return;
        }

        cargarPortadaOpenLibrary(el, titulo, autor, deco, token);
    }

    function cargarPortadaOpenLibrary(el, titulo, autor, deco, token) {
        obtenerPortada(titulo, autor).then(url => {
            if (token !== el._coverToken || !url || !el.isConnected) return;
            cargarImagen(el, titulo, url, deco, token, () => {});
        });
    }

    global.PortadasOL = { obtenerPortada, aplicarPortada };
})(window);
