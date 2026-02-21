/**
 * @file
 * Preloader: remove on DOMContentLoaded; hard timeout 1500ms; once per session.
 * No jQuery. Minimal JS. Overlay blocks interaction while visible; removed after fade-out.
 * When script runs late (after DOM ready), show for at least duration ms so the overlay is visible.
 */

(function (Drupal, once) {
  const SESSION_KEY = 'solo_preloader_seen';
  const HARD_TIMEOUT_MS = 1500;
  const FORCE_SHOW_TIMEOUT_MS = 30000;

  function run() {
    const el = document.getElementById('solo-preloader');
    if (!el) return;

    const duration = parseInt(el.getAttribute('data-duration'), 10) || 600;
    const forceShow = el.getAttribute('data-force-show') === 'true';
    const maxTimeMs = forceShow ? FORCE_SHOW_TIMEOUT_MS : HARD_TIMEOUT_MS;
    const oncePerSession = !forceShow && el.getAttribute('data-once-per-session') !== 'false';

    if (oncePerSession && sessionStorage.getItem(SESSION_KEY) === '1') {
      hide(el);
      return;
    }

    const hideAndMark = () => {
      hide(el);
      if (oncePerSession) sessionStorage.setItem(SESSION_KEY, '1');
    };

    let removed = false;
    function hide(container) {
      if (removed) return;
      removed = true;
      container.classList.add('is-hidden');
      setTimeout(() => {
        if (container.parentNode) container.parentNode.removeChild(container);
      }, 220);
    }

    const maxTime = Math.min(duration, maxTimeMs);
    let done = false;
    const maybeHide = () => {
      if (done) return;
      done = true;
      hideAndMark();
    };

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', maybeHide);
    } else {
      // Script runs after DOM ready (Drupal behaviors): show overlay for full duration so it's visible.
      setTimeout(maybeHide, maxTime);
      return;
    }

    window.setTimeout(maybeHide, maxTimeMs);
  }

  Drupal.behaviors.soloPreloader = {
    attach: function (context, settings) {
      once('solo-preloader', 'body', context).forEach(function () {
        run();
      });
    },
  };
})(Drupal, once);
