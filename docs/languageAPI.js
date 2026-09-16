const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');
const resultsEl = document.getElementById('results');
const statusEl = document.getElementById('status');
const libroInfoEl = document.getElementById('libroInfo');

// Flujo:
//   1) Buscamos la OBRA que mejor matchea el título (search.json)
//   2) Pedimos TODAS sus ediciones (/works/{id}/editions.json)
//   3) Mostramos cada portada que aparezca, sea del idioma que sea,
//      junto con el idioma de esa edición y la URL exacta de donde sale
//      (para poder comparar / debuggear a ojo)
async function buscarLibro(query) {
  resultsEl.innerHTML = '';
  libroInfoEl.innerHTML = '';
  statusEl.textContent = 'Buscando el libro...';

  try {
    const searchUrl = `https://openlibrary.org/search.json?q=${encodeURIComponent(query)}&fields=key,title,author_name&limit=1`;
    const searchRes = await fetchConReintento(searchUrl);
    if (!searchRes.ok) throw new Error(`Error HTTP ${searchRes.status} buscando el libro`);

    const searchData = await searchRes.json();
    const obra = (searchData.docs || [])[0];

    if (!obra) {
      statusEl.textContent = 'No se encontró ningún libro con ese título.';
      return;
    }

    mostrarInfoLibro(obra, searchUrl);
    statusEl.textContent = 'Buscando ediciones y portadas...';

    const edicionesUrl = `https://openlibrary.org${obra.key}/editions.json?limit=50`;
    const edicionesRes = await fetchConReintento(edicionesUrl);
    if (!edicionesRes.ok) throw new Error(`Error HTTP ${edicionesRes.status} buscando ediciones`);

    const edicionesData = await edicionesRes.json();
    const ediciones = edicionesData.entries || [];

    // Armamos una tarjeta por cada portada encontrada (una edición puede
    // tener más de una portada subida, o ninguna)
    const portadas = [];
    ediciones.forEach(ed => {
      const covers = Array.isArray(ed.covers) ? ed.covers.filter(id => id > 0) : [];
      const idiomas = Array.isArray(ed.languages)
        ? ed.languages.map(l => l.key.replace('/languages/', ''))
        : [];

      // Solo nos interesan las ediciones marcadas como español (spa)
      if (!idiomas.includes('spa')) return;

      covers.forEach(coverId => {
        portadas.push({
          coverId,
          idiomas,
          tituloEdicion: ed.title || obra.title,
          publishYear: ed.publish_date || '',
          editionKey: ed.key
        });
      });
    });

    if (portadas.length === 0) {
      statusEl.textContent = 'Este libro no tiene ninguna portada en español cargada en Open Library.';
      return;
    }

    statusEl.textContent = `${portadas.length} portada(s) en español encontrada(s) (de ${ediciones.length} edición(es) en total)`;
    renderizarPortadas(portadas, edicionesUrl);

  } catch (err) {
    statusEl.textContent = 'Ocurrió un error al buscar el libro.';
    console.error(err);
  }
}

function mostrarInfoLibro(obra, searchUrl) {
  const autores = obra.author_name ? obra.author_name.join(', ') : 'Autor desconocido';
  libroInfoEl.innerHTML = `
    <h2>${escapeHtml(obra.title)}</h2>
    <p>${escapeHtml(autores)}</p>
    <p><a href="${searchUrl}" target="_blank" rel="noopener">ver respuesta de la Search API (JSON)</a></p>
  `;
}

async function fetchConReintento(url, intento = 1) {
  const res = await fetch(url);
  if (res.status === 429 && intento <= 3) {
    await esperar(800 * intento);
    return fetchConReintento(url, intento + 1);
  }
  return res;
}

function esperar(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function renderizarPortadas(portadas, edicionesUrl) {
  resultsEl.innerHTML = '';

  portadas.forEach(p => {
    const urlImg = `https://covers.openlibrary.org/b/id/${p.coverId}-M.jpg`;
    const idiomasTexto = p.idiomas.length > 0 ? p.idiomas.join(', ') : 'sin idioma registrado';
    const urlEdicion = `https://openlibrary.org${p.editionKey}.json`;

    const card = document.createElement('div');
    card.className = 'card';

    card.innerHTML = `
      <img src="${urlImg}" alt="Portada de ${escapeHtml(p.tituloEdicion)}" loading="lazy"
           onerror="this.src='${placeholderSVG()}'">
      <div class="info">
        <span class="idioma">${escapeHtml(idiomasTexto)}</span>
        <span class="edicion-titulo">${escapeHtml(p.tituloEdicion)}</span>
        <span>${escapeHtml(p.publishYear)}</span>
        <span class="fuente">
          cover id: ${p.coverId}<br>
          <a href="${urlImg}" target="_blank" rel="noopener">imagen</a> ·
          <a href="${urlEdicion}" target="_blank" rel="noopener">edición (JSON)</a>
        </span>
      </div>
    `;

    resultsEl.appendChild(card);
  });
}

function placeholderSVG() {
  return 'data:image/svg+xml;utf8,' + encodeURIComponent(
    `<svg xmlns="http://www.w3.org/2000/svg" width="150" height="220">
       <rect width="100%" height="100%" fill="#ddd"/>
       <text x="50%" y="50%" font-size="14" text-anchor="middle" fill="#888">Sin portada</text>
     </svg>`
  );
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

searchBtn.addEventListener('click', () => {
  const q = searchInput.value.trim();
  if (q) buscarLibro(q);
});

searchInput.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') searchBtn.click();
});

buscarLibro(searchInput.value.trim());