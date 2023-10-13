/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal, once) => {
  'use strict';


  Drupal.behaviors.soloFixedSearchBlock = {
    attach: function(context, settings) {


      const mainSideNav = document.querySelector('.primary-sidebar-menu .navigation-sidebar ul');

      const searchBlock = document.getElementById('fixed-search-block');
      const openSearch = document.querySelector('.search-button-open');
      const closeSearch = document.querySelector('.search-button-close');

      const removeActiveClass = () => {
        searchBlock.classList.remove('toggled');
      }

      const closeSearchHandler = () => {
        searchBlock.style.height = "0px";
        searchBlock.addEventListener('transitionend', removeActiveClass, {once: true});
      }

      const openSearchHandler = () => {
        // close sidenave if found.
        if (mainSideNav ) {
        Drupal.solo.sideMenubarToggleNav(false);
        }

        searchBlock.classList.add('toggled');
        searchBlock.style.height = "auto";
        let height = searchBlock.clientHeight + "px";
        searchBlock.style.height = "0px";
        setTimeout(() => {
          searchBlock.style.height = height;
        }, 0);
      }

      closeSearch?.addEventListener('click', closeSearchHandler);
      openSearch?.addEventListener('click', openSearchHandler);


    }
  };

})(Drupal, once);
