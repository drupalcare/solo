/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal, once) => {

  'use strict';

    const sideMenubarCloseOpen = (selector, callback) => {
      const element = document.querySelector(selector);
      if (element) {
        element.addEventListener('click', callback);
      }
    };


    const sideMenubarToggleNav = (isOpen) => {
      const verticalNav = document.getElementById('primary-sidebar-menu');
      let cosBtns = document.querySelectorAll('.sidebar-hamburger-icon');
      cosBtns?.forEach((cosBtn) => {
        cosBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        cosBtn.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
      });

      if (isOpen) {
        verticalNav.classList.add('toggled');
      } else {
        verticalNav.classList.remove('toggled');
        const subMenus = document.querySelectorAll('.navigation__primary__sidebar li ul.sub__menu');
        subMenus.forEach(Drupal.solo.hideSubMenus);
      }
    };
    Drupal.solo.sideMenubarToggleNav = sideMenubarToggleNav;
    // Close nav button found in page.html.twig in vertical menu region.
    sideMenubarCloseOpen('#primary-sidebar-menu #sidebar-button-close', () => sideMenubarToggleNav(false));

    // Open nav button found in page.html.twig in header region.
    sideMenubarCloseOpen('#sidebar-button-open', () => sideMenubarToggleNav(true));


})(Drupal, once);
