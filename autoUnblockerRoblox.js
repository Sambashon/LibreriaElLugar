const blockBtns = document.querySelectorAll('.user-blocking-btn');
blockBtns.forEach(btn => {
    btn.click();
});

setInterval(() => {
    let currentBlockBtn = document.querySelector('.modal-half-width-button');

    if (currentBlockBtn) {
        currentBlockBtn.click();
    }
}, 2000);