// ─────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────

function showNotification(type, message) {
    const notification = document.getElementById(`${type}Notification`);
    const messageEl = document.getElementById(`${type}Message`);

    if (!notification || !messageEl) {
        console.warn(`Notification element missing: ${type}`);
        return;
    }

    messageEl.textContent = message;

    notification.classList.remove("hide");
    notification.classList.add("show");

    setTimeout(() => {
        notification.classList.remove("show");
        notification.classList.add("hide");
    }, 4000);
}

const showSuccessNotification = (msg) =>
    showNotification("success", msg);

const showErrorNotification = (msg) =>
    showNotification("error", msg);

function hideModal(modalId) {
    bootstrap.Modal
        .getInstance(document.getElementById(modalId))
        ?.hide();
}

function showModal(modalId) {
    bootstrap.Modal
        .getOrCreateInstance(document.getElementById(modalId))
        .show();
}

function setCurrentUser(user) {
    currentUser = user;
    updateProfileButton();
}

function clearCurrentUser() {
    currentUser = null;
    updateProfileButton();
}

function bindClick(id, callback) {
    const element = document.getElementById(id);

    if (element) {
        element.addEventListener("click", callback);
    }
}

// ─────────────────────────────────────────────────────────────
// State
// ─────────────────────────────────────────────────────────────

let currentUser = null;

// ─────────────────────────────────────────────────────────────
// Session
// ─────────────────────────────────────────────────────────────

async function checkSession() {
    try {
        const res = await fetch("php/scripts/sesion.php");
        const data = await res.json();

        if (data.logged) {
            setCurrentUser(data.user);
        }

    } catch (error) {
        console.error("Error checking session:", error);
    }
}

// ─────────────────────────────────────────────────────────────
// Profile Button
// ─────────────────────────────────────────────────────────────

function updateProfileButton() {
    const profileBtn = document.getElementById("profileBtn");

    if (!profileBtn) return;

    profileBtn.onclick = null;

    if (currentUser) {

        profileBtn.removeAttribute("data-bs-toggle");
        profileBtn.removeAttribute("data-bs-target");

        profileBtn.onclick = showUserDashboard;

    } else {

        profileBtn.setAttribute(
            "data-bs-toggle",
            "modal"
        );

        profileBtn.setAttribute(
            "data-bs-target",
            "#formModal"
        );
    }
}

// ─────────────────────────────────────────────────────────────
// Dashboards
// ─────────────────────────────────────────────────────────────

function showUserDashboard() {
    if (!currentUser) return;

    if (currentUser.admin) {
        return showAdminPanel();
    }

    const greeting =
        document.getElementById("userGreeting");

    const email =
        document.getElementById("userEmail");

    if (greeting) {
        greeting.textContent =
            `Bienvenido, ${currentUser.nombre}`;
    }

    if (email) {
        email.textContent =
            currentUser.email;
    }

    showModal("userDashboardModal");
}

function showAdminPanel() {
    if (!currentUser?.admin) return;

    const greeting =
        document.getElementById("adminGreeting");

    if (greeting) {
        greeting.textContent =
            `Panel de Administrador - ${currentUser.nombre}`;
    }

    showModal("adminPanelModal");
}

// ─────────────────────────────────────────────────────────────
// Auth Forms
// ─────────────────────────────────────────────────────────────

async function handleAuthForm(form, endpoint) {

    form.addEventListener("submit", async function (e) {

        e.preventDefault();

        try {

            const res = await fetch(endpoint, {
                method: "POST",
                body: new FormData(this)
            });

            const data = await res.json();

            if (data.state === "success") {

                showSuccessNotification(
                    data.message
                );

                hideModal("formModal");

                this.reset();

                setCurrentUser(data.user);

            } else {

                showErrorNotification(
                    data.message
                );
            }

        } catch (error) {

            console.error(error);

            showErrorNotification(
                "Error en el servidor"
            );
        }
    });
}

const registerForm =
    document.getElementById("registerForm");

const loginForm =
    document.getElementById("loginForm");

if (registerForm) {
    handleAuthForm(
        registerForm,
        "php/scripts/registrar.php"
    );
}

if (loginForm) {
    handleAuthForm(
        loginForm,
        "php/scripts/loguear.php"
    );
}

// ─────────────────────────────────────────────────────────────
// Logout
// ─────────────────────────────────────────────────────────────

async function logout(modalId) {

    try {

        const res = await fetch(
            "php/scripts/logout.php",
            {
                method: "POST"
            }
        );

        const data = await res.json();

        if (data.state === "success") {

            showSuccessNotification(
                data.message
            );

            hideModal(modalId);

            clearCurrentUser();

        } else {

            showErrorNotification(
                data.message
            );
        }

    } catch (error) {

        console.error(error);

        showErrorNotification(
            "Error al cerrar sesión"
        );
    }
}

bindClick(
    "logoutBtn",
    () => logout("userDashboardModal")
);

bindClick(
    "adminLogoutBtn",
    () => logout("adminPanelModal")
);

// ─────────────────────────────────────────────────────────────
// Admin Panel
// ─────────────────────────────────────────────────────────────

const adminActions = {
    adminLibrosBtn:
        "Función 'Gestionar Libros' en construcción",

    adminUsuariosBtn:
        "Función 'Gestionar Usuarios' en construcción",

    adminPedidosBtn:
        "Función 'Ver Pedidos' en construcción"
};

Object.entries(adminActions).forEach(
    ([id, message]) => {

        bindClick(id, () => {
            showSuccessNotification(message);
        });

    }
);

// ─────────────────────────────────────────────────────────────
// Delete Account
// ─────────────────────────────────────────────────────────────

const deleteBtn =
    document.getElementById("deleteBtn");

if (deleteBtn) {

    deleteBtn.addEventListener(
        "click",
        function () {

            const dashboardModal =
                document.getElementById(
                    "userDashboardModal"
                );

            if (!dashboardModal) return;

            dashboardModal.addEventListener(
                "hidden.bs.modal",
                () => {
                    showModal(
                        "deleteConfirmModal"
                    );
                },
                { once: true }
            );

            hideModal(
                "userDashboardModal"
            );
        }
    );
}

const deleteForm =
    document.getElementById(
        "deleteConfirmForm"
    );

if (deleteForm) {

    deleteForm.addEventListener(
        "submit",
        async function (e) {

            e.preventDefault();

            const password =
                document.getElementById(
                    "deletePassword"
                )?.value;

            const formData =
                new FormData();

            formData.append(
                "password",
                password ?? ""
            );

            try {

                const res = await fetch(
                    "php/scripts/eliminar.php",
                    {
                        method: "POST",
                        body: formData
                    }
                );

                const data =
                    await res.json();

                if (
                    data.state === "success"
                ) {

                    showSuccessNotification(
                        data.message
                    );

                    hideModal(
                        "deleteConfirmModal"
                    );

                    clearCurrentUser();

                    this.reset();

                } else {

                    showErrorNotification(
                        data.message
                    );
                }

            } catch (error) {

                console.error(error);

                showErrorNotification(
                    "Error al eliminar cuenta"
                );
            }
        }
    );
}

// ─────────────────────────────────────────────────────────────
// Init
// ─────────────────────────────────────────────────────────────

updateProfileButton();
checkSession();