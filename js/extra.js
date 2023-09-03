/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal, once) => {
  'use strict';

  // Cache the selectors
  const socialMediaIcons = [...document.querySelectorAll(
    '.d-inner .social-media > a > i')];
  socialMediaIcons.forEach(icon => {
    icon.classList.remove('fa');
  });

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

  const addClassAccordingToDepth = (element, depth) => {

    element.classList.add(`ul-${depth}`);
    Array.from(element.children)
      .forEach(child => child.classList.add(`li-${depth}`));
  };

  const calculateDepth = (element) => {
    let depth = 1;
    let parent = element.parentElement;

    while (parent) {
      if (parent.tagName.toLowerCase() === 'ul') {
        depth++;
      }
      parent = parent.parentElement;
    }
    return depth;
  };

  Drupal.behaviors.soloMenuDepth = {
    attach: (settings) => {
      Array.from(document.querySelectorAll('.d-inner ul'))
        .forEach((element) => {
          const depth = calculateDepth(element);
          addClassAccordingToDepth(element, depth);
        });
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

      // apply background and color to opened dialog.
      let layoutBuilderBox = function() {
        if (document.querySelector('.ui-dialog') !== null && document
          .querySelector('.ui-dialog')
          .style.display !== 'none') {

          const pageTitle = document.querySelector('#page-title');
          const pageTitleBg = window.getComputedStyle(pageTitle)
            .getPropertyValue('background-color');

          const pageTitleH1 = document.querySelector('#page-title h1');
          const pageTitleTxt = window.getComputedStyle(pageTitleH1)
            .getPropertyValue('color');

          const mainContainer = document.querySelector(
            '#main-container');
          const mainContainerBg = window.getComputedStyle(mainContainer)
            .getPropertyValue('background-color');

          const mainContainerP = document.querySelector(
            '#main-container p');
          const mainContainerTxt = window.getComputedStyle(
              mainContainerP)
            .getPropertyValue('color');

          let dialogTitlebar = document.querySelector(
            '.ui-dialog .ui-dialog-titlebar');

          if (dialogTitlebar) {
            dialogTitlebar.style.background = pageTitleBg;
            dialogTitlebar.style.color = pageTitleTxt;
          }

          let body = document.querySelector('body .ui-dialog');

          if (body) {
            body.style.background = mainContainerBg;
            body.style.color = mainContainerTxt;
          }

          let dialogContent = document.querySelector(
            '.ui-dialog .ui-dialog-content');

          if (dialogContent) {
            dialogContent.style.background = mainContainerBg;
            dialogContent.style.color = mainContainerTxt;
          }

          let dialogButtonpane = document.querySelector(
            '.ui-dialog .ui-dialog-buttonpane');

          if (dialogButtonpane) {
            dialogButtonpane.style.background = footerFormBg;
            dialogButtonpane.style.color = footerFormTxt;
          }

        }
      } // End of layoutBuilderBox

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
