/**
 * @file
 * Adds toggle functionality to a fixed search block.
 *
 * Filename: solo-fixed-search-block.js
 * Website: https://www.flashwebcenter.com
 * Developer: Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal, drupalSettings, once) => {
  'use strict';

  const searchBlock = document.getElementById('fixed-search-block');
  const openSearch = document.querySelectorAll('.search-button-open>button');
  const closeSearch = document.querySelectorAll('.search-button-close>button');

  // Function to add a click event listener to a specified element
  const searchBlockCloseOpen = (selector, callback) => {
    const element = document.querySelector(selector);
    if (element) {
      element.addEventListener('click', callback);
    }
  };

  // Function to toggle the sidebar menu and update the aria-expanded
  // and aria-hidden attributes of the hamburger icons
  const setAriaAttributes = (element, isOpen) => {
    element.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    element.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
  };

  const searchBlockToggle = (isOpen) => {
    closeSearch?.forEach(cosBtn => setAriaAttributes(cosBtn, isOpen));
    openSearch?.forEach(opnBtn => setAriaAttributes(opnBtn, isOpen));

    if (isOpen) {
      searchBlock.classList.add('toggled');
      searchBlock.style.height = 'auto';

      const height = searchBlock.clientHeight + 'px';
      searchBlock.style.height = '0px';

      setTimeout(() => {
        searchBlock.style.height = height;
      }, 0);
      if (mainSideNav) {
        Drupal.solo.sideMenubarToggleNav(false);
      }
    }
    else {

      searchBlock.style.height = '0px';
      searchBlock.addEventListener('transitionend', (event) => {
        if (event.propertyName === 'height') {
          searchBlock.classList.remove('toggled');

        }
      }, { once: true});

    }
  };

  Drupal.solo.searchBlockToggle = searchBlockToggle;

  // Attach these behaviors to the Drupal system
  Drupal.behaviors.soloFixedSearchBlock = {
    attach: function(settings) {
      // Close nav button found in page.html.twig in vertical menu region.
      searchBlockCloseOpen('#search-button-close', () => searchBlockToggle(false));

      // Open nav button found in page.html.twig in header region.
      searchBlockCloseOpen('#search-button-open', () => searchBlockToggle(true));

      // Click anywhere to close any submenu.
      document.addEventListener('click', (event) => {
        if (event.target == searchBlock) {
          searchBlockToggle(false);
        }
      });

    }
  };

})(Drupal, drupalSettings, once);
