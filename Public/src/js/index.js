const header = document.getElementById("header");
const mainnav = document.querySelector(".main-nav");
//scroll detection for dynamic header and elements
let prevScroll = 0;

window.addEventListener("scroll", function(){
    let currentScroll = window.scrollY;
    if(currentScroll > 80 && currentScroll > prevScroll){
        mainnav.classList.add("hidden");

    }else{
        mainnav.classList.remove("hidden");

    }
    prevScroll = currentScroll;
});