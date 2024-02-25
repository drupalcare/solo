/**
 * @file
 * Solo
 *
 * Filename:     solo-menu-mobile.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal) => {

    'use strict';
    //////////////////////////////////////////////////////////////////////
    // Steps when click hamburger icon.
    // hamburgerIconIsClicked >> closeMobileMenuHandler >> closeMobileMenu
    // hamburgerIconIsClicked >> openMobileMenuHandler >> openMobileMenu
    let currentWidth;
    const hamburgerIconButtons = document.querySelectorAll('.solo-inner .navigation-responsive .mobile-nav');
    // Get current width
    const getCurrentWidth = () => window.innerWidth || document.documentElement
        .clientWidth || document.body.clientWidth;

    const openMobileMenu = navTagId => {
        const navigationMenubarClass = Drupal.solo.getNavigationMenubarClass(navTagId);
        const subMenuClasses = Drupal.solo.getSubMenuClasses(navTagId);

        subMenuClasses?.forEach((subMenu) => {
            Drupal.solo.hideSubMenus(subMenu);
            Drupal.solo.revertIcons(navTagId);
        });
        Drupal.solo.slideDown(navigationMenubarClass);
        const menuElement = document.getElementById(navTagId);
        if (menuElement) {
            menuElement.setAttribute('aria-hidden', 'false');
        }
    };

    const closeMobileMenu = navTagId => {
        const navigationMenubarClass = Drupal.solo.getNavigationMenubarClass(navTagId);
        const subMenuClasses = Drupal.solo.getSubMenuClasses(navTagId);

        subMenuClasses?.forEach((subMenu) => {
            Drupal.solo.hideSubMenus(subMenu);
            Drupal.solo.revertIcons(navTagId);
        });

        Drupal.solo.slideUp(navigationMenubarClass, 400);
        const menuElement = document.getElementById(navTagId);
        if (menuElement) {
            menuElement.setAttribute('aria-hidden', 'true');
        }
    };

    const getMobileNavType = (hamburgerIcon) => {
        const hamburgerIconChild = hamburgerIcon.children[0];
        const navTagId = hamburgerIcon.closest('nav').id;

        const menuElement = document.getElementById(navTagId);
        return [hamburgerIconChild, navTagId, menuElement];
    };

    const hamburgerIconIsClicked = (hamburgerIcon) => {
        const [hamburgerIconChild, navTagId] = getMobileNavType(hamburgerIcon);

        if (!hamburgerIcon.classList.contains('toggled')) {
            hamburgerIconChild.setAttribute('aria-expanded', 'true');
            hamburgerIcon.classList.add('toggled');
            openMobileMenu(navTagId);
        } else {
            hamburgerIconChild.setAttribute('aria-expanded', 'false');
            hamburgerIcon.classList.remove('toggled');
            closeMobileMenu(navTagId);
        }
    };

    const addAriaControlToButton = (hamburgerIcon) => {
        const [hamburgerIconChild, navTagId] = getMobileNavType(hamburgerIcon);
        let ariaControl = document.querySelector(`#${navTagId} .navigation__responsive`).getAttribute('id');
        const pageClass = document.querySelector('.page-wrapper');
        const brNum = Drupal.solo.getMyBreakpoints(pageClass, 'mn');
        if (currentWidth <= brNum) {
            hamburgerIconChild.setAttribute('aria-controls', ariaControl);
        } else {
            hamburgerIconChild.removeAttribute('aria-controls');
        }

    };

    // Onload tage the menubar id  and assign it to hamburger button.
    function processHamburgerIcons(hamburgerIconButtons) {
        hamburgerIconButtons.forEach((hamburgerIcon) => {
            addAriaControlToButton(hamburgerIcon);
        });
    }

    // Hamburger icon is clicked
    hamburgerIconButtons.forEach((hamburgerIcon) => {
        hamburgerIcon.addEventListener('click', () => {
            Drupal.solo.clickedHandler(() => {
                hamburgerIconIsClicked(hamburgerIcon);
            });
        });
    });

    const closeOnResize = (hamburgerIcon) => {
        const [hamburgerIconChild, navTagId] = getMobileNavType(hamburgerIcon);

        hamburgerIconChild.setAttribute('aria-expanded', 'false');
        hamburgerIconChild.setAttribute('aria-hidden', 'true');
        hamburgerIcon.classList.remove('toggled');
        closeMobileMenu(navTagId);

    };

    // This function is to keep the menubar open when changing the screen
    // resolution over 992 px and it is called in resize event listner.
    const resetMenusOnResize = () => {
        hamburgerIconButtons?.forEach((hamburgerIconButton) => {
            closeOnResize(hamburgerIconButton);
        });
    }

    // Prevent page scrolling on mobile devices when the navigation dropdown
    // menu is activated.
    const menu = document.querySelector('.primary-menu .mobile-nav');

    Drupal.behaviors.mobileMenu = {
        attach: function(settings) {
            menu.addEventListener('click', toggleOverflow);
            menu.addEventListener('resize', toggleOverflow);

            window.addEventListener('resize', () => {
                processHamburgerIcons(hamburgerIconButtons);
                currentWidth = getCurrentWidth();
                const pageClass = document.querySelector('.page-wrapper');
                const brNum = Drupal.solo.getMyBreakpoints(pageClass, 'mn');
                if (currentWidth <= brNum) {
                    resetMenusOnResize();
                }
            });

        }
    };

})(Drupal);
