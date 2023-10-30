/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal, once) => {
  'use strict';

  const loginBlock = document.getElementById('popup-login-block');
  const loginBlockInner = document.querySelector('.popup-login-block-inner');
  const openBlock = document.querySelector('.login-button-open');
  const closeBlock = document.querySelector('.login-button-close');

  const setButtonAttributes = (element, expanded, hidden) => {
    const button = element.querySelector('button');
    button.setAttribute('aria-expanded', expanded);
    button.setAttribute('aria-hidden', hidden);
  }

  const closeSearchHandler = () => {
    loginBlock.style.display = 'none';
    loginBlock.classList.remove('toggled');
    setButtonAttributes(closeBlock, 'false', 'true');
    setButtonAttributes(openBlock, 'false', 'true');
  }

  const openSearchHandler = () => {
    loginBlock.style.display = "block";
    loginBlock.classList.add('toggled');
    setButtonAttributes(openBlock, 'true', 'false');
    setButtonAttributes(closeBlock, 'true', 'false');
  }

  Drupal.behaviors.soloFixedLoginBlock = {
    attach: function (settings) {

      closeBlock?.addEventListener('click', closeSearchHandler);
      openBlock?.addEventListener('click', openSearchHandler);

      //click any where to close any submenu.
      document.addEventListener('click', (event) => {
        if (event.target == loginBlock) {
          closeSearchHandler();
        }
      });

    }
  };

})(Drupal, once);
