document.addEventListener('DOMContentLoaded', function () {
    const currentPage = window.location.pathname.split('/').pop();
    const navButtons = document.querySelectorAll('.nav-button');

    navButtons.forEach(button => {
        const buttonPage = button.getAttribute('href');
        if (buttonPage === currentPage ||
            (currentPage === '' && buttonPage === 'index.html')) {
            button.classList.add('active');
        }
    });
});