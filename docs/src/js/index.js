const underConstructions =  document.querySelectorAll(".uC");

underConstructions.forEach(element => {
    element.addEventListener("click", function(){
        alert("Función en construcción");
    })
});

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

