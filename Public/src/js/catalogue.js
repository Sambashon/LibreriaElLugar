const inicioBtn = document.getElementById("inicioBtn");
inicioBtn.addEventListener("click", () => {
    window.location.href = "index.html";
});

const underConstructions =  document.querySelectorAll(".uC");

underConstructions.forEach(element => {
    element.addEventListener("click", function(){
        alert("Función en construcción");
    })
});


/* Nav active */
document.querySelectorAll(".nav-item").forEach(item => {
    item.addEventListener("click", () => {
        document.querySelectorAll(".nav-item").forEach(i => i.classList.remove("active"));
        item.classList.add("active");
    });
});

/* Acordeón: solo uno abierto a la vez */
function toggleFilter(header) {
    const arrow  = header.querySelector(".arrow-container");
    const body   = header.nextElementSibling;
    const isOpen = body.classList.contains("open");

    document.querySelectorAll(".filter-body").forEach(b => b.classList.remove("open"));
    document.querySelectorAll(".arrow-container").forEach(a => a.classList.remove("active"));

    if (!isOpen) {
        body.classList.add("open");
        arrow.classList.add("active");
    }
}

/* Lista de géneros */
const genres = [
    "Ficción literaria", "Ficción histórica", "Ciencia ficción",
    "Fantasía", "Terror", "Suspenso / Thriller", "Romance",
    "Aventura", "Policial / Noir", "Distopía", "Realismo mágico",
    "Cuento", "Poesía", "Teatro / Drama",
    "Historia", "Biografía", "Autobiografía", "Ensayo",
    "Filosofía", "Psicología", "Ciencia", "Economía",
    "Política", "Sociología", "Antropología", "Arte",
    "Arquitectura", "Cocina", "Viajes", "Humor",
    "Infantil", "Juvenil", "Manga / Cómic", "Autoayuda",
    "Espiritualidad", "Derecho", "Educación", "Tecnología",
];

function renderGenres(list) {
    document.getElementById("genreList").innerHTML = list.map(g =>
        `<label class="genre-item"><input type="checkbox"> ${g}</label>`
    ).join("");
}

function filterGenres(q) {
    renderGenres(genres.filter(g => g.toLowerCase().includes(q.toLowerCase())));
}

renderGenres(genres);