/*!
    * Start Bootstrap - SB Admin Pro v2.0.5 (https://shop.startbootstrap.com/product/sb-admin-pro)
    * Copyright 2013-2023 Start Bootstrap
    * Licensed under SEE_LICENSE (https://github.com/StartBootstrap/sb-admin-pro/blob/master/LICENSE)
    */
    window.addEventListener('DOMContentLoaded', event => {
    // Activate feather
    feather.replace();

    // Enable tooltips globally
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Enable popovers globally
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

// --- Toggle the sidebar ---
const sidebarToggle = document.querySelector('#sidebarToggle');

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', event => {
        event.preventDefault();
        document.body.classList.toggle('sidenav-toggled');
        localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sidenav-toggled'));
    });
}

// --- Hide sidebar automatically when window < LG (992px) ---
function autoHideSidebarOnMobile() {
    const BOOTSTRAP_LG_WIDTH = 992;

    if (window.innerWidth < BOOTSTRAP_LG_WIDTH) {
        // FORCE sidebar to be collapsed on mobile/tablet
        document.body.classList.remove('sidenav-toggled');
        localStorage.setItem('sb|sidebar-toggle', false);
    }
}

// Run on page load
autoHideSidebarOnMobile();

// Run every time window resizes
window.addEventListener('resize', autoHideSidebarOnMobile);

// --- Close sidebar if user clicks content area on mobile ---
const sidenavContent = document.querySelector('#layoutSidenav_content');

if (sidenavContent) {
    sidenavContent.addEventListener('click', () => {
        const BOOTSTRAP_LG_WIDTH = 992;

        // only close on mobile
        if (window.innerWidth < BOOTSTRAP_LG_WIDTH) {
            document.body.classList.remove('sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', false);
        }
    });
}

    // Add active state to sidbar nav links
    let activatedPath = window.location.pathname.match(/([\w-]+\.html)/, '$1');

    if (activatedPath) {
        activatedPath = activatedPath[0];
    } else {
        activatedPath = 'index.html';
    }

    const targetAnchors = document.body.querySelectorAll('[href="' + activatedPath + '"].nav-link');

    targetAnchors.forEach(targetAnchor => {
        let parentNode = targetAnchor.parentNode;
        while (parentNode !== null && parentNode !== document.documentElement) {
            if (parentNode.classList.contains('collapse')) {
                parentNode.classList.add('show');
                const parentNavLink = document.body.querySelector(
                    '[data-bs-target="#' + parentNode.id + '"]'
                );
                parentNavLink.classList.remove('collapsed');
                parentNavLink.classList.add('active');
            }
            parentNode = parentNode.parentNode;
        }
        targetAnchor.classList.add('active');
    });
});
