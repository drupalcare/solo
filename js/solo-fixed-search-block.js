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

  const mainSideNav = document.getElementById('primary-sidebar-menu');
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

  // Function to toggle the aria-expanded attribute for open/close buttons
  // and aria-hidden for search block
  const setAriaAttributes = (isOpen) => {
    openSearch.forEach(btn => btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false'));
    closeSearch.forEach(btn => btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false'));
    searchBlock.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
  };

  // Function to set tabindex on buttons and inputs inside the search block
  const setTabindexOnElements = (isOpen) => {
    const elements = searchBlock.querySelectorAll('button, input');
    elements.forEach(element => {
      if (isOpen) {
        element.removeAttribute('tabindex');
      } else {
        element.setAttribute('tabindex', '-1');
      }
    });
  };

  const searchBlockToggle = (isOpen) => {
    setAriaAttributes(isOpen);
    setTabindexOnElements(isOpen);

    if (isOpen) {
      searchBlock.classList.add('toggled');
      searchBlock.style.height = 'auto';

      const height = searchBlock.clientHeight + 'px';
      searchBlock.style.height = '0px';

      setTimeout(() => {
        searchBlock.style.height = height;
      }, 10); // Added a small delay

      if (mainSideNav) {
        Drupal.solo.sideMenubarToggleNav(false);
      }
    } else {
      searchBlock.style.height = '0px';
      searchBlock.addEventListener('transitionend', (event) => {
        if (event.propertyName === 'height' && event.target === searchBlock) {
          searchBlock.classList.remove('toggled');
        }
      }, { once: true });
    }
  };

  if (searchBlock) {
    Drupal.solo.searchBlockToggle = searchBlockToggle;

    // Attach these behaviors to the Drupal system
    Drupal.behaviors.soloFixedSearchBlock = {
      attach: function(context, settings) {
        // Close nav button found in page.html.twig in vertical menu region.
        searchBlockCloseOpen('#search-button-close', () => searchBlockToggle(false));

        // Open nav button found in page.html.twig in header region.
        searchBlockCloseOpen('#search-button-open', () => searchBlockToggle(true));

        // Click anywhere outside the search block to close it.
        document.addEventListener('click', (event) => {
          if (searchBlock && searchBlock.classList.contains('toggled')) {
            // Check if the click is outside the search block
            if (!searchBlock.contains(event.target) && !event.target.closest('.search-button-open')) {
              searchBlockToggle(false);
            }
          }
        });

        // Initialize the search block as hidden and non-focusable
        searchBlockToggle(false);
      }
    };
  }

})(Drupal, drupalSettings, once);

