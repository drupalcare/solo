/**
 * @file
 * Solo Preloader (Load + Navigation aware).
 *
 * Three responsibilities:
 * 1. Detect real navigation clicks (isRealNavigationClick): left click only, no
 *    modifiers, real href, same origin, not toolbar/excluded. Prevents false
 *    triggers (AJAX, modals, #anchors, admin). Only full page reloads.
 * 2. Show preloader instantly on valid click (showPreloader): remove hidden/
 *    removed classes, set aria-hidden=false, lock scroll (solo-preloader-active
 *    on html). Uses capture phase so overlay appears before browser navigates.
 * 3. Hide when page fully loads: original logic unchanged — window.load, percent
 *    animation, fallback timeout, fade out, remove from layout; unlockPage()
 *    cleans up scroll lock after hide transition.
 *
 * Flow: click → validate → show overlay → browser navigates → new page load →
 * window.load → hide. Does not prevent default, use AJAX, or touch history API.
 * Does not run when library not attached (server-side disabled). Does not
 * trigger on back/forward or programmatic redirects.
 *
 * No minimum display time: hide as soon as window.load fires (depends on page
 * size). Max display is configurable (data-max-display seconds) as fallback
 * if load never fires.
 *
 * Also: force-show mode, reduced motion, once-binding, no duplicate listeners.
 */

(function (Drupal, once) {
  'use strict';

  const DEFAULT_MAX_DISPLAY_MS = 8000;
  const HIDDEN_TRANSITION_MS = 350;
  const FORCE_SHOW_TIMEOUT_MS = 30000;

  function getPreloader() {
    return document.getElementById('solo-preloader');
  }

  function lockPage() {
    document.documentElement.classList.add('solo-preloader-active');
  }

  function unlockPage() {
    document.documentElement.classList.remove('solo-preloader-active');
  }

  function showPreloader(el) {
    if (!el) return;

    const percentEl = el.querySelector('.solo-preloader__percent');
    if (percentEl) percentEl.textContent = '0%';

    el.classList.remove('solo-preloader--hidden');
    el.classList.remove('solo-preloader--removed');
    el.classList.add('solo-preloader--instant-show');
    el.setAttribute('aria-hidden', 'false');

    lockPage();
  }

  function hidePreloader(el, delay) {
    if (!el) return;

    el.classList.add('solo-preloader--hidden');
    el.setAttribute('aria-hidden', 'true');

    const timeout = delay >= 0 ? delay : HIDDEN_TRANSITION_MS;

    window.setTimeout(function () {
      if (el.parentNode) {
        el.classList.add('solo-preloader--removed');
      }
      unlockPage();
    }, timeout);
  }

  function isRealNavigationClick(event, link) {
    if (!link) return false;

    if (event.button !== 0) return false;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;

    const href = link.getAttribute('href');
    if (!href) return false;

    if (href.startsWith('#')) return false;
    if (href.startsWith('javascript:')) return false;
    if (link.hasAttribute('download')) return false;
    if (link.target && link.target !== '_self') return false;

    if (link.origin !== window.location.origin) return false;
    if (link.closest('#toolbar-bar')) return false;
    if (link.hasAttribute('data-solo-preloader-ignore')) return false;

    return true;
  }

  function attachNavigationListener(el) {
    if (document.body.hasAttribute('data-solo-preloader-nav')) return;

    document.body.setAttribute('data-solo-preloader-nav', 'true');

    // Use capture phase so the preloader shows before the link’s default action (navigation).
    // That way the overlay is painted immediately on click, then the page unloads.
    document.addEventListener('click', function (event) {
      const link = event.target.closest('a');
      if (!isRealNavigationClick(event, link)) return;

      showPreloader(el);
    }, true);

    document.addEventListener('submit', function (event) {
      if (event.target && !event.target.closest('#toolbar-bar')) {
        showPreloader(el);
      }
    }, true);
  }

  function run() {
    if (!document.documentElement.classList.contains('solo-preloader-enabled')) {
      return;
    }
    const el = getPreloader();
    if (!el) return;

    attachNavigationListener(el);

    const prefersReducedMotion =
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const transitionDelay = prefersReducedMotion ? 0 : HIDDEN_TRANSITION_MS;

    const maxDisplaySec = parseInt(el.getAttribute('data-max-display'), 10);
    const maxDisplayMs = (Number.isNaN(maxDisplaySec) || maxDisplaySec < 3 || maxDisplaySec > 60)
      ? DEFAULT_MAX_DISPLAY_MS
      : maxDisplaySec * 1000;

    const forceShow = el.getAttribute('data-force-show') === 'true';
    let fallbackTimeoutId = null;

    function clearFallback() {
      if (fallbackTimeoutId !== null) {
        clearTimeout(fallbackTimeoutId);
        fallbackTimeoutId = null;
      }
    }

    const percentEl = el.querySelector('.solo-preloader__percent');
    let percentIntervalId = null;

    function stopPercent() {
      if (percentIntervalId !== null) {
        clearInterval(percentIntervalId);
        percentIntervalId = null;
      }
    }

    function setPercent(value) {
      if (percentEl) {
        percentEl.textContent = Math.min(100, Math.round(value)) + '%';
      }
    }

    function doHide() {
      clearFallback();
      stopPercent();
      window.removeEventListener('load', doHide);
      setPercent(100);
      hidePreloader(el, transitionDelay);
    }

    if (forceShow) {
      if (percentEl) {
        const start = Date.now();
        percentIntervalId = setInterval(function () {
          const elapsed = (Date.now() - start) / FORCE_SHOW_TIMEOUT_MS;
          setPercent(elapsed * 100);
          if (elapsed >= 1) stopPercent();
        }, 100);
      }

      fallbackTimeoutId = window.setTimeout(function () {
        clearFallback();
        stopPercent();
        setPercent(100);
        hidePreloader(el, transitionDelay);
      }, FORCE_SHOW_TIMEOUT_MS);

      return;
    }

    if (document.readyState === 'complete') {
      if (percentEl) setPercent(0);
      hidePreloader(el, transitionDelay);
      return;
    }

    if (percentEl) {
      setPercent(0);
      const start = Date.now();

      percentIntervalId = setInterval(function () {
        const elapsed = (Date.now() - start) / maxDisplayMs;
        setPercent(Math.min(99, elapsed * 95));
      }, 80);
    }

    window.addEventListener('load', doHide);
    fallbackTimeoutId = window.setTimeout(doHide, maxDisplayMs);
  }

  Drupal.behaviors.soloPreloader = {
    attach(context) {
      once('solo-preloader', 'body', context).forEach(run);
    },
  };
})(Drupal, once);
