/**
 * @file
 * Solo
 *
 * Filename:     solo-scripts.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal, drupalSettings, once) => {
  'use strict';

  // Get current widht
  const getCurrentWidth = () => window.innerWidth || document.documentElement
    .clientWidth || document.body.clientWidth;

  // Add/remove css classes according to screen changes.
  let mediaSize = function() {
    const currentWidth = getCurrentWidth();

    const bodyTag = document.body;

    if (currentWidth >= 992) {

      bodyTag.classList.add('large-screen');
      bodyTag.classList.remove('small-screen', 'medium-screen');

    }

    if ((currentWidth >= 576) && (currentWidth <= 992)) {
      bodyTag.classList.add('medium-screen');
      bodyTag.classList.remove('large-screen', 'small-screen');
    }

    if (currentWidth <= 576) {
      bodyTag.classList.add('small-screen');
      bodyTag.classList.remove('large-screen', 'medium-screen');
    }

  };

  Drupal.behaviors.soloTheme = {
    attach: function(context, settings) {
      // Change the form color to match the footer color.
      const footerFormBg = window.getComputedStyle(document.querySelector(
          '#footer-menu'))
        .backgroundColor;
      const footerFormTxt = window.getComputedStyle(document
          .querySelector('#footer-menu'))
        .color;

      let footerMenu = document.querySelector('#footer-menu');
      let footerMenuForm = document.querySelector('#footer-menu form');

      if (footerMenu && footerMenuForm) {
        footerMenuForm.style.background = footerFormBg;
        footerMenuForm.style.color = footerFormTxt;
      }

      // Remove attribute 'open'
      const detailsElements = document.querySelectorAll('#system-theme-settings details');
      detailsElements.forEach(element => {
        element.removeAttribute('open');
      });

      // Select all <img> and <picture> elements inside an <a> tag, excluding
      // those with a specific class and also excluding those with classes
      // that contain the word 'icon'
      let clickableElements = document.querySelectorAll(
        'a > img:not(.field--name-user-picture img):not([class*="icon"]), ' +
        'a > picture:not(.field--name-user-picture img):not([class*="icon"])' +
        'a > img:not(.field--type-text-long img)' +
        'a > img:not(.field--type-text-with-summary img)' +
        'a > picture:not(.field--type-text-long img)' +
        'a > picture:not(.field--type-text-with-summary img)'
      );

      // Add a class to the parent <a> tag of each selected element
      clickableElements.forEach(function(clickableElement) {
        clickableElement.parentElement.classList.add('img--is-clickable');
      });

      mediaSize();
      getCurrentWidth();
      window.addEventListener('resize', mediaSize, getCurrentWidth);

    }
  };

})(Drupal, drupalSettings, once);
