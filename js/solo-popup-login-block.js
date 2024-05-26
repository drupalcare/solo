/**
 * @file
 * Solo
 *
 * Filename:     solo-popup-login-block.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal, drupalSettings, once) => {
  'use strict';

  Drupal.behaviors.soloFixedLoginBlock = {
    attach: function (settings) {
      const loginBlock = document.getElementById('popup-login-block');
      const openBlock = document.querySelector('.login-button-open>button');
      const closeBlock = document.querySelector('.login-button-close>button');

      // Function to set tabindex on buttons and inputs inside the login block
      const setTabindexOnElements = (isOpen) => {
        const elements = loginBlock.querySelectorAll('button, input');
        elements.forEach(element => {
          if (isOpen) {
            element.removeAttribute('tabindex');
          } else {
            element.setAttribute('tabindex', '-1');
          }
        });
      };

      // Proceed only if loginBlock is found
      if (loginBlock) {
        const toggleAriaAttributes = (isOpen) => {
          // Set aria-expanded on the open and close buttons if they exist
          openBlock?.setAttribute('aria-expanded', isOpen.toString());
          closeBlock?.setAttribute('aria-expanded', isOpen.toString());

          // Set aria-hidden on the login block
          loginBlock.setAttribute('aria-hidden', (!isOpen).toString());

          // Set tabindex on buttons and inputs inside the login block
          setTabindexOnElements(isOpen);
        };

        const closeBlockHandler = () => {
          loginBlock.style.display = 'none';
          loginBlock.classList.remove('toggled');
          toggleAriaAttributes(false);
        };

        const openBlockHandler = () => {
          loginBlock.style.display = 'block';
          loginBlock.classList.add('toggled');
          toggleAriaAttributes(true);
        };

        closeBlock?.addEventListener('click', closeBlockHandler);
        openBlock?.addEventListener('click', openBlockHandler);

        // Click anywhere to close the login block, excluding clicks within the loginBlock itself.
        document.addEventListener('click', (event) => {
          if (!loginBlock.contains(event.target) && !event.target.closest('.login-button-open')) {
            closeBlockHandler();
          }
        }, true);

        // Initialize the login block as hidden and non-focusable
        closeBlockHandler();
      }
    }
  };

})(Drupal, drupalSettings, once);


