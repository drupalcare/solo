/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal) => {

  'use strict';
  // Apply background color to all site submenus. The region background color
  // will be used. Ex. if the submenu in header region then the heaser background
  // color will be applied to this submenu.

  const siteSubMenus = document.querySelectorAll('.solo-inner nav .navigation__menubar ul');
  const getParentBg = (el) => {
    const closestParent = el.closest('.page-wrapper>div') ?? el.closest('.page-wrapper>header');
    if (closestParent) {
      let parentBg = window.getComputedStyle(closestParent).backgroundColor;
      return parentBg;
    }
  }
  siteSubMenus.forEach(el => el.style.backgroundColor = getParentBg(el));

  // Apply static position to the main menu on scroll so it will be sticky
  // on the top.
  let origOffsetY;
  const mainNavigation = document.querySelector('#primary-menu');
  if (mainNavigation && mainNavigation.querySelector('.solo-inner .navigation__menubar')) {
    origOffsetY = mainNavigation.offsetTop;
  }

  const scrollWindow = () => {
    if (mainNavigation) {
      mainNavigation.classList.toggle('solo-sticky', window.scrollY >
        origOffsetY);
    }
  };

  Drupal.behaviors.globalMenu = {
    attach: function(settings) {

      window.addEventListener('scroll', scrollWindow);

    }
  };

})(Drupal);
