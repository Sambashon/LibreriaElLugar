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
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;1,300&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --amber:      #C8922B;
            --green-dark: #23693e;
            --green-deep: #133620;
            --cream:      #EBE9DA;
            --cream-dark: #d9d7c6;
            --text:       #1a1a18;
            --text-muted: #6b6b5e;
            --radius:     6px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--cream);
            color: var(--text);
            min-height: 100vh;
        }

        /* ── HEADER ── */
        header {
            background: var(--green-deep);
            padding: 2rem 2.5rem;
            display: flex;
            align-items: baseline;
            gap: 1.2rem;
        }

        header h1 {
            font-family: 'Fraunces', serif;
            font-weight: 300;
            font-size: 2rem;
            color: var(--cream);
            letter-spacing: -0.02em;
        }

        header h1 em {
            font-style: italic;
            color: var(--amber);
        }

        .header-count {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--green-dark);
            background: rgba(235,233,218,0.1);
            border: 1px solid rgba(235,233,218,0.15);
            padding: 0.25rem 0.7rem;
            border-radius: 99px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* ── TOOLBAR ── */
        .toolbar {
            background: var(--cream-dark);
            border-bottom: 1px solid #c8c6b4;
            padding: 0.85rem 2.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .toolbar input {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            padding: 0.5rem 0.9rem;
            border: 1px solid #b8b6a4;
            border-radius: var(--radius);
            background: var(--cream);
            color: var(--text);
            width: 280px;
            outline: none;
            transition: border-color 0.2s;
        }

        .toolbar input:focus {
            border-color: var(--amber);
        }

        .toolbar input::placeholder { color: var(--text-muted); }

        .toolbar label {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ── TABLE WRAPPER ── */
        .wrapper {
            padding: 2rem 2.5rem;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        thead th {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            background: var(--cream-dark);
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 2px solid #c0bfae;
            white-space: nowrap;
        }

        thead th:first-child { border-radius: var(--radius) 0 0 0; }
        thead th:last-child  { border-radius: 0 var(--radius) 0 0; }

        tbody tr {
            border-bottom: 1px solid #d4d2c2;
            transition: background 0.15s;
            animation: fadeIn 0.3s ease both;
        }

        tbody tr:hover { background: #e2e0d0; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            color: var(--text);
        }

        /* Título destacado */
        td.titulo {
            font-family: 'Fraunces', serif;
            font-weight: 400;
            font-size: 0.95rem;
            max-width: 300px;
        }

        td.precio {
            font-weight: 600;
            color: var(--green-dark);
            white-space: nowrap;
        }

        td.stock {
            font-weight: 500;
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 99px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .badge-disponible {
            background: #d4edda;
            color: var(--green-dark);
            border: 1px solid #b8dfc4;
        }

        .badge-agotado {
            background: #fdecea;
            color: #b03a2e;
            border: 1px solid #f5c0bb;
        }

        /* Empty state */
        .empty {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
        }

        .empty p {
            font-family: 'Fraunces', serif;
            font-size: 1.2rem;
            font-style: italic;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 1.5rem;
            font-size: 0.75rem;
            color: var(--text-muted);
            border-top: 1px solid #c8c6b4;
        }
    </style>
</head>
<body>

<header>
    <h1>Catálogo de <em>Libros</em></h1>
    <span class="header-count"><?= count($libros) ?> registros</span>
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
                <th>Estado</th>
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
                <td>
                    <?php $stock = (int)($libro['stock'] ?? 0); ?>
                    <span class="badge <?= $stock > 0 ? 'badge-disponible' : 'badge-agotado' ?>">
                        <?= $stock > 0 ? 'Disponible' : 'Agotado' ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<footer>
    <?= count($libros) ?> libros · <?= date('d/m/Y H:i') ?>
</footer>

<script>
    document.getElementById('buscar').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#tabla tbody tr').forEach(tr => {
            tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>

</body>
</html>