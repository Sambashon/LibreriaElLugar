<?php
require_once __DIR__ . "/php/clases/libreriaDb.php";

$db = new LibreriaDB();
$libros = $db->fetchAll("SELECT * FROM libros ORDER BY titulo ASC");
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
        <span class="header-count"><?= count($libros) ?> registros</span>
    </div>
    <div class="header-container">
        <button class="button" id="backBtn">Volver a inicio</button>
        <button class="button">Cargar Libros</button>
    </div>
</header>

<div class="toolbar">
    <label for="buscar">Buscar</label>
    <input type="text" id="buscar" placeholder="Título, autor, editorial…">
</div>

<div class="wrapper">
    <?php if (empty($libros)): ?>
        <div class="empty"><p>No hay libros en la base de datos.</p></div>
    <?php else: ?>
    <table id="tabla">
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
        <tbody>
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
    <?php endif; ?>
</div>

<footer>
    <?= count($libros) ?> libros · <?= date('d/m/Y H:i') ?>
</footer>
<!--___________EDIT BOOK POP UP___________-->
<div class="popUp-overlay" id="popUpOverlay"></div>
<div class="popUp" id="popUp">
    <header class="popUp-header">
        <div class="popUp-title">Editar libro</div>
        <button class="popUp-close" id="popUpClose">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </header>
    <div class="popUp-body">
        <form id="editForm" action="" method="POST">
            <input type="hidden" id="id_libro" name="id_libro">

            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="autor">Autor</label>
                    <input type="text" id="autor" name="autor">
                </div>
                <div class="form-group">
                    <label for="editorial">Editorial</label>
                    <input type="text" id="editorial" name="editorial">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="genero">Género</label>
                    <input type="text" id="genero" name="genero">
                </div>
                <div class="form-group form-group-small">
                    <label for="precio">Precio</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0">
                </div>
                <div class="form-group form-group-small">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" min="0">
                </div>
            </div>
        </form>
    </div>
    <footer class="popUp-footer">
        <button type="button" class="button button-ghost" id="popUpCancel">Cancelar</button>
        <button type="submit" form="editForm" class="button button-primary">Guardar cambios</button>
    </footer>
</div>
<script>
    const popUp = document.getElementById('popUp');
    const popUpOverlay = document.getElementById('popUpOverlay');
    
    function openPopUp(id) {
        document.getElementById('id_libro').value = id;
        // TODO: fetch book data by id and fill titulo/autor/editorial/genero/precio/stock
        popUp.classList.add('active');
        popUpOverlay.classList.add('active');
    }
    function closePopUp() {
        popUp.classList.remove('active');
        popUpOverlay.classList.remove('active');
    }
    document.getElementById('buscar').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#tabla tbody tr').forEach(tr => {
            tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // Delegated listener: works for every row regardless of how many
    // are rendered or how the table is resized/reflowed at medium widths.
    const tabla = document.getElementById('tabla');
    if (tabla) {
        tabla.addEventListener('click', function (e) {
            const btn = e.target.closest('.editBtn');
            if (!btn) return;
            openPopUp(btn.dataset.id);
        });
    }
    document.getElementById('popUpClose').addEventListener('click', closePopUp);
    document.getElementById('popUpCancel').addEventListener('click', closePopUp);
    popUpOverlay.addEventListener('click', closePopUp);
</script>

</body>
</html>