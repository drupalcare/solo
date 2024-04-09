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

  const checkRegionsWidth = () => {
    const regions = document.querySelectorAll('.region-inner, .copyright-inner, .footer-menu-inner');
    regions.forEach(region => {
      const regionWidth = region.offsetWidth;

      // Remove all previous size classes to prevent class duplication
      region.classList.remove('region-xs', 'region-s', 'region-m', 'region-l', 'region-xl', 'region-xxl');

      // Assign new class based on region width
      if (regionWidth <= 320) {
        region.classList.add('region-xs'); // Extra Small Devices
      } else if (regionWidth > 320 && regionWidth <= 600) {
        region.classList.add('region-s'); // Small Devices
      } else if (regionWidth > 600 && regionWidth <= 768) {
        region.classList.add('region-m'); // Medium Devices
      } else if (regionWidth > 768 && regionWidth <= 992) {
        region.classList.add('region-l'); // Large Devices
      } else if (regionWidth > 992 && regionWidth <= 1200) {
        region.classList.add('region-xl'); // Extra Large Devices
      } else if (regionWidth > 1200) {
        region.classList.add('region-xxl'); // Extra Extra Large Devices
      }
    });
  };

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

    checkRegionsWidth();

  };

  Drupal.behaviors.soloTheme = {
    attach: function(context, settings) {
      // Ensure code only runs once per element
      const footerMenu = document.querySelector('#footer-menu', context);
      if (footerMenu) {
        const footerFormBg = window.getComputedStyle(footerMenu).backgroundColor;
        const footerFormTxt = window.getComputedStyle(footerMenu).color;
        let footerMenuForm = document.querySelector('#footer-menu form', context);

        if (footerMenuForm) {
          footerMenuForm.style.background = footerFormBg;
          footerMenuForm.style.color = footerFormTxt;
        }
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
        'a > img:not(.field--name-user-picture):not([class*="icon"]),' +
        'a > picture:not(.field--name-user-picture):not([class*="icon"]),' +
        'a > img:not(.field--type-text-long):not(.field--type-text-with-summary),' +
        'a > picture:not(.field--type-text-long):not(.field--type-text-with-summary)'
      );

      // Add a class to the parent <a> tag of each selected element
      clickableElements.forEach(function(clickableElement) {
        clickableElement.parentElement.classList.add('img--is-clickable');
      });

      mediaSize();
      window.addEventListener('resize', () => {
        mediaSize();
      });


    }
  };

})(Drupal, drupalSettings, once);
