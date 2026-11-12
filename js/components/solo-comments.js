/**
 * @file
 * Comment replies toggle behavior.
 */

(function (Drupal, once) {
  /**
   * Initialize show/hide button for comment replies.
   *
   * @param {Element} comments
   *   The comment wrapper element.
   */
  function init(comments) {
    // Mark comments that have children
    comments
      .querySelectorAll('[data-drupal-selector="comment"]')
      .forEach(function (comment) {
        if (comment.nextElementSibling?.matches('.indented')) {
          comment.classList.add('has-children');
        }
      });

    // Add toggle button for each indented group
    comments.querySelectorAll('.indented').forEach(function (commentGroup) {
      const parentComment = commentGroup.previousElementSibling;
      if (!parentComment) {
        return;
      }

      const footer = parentComment.querySelector('.solo-comment__footer');
      if (!footer) {
        return;
      }

      // Count replies
      const replyCount = commentGroup.querySelectorAll(
        '[data-drupal-selector="comment"]',
      ).length;

      // Create toggle button wrapper
      const toggleWrapper = document.createElement('div');
      toggleWrapper.setAttribute('class', 'solo-comment__replies-wrapper');

      // Create toggle button
      const toggleButton = document.createElement('button');
      toggleButton.setAttribute('type', 'button');
      toggleButton.setAttribute(
        'class',
        'solo-comment__replies-toggle solo-button',
      );
      toggleButton.setAttribute('aria-expanded', 'true');
      // Deprecated in 2.0, removed in the next major: data-comment-id.
      const commentId =
        parentComment.getAttribute('data-solo-comment-id') ??
        parentComment.getAttribute('data-comment-id');
      toggleButton.setAttribute(
        'aria-controls',
        `solo-comment-replies-${commentId}`,
      );

      // Create icon wrapper for open state (minus icon)
      const iconOpen = document.createElement('span');
      iconOpen.setAttribute(
        'class',
        'solo-comment__replies-icon solo-comment__replies-icon--open',
      );
      iconOpen.innerHTML =
        '<svg fill="var(--r-text)" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><path d="M240-440v-80h480v80H240Z"/></svg>';

      // Create icon wrapper for close state (plus icon)
      const iconClose = document.createElement('span');
      iconClose.setAttribute(
        'class',
        'solo-comment__replies-icon solo-comment__replies-icon--close',
      );
      iconClose.setAttribute('hidden', '');
      iconClose.innerHTML =
        '<svg fill="var(--r-text)" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><path d="M440-80v-340H120v-120h320v-340h80v340h320v120H520v340h-80Z"/></svg>';

      // Create text
      const text = document.createElement('span');
      text.setAttribute('class', 'solo-comment__replies-text');
      text.textContent = `${Drupal.t('Replies')} (${replyCount})`;

      // Append elements
      toggleButton.appendChild(iconOpen);
      toggleButton.appendChild(iconClose);
      toggleButton.appendChild(text);
      toggleWrapper.appendChild(toggleButton);
      footer.appendChild(toggleWrapper);

      // Set ID for aria-controls
      commentGroup.id = `solo-comment-replies-${commentId}`;

      // Add click event using Solo slideUp/slideDown
      toggleButton.addEventListener('click', function (e) {
        e.preventDefault();
        const isExpanded =
          toggleButton.getAttribute('aria-expanded') === 'true';

        if (isExpanded) {
          // Close replies
          Drupal.solo.slideUp(
            commentGroup,
            Drupal.solo.animations.slideUp,
            'comments',
          );
          toggleButton.setAttribute('aria-expanded', 'false');
        } else {
          // Open replies
          Drupal.solo.slideDown(
            commentGroup,
            Drupal.solo.animations.slideDown,
            'block',
            'comments',
          );
          toggleButton.setAttribute('aria-expanded', 'true');
        }
      });
    });
  }

  // Deprecated in 2.0, removed in the next major: the old behavior name
  // commentRepliesToggle reads and writes Drupal.behaviors.soloComments.
  // It is not enumerable, so Drupal.attachBehaviors() runs it once.
  if (!Object.prototype.hasOwnProperty.call(Drupal.behaviors, 'commentRepliesToggle')) {
    Object.defineProperty(Drupal.behaviors, 'commentRepliesToggle', {
      configurable: true,
      get: () => Drupal.behaviors.soloComments,
      set: (value) => {
        Drupal.behaviors.soloComments = value;
      },
    });
  }

  /**
   * Toggle threaded comment replies visibility.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.soloComments = {
    attach(context) {
      once(
        'solo-comment-replies-toggle',
        '[data-drupal-selector="comments"]',
        context,
      ).forEach(init);
    },
  };
})(Drupal, once);
