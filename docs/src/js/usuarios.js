const app = document.getElementById("usuariosApp");
const csrfToken = app.dataset.csrfToken;
const feedback = document.getElementById("usuariosFeedback");
const usuarioDialog = document.getElementById("usuarioDialog");
const pedidosDialog = document.getElementById("pedidosDialog");
const usuarioForm = document.getElementById("usuarioForm");
const usuarioPassword = document.getElementById("usuarioPassword");
const passwordHint = document.getElementById("passwordHint");

function mostrarFeedback(message, isError = false) {
    feedback.textContent = message;
    feedback.classList.toggle("error", isError);
}

function escapar(value) {
    return String(value).replace(/[&<>"']/g, (character) => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    })[character]);
}

async function request(url, options = {}) {
    const response = await fetch(url, {
        credentials: "same-origin",
        ...options
    });
    const data = await response.json();
    if (!response.ok || data.state !== "success") {
        throw new Error(data.message || "No se pudo completar la operación.");
    }
    return data;
}

function abrirFormulario(mode, values = {}) {
    usuarioForm.reset();
    usuarioForm.querySelector('[name="action"]').value = mode;
    usuarioForm.elements.id_usuario.value = values.id || "";
    usuarioForm.elements.nombre.value = values.nombre || "";
    usuarioForm.elements.apellido.value = values.apellido || "";
    usuarioForm.elements.email.value = values.email || "";
    usuarioPassword.required = mode === "create";
    usuarioPassword.value = "";
    passwordHint.textContent = mode === "create"
        ? "(mínimo 8 caracteres)"
        : "(dejar vacío para conservarla)";
    document.getElementById("usuarioDialogTitle").textContent =
        mode === "create" ? "Crear usuario" : "Editar usuario";
    usuarioDialog.showModal();
}

function renderPedidos(pedidos) {
    const content = document.getElementById("pedidosContenido");
    if (pedidos.length === 0) {
        content.innerHTML = '<p class="empty">Este usuario todavía no tiene pedidos.</p>';
        return;
    }

    content.innerHTML = pedidos.map((pedido) => {
        const items = pedido.detalles.map((detalle) =>
            `<li>${Number(detalle.cantidad)} × ${escapar(detalle.titulo_libro)} — $${Number(detalle.precio_unitario).toLocaleString("es-UY", { minimumFractionDigits: 2 })} c/u</li>`
        ).join("");
        return `
            <article class="usuarios-order">
                <h3>Pedido #${Number(pedido.id_pedido)} · ${escapar(new Date(pedido.fecha_creacion.replace(" ", "T")).toLocaleString("es-UY"))}</h3>
                <p>Estado: ${escapar(pedido.estado)} · Total: $${Number(pedido.total).toLocaleString("es-UY", { minimumFractionDigits: 2 })}</p>
                <p>Pago: ${escapar(pedido.metodo_pago)}${pedido.metodo_pago === "handy" ? ` (${escapar(pedido.handy_payment_status)})` : ""}</p>
                <ul>${items}</ul>
            </article>
        `;
    }).join("");
}

document.getElementById("crearUsuario").addEventListener("click", () => abrirFormulario("create"));

document.getElementById("buscarUsuario").addEventListener("input", (event) => {
    const query = event.target.value.trim().toLocaleLowerCase();
    document.querySelectorAll("#usuariosTabla tbody tr").forEach((row) => {
        row.hidden = !row.dataset.search.includes(query);
    });
});

document.querySelector("#usuariosTabla tbody")?.addEventListener("click", async (event) => {
    const button = event.target.closest("button[data-action]");
    if (!button) return;

    const { action, id, name } = button.dataset;
    if (action === "edit") {
        abrirFormulario("update", {
            id,
            nombre: button.dataset.name,
            apellido: button.dataset.lastname,
            email: button.dataset.email
        });
        return;
    }

    if (action === "delete") {
        if (!window.confirm(`¿Eliminar la cuenta de ${name}? Sus pedidos históricos se conservarán.`)) return;
        button.disabled = true;
        try {
            const body = new FormData();
            body.set("action", "delete");
            body.set("id_usuario", id);
            body.set("csrf_token", csrfToken);
            const data = await request("php/scripts/gestionarUsuarios.php", { method: "POST", body });
            mostrarFeedback(data.message);
            button.closest("tr").remove();
            window.location.reload();
        } catch (error) {
            mostrarFeedback(error.message, true);
            button.disabled = false;
        }
        return;
    }

    if (action === "orders") {
        button.disabled = true;
        document.getElementById("pedidosUsuarioNombre").textContent = name;
        document.getElementById("pedidosResumen").textContent = "Cargando pedidos...";
        document.getElementById("pedidosContenido").replaceChildren();
        pedidosDialog.showModal();
        try {
            const data = await request(`php/scripts/gestionarUsuarios.php?action=orders&id_usuario=${encodeURIComponent(id)}`);
            document.getElementById("pedidosResumen").textContent = `${data.pedidos.length} pedidos`;
            renderPedidos(data.pedidos);
        } catch (error) {
            document.getElementById("pedidosResumen").textContent = "";
            document.getElementById("pedidosContenido").textContent = error.message;
        } finally {
            button.disabled = false;
        }
    }
});

usuarioForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const submitButton = usuarioForm.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    const body = new FormData(usuarioForm);
    body.set("csrf_token", csrfToken);

    try {
        const data = await request("php/scripts/gestionarUsuarios.php", { method: "POST", body });
        usuarioDialog.close();
        mostrarFeedback(data.message);
        window.location.reload();
    } catch (error) {
        mostrarFeedback(error.message, true);
    } finally {
        submitButton.disabled = false;
    }
});

document.querySelectorAll("[data-close]").forEach((button) => {
    button.addEventListener("click", () => document.getElementById(button.dataset.close).close());
});

[usuarioDialog, pedidosDialog].forEach((dialog) => {
    dialog.addEventListener("click", (event) => {
        if (event.target === dialog) dialog.close();
    });
});
