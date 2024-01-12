/**
 * @file
 * Solo
 *
 * Filename:     solo-popup-login-block.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal, once) => {
  'use strict';

  const loginBlock = document.getElementById('popup-login-block');
  const openBlock = document.querySelector('.login-button-open>button');
  const closeBlock = document.querySelector('.login-button-close>button');

  const toggleAriaAttributes = (isOpen) => {
    // Set aria-expanded on the open and close buttons
    openBlock.setAttribute('aria-expanded', isOpen.toString());
    closeBlock.setAttribute('aria-expanded', isOpen.toString());

    // Set aria-hidden on the login block
    loginBlock.setAttribute('aria-hidden', (!isOpen).toString());
  }

  const closeBlockHandler = () => {
    loginBlock.style.display = 'none';
    loginBlock.classList.remove('toggled');
    toggleAriaAttributes(false);
  }

  const openBlockHandler = () => {
    loginBlock.style.display = "block";
    loginBlock.classList.add('toggled');
    toggleAriaAttributes(true);
  }

  Drupal.behaviors.soloFixedLoginBlock = {
    attach: function (settings) {
      closeBlock?.addEventListener('click', closeBlockHandler);
      openBlock?.addEventListener('click', openBlockHandler);

      // Click anywhere to close the login block.
      document.addEventListener('click', (event) => {
        if (event.target === loginBlock) {
          closeBlockHandler();
        }
      });
    }
  };

})(Drupal, once);

