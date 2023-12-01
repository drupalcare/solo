/**
 * @file
 * Solo
 *
 * Filename:     solo-menu-side.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal, once) => {

  'use strict';

  // Get the primary sidebar menu and all the sidebar hamburger icons
  const verticalNav = document.getElementById('primary-sidebar-menu');
  let cosBtns = document.querySelectorAll('.sidebar-hamburger-icon');

  // Function to add a click event listener to a specified element
  const sideMenubarCloseOpen = (selector, callback) => {
    const element = document.querySelector(selector);
    if (element) {
      element.addEventListener('click', callback);
    }
  };

  // Function to toggle the sidebar menu and update the aria-expanded and aria-hidden attributes of the hamburger icons
  const sideMenubarToggleNav = (isOpen) => {
    cosBtns?.forEach((cosBtn) => {
      cosBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      cosBtn.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    });

    if (isOpen) {
      verticalNav.classList.add('toggled');
    } else {
      verticalNav.classList.remove('toggled');
      const subMenus = document.querySelectorAll('.navigation__sidebar li ul.sub__menu');
      subMenus?.forEach(Drupal.solo.hideSubMenus);
    }
  };
  Drupal.solo.sideMenubarToggleNav = sideMenubarToggleNav;

  // Attach these behaviors to the Drupal system
  Drupal.behaviors.soloPrimarySideMenu = {
    attach: function(settings) {
      // Close nav button found in page.html.twig in vertical menu region.
      sideMenubarCloseOpen('#primary-sidebar-menu #sidebar-button-close', () => sideMenubarToggleNav(false));

      // Open nav button found in page.html.twig in header region.
      sideMenubarCloseOpen('#sidebar-button-open', () => sideMenubarToggleNav(true));

      // Click anywhere to close any submenu.
      document.addEventListener('click', (event) => {
        if (event.target == verticalNav) {
          sideMenubarToggleNav(false);
        }
      });

    }
  };

})(Drupal, once);

