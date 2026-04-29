(function () {
    var toggleButton = document.querySelector('.menu-toggle');
    var mobileMenu = document.getElementById('mobile-menu');
    var nav = document.querySelector('.site-nav');

    if (!toggleButton || !mobileMenu || !nav) {
        return;
    }

    function closeMenu() {
        mobileMenu.classList.remove('is-open');
        toggleButton.setAttribute('aria-expanded', 'false');
    }

    toggleButton.addEventListener('click', function () {
        var isOpen = mobileMenu.classList.toggle('is-open');
        toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('click', function (event) {
        if (!nav.contains(event.target)) {
            closeMenu();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 960) {
            closeMenu();
        }
    });
})();
