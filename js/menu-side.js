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
  navClickListener('#main-navigation-v #close-nav', () => {
    const verticalNav = document.getElementById('main-navigation-v');
    let cosBtns = document.querySelectorAll('.cos-btn');
    cosBtns?.forEach((cosBtn) => {
      cosBtn.setAttribute('aria-expanded', 'false');
      cosBtn.setAttribute('aria-hidden', 'true');

    })

    verticalNav.style.display = 'none';
    const subMenus = document.querySelectorAll(
      '#main-navigation-v .navigation__menubar li ul.sub__menu');
    subMenus.forEach(Drupal.solo.hideSubMenus);
  });

  // Open nav button found in page.html.twig in header region.
  navClickListener('#open-nav-inner', () => {

    let cosBtns = document.querySelectorAll('.cos-btn');
    cosBtns?.forEach((cosBtn) => {
      cosBtn.setAttribute('aria-expanded', 'true');
      cosBtn.setAttribute('aria-hidden', 'false');

    })

    const verticalNav = document.getElementById('main-navigation-v');
    verticalNav.style.display = 'flex';
  });

})(Drupal, once);
