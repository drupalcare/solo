/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal, once) => {

  'use strict';

  // Main menu vertical nav start Close or Open.
  const navClickListener = (selector, callback) => {
    const element = document.querySelector(selector);
    if (element) {
      element.addEventListener('click', callback);
    }
  };

  // Close nav button found in page.html.twig in vertical menu region.
  navClickListener('#primary-sidebar-menu #sidebar-button-close', () => {
    const verticalNav = document.getElementById('primary-sidebar-menu');
    let cosBtns = document.querySelectorAll('.sidebar-hamburger-icon');
    cosBtns?.forEach((cosBtn) => {
      cosBtn.setAttribute('aria-expanded', 'false');
      cosBtn.setAttribute('aria-hidden', 'true');

    })

    //verticalNav.style.display = 'none';
    verticalNav.classList.remove('toggled');

    const subMenus = document.querySelectorAll(
      '.navigation__primary__sidebar li ul.sub__menu');
    subMenus.forEach(Drupal.solo.hideSubMenus);

  });

  // Open nav button found in page.html.twig in header region.
  navClickListener('#sidebar-button-open', () => {

    let cosBtns = document.querySelectorAll('.sidebar-hamburger-icon');
    cosBtns?.forEach((cosBtn) => {
      cosBtn.setAttribute('aria-expanded', 'true');
      cosBtn.setAttribute('aria-hidden', 'false');

    })

    const verticalNav = document.getElementById('primary-sidebar-menu');

    verticalNav.classList.add('toggled');
    // verticalNav.style.display = 'flex'
  });

})(Drupal, once);
