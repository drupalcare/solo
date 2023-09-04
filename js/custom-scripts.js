/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal, once) => {
  'use strict';

  // In case the admin toolbar search installed.
  const adminToolbarSearchTab = document.querySelector(
    '#admin-toolbar-search-tab > div');
  adminToolbarSearchTab?.classList.remove('w3-section');

  // Get current widht
  const getCurrentWidth = () => window.innerWidth || document.documentElement
    .clientWidth || document.body.clientWidth;

  // Add/remove css classes according to screen changes.
  let mediaSize = function() {
    const currentWidth = getCurrentWidth();
    const containers = ['.top-container-inner',
      '.main-container-inner > .w3-clear', '.bottom-container-inner',
      '.footer-container-inner'
    ];
    const bodyTag = document.body;

    const addDisplayClass = (selector) => {
      if (document.querySelectorAll(selector + ' > div')
        .length > 1) {
        document.querySelector(selector)
          .classList.add('w3-f-display');
      }
    };

    const removeDisplayClass = (selectors) => {
      selectors.forEach(selector => {
        document.querySelectorAll(selector)
          .forEach(el => {
            el.classList.remove('w3-f-display');
          });
      });
    };

    if (currentWidth >= 993) {

      bodyTag.classList.add('large-screen');
      bodyTag.classList.remove('small-screen', 'medium-screen');
      containers.forEach(addDisplayClass);

    } else {
      removeDisplayClass(containers);
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


      function addClasses(selector, childSelector, classes, context) {

        const elements = context.querySelectorAll(
          `${selector} ${childSelector}`);
        elements.forEach(element => {
          element.classList.add(...classes.split(' '));
        });
      }

      // Add classes to search page.
      addClasses('.search-form', '.search-advanced',
        'w3-padding-large w3-border w3-bar w3-left-align w3-margin-bottom',
        document);
      addClasses('.search-form', '.search-advanced > summary',
        'w3-button w3-bar w3-left-align', document);
      addClasses('.search-form', '.search-help-link',
        'w3-button w3-right w3-border w3-margin-top', document);
      addClasses('.tabledrag-toggle-weight-wrapper', 'button',
        'w3-button', document);

      // Theme settings.
      addClasses('#system-theme-settings', 'details',
        'w3-border w3-bar w3-left-align w3-margin-bottom', document);
      addClasses('#system-theme-settings', 'details > summary',
        'w3-button w3-bar w3-left-align', document);
      addClasses('#system-theme-settings', 'details > .details-wrapper',
        'w3-padding w3-left-align', document);
      // Remove attribute 'open'

      const detailsElements = document.querySelectorAll(
        '#system-theme-settings details');
      detailsElements.forEach(element => {
        element.removeAttribute('open');
      });

      // Disable top margin if breadcrumb exist.
      let breadcrumb = document.querySelector('nav.breadcrumb');
      let mainBox = document.querySelectorAll('body .main-box');
      let w3Image = document.querySelectorAll('body a > .w3-image');

      if (breadcrumb && mainBox) {
        mainBox.forEach(function(element) {
          element.classList.add('breadcrumb-found');
        });
      }

      w3Image.forEach(function(image) {
        image.parentElement.classList.add('d8-has-image');
      });

      mediaSize();
      getCurrentWidth();
      window.addEventListener('resize', mediaSize, getCurrentWidth);

    }
  };

})(Drupal, once);
