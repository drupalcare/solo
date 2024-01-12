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
  let cosBtns = document.querySelectorAll('.sidebar-button-close>button');
  let opnBtns = document.querySelectorAll('.sidebar-button-open>button');

  // Function to add a click event listener to a specified element
  const sideMenubarCloseOpen = (selector, callback) => {
    const element = document.querySelector(selector);
    if (element) {
      element.addEventListener('click', callback);
    }
  };

  // Function to toggle aria-expanded on the hamburger icons
  const setAriaExpanded = (element, isOpen) => {
    element.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  };

  // Function to toggle aria-hidden on the vertical navigation
  const setAriaHidden = (element, isHidden) => {
    element.setAttribute('aria-hidden', isHidden ? 'true' : 'false');
  };

  const sideMenubarToggleNav = (isOpen) => {
    // Set aria-expanded for buttons
    cosBtns?.forEach(cosBtn => setAriaExpanded(cosBtn, isOpen));
    opnBtns?.forEach(opnBtn => setAriaExpanded(opnBtn, isOpen));

    // Set aria-hidden for vertical navigation
    setAriaHidden(verticalNav, !isOpen);

    // Toggle the class for the vertical navigation
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
      // Close nav button event
      sideMenubarCloseOpen('#sidebar-button-close', () => sideMenubarToggleNav(false));

      // Open nav button event
      sideMenubarCloseOpen('#sidebar-button-open', () => sideMenubarToggleNav(true));

      // Click event to close any submenu
      document.addEventListener('click', (event) => {
        if (event.target === verticalNav) {
          sideMenubarToggleNav(false);
        }
      });

    }
  };

})(Drupal, once);

