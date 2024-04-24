document.addEventListener('DOMContentLoaded', function () {
    let urlPathname = window.location.pathname;
    let lastSegment = urlPathname.split('/').pop();

    let navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(function (item) {
        if (item.dataset.id === lastSegment) {
            item.classList.add('custom-active');
        }
    });
});
