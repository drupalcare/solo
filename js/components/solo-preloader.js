/**
 * @file
 * Preloader: load-based. Hides when the page has actually loaded (window.load).
 * No minimum display time. Late-script safe (checks document.readyState).
 * Mandatory timeout fallback for hung resources. Accessibility: hidden state
 * in class .solo-preloader--removed. Respects prefers-reduced-motion (no transition delay when set).
 */

(function (Drupal, once) {
  'use strict';

  const FALLBACK_TIMEOUT_MS = 8000;
  const HIDDEN_TRANSITION_MS = 400;
  const FORCE_SHOW_TIMEOUT_MS = 30000;

  function getPreloader() {
    return document.getElementById('solo-preloader');
  }

  function hidePreloader(el, transitionDelayMs) {
    if (!el) return;

    el.classList.add('solo-preloader--hidden');
    el.setAttribute('aria-hidden', 'true');

    const delay = transitionDelayMs >= 0 ? transitionDelayMs : HIDDEN_TRANSITION_MS;
    window.setTimeout(function () {
      if (el.parentNode) {
        el.classList.add('solo-preloader--removed');
      }
    }, delay);
  }

  function run() {
    const el = getPreloader();
    if (!el) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const transitionDelay = prefersReducedMotion ? 0 : HIDDEN_TRANSITION_MS;

    const forceShow = el.getAttribute('data-force-show') === 'true';
    let fallbackTimeoutId = null;

    function clearFallback() {
      if (fallbackTimeoutId !== null) {
        clearTimeout(fallbackTimeoutId);
        fallbackTimeoutId = null;
      }
    }

    function doHide() {
      clearFallback();
      window.removeEventListener('load', doHide);
      hidePreloader(el, transitionDelay);
    }

    if (forceShow) {
      fallbackTimeoutId = window.setTimeout(function () {
        clearFallback();
        hidePreloader(el, transitionDelay);
      }, FORCE_SHOW_TIMEOUT_MS);
      return;
    }

    if (document.readyState === 'complete') {
      hidePreloader(el, transitionDelay);
      return;
    }

    window.addEventListener('load', doHide);
    fallbackTimeoutId = window.setTimeout(doHide, FALLBACK_TIMEOUT_MS);
  }

  Drupal.behaviors.soloPreloader = {
    attach: function (context, settings) {
      once('solo-preloader', 'body', context).forEach(run);
    },
  };
})(Drupal, once);
