/**
 * @file
 * Adds toggle functionality to a fixed search block with Drupal best practices.
 *
 * Filename: solo-fixed-search-block.js
 * Website: https://www.flashwebcenter.com
 * Developer: Alaa Haddad https://www.alaahaddad.com
 *
 * Dependencies:
 * - core/drupal
 * - core/drupalSettings
 * - core/once
 */

(function (Drupal, drupalSettings, once) {
  /**
   * Solo theme namespace for public APIs.
   * @namespace
   */
  Drupal.solo = Drupal.solo || {};

  /**
   * Search block toggle functionality.
   *
   * @type {Object}
   * @property {boolean} isOpen - Current state of the search block
   * @property {number} animationId - Current animation frame ID
   */
  Drupal.solo.searchBlock = {
    isOpen: false,
    animationId: null,
  };

  /**
   * Main behavior for the fixed search block functionality.
   */
  Drupal.behaviors.soloFixedSearchBlock = {
    /**
     * Attach the search block toggle behavior.
     *
     * @param {HTMLElement} context - The context element
     * @param {Object} settings - Drupal settings object
     */
    attach(context, settings) {
      // Initialize search block functionality with once() to prevent duplicates
      once(
        'solo-fixed-search-block',
        '[id="solo-fixed-search-block"]',
        context,
      ).forEach((searchBlock) => {
        try {
          this.initializeSearchBlock(searchBlock, context);
        } catch (error) {
          this.handleError('Failed to initialize search block', error);
        }
      });

      // Attach open button handlers
      once(
        'solo-fixed-search-block-open',
        // Deprecated in 2.0, removed in the next major: old class alias.
        '.solo-fixed-search-block__open > button, .solo-fixed-search__open > button',
        context,
      ).forEach((button) => {
        this.attachOpenButtonHandler(button);
      });
    },

    /**
     * Detach the search block behavior.
     *
     * @param {HTMLElement} context - The context element
     * @param {Object} settings - Drupal settings object
     * @param {string} trigger - The trigger for the detach ('unload', 'move', 'serialize')
     */
    detach(context, settings, trigger) {
      if (trigger === 'unload') {
        // Clean up event listeners and animations
        once.remove(
          'solo-fixed-search-block',
          '[id="solo-fixed-search-block"]',
          context,
        );
        once.remove(
          'solo-fixed-search-block-open',
          // Deprecated in 2.0, removed in the next major: old class alias.
          '.solo-fixed-search-block__open > button, .solo-fixed-search__open > button',
          context,
        );
        once.remove(
          'solo-fixed-search-block-close',
          // Deprecated in 2.0, removed in the next major: old class alias.
          '.solo-fixed-search-block__close > button, .solo-fixed-search__close > button',
          context,
        );
        once.remove('solo-search-outside-click', 'body', document);

        // Remove the open-state document listeners.
        this.setDocumentListeners(false);

        // Cancel any pending animations
        if (Drupal.solo.searchBlock.animationId) {
          cancelAnimationFrame(Drupal.solo.searchBlock.animationId);
        }
      }
    },

    /**
     * Initialize the search block with all necessary handlers.
     *
     * @param {HTMLElement} searchBlock - The search block element
     * @param {HTMLElement} context - The context element
     */
    initializeSearchBlock(searchBlock, context) {
      const closeButton = searchBlock.querySelector(
        // Deprecated in 2.0, removed in the next major: old class alias.
        '.solo-fixed-search-block__close > button, .solo-fixed-search__close > button',
      );

      if (!searchBlock) {
        this.logWarning('Search block element not found');
        return;
      }

      // Cache elements for performance
      const cache = this.createElementCache(searchBlock);

      // Expose public API method
      Drupal.solo.searchBlockToggle = this.createToggleFunction(
        searchBlock,
        cache,
      );

      // Attach close button handler
      if (closeButton) {
        once('solo-fixed-search-block-close', closeButton).forEach((button) => {
          this.attachCloseButtonHandler(button, searchBlock, cache);
        });
      }

      // Add keyboard trap handler
      this.attachKeyboardTrap(searchBlock);

      // Handlers added to document only while the block is open.
      this.createDocumentHandlers();

      // Add outside click handler
      this.attachOutsideClickHandler(searchBlock, context);

      // Set initial ARIA attributes based on current DOM state.
      const initiallyOpen = searchBlock.classList.contains('solo-is-open');
      Drupal.solo.searchBlock.isOpen = initiallyOpen;
      this.setDocumentListeners(initiallyOpen);
      this.setInitialAriaAttributes(searchBlock, cache);
      this.setAriaAttributes(initiallyOpen, searchBlock, cache);
      this.setTabindexOnElements(initiallyOpen, cache.focusableElements);
    },

    /**
     * Create a cache of commonly accessed elements.
     *
     * @param {HTMLElement} searchBlock - The search block element
     * @returns {Object} Cache object with element references
     */
    createElementCache(searchBlock) {
      return {
        openButtons: document.querySelectorAll(
          // Deprecated in 2.0, removed in the next major: old class alias.
          '.solo-fixed-search-block__open > button, .solo-fixed-search__open > button',
        ),
        closeButton: searchBlock.querySelector(
          // Deprecated in 2.0, removed in the next major: old class alias.
          '.solo-fixed-search-block__close > button, .solo-fixed-search__close > button',
        ),
        inputs: searchBlock.querySelectorAll('input'),
        focusableElements: searchBlock.querySelectorAll(
          'button, input, [tabindex]:not([tabindex="-1"])',
        ),
        mainSideNav: document.getElementById('solo-primary-sidebar-menu'),
      };
    },

    /**
     * Create the toggle function with proper error handling.
     *
     * @param {HTMLElement} searchBlock - The search block element
     * @param {Object} cache - Cached element references
     * @returns {Function} The toggle function
     */
    createToggleFunction(searchBlock, cache) {
      const self = this;
      return function (isOpen) {
        try {
          self.toggleSearchBlock(isOpen, searchBlock, cache);
        } catch (error) {
          self.handleError('Failed to toggle search block', error);
        }
      };
    },

    /**
     * Toggle the search block open/closed state.
     *
     * @param {boolean} isOpen - Whether to open or close
     * @param {HTMLElement} searchBlock - The search block element
     * @param {Object} cache - Cached element references
     */
    toggleSearchBlock(isOpen, searchBlock, cache) {
      if (!searchBlock) {
        this.logWarning('Cannot toggle: search block not found');
        return;
      }

      // Tell other overlays (the login popup) to close before this opens.
      if (isOpen) {
        searchBlock.dispatchEvent(
          new CustomEvent('solo:searchBlockOpen', {
            detail: { searchBlock },
            bubbles: true,
          }),
        );
      }

      // Update state
      Drupal.solo.searchBlock.isOpen = isOpen;

      // Document listeners exist only while the block is open.
      this.setDocumentListeners(isOpen);

      // Set ARIA attributes
      this.setAriaAttributes(isOpen, searchBlock, cache);

      // Set tabindex on interactive elements
      this.setTabindexOnElements(isOpen, cache.focusableElements);

      if (isOpen) {
        this.openSearchBlock(searchBlock, cache);
      } else {
        this.closeSearchBlock(searchBlock);
      }

      // Announce state change to screen readers
      this.announceStateChange(isOpen);
    },

    /**
     * Open the search block with animation.
     *
     * @param {HTMLElement} searchBlock - The search block element
     * @param {Object} cache - Cached element references
     */
    openSearchBlock(searchBlock, cache) {
      searchBlock.classList.add('solo-is-open');

      // Prepare for animation
      const targetHeight = this.calculateHeight(searchBlock);
      searchBlock.style.height = '0px';

      // Force reflow
      searchBlock.getBoundingClientRect();

      // Animate open using requestAnimationFrame
      if (Drupal.solo.searchBlock.animationId) {
        cancelAnimationFrame(Drupal.solo.searchBlock.animationId);
        Drupal.solo.searchBlock.animationId = null;
      }
      Drupal.solo.searchBlock.animationId = requestAnimationFrame(() => {
        searchBlock.style.transition = 'height 0.3s ease-in-out';
        searchBlock.style.height = `${targetHeight}px`;
      });

      // Finish on transitionend, or after a fallback delay when no height
      // transition runs (reduced motion, unchanged height, hidden tab), so
      // the input always receives focus (WCAG 2.4.3).
      let openDone = false;
      let openTimer = null;
      let onOpenEnd = null;
      const finishOpen = () => {
        if (openDone) return;
        openDone = true;
        clearTimeout(openTimer);
        searchBlock.removeEventListener('transitionend', onOpenEnd);
        if (!searchBlock.classList.contains('solo-is-open')) return;
        searchBlock.style.height = 'auto';

        // Focus first input after animation
        this.focusFirstInput(cache.inputs);
      };
      onOpenEnd = (event) => {
        if (event.propertyName === 'height') {
          finishOpen();
        }
      };
      searchBlock.addEventListener('transitionend', onOpenEnd);
      openTimer = setTimeout(finishOpen, 400);

      // Close sidebar if present
      if (
        cache.mainSideNav &&
        typeof Drupal.solo.sideMenubarToggleNav === 'function'
      ) {
        try {
          Drupal.solo.sideMenubarToggleNav(false);
        } catch (error) {
          this.logWarning('Failed to close sidebar', error);
        }
      }
    },

    /**
     * Close the search block with animation.
     *
     * @param {HTMLElement} searchBlock - The search block element
     */
    closeSearchBlock(searchBlock) {
      // Get current height before closing
      searchBlock.style.height = getComputedStyle(searchBlock).height;

      // Force reflow
      searchBlock.getBoundingClientRect();

      // Animate close
      if (Drupal.solo.searchBlock.animationId) {
        cancelAnimationFrame(Drupal.solo.searchBlock.animationId);
        Drupal.solo.searchBlock.animationId = null;
      }
      Drupal.solo.searchBlock.animationId = requestAnimationFrame(() => {
        searchBlock.style.height = '0px';
      });

      // Return focus to the element that opened the search now, not after
      // the animation: the panel is already inert, so focus left inside it
      // would drop to the page body (WCAG 2.4.3).
      if (Drupal.solo.searchBlock.triggerElement) {
        try {
          Drupal.solo.searchBlock.triggerElement.focus();
        } catch (e) {
          // no-op
        }
        Drupal.solo.searchBlock.triggerElement = null;
      }

      // Finish on transitionend, or after a fallback delay when no height
      // transition runs, so the panel never stays marked open.
      let closeDone = false;
      let closeTimer = null;
      let onCloseEnd = null;
      const finishClose = () => {
        if (closeDone) return;
        closeDone = true;
        clearTimeout(closeTimer);
        searchBlock.removeEventListener('transitionend', onCloseEnd);
        if (searchBlock.getAttribute('aria-hidden') === 'false') return;
        searchBlock.classList.remove('solo-is-open');
        searchBlock.style.height = '';
      };
      onCloseEnd = (event) => {
        if (event.propertyName === 'height') {
          finishClose();
        }
      };
      searchBlock.addEventListener('transitionend', onCloseEnd);
      closeTimer = setTimeout(finishClose, 400);
    },

    /**
     * Calculate the natural height of an element.
     *
     * @param {HTMLElement} element - The element to measure
     * @returns {number} The natural height in pixels
     */
    calculateHeight(element) {
      const originalHeight = element.style.height;
      element.style.height = 'auto';
      const height = element.clientHeight;
      element.style.height = originalHeight;
      return height;
    },

    /**
     * Set ARIA attributes for accessibility.
     *
     * @param {boolean} isOpen - Whether the search block is open
     * @param {HTMLElement} searchBlock - The search block element
     * @param {Object} cache - Cached element references
     */
    setAriaAttributes(isOpen, searchBlock, cache) {
      const expandedValue = isOpen ? 'true' : 'false';
      const hiddenValue = isOpen ? 'false' : 'true';

      // Update open buttons
      if (cache.openButtons) {
        cache.openButtons.forEach((btn) => {
          if (btn) btn.setAttribute('aria-expanded', expandedValue);
        });
      }

      // Update close button
      if (cache.closeButton) {
        cache.closeButton.setAttribute('aria-expanded', expandedValue);
      }

      // Update search block
      searchBlock.setAttribute('aria-hidden', hiddenValue);
      if (isOpen) {
        Drupal.solo.setInert(searchBlock, false);
      } else {
        Drupal.solo.setInert(searchBlock, true);
      }
    },

    /**
     * Set initial ARIA attributes.
     *
     * @param {HTMLElement} searchBlock - The search block element
     * @param {Object} cache - Cached element references
     */
    setInitialAriaAttributes(searchBlock, cache) {
      // Set initial state as closed
      this.setAriaAttributes(false, searchBlock, cache);

      // Add ARIA labels if missing
      if (cache.openButtons) {
        cache.openButtons.forEach((btn) => {
          if (!btn.getAttribute('aria-label')) {
            btn.setAttribute('aria-label', Drupal.t('Open search'));
          }
        });
      }

      if (cache.closeButton && !cache.closeButton.getAttribute('aria-label')) {
        cache.closeButton.setAttribute('aria-label', Drupal.t('Close search'));
      }
    },

    /**
     * Set tabindex on focusable elements.
     *
     * @param {boolean} isOpen - Whether the search block is open
     * @param {NodeList} elements - The focusable elements
     */
    setTabindexOnElements(isOpen, elements) {
      if (!elements) return;

      elements.forEach((element) => {
        if (element) {
          if (isOpen) {
            element.removeAttribute('tabindex');
          } else {
            element.setAttribute('tabindex', '-1');
          }
        }
      });
    },

    /**
     * Focus the first input field.
     *
     * @param {NodeList} inputs - The input elements
     */
    focusFirstInput(inputs) {
      if (inputs && inputs.length > 0 && inputs[0]) {
        // Small delay to ensure animation has started
        setTimeout(() => {
          try {
            inputs[0].focus();
          } catch (error) {
            this.logWarning('Failed to focus input', error);
          }
        }, 100);
      }
    },

    /**
     * Attach keyboard trap for accessibility.
     *
     * @param {HTMLElement} searchBlock - The search block element
     */
    attachKeyboardTrap(searchBlock) {
      const trapHandler = this.createKeyboardTrapHandler(searchBlock);
      searchBlock.addEventListener('keydown', trapHandler);

      // Store reference for cleanup
      searchBlock.dataset.keyboardTrapHandler = 'attached';
    },

    /**
     * Create keyboard trap handler.
     *
     * @param {HTMLElement} searchBlock - The search block element
     * @returns {Function} The keyboard trap handler
     */
    createKeyboardTrapHandler(searchBlock) {
      return (event) => {
        if (!Drupal.solo.searchBlock.isOpen) {
          return;
        }

        // Allow Escape to close any time the block is open.
        if (event.key === 'Escape') {
          event.preventDefault();
          if (typeof Drupal.solo.searchBlockToggle === 'function') {
            Drupal.solo.searchBlockToggle(false);
          }
          // If we know who opened it, return focus there; otherwise focus the first opener.
          const opener =
            Drupal.solo.searchBlock.triggerElement ||
            // Deprecated in 2.0, removed in the next major: old class alias.
            document.querySelector('.solo-fixed-search-block__open > button, .solo-fixed-search__open > button');
          if (opener) {
            opener.focus();
          }
          return;
        }

        if (event.key !== 'Tab') {
          return;
        }

        const focusable = Array.from(
          searchBlock.querySelectorAll(
            'button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])',
          ),
        ).filter((el) => el.offsetParent !== null);

        if (focusable.length === 0) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && event.target === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && event.target === last) {
          event.preventDefault();
          first.focus();
        }
      };
    },

    /**
     * Create the document-level handlers used while the block is open.
     *
     * Escape closes the block even before focus has moved into it (focus
     * moves in only after the open transition). Opening the login popup
     * closes the block without returning focus to the search opener.
     */
    createDocumentHandlers() {
      const state = Drupal.solo.searchBlock;

      state.escapeHandler = (event) => {
        if (event.key !== 'Escape' && event.key !== 'Esc') {
          return;
        }
        // The in-block handler, or another component, already handled it.
        if (event.defaultPrevented || !state.isOpen) {
          return;
        }
        event.preventDefault();
        const opener =
          state.triggerElement ||
          // Deprecated in 2.0, removed in the next major: old class alias.
          document.querySelector('.solo-fixed-search-block__open > button, .solo-fixed-search__open > button');
        if (typeof Drupal.solo.searchBlockToggle === 'function') {
          Drupal.solo.searchBlockToggle(false);
        }
        if (opener) {
          opener.focus();
        }
      };

      state.loginPopupOpenHandler = () => {
        if (!state.isOpen) {
          return;
        }
        // Else the close returns focus to the search opener, away from the popup.
        state.triggerElement = null;
        if (typeof Drupal.solo.searchBlockToggle === 'function') {
          Drupal.solo.searchBlockToggle(false);
        }
      };
    },

    /**
     * Add or remove the document-level handlers.
     *
     * @param {boolean} isOpen - Whether the search block is open
     */
    setDocumentListeners(isOpen) {
      const state = Drupal.solo.searchBlock;
      if (!state.escapeHandler || !state.loginPopupOpenHandler) {
        return;
      }
      if (isOpen) {
        document.addEventListener('keydown', state.escapeHandler);
        document.addEventListener(
          'solo:loginPopupOpen',
          state.loginPopupOpenHandler,
        );
      } else {
        document.removeEventListener('keydown', state.escapeHandler);
        document.removeEventListener(
          'solo:loginPopupOpen',
          state.loginPopupOpenHandler,
        );
      }
    },

    /**
     * Attach open button handler with debouncing.
     *
     * @param {HTMLElement} button - The open button element
     */
    attachOpenButtonHandler(button) {
      const debouncedHandler = this.debounce(() => {
        if (typeof Drupal.solo.searchBlockToggle === 'function') {
          Drupal.solo.searchBlockToggle(true);
        }
      }, 200);

      button.addEventListener('click', (event) => {
        Drupal.solo.searchBlock.triggerElement = event.currentTarget;
        debouncedHandler();
      });
    },

    /**
     * Attach close button handler with debouncing.
     *
     * @param {HTMLElement} button - The close button element
     * @param {HTMLElement} searchBlock - The search block element
     * @param {Object} cache - Cached element references
     */
    attachCloseButtonHandler(button, searchBlock, cache) {
      const debouncedHandler = this.debounce(() => {
        this.toggleSearchBlock(false, searchBlock, cache);
      }, 200);

      button.addEventListener('click', debouncedHandler);
    },

    /**
     * Attach outside click handler.
     *
     * @param {HTMLElement} searchBlock - The search block element
     * @param {HTMLElement} context - The context element
     */
    attachOutsideClickHandler(searchBlock, context) {
      // Attach a single, global outside-click listener for the page lifetime.
      // Using document as the context ensures this runs once even after Ajax attaches.
      once('solo-search-outside-click', 'body', document).forEach(() => {
        document.addEventListener('click', (event) => {
          if (!searchBlock || !Drupal.solo.searchBlock.isOpen) {
            return;
          }

          const target = event.target;
          // Treat clicks as "outside" if they are neither inside the block nor on any open button.
          const isOutsideClick =
            !searchBlock.contains(target) &&
            // Deprecated in 2.0, removed in the next major: old class alias.
            !(target.closest && target.closest('.solo-fixed-search-block__open, .solo-fixed-search__open'));

          if (
            isOutsideClick &&
            typeof Drupal.solo.searchBlockToggle === 'function'
          ) {
            Drupal.solo.searchBlockToggle(false);
          }
        });
      });
    },

    /**
     * Announce state change to screen readers.
     *
     * @param {boolean} isOpen - Whether the search block is open
     */
    announceStateChange(isOpen) {
      const message = isOpen
        ? Drupal.t('Search form opened')
        : Drupal.t('Search form closed');

      if (Drupal.announce) {
        Drupal.announce(message);
      }
    },

    /**
     * Debounce function for performance.
     *
     * @param {Function} func - The function to debounce
     * @param {number} wait - The debounce delay in milliseconds
     * @returns {Function} The debounced function
     */
    debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    },

    /**
     * Handle errors gracefully.
     *
     * @param {string} message - The error message
     * @param {Error} error - The error object
     */
    handleError(message, error) {
      if (typeof console !== 'undefined' && console.error) {
        console.error('[Solo Theme]', message, error);
      }
    },

    /**
     * Log warnings for development.
     *
     * @param {string} message - The warning message
     * @param {Error} [error] - Optional error object
     */
    logWarning(message, error) {
      if (typeof console !== 'undefined' && console.warn) {
        if (error) {
          console.warn('[Solo Theme]', message, error);
        } else {
          console.warn('[Solo Theme]', message);
        }
      }
    },
  };
})(Drupal, drupalSettings, once);
