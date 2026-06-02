const underConstructions =  document.querySelectorAll(".uC");

underConstructions.forEach(element => {
    element.addEventListener("click", function(){
        alert("Función en construcción");
    })
});

const profileBtn = document.getElementById("profileBtn");
profileBtn.addEventListener("click", function(){
    //window.location.href = "auth.html";
})

AOS.init({
    once: true,
    offset: 40,
    easing: 'ease-out-quart'
  });

/*
const row = document.getElementById("row");

const rows = document.querySelectorAll("#row");

let autoScrollInterval;
let resumeTimeout;

function startAutoScroll() {
    autoScrollInterval = setInterval(() => {
        rows.forEach(row => {
            row.scrollBy({
                left: 250,
                behavior: "smooth"
            });
        });
        

        // loop infinito
        if (row.scrollLeft + row.clientWidth >= row.scrollWidth - 5) {
            row.scrollTo({ left: 0, behavior: "smooth" });
        }
    }, 3500);
}

function stopAutoScroll() {
    clearInterval(autoScrollInterval);
}

function resetAutoScroll() {
    stopAutoScroll();
    clearTimeout(resumeTimeout);

    resumeTimeout = setTimeout(() => {
        startAutoScroll();
    }, 10000); // 10 segundos
}

function manualScroll(amount) {
    row.scrollBy({
        left: amount,
        behavior: "smooth"
    });
    resetAutoScroll();
}

// Detectar interacción del usuario
row.addEventListener("mousedown", resetAutoScroll);
row.addEventListener("touchstart", resetAutoScroll);

// iniciar
//startAutoScroll();
*/
const cifras = document.querySelectorAll(".cifra");


function countUp(cifra){
    const original = cifra.innerText;
    const target = cifra.innerText;
    let current = 0;

    const interval = setInterval(() => {

        current += (target - current) * 0.05; 

        if (Math.abs(target - current) < 1) {
            cifra.innerText = original;
            clearInterval(interval);
        } else {
            cifra.innerText = Math.floor(current);
        }

    }, 20);
}

cifras.forEach(cifra=> {
    countUp(cifra);
})

const buttons = document.querySelectorAll(".modal-nav .button");
const register = document.querySelector(".register");
const login = document.querySelector(".login");
const modalContent = document.querySelector(".modal-content .modal-body");

function getHeight(panel) {
    panel.style.cssText = "display:flex; position:absolute; visibility:hidden; pointer-events:none;";
    const h = panel.scrollHeight;
    panel.style.cssText = "";
    return h;
}

function setModalHeight(panel) {
    const navHeight = document.querySelector(".modal-nav").offsetHeight;
    modalContent.style.height = (getHeight(panel) + navHeight) + "px";
}

modalContent.style.transition = "height 0.4s cubic-bezier(0.4, 0, 0.2, 1)";
modalContent.style.position = "relative";
modalContent.style.overflow = "hidden";

document.getElementById("formModal").addEventListener("shown.bs.modal", () => {
    const active = document.querySelector(".register.active, .login.active");
    const navHeight = document.querySelector(".modal-nav").offsetHeight;
    modalContent.style.height = (active.scrollHeight + navHeight) + "px";
});

document.getElementById("formModal").addEventListener("hidden.bs.modal", () => {
    modalContent.style.height = "";
});

function switchTo(incoming, outgoing) {
    // Freeze current height before animating
    const navHeight = document.querySelector(".modal-nav").offsetHeight;
    modalContent.style.height = (outgoing.scrollHeight + navHeight) + "px";

    // Slide + fade out
    outgoing.style.transition = "opacity 0.2s ease, transform 0.2s ease";
    outgoing.style.opacity = "0";
    outgoing.style.transform = "translateY(-6px)";

    setTimeout(() => {
        outgoing.classList.remove("active");
        outgoing.style.cssText = "";

        incoming.classList.add("active");
        incoming.style.opacity = "0";
        incoming.style.transform = "translateY(8px)";

        // Animate height to new panel
        setModalHeight(incoming);

        // Force reflow then fade in
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                incoming.style.transition = "opacity 0.25s ease, transform 0.25s ease";
                incoming.style.opacity = "1";
                incoming.style.transform = "translateY(0)";
            });
        });

        setTimeout(() => { incoming.style.cssText = ""; }, 300);
    }, 200);
}

buttons.forEach(button => {
    button.addEventListener("click", () => {
        buttons.forEach(btn => btn.classList.remove("active"));
        button.classList.add("active");

        if (button.id === "registerTab") {
            switchTo(register, login);
        } else {
            switchTo(login, register);
        }
    });
});