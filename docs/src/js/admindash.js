document.getElementById("adminLogoutBtn")?.addEventListener("click", async () => {
    try {
        const body = new FormData();
        body.set("csrf_token", document.querySelector('meta[name="csrf-token"]').content);
        const response = await fetch("php/scripts/logout.php", {
            method: "POST",
            body
        });
        const result = await response.json();

        if (!response.ok || result.state !== "success") {
            throw new Error(result.message || "No se pudo cerrar la sesión");
        }

        window.location.href = "index.html";
    } catch (error) {
        console.error("Error al cerrar sesión:", error);
        window.alert(error.message || "Error al cerrar sesión");
    }
});
