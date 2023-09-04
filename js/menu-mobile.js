/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal) => {

  'use strict';
  //////////////////////////////////////////////////////////////////////
  // Steps when click hamburger icon.
  // hamburgerIconIsClicked >> closeMobileMenuHandler >> closeMobileMenu
  // hamburgerIconIsClicked >> openMobileMenuHandler >> openMobileMenu

  const hamburgerIconButtons = document.querySelectorAll('.d-inner .mobile-nav');
  // Get current width
  const getCurrentWidth = () => window.innerWidth || document.documentElement
    .clientWidth || document.body.clientWidth;
  let currentWidth = getCurrentWidth();

  const openMobileMenu = navTagId => {
    const navigationMenubarClass = Drupal.solo.getNavigationMenubarClass(navTagId);
    const subMenuClasses = Drupal.solo.getSubMenuClasses(navTagId);

    subMenuClasses?.forEach((subMenu) => {
      Drupal.solo.hideSubMenus(subMenu);
      Drupal.solo.revertIcons(navTagId);
    });
    Drupal.solo.slideDown(navigationMenubarClass, 'flex');

  };

  const closeMobileMenu = navTagId => {
    const navigationMenubarClass = Drupal.solo.getNavigationMenubarClass(navTagId);
    const subMenuClasses = Drupal.solo.getSubMenuClasses(navTagId);

    subMenuClasses?.forEach((subMenu) => {
      Drupal.solo.hideSubMenus(subMenu);
      Drupal.solo.revertIcons(navTagId);
    });

    Drupal.solo.slideUp(navigationMenubarClass, 400);

  };

  const getMobileNavType = (hamburgerIcon) => {
    const hamburgerIconChild = hamburgerIcon.children[0];
    const navTagId = hamburgerIcon.parentElement.classList.contains('responsive-navigation') ?
      hamburgerIcon.closest('nav').id :
      hamburgerIcon.nextElementSibling.id;
    return [hamburgerIconChild, navTagId];
  };

  const hamburgerIconIsClicked = (hamburgerIcon) => {
    const [hamburgerIconChild, navTagId] = getMobileNavType(hamburgerIcon);

    if (!hamburgerIcon.classList.contains('toggled')) {
      hamburgerIconChild.setAttribute('aria-expanded', 'true');
      hamburgerIconChild.setAttribute('aria-hidden', 'false');
      hamburgerIcon.classList.add('toggled');
      openMobileMenu(navTagId);
    } else {
      hamburgerIconChild.setAttribute('aria-expanded', 'false');
      hamburgerIconChild.setAttribute('aria-hidden', 'true');
      hamburgerIcon.classList.remove('toggled');
      closeMobileMenu(navTagId);
    }
  };

  const addAriaControlToButton = (hamburgerIcon) => {
    const [hamburgerIconChild, navTagId] = getMobileNavType(hamburgerIcon);
    let ariaControl = document.querySelector(`#${navTagId} .navigation__menubar`).getAttribute('id');

    if (currentWidth < 993) {
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

  Drupal.behaviors.mobileMenu = {
    attach: function(settings) {

      window.addEventListener('resize', () => {
        processHamburgerIcons(hamburgerIconButtons);
        currentWidth = getCurrentWidth();
      });

    }
  };

})(Drupal);
