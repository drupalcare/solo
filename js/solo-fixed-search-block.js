/**
 * @file
 * Solo
 *
 * Filename:     solo-fixed-search-block.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal, once) => {
  'use strict';

  const mainSideNav = document.querySelector('.primary-sidebar-menu .navigation-sidebar ul');
  const searchBlock = document.getElementById('fixed-search-block');
  const openSearch = document.querySelector('.search-button-open');
  const closeSearch = document.querySelector('.search-button-close');

  const setButtonAttributes = (element, expanded, hidden) => {
    const button = element.querySelector('button');
    button.setAttribute('aria-expanded', expanded);
    button.setAttribute('aria-hidden', hidden);
  };

  const removeActiveClass = () => {
    searchBlock.classList.remove('toggled');
    setButtonAttributes(closeSearch, 'false', 'true');
    setButtonAttributes(openSearch, 'false', 'true');
  };

  const closeSearchHandler = () => {
    searchBlock.style.height = "0px";
    searchBlock.addEventListener('transitionend', removeActiveClass, {
      once: true
    });
  };

  const openSearchHandler = () => {
    if (mainSideNav) {
      Drupal.solo.sideMenubarToggleNav(false);
    }

    searchBlock.classList.add('toggled');
    setButtonAttributes(openSearch, 'true', 'false');
    setButtonAttributes(closeSearch, 'true', 'false');

    searchBlock.style.height = "auto";
    let height = searchBlock.clientHeight + "px";
    searchBlock.style.height = "0px";

    setTimeout(() => {
      searchBlock.style.height = height;
    }, 0);
  };

  Drupal.behaviors.soloFixedSearchBlock = {
    attach: function(settings) {
      closeSearch?.addEventListener('click', closeSearchHandler);
      openSearch?.addEventListener('click', openSearchHandler);

      //click any where to close any submenu.
      document.addEventListener('click', (event) => {
        if (event.target == searchBlock) {
          closeSearchHandler();
        }
      });
    }
  };

})(Drupal, once);
