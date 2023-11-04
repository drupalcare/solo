/**
 * @file
 * Defines Javascript behaviors for the Solo Theme.
 * https://www.drupal.org/node/3158256
 */
((Drupal, once) => {

'use strict';

  /**
   * solo helper functions.
   *
   * @namespace
   */
  Drupal.solo = {};

  const cssStyles = {
    overflow: 'hidden',
    height: '0',
    paddingTop: '0',
    paddingBottom: '0',
    marginTop: '0',
    marginBottom: '0'
  };

  const slideUp = (target, duration = 600) => {
    target.style.transitionProperty = 'height, margin, padding';
    target.style.transitionDuration = `${duration}ms`;
    target.style.boxSizing = 'border-box';
    target.style.height = `${target.offsetHeight}px`;
    target.offsetHeight;
    target.classList.remove('toggled');

    Object.keys(cssStyles)
      .forEach(style => {
        target.style[style] = cssStyles[style];
      });

    setTimeout(() => {
      target.style.display = 'none';
      removeStyles(target);
    }, duration);
  }
  Drupal.solo.slideUp = slideUp;

  const removeStyles = (target) => {
    const stylesToRemove = ['height', 'paddingTop', 'paddingBottom', 'marginTop', 'marginBottom', 'overflow', 'transitionDuration', 'transitionProperty'];
    stylesToRemove.forEach(st => target.style.removeProperty(st));
  }

  Drupal.solo.removeStyles = removeStyles;

  const slideDown = (target, menuDisplay = 'block', duration = 600) => {
    target.style.removeProperty('display');
    let currentDisplay = window.getComputedStyle(target).display;

    if (currentDisplay === 'none') currentDisplay = menuDisplay;

    target.style.display = menuDisplay;
    let height = target.offsetHeight;
    height = Math.round(height);
    Object.keys(cssStyles)
      .forEach(style => {
        target.style[style] = cssStyles[style];
      });

    target.offsetHeight;
    target.style.boxSizing = 'border-box';
    target.style.transitionProperty = "height, margin, padding";
    target.style.transitionDuration = `${duration}ms`;
    target.style.height = `${height}px`;
    target.classList.add('toggled');

    ['paddingTop', 'paddingBottom', 'marginTop', 'marginBottom'].forEach(
      property => {
        target.style.removeProperty(property);
      });

    setTimeout(() => {
      ['height', 'overflow', 'transitionDuration', 'transitionProperty'].forEach(
        property => target.style.removeProperty(property));
    }, duration);
  }

  Drupal.solo.slideDown = slideDown;

  const slideToggle = (target, duration = 500) => {
    if (!target.classList.contains('toggled')) {
      return slideDown(target, duration);
    } else {
      return slideUp(target, duration);
    }
  }
  Drupal.solo.slideToggle = slideToggle;

    // Get current width
  const getCurrentWidth = () => window.innerWidth || document.documentElement
    .clientWidth || document.body.clientWidth;

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
  const addClassAccordingToDepth = (element, depth) => {

    element.classList.add(`ul-${depth}`);
    Array.from(element.children)
      .forEach(child => child.classList.add(`li-${depth}`));
  };

  Drupal.solo.calculateDepth = calculateDepth;

  Drupal.behaviors.soloMenuDepth = {
    attach: (settings) => {
      Array.from(document.querySelectorAll('.solo-inner ul'))
        .forEach((element) => {
          const depth = calculateDepth(element);
          addClassAccordingToDepth(element, depth);
        });
    }
  };

})(Drupal, once);
