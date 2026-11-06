/**
 * @file
 * Back to top: IntersectionObserver on sentinel; smooth scroll on click.
 * Drupal 11: Drupal.behaviors, once(), no jQuery. Toggles aria-hidden for a11y.
 *
 * @see partials/back-to-top.html.twig
 */
(function (Drupal, once) {
  function run(button) {
    const sentinel = document.getElementById('solo-back-to-top-sentinel');
    if (!sentinel || !button) {
      return;
    }

    function setVisible(visible) {
      if (visible) {
        button.classList.add('solo-is-visible');
        button.setAttribute('aria-hidden', 'false');
      } else {
        button.classList.remove('solo-is-visible');
        button.setAttribute('aria-hidden', 'true');
      }
    }

    const observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          setVisible(!entry.isIntersecting);
        });
      },
      { root: null, rootMargin: '0px', threshold: 0 },
    );
    observer.observe(sentinel);

    button.addEventListener('click', function (e) {
      e.preventDefault();
      const motionOk = !window.matchMedia('(prefers-reduced-motion: reduce)')
        .matches;
      window.scrollTo({ top: 0, behavior: motionOk ? 'smooth' : 'instant' });
      // The button hides once the top is reached; move focus to the top of
      // the page first, so the next Tab starts there instead of from a
      // hidden button (WCAG 2.4.3).
      const top = document.getElementById('solo-page-wrapper') || document.body;
      if (!top.hasAttribute('tabindex')) {
        top.setAttribute('tabindex', '-1');
        top.addEventListener(
          'blur',
          function () {
            top.removeAttribute('tabindex');
          },
          { once: true },
        );
      }
      top.focus({ preventScroll: true });
    });
  }

  Drupal.behaviors.soloBackToTop = {
    attach(context, settings) {
      once('solo-back-to-top', '#solo-back-to-top', context).forEach(run);
    },
  };
})(Drupal, once);
