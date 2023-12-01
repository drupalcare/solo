/**
 * @file
 * Solo
 *
 * Filename:     solo-scripts.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal, once) => {
  'use strict';

  // Get current widht
  const getCurrentWidth = () => window.innerWidth || document.documentElement
    .clientWidth || document.body.clientWidth;

  // Add/remove css classes according to screen changes.
  let mediaSize = function() {
    const currentWidth = getCurrentWidth();

    const bodyTag = document.body;

    if (currentWidth >= 993) {

      bodyTag.classList.add('large-screen');
      bodyTag.classList.remove('small-screen', 'medium-screen');

    }

    if ((currentWidth >= 601) && (currentWidth <= 992)) {
      bodyTag.classList.add('medium-screen');
      bodyTag.classList.remove('large-screen', 'small-screen');
    }

    if (currentWidth <= 600) {
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

      // Disable top margin if breadcrumb exist.

      let clickableImages = document.querySelectorAll('a > img');

      clickableImages.forEach(function(clickableImage) {
        clickableImage.parentElement.classList.add('img--is-clickable');
      });

      mediaSize();
      getCurrentWidth();
      window.addEventListener('resize', mediaSize, getCurrentWidth);

    }
  };

})(Drupal, once);
