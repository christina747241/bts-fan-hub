
const mobNavBtn = document.querySelector('.nav-button');
const mobileNav = document.querySelector('.mobile-nav');
const body = document.body;

// Подключаем обработчики только на страницах,
// где есть мобильное меню и кнопка его открытия.
if (mobNavBtn && mobileNav) {
    mobNavBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        toggleMobileNav();
    });

    window.addEventListener('click', function () {
        if (body.classList.contains('no-scroll')) {
            toggleMobileNav();
        }
    });

    mobileNav.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    function toggleMobileNav() {
        mobileNav.classList.toggle('mobile-nav-active');
        mobNavBtn.classList.toggle('nav-button-close');
        body.classList.toggle('no-scroll');
    }
}