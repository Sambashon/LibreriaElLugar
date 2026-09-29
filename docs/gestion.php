<?php
require_once __DIR__ . "/php/bootstrap.php";

use App\Helpers\AdminAccess;

AdminAccess::requireAdminPage();

$db = new LibreriaDB();
$libros = $db->fetchAll("SELECT * FROM libros ORDER BY titulo ASC");
$editoriales = array_column(
    $db->fetchAll(
        "SELECT DISTINCT editorial FROM libros
         WHERE editorial IS NOT NULL AND TRIM(editorial) != ''
         ORDER BY editorial ASC"
    ),
    'editorial'
);
$autores = array_column(
    $db->fetchAll(
        "SELECT DISTINCT autor FROM libros
         WHERE autor IS NOT NULL AND TRIM(autor) != ''
         ORDER BY autor ASC"
    ),
    'autor'
);
$generos = array_column(
    $db->fetchAll(
        "SELECT DISTINCT genero FROM libros
         WHERE genero IS NOT NULL AND TRIM(genero) != ''
         ORDER BY genero ASC"
    ),
    'genero'
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Libros</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="src/css/components.css">
    <link rel="stylesheet" href="src/css/gestion.css">
</head>
<body>

<header>
    <div class="header-container">
        <h1>Catálogo de <em>Libros</em></h1>
        <span class="header-count" id="headerCount"><?= count($libros) ?> registros</span>
    </div>
    <div class="header-container">
        <button class="button" onclick="location.href='index.html'">Volver a inicio</button>
        <input type="file" id="importFile" accept=".xlsx,.xls,.csv" hidden>
        <button class="button" id="importExcelBtn" type="button">Importar Excel</button>
    </div>
</header>

<div class="toolbar">
    <label for="buscar">Buscar</label>
    <input type="text" id="buscar" placeholder="Título, autor, editorial…">
    <button class="button" id="addBookBtn">Agregar libro</button>
</div>

<div class="wrapper">
    <?php if (empty($libros)): ?>
        <div class="empty" id="emptyState"><p>No hay libros en la base de datos.</p></div>
    <?php endif; ?>
    <table id="tabla" <?= empty($libros) ? 'hidden' : '' ?>>
        <thead>
            <tr>
                <th>#</th>
                <th>Título</th>
                <th>Autor</th>
                <th>Editorial</th>
                <th>Género</th>
                <th>Precio</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody id="tablaBody">
            <?php foreach ($libros as $i => $libro): ?>
            <tr>
                <td><?= htmlspecialchars($libro['id_libro'] ?? $i + 1) ?></td>
                <td class="titulo"><?= htmlspecialchars($libro['titulo'] ?? '—') ?></td>
                <td><?= htmlspecialchars($libro['autor'] ?? '—') ?></td>
                <td><?= htmlspecialchars($libro['editorial'] ?? '—') ?></td>
                <td><?= htmlspecialchars($libro['genero'] ?? '—') ?></td>
                <td class="precio">
                    <?= isset($libro['precio']) ? '$' . number_format((float)$libro['precio'], 2, ',', '.') : '—' ?>
                </td>
                <td class="stock"><?= htmlspecialchars($libro['stock'] ?? '0') ?></td>
                <td class="acciones"><div title="Editar libro..." class="editBtn" data-id="<?= htmlspecialchars($libro['id_libro'] ?? $i + 1) ?>"><img src="Resources/icons/edit.svg" alt="Editar"></div></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<footer id="footerCount">
    <?= count($libros) ?> libros · <?= date('d/m/Y H:i') ?>
</footer>
<!--___________BOOK POP UP___________-->
<div class="popUp-overlay" id="popUpOverlay"></div>
<div class="popUp" id="popUp">
    <header class="popUp-header">
        <div class="popUp-title" id="popUpTitle">Editar libro</div>
        <button class="popUp-close" id="popUpClose">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </header>
    <div class="popUp-body">
        <form id="editForm" action="php/scripts/actualizarLibro.php" method="POST">
            <input type="hidden" id="id_libro" name="id_libro">

            <section class="cover-upload" id="coverUpload" hidden>
                <label for="coverFile">Portada del libro</label>
                <div class="cover-upload-controls">
                    <input type="file" id="coverFile" accept="image/jpeg,image/png,image/webp">
                    <button type="button" class="button" id="uploadCoverBtn">Subir portada</button>
                </div>
                <small>JPG, PNG o WebP (máximo 8 MB). Esta portada tiene prioridad sobre Open Library.</small>
                <p id="coverUploadStatus" role="status"></p>
                <img id="coverPreview" alt="Portada actual del libro" hidden>
            </section>

            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="autor">Autor</label>
                    <div class="combobox" id="autorCombobox">
                        <input type="text" id="autor" name="autor" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="autorList" aria-autocomplete="list" required>
                        <ul class="combobox-list" id="autorList" role="listbox" hidden></ul>
                    </div>
                </div>
                <div class="form-group">
                    <label for="editorial">Editorial</label>
                    <div class="combobox" id="editorialCombobox">
                        <input type="text" id="editorial" name="editorial" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="editorialList" aria-autocomplete="list">
                        <ul class="combobox-list" id="editorialList" role="listbox" hidden></ul>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="genero">Género</label>
                    <div class="combobox" id="generoCombobox">
                        <input type="text" id="genero" name="genero" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="generoList" aria-autocomplete="list">
                        <ul class="combobox-list" id="generoList" role="listbox" hidden></ul>
                    </div>
                </div>
                <div class="form-group form-group-small">
                    <label for="precio">Precio</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0">
                </div>
                
            </div>
            <div class="form-row">
                <div class="form-group form-group-small">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" min="0">
                </div>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4" placeholder="Sinopsis, notas o información sobre el libro…"></textarea>
            </div>
        </form>
    </div>
    <footer class="popUp-footer">
        <button type="button" class="button button-danger" id="deleteBookBtn" hidden>Eliminar libro</button>
        <button type="button" class="button button-ghost" id="popUpCancel">Cancelar</button>
        <button type="submit" form="editForm" class="button button-primary" id="popUpSubmit">Guardar cambios</button>
    </footer>
</div>
<script src="src/js/search-utils.js"></script>
<script>
    const librosDB = <?= json_encode(array_map(fn($l) => [
        'id'        => (int)  ($l['id_libro'] ?? 0),
        'uid'       =>        $l['uid']       ?? '',
        'titulo'    =>        $l['titulo']    ?? '',
        'autor'     =>        $l['autor']     ?? '',
        'editorial' =>        $l['editorial'] ?? '',
        'genero'    =>        $l['genero']    ?? '',
        'precio'      => (float)($l['precio']     ?? 0),
        'stock'       => (int)  ($l['stock']      ?? 0),
        'descripcion' =>        $l['descripcion'] ?? '',
    ], $libros), JSON_UNESCAPED_UNICODE) ?>;

    let editoriales = <?= json_encode(array_values($editoriales), JSON_UNESCAPED_UNICODE) ?>;
    let autores    = <?= json_encode(array_values($autores),    JSON_UNESCAPED_UNICODE) ?>;
    let generos    = <?= json_encode(array_values($generos),    JSON_UNESCAPED_UNICODE) ?>;

    const popUp = document.getElementById('popUp');
    const popUpOverlay = document.getElementById('popUpOverlay');
    const editForm = document.getElementById('editForm');
    const popUpTitle = document.getElementById('popUpTitle');
    const popUpSubmit = document.getElementById('popUpSubmit');
    const tabla = document.getElementById('tabla');
    const tablaBody = document.getElementById('tablaBody');
    let emptyState = document.getElementById('emptyState');
    const headerCount = document.getElementById('headerCount');
    const footerCount = document.getElementById('footerCount');
    const deleteBookBtn = document.getElementById('deleteBookBtn');
    const coverUpload = document.getElementById('coverUpload');
    const coverFile = document.getElementById('coverFile');
    const uploadCoverBtn = document.getElementById('uploadCoverBtn');
    const coverUploadStatus = document.getElementById('coverUploadStatus');
    const coverPreview = document.getElementById('coverPreview');

    const ENDPOINTS = {
        edit: 'php/scripts/actualizarLibro.php',
        add: 'php/scripts/crearLibro.php',
        delete: 'php/scripts/eliminarLibro.php',
        clearAll: 'php/scripts/eliminarTODOSlosLibros.php',
        import: 'php/scripts/cargarLibros.php',
    };

    let popUpMode = 'edit';

    function createCombobox({ fieldId, listId, comboboxId, items, emptyLabelPlural }) {
        const input = document.getElementById(fieldId);
        const list = document.getElementById(listId);
        const combobox = document.getElementById(comboboxId);
        let activeIndex = -1;

        function normalize(value) {
            const trimmed = value.trim();
            if (!trimmed) return '';
            const match = items.find(e => e.toLowerCase() === trimmed.toLowerCase());
            return match ?? trimmed;
        }

        function register(value) {
            const normalized = normalize(value);
            if (!normalized) return '';
            if (!items.some(e => e.toLowerCase() === normalized.toLowerCase())) {
                items.push(normalized);
                items.sort((a, b) => a.localeCompare(b, 'es'));
            }
            return normalized;
        }

        function filterItems(query) {
            const q = query.trim().toLowerCase();
            if (!q) return items;
            return items.filter(e => e.toLowerCase().includes(q));
        }

        function closeList() {
            list.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        }

        function renderList(filtered) {
            list.innerHTML = '';
            if (!filtered.length) {
                const empty = document.createElement('li');
                empty.className = 'combobox-item combobox-empty';
                empty.textContent = input.value.trim()
                    ? 'Sin coincidencias — se usará el valor ingresado'
                    : `No hay ${emptyLabelPlural} registrados`;
                empty.setAttribute('role', 'option');
                list.appendChild(empty);
            } else {
                filtered.forEach((item, index) => {
                    const li = document.createElement('li');
                    li.className = 'combobox-item';
                    li.textContent = item;
                    li.setAttribute('role', 'option');
                    li.dataset.index = index;
                    li.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        input.value = item;
                        closeList();
                    });
                    list.appendChild(li);
                });
            }
            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        function highlightItem(index) {
            const nodes = list.querySelectorAll('.combobox-item:not(.combobox-empty)');
            nodes.forEach((item, i) => item.classList.toggle('selected', i === index));
            if (nodes[index]) {
                nodes[index].scrollIntoView({ block: 'nearest' });
            }
        }

        function openList() {
            renderList(filterItems(input.value));
            activeIndex = -1;
        }

        input.addEventListener('focus', openList);
        input.addEventListener('input', openList);
        input.addEventListener('keydown', (e) => {
            const nodes = list.querySelectorAll('.combobox-item:not(.combobox-empty)');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (list.hidden) openList();
                activeIndex = Math.min(activeIndex + 1, nodes.length - 1);
                highlightItem(activeIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                highlightItem(activeIndex);
            } else if (e.key === 'Enter' && activeIndex >= 0 && nodes[activeIndex]) {
                e.preventDefault();
                input.value = nodes[activeIndex].textContent;
                closeList();
            } else if (e.key === 'Escape') {
                closeList();
            }
        });

        input.addEventListener('blur', () => {
            window.setTimeout(() => {
                if (!combobox.contains(document.activeElement)) {
                    input.value = normalize(input.value);
                    closeList();
                }
            }, 120);
        });

        document.addEventListener('click', (e) => {
            if (!combobox.contains(e.target)) {
                closeList();
            }
        });

        return { normalize, register };
    }

    const editorialBox = createCombobox({
        fieldId: 'editorial', listId: 'editorialList', comboboxId: 'editorialCombobox',
        items: editoriales, emptyLabelPlural: 'editoriales',
    });
    const autorBox = createCombobox({
        fieldId: 'autor', listId: 'autorList', comboboxId: 'autorCombobox',
        items: autores, emptyLabelPlural: 'autores',
    });
    const generoBox = createCombobox({
        fieldId: 'genero', listId: 'generoList', comboboxId: 'generoCombobox',
        items: generos, emptyLabelPlural: 'géneros',
    });

    function formatPrecio(n) {
        return '$' + Number(n).toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function displayValue(value) {
        return value === '' || value == null ? '—' : value;
    }

    function buildTableRow(libro) {
        const row = document.createElement('tr');
        const values = [
            String(libro.id),
            displayValue(libro.titulo),
            displayValue(libro.autor),
            displayValue(libro.editorial),
            displayValue(libro.genero),
            formatPrecio(libro.precio),
            String(libro.stock),
        ];
        const cellClasses = ['', 'titulo', '', '', '', 'precio', 'stock'];

        values.forEach((text, i) => {
            const td = document.createElement('td');
            td.textContent = text;
            if (cellClasses[i]) td.className = cellClasses[i];
            row.appendChild(td);
        });

        const actions = document.createElement('td');
        actions.className = 'acciones';
        const btn = document.createElement('div');
        btn.title = 'Editar libro...';
        btn.className = 'editBtn';
        btn.dataset.id = libro.id;
        btn.innerHTML = '<img src="Resources/icons/edit.svg" alt="Editar">';
        actions.appendChild(btn);
        row.appendChild(actions);

        return row;
    }

    function updateTableRow(libro) {
        const btn = document.querySelector(`.editBtn[data-id="${libro.id}"]`);
        const row = btn?.closest('tr');
        if (!row) return;

        row.querySelector('.titulo').textContent = displayValue(libro.titulo);
        row.cells[2].textContent = displayValue(libro.autor);
        row.cells[3].textContent = displayValue(libro.editorial);
        row.cells[4].textContent = displayValue(libro.genero);
        row.querySelector('.precio').textContent = formatPrecio(libro.precio);
        row.querySelector('.stock').textContent = libro.stock;
    }

    function appendTableRow(libro) {
        if (emptyState) {
            emptyState.remove();
            emptyState = null;
        }
        tabla.hidden = false;
        tablaBody.appendChild(buildTableRow(libro));
    }

    function removeTableRow(id) {
        const index = librosDB.findIndex(libro => libro.id === id);
        if (index === -1) return;

        librosDB.splice(index, 1);
        document.querySelector(`.editBtn[data-id="${id}"]`)?.closest('tr')?.remove();

        if (librosDB.length === 0) {
            emptyState = document.createElement('div');
            emptyState.className = 'empty';
            emptyState.id = 'emptyState';
            const message = document.createElement('p');
            message.textContent = 'No hay libros en la base de datos.';
            emptyState.appendChild(message);
            tabla.before(emptyState);
            tabla.hidden = true;
        }

        updateCounts();
    }

    function updateCounts() {
        const count = librosDB.length;
        headerCount.textContent = `${count} registros`;
        footerCount.textContent = `${count} libros · ${new Date().toLocaleString('es-AR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        })}`;
    }

    function fillForm(libro = null) {
        document.getElementById('id_libro').value = libro?.id ?? '';
        document.getElementById('titulo').value = libro?.titulo ?? '';
        document.getElementById('autor').value = libro?.autor ?? '';
        document.getElementById('editorial').value = libro?.editorial ?? '';
        document.getElementById('genero').value = libro?.genero ?? '';
        document.getElementById('precio').value = libro?.precio ?? '';
        document.getElementById('stock').value = libro?.stock ?? '';
        document.getElementById('descripcion').value = libro?.descripcion ?? '';
    }

    function openPopUp(mode, id = null) {
        popUpMode = mode;
        editForm.action = ENDPOINTS[mode];
        popUpTitle.textContent = mode === 'add' ? 'Agregar libro' : 'Editar libro';
        popUpSubmit.textContent = mode === 'add' ? 'Agregar libro' : 'Guardar cambios';
        deleteBookBtn.hidden = mode === 'add';

        if (mode === 'add') {
            editForm.reset();
            document.getElementById('id_libro').value = '';
            coverUpload.hidden = true;
        } else {
            const libro = librosDB.find(l => l.id === Number(id));
            fillForm(libro);
            document.getElementById('id_libro').value = id;
            coverUpload.hidden = false;
            coverFile.value = '';
            coverUploadStatus.textContent = '';
            coverPreview.hidden = !libro?.uid;
            coverPreview.src = libro?.uid
                ? `php/scripts/portadaLibro.php?uid=${encodeURIComponent(libro.uid)}&t=${Date.now()}`
                : '';
            coverPreview.onerror = () => {
                coverPreview.hidden = true;
            };
        }

        popUp.classList.add('active');
        popUpOverlay.classList.add('active');
    }
    function closePopUp() {
        popUp.classList.remove('active');
        popUpOverlay.classList.remove('active');
    }
    document.getElementById('buscar').addEventListener('input', function () {
        const q = this.value;
        document.querySelectorAll('#tabla tbody tr').forEach(tr => {
            tr.style.display = textIncludesSearch(tr.textContent, q) ? '' : 'none';
        });
    });

    document.getElementById('addBookBtn').addEventListener('click', () => openPopUp('add'));

    const importFileInput = document.getElementById('importFile');
    const importExcelBtn = document.getElementById('importExcelBtn');

    importExcelBtn.addEventListener('click', () => importFileInput.click());

    importFileInput.addEventListener('change', async function () {
        const file = this.files[0];
        this.value = '';
        if (!file) return;

        const confirmed = confirm(
            'Se eliminarán todos los libros actuales y se importará el archivo seleccionado.\n\n¿Desea continuar?'
        );
        if (!confirmed) return;

        importExcelBtn.disabled = true;
        importExcelBtn.textContent = 'Importando…';

        try {
            const clearRes = await fetch(ENDPOINTS.clearAll, { method: 'POST' });
            const clearData = await clearRes.json();
            if (clearData.state !== 'success') {
                throw new Error(clearData.message || 'No se pudo vaciar la base de datos');
            }

            const formData = new FormData();
            formData.append('archivo', file);

            const importRes = await fetch(ENDPOINTS.import, {
                method: 'POST',
                body: formData,
            });
            const importData = await importRes.json();
            if (importData.state !== 'success') {
                throw new Error(importData.message || 'No se pudo importar el archivo');
            }

            const errores = importData.errores?.length ?? 0;
            let message = `Importación completada: ${importData.insertados} libros cargados.`;
            if (errores > 0) {
                message += `\n${errores} fila(s) con errores fueron omitidas.`;
            }
            alert(message);
            location.reload();
        } catch (err) {
            alert(err.message || 'Error al importar el archivo');
        } finally {
            importExcelBtn.disabled = false;
            importExcelBtn.textContent = 'Importar Excel';
        }
    });

    if (tabla) {
        tabla.addEventListener('click', function (e) {
            const btn = e.target.closest('.editBtn');
            if (!btn) return;
            openPopUp('edit', btn.dataset.id);
        });
    }
    document.getElementById('popUpClose').addEventListener('click', closePopUp);
    document.getElementById('popUpCancel').addEventListener('click', closePopUp);
    popUpOverlay.addEventListener('click', closePopUp);

    deleteBookBtn.addEventListener('click', async () => {
        const idLibro = document.getElementById('id_libro').value;
        const libro = librosDB.find(item => item.id === Number(idLibro));
        if (!libro) return;

        if (!confirm(`¿Eliminar "${libro.titulo}"? Esta acción no se puede deshacer.`)) {
            return;
        }

        deleteBookBtn.disabled = true;
        try {
            const formData = new FormData();
            formData.append('id_libro', idLibro);
            const response = await fetch(ENDPOINTS.delete, {
                method: 'POST',
                body: formData,
            });
            const data = await response.json();
            if (!response.ok || data.state !== 'success') {
                throw new Error(data.message || 'No se pudo eliminar el libro');
            }

            removeTableRow(Number(idLibro));
            closePopUp();
        } catch (error) {
            alert(error.message || 'Error al eliminar el libro');
        } finally {
            deleteBookBtn.disabled = false;
        }
    });

    uploadCoverBtn.addEventListener('click', async () => {
        const idLibro = document.getElementById('id_libro').value;
        const file = coverFile.files[0];

        if (!idLibro || !file) {
            coverUploadStatus.textContent = 'Seleccioná una imagen para subir.';
            return;
        }

        uploadCoverBtn.disabled = true;
        coverUploadStatus.textContent = 'Subiendo portada…';
        const formData = new FormData();
        formData.append('id_libro', idLibro);
        formData.append('portada', file);

        try {
            const response = await fetch('php/scripts/subirPortada.php', {
                method: 'POST',
                body: formData,
            });
            const responseText = await response.text();
            let result;
            try {
                result = JSON.parse(responseText);
            } catch {
                const detail = responseText.replace(/<[^>]*>/g, ' ').trim().slice(0, 200);
                throw new Error(
                    `El servidor no devolvió una respuesta JSON (HTTP ${response.status}).`
                    + (detail ? ` ${detail}` : '')
                );
            }
            if (!response.ok || result.state !== 'success') {
                throw new Error(result.message || 'No se pudo subir la portada.');
            }

            const libro = librosDB.find(item => item.id === Number(idLibro));
            if (libro) libro.uid = result.uid;
            coverPreview.src = `${result.url}&t=${Date.now()}`;
            coverPreview.hidden = false;
            coverUploadStatus.textContent = 'Portada subida correctamente.';
            coverFile.value = '';
        } catch (error) {
            coverUploadStatus.textContent = error.message || 'Error al subir la portada.';
        } finally {
            uploadCoverBtn.disabled = false;
        }
    });

    editForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        popUpSubmit.disabled = true;

        document.getElementById('editorial').value = editorialBox.normalize(document.getElementById('editorial').value);
        document.getElementById('autor').value = autorBox.normalize(document.getElementById('autor').value);
        document.getElementById('genero').value = generoBox.normalize(document.getElementById('genero').value);

        const formData = new FormData(this);
        if (popUpMode === 'add') {
            formData.delete('id_libro');
        }

        try {
            const res = await fetch(this.action, {
                method: 'POST',
                body: formData,
            });

            const data = await res.json();

            if (data.state !== 'success') {
                throw new Error(data.message || 'No se pudo guardar el libro');
            }

            const libro = data.libro;
            libro.editorial = editorialBox.register(libro.editorial);
            libro.autor = autorBox.register(libro.autor);
            libro.genero = generoBox.register(libro.genero);

            if (popUpMode === 'add') {
                librosDB.push(libro);
                appendTableRow(libro);
                updateCounts();
            } else {
                const idx = librosDB.findIndex(l => l.id === libro.id);
                if (idx !== -1) {
                    librosDB[idx] = libro;
                }
                updateTableRow(libro);
            }

            closePopUp();
        } catch (err) {
            alert(err.message || 'Error al guardar los cambios');
        } finally {
            popUpSubmit.disabled = false;
        }
    });
</script>

</body>
</html>