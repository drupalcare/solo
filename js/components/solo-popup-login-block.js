/**
 * @file
 * Solo Popup Login Block functionality.
 *
 * Provides an accessible popup login block with keyboard navigation,
 * ARIA support, and proper Ajax compatibility.
 *
 * @package Solo
 * @author Alaa Haddad
 * @see https://www.flashwebcenter.com
 */

(function (Drupal, drupalSettings, once) {
  Drupal.solo = Drupal.solo || {};

  /**
   * Popup login block behavior.
   */
  Drupal.behaviors.soloPopupLoginBlock = {
    attach(context, settings) {
      // Initialize popup instance on the login block.
      once('solo-popup-login-init', '#solo-popup-login-block', context).forEach(
        (loginBlock) => {
          try {
            const popupLogin = new Drupal.solo.PopupLogin(loginBlock, context);
            loginBlock.soloPopupLogin = popupLogin;
            popupLogin.init();
          } catch (error) {
            if (window.console && console.error) {
              console.error('Solo popup login initialization error:', error);
            }
          }
        },
      );

      // Attach click/keyboard handlers to standard triggers.
      once(
        'solo-login-trigger',
        '[data-solo-login-popup-trigger], .solo-popup-login__trigger',
        context,
      ).forEach((trigger) => {
        trigger.addEventListener('click', Drupal.solo.handleLoginTriggerClick);

        // Ensure non-interactive elements act like buttons for accessibility.
        const tag = trigger.tagName.toLowerCase();
        const isInteractive =
          tag === 'button' || tag === 'a' || trigger.hasAttribute('role');
        if (!isInteractive) {
          trigger.setAttribute('role', 'button');
        }
        if (!trigger.hasAttribute('tabindex')) {
          trigger.setAttribute('tabindex', '0');
        }
        trigger.addEventListener(
          'keydown',
          Drupal.solo.handleLoginTriggerKeydown,
        );
      });

      // Custom triggers provided via drupalSettings.
      const popupSettings =
        (drupalSettings.solo && drupalSettings.solo.popupLogin) || {};
      if (popupSettings.customTriggers) {
        const selectors = popupSettings.customTriggers
          .split(',')
          .map((s) => s.trim())
          .filter((s) => s);
        selectors.forEach((selector) => {
          try {
            const onceKey = `solo-custom-trigger-${selector.replace(/[^a-zA-Z0-9]/g, '-')}`;
            once(onceKey, selector, context).forEach((trigger) => {
              trigger.addEventListener(
                'click',
                Drupal.solo.handleLoginTriggerClick,
              );

              // Apply the same keyboard and focusability enhancements.
              const tag = trigger.tagName.toLowerCase();
              const isInteractive =
                tag === 'button' || tag === 'a' || trigger.hasAttribute('role');
              if (!isInteractive) {
                trigger.setAttribute('role', 'button');
              }
              if (!trigger.hasAttribute('tabindex')) {
                trigger.setAttribute('tabindex', '0');
              }
              trigger.addEventListener(
                'keydown',
                Drupal.solo.handleLoginTriggerKeydown,
              );
            });
          } catch (e) {
            // Ignore invalid selectors
          }
        });
      }
    },

    detach(context, settings, trigger) {
      if (trigger === 'unload' || trigger === 'move') {
        // Destroy popup instances and remove their listeners.
        once
          .remove('solo-popup-login-init', '#solo-popup-login-block', context)
          .forEach((loginBlock) => {
            if (
              loginBlock.soloPopupLogin &&
              typeof loginBlock.soloPopupLogin.destroy === 'function'
            ) {
              loginBlock.soloPopupLogin.destroy();
              delete loginBlock.soloPopupLogin;
            }
          });

        // Remove standard trigger listeners.
        once
          .remove(
            'solo-login-trigger',
            '[data-solo-login-popup-trigger], .solo-popup-login__trigger',
            context,
          )
          .forEach((triggerEl) => {
            triggerEl.removeEventListener(
              'click',
              Drupal.solo.handleLoginTriggerClick,
            );
            triggerEl.removeEventListener(
              'keydown',
              Drupal.solo.handleLoginTriggerKeydown,
            );
          });

        // Remove custom trigger listeners to avoid leaks across Ajax updates.
        const popupSettings =
          (drupalSettings.solo && drupalSettings.solo.popupLogin) || {};
        if (popupSettings.customTriggers) {
          popupSettings.customTriggers
            .split(',')
            .map((s) => s.trim())
            .filter(Boolean)
            .forEach((selector) => {
              try {
                const onceKey = `solo-custom-trigger-${selector.replace(
                  /[^a-zA-Z0-9]/g,
                  '-',
                )}`;
                once.remove(onceKey, selector, context).forEach((triggerEl) => {
                  triggerEl.removeEventListener(
                    'click',
                    Drupal.solo.handleLoginTriggerClick,
                  );
                  triggerEl.removeEventListener(
                    'keydown',
                    Drupal.solo.handleLoginTriggerKeydown,
                  );
                });
              } catch (e) {
                // Ignore invalid selectors
              }
            });
        }
      }
    },
  };

  /**
   * Handle clicks on login popup triggers.
   */
  Drupal.solo.handleLoginTriggerClick = function (event) {
    event.preventDefault();
    event.stopPropagation();
    const loginBlock = document.getElementById('solo-popup-login-block');
    if (loginBlock && loginBlock.soloPopupLogin) {
      loginBlock.soloPopupLogin.open();
    }
  };

  /**
   * Enable keyboard activation (Enter/Space) for non-button triggers.
   */
  Drupal.solo.handleLoginTriggerKeydown = function (event) {
    const isSpace = event.key === ' ' || event.key === 'Spacebar';
    const isEnter = event.key === 'Enter';
    if (isEnter || isSpace) {
      event.preventDefault();
      Drupal.solo.handleLoginTriggerClick(event);
    }
  };

  /**
   * Popup Login handler class.
   */
  Drupal.solo.PopupLogin = function (loginBlock, context) {
    this.loginBlock = loginBlock;
    this.context = context;
    this.openButton = null;
    this.closeButton = null;
    this.isOpen = false;
    this.boundHandlers = {};
    this.lastFocusedElement = null;
    // Background nodes this instance made inert; only these are restored.
    this.inertedNodes = [];

    // Settings with defaults.
    this.settings = {
      animationDuration: 300,
      closeOnEscape: true,
      closeOnOutsideClick: true,
      focusTrap: true,
      announceToScreenReaders: true,
      returnFocusOnClose: true,
      customTriggers: '',
      zIndex: 10000,
      overlayOpacity: 50,
      useInlineStyles: false,
      backgroundColorRgb: '',
    };

    // Merge with drupalSettings.
    if (drupalSettings.solo && drupalSettings.solo.popupLogin) {
      Object.assign(this.settings, drupalSettings.solo.popupLogin);
    }

    // Sanitize numeric settings.
    this.settings.animationDuration = Math.max(
      0,
      Number(this.settings.animationDuration) || 0,
    );
    this.settings.overlayOpacity = Math.min(
      100,
      Math.max(0, Number(this.settings.overlayOpacity) || 0),
    );
    this.settings.zIndex = Number.isFinite(Number(this.settings.zIndex))
      ? Number(this.settings.zIndex)
      : 10000;
  };

  /**
   * Initialize the popup login.
   */
  Drupal.solo.PopupLogin.prototype.init = function () {
    // Find buttons.
    this.openButton =
      document.querySelector('.solo-popup-login__open > button') ||
      document.querySelector('.solo-popup-login__open button') ||
      document.querySelector('[data-solo-login-popup-trigger]');

    this.closeButton =
      this.loginBlock.querySelector('.solo-popup-login__close button') ||
      this.loginBlock.querySelector('.solo-popup-login__close-inner');

    if (!this.closeButton) {
      if (window.console && console.warn) {
        console.warn('Solo popup login: Close button not found');
      }
      return;
    }

    // Fix role attribute if mis-set.
    if (this.loginBlock.getAttribute('role') === 'login') {
      this.loginBlock.setAttribute('role', 'dialog');
    }

    // Setup ARIA.
    this.setupAria();

    // Create screen reader region.
    if (this.settings.announceToScreenReaders) {
      this.createAriaLiveRegion();
    }

    // ALWAYS set CSS variables - they are used by CSS regardless of inline styles mode.
    this.loginBlock.style.setProperty(
      '--solo-popup-z-index',
      this.settings.zIndex,
    );
    this.loginBlock.style.setProperty(
      '--solo-popup-overlay-opacity',
      this.settings.overlayOpacity / 100,
    );
    this.loginBlock.style.setProperty(
      '--solo-popup-animation-duration',
      `${this.settings.animationDuration}ms`,
    );

    // Set background RGB (default to black if not provided)
    const bgRgb = this.settings.backgroundColorRgb || '0, 0, 0';
    this.loginBlock.style.setProperty('--solo-popup-bg-rgb', bgRgb);
    // Add class to indicate variables are set.
    this.loginBlock.classList.add('solo-popup-css-vars');

    // Bind events.
    this.bindEvents();

    // Initialize as closed.
    this.close(false);
  };

  /**
   * Setup ARIA attributes.
   */
  Drupal.solo.PopupLogin.prototype.setupAria = function () {
    const loginId = this.loginBlock.id || 'solo-popup-login-block';

    if (this.openButton) {
      this.openButton.setAttribute('aria-controls', loginId);
      // Name an icon-only opener; never replace a visible label such as
      // "Log in", so the spoken name matches what is shown (WCAG 2.5.3).
      if (
        !this.openButton.hasAttribute('aria-label') &&
        !this.openButton.textContent.trim()
      ) {
        this.openButton.setAttribute('aria-label', Drupal.t('Open login form'));
      }
    }

    this.closeButton.setAttribute('aria-label', Drupal.t('Close login form'));
    this.loginBlock.setAttribute('role', 'dialog');
    this.loginBlock.setAttribute('aria-modal', 'true');

    // Strengthen dialog labeling: aria-labelledby / aria-describedby when possible.
    const labelEl = this.loginBlock.querySelector(
      '[data-sr-dialog-title], .solo-popup-login__title, h1, h2, h3',
    );
    if (labelEl) {
      if (!labelEl.id) {
        labelEl.id = `solo-login-title-${Math.random().toString(36).slice(2)}`;
      }
      this.loginBlock.setAttribute('aria-labelledby', labelEl.id);
    }

    const descEl = this.loginBlock.querySelector(
      '[data-sr-dialog-description], .solo-popup-login__description, .description',
    );
    if (descEl) {
      if (!descEl.id) {
        descEl.id = `solo-login-desc-${Math.random().toString(36).slice(2)}`;
      }
      this.loginBlock.setAttribute('aria-describedby', descEl.id);
    }

    // Mirror aria-controls on all known triggers.
    const allTriggers = document.querySelectorAll(
      '[data-solo-login-popup-trigger], .solo-popup-login__trigger, .solo-popup-login__open button, .solo-popup-login__open > button',
    );
    allTriggers.forEach((el) => {
      el.setAttribute('aria-controls', loginId);
    });
  };

  /**
   * Create ARIA live region.
   */
  Drupal.solo.PopupLogin.prototype.createAriaLiveRegion = function () {
    if (!document.getElementById('solo-popup-login-live-region')) {
      const liveRegion = document.createElement('div');
      liveRegion.id = 'solo-popup-login-live-region';
      liveRegion.className = 'visually-hidden';
      liveRegion.setAttribute('aria-live', 'polite');
      liveRegion.setAttribute('aria-atomic', 'true');
      document.body.appendChild(liveRegion);
    }
    this.liveRegion = document.getElementById('solo-popup-login-live-region');
  };

  /**
   * Announce to screen readers.
   */
  Drupal.solo.PopupLogin.prototype.announce = function (message) {
    if (this.liveRegion) {
      this.liveRegion.textContent = '';
      setTimeout(() => {
        this.liveRegion.textContent = message;
        setTimeout(() => {
          this.liveRegion.textContent = '';
        }, 1000);
      }, 100);
    }
  };

  /**
   * Bind event handlers.
   */
  Drupal.solo.PopupLogin.prototype.bindEvents = function () {
    // Button handlers.
    if (this.openButton) {
      this.boundHandlers.open = (e) => {
        e.preventDefault();
        this.open();
      };
      this.openButton.addEventListener('click', this.boundHandlers.open);
    }

    this.boundHandlers.close = (e) => {
      e.preventDefault();
      this.close();
    };
    this.closeButton.addEventListener('click', this.boundHandlers.close);

    // Document click (outside) handler.
    if (this.settings.closeOnOutsideClick) {
      this.boundHandlers.documentClick = (e) => {
        if (!this.isOpen) return;

        // Inner popup container.
        const popupInner = this.loginBlock.querySelector(
          '.solo-popup-login__inner',
        );

        // Fallback if inner container is missing.
        if (!popupInner) {
          if (
            !this.loginBlock.contains(e.target) &&
            !e.target.closest('.solo-popup-login__open') &&
            !e.target.closest('[data-solo-login-popup-trigger]') &&
            !e.target.closest('.solo-popup-login__trigger')
          ) {
            this.close();
          }
          return;
        }

        // Close only if click is outside the inner popup and not on any trigger.
        if (
          !popupInner.contains(e.target) &&
          !e.target.closest('.solo-popup-login__open') &&
          !e.target.closest('[data-solo-login-popup-trigger]') &&
          !e.target.closest('.solo-popup-login__trigger')
        ) {
          this.close();
        }
      };
    }

    // Escape key handler.
    if (this.settings.closeOnEscape) {
      this.boundHandlers.escapeKey = (e) => {
        if ((e.key === 'Escape' || e.key === 'Esc') && this.isOpen) {
          e.preventDefault();
          this.close();
        }
      };
    }

    // Focus trap handler.
    if (this.settings.focusTrap) {
      this.boundHandlers.focusTrap = (e) => {
        if (e.key !== 'Tab') return;

        const focusableElements = this.getFocusableElements();
        if (focusableElements.length === 0) return;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (e.shiftKey && document.activeElement === firstElement) {
          e.preventDefault();
          lastElement.focus();
        } else if (!e.shiftKey && document.activeElement === lastElement) {
          e.preventDefault();
          firstElement.focus();
        }
      };
    }

    // Close when the fixed search opens. Attached only while the popup is
    // open. Focus is not returned to the popup opener, so the search keeps it.
    this.boundHandlers.searchBlockOpen = () => {
      if (!this.isOpen) return;
      this.lastFocusedElement = null;
      this.close();
    };
  };

  /**
   * Make the page behind the popup inert.
   *
   * Sets native inert on the siblings of every ancestor of the popup, up to
   * body. Skips nodes that are already inert and aria-live regions, so
   * announcements still reach screen readers. Records what it set.
   */
  Drupal.solo.PopupLogin.prototype.setBackgroundInert = function () {
    this.releaseBackgroundInert();
    if (!('inert' in HTMLElement.prototype)) return;

    const skipTags = ['SCRIPT', 'STYLE', 'LINK', 'TEMPLATE', 'NOSCRIPT'];
    let node = this.loginBlock;
    while (node && node !== document.body && node.parentElement) {
      const parent = node.parentElement;
      const current = node;
      Array.from(parent.children).forEach((sibling) => {
        if (
          sibling === current ||
          skipTags.indexOf(sibling.tagName) !== -1 ||
          sibling.hasAttribute('inert') ||
          sibling.hasAttribute('aria-live')
        ) {
          return;
        }
        sibling.inert = true;
        this.inertedNodes.push(sibling);
      });
      node = parent;
    }
  };

  /**
   * Remove inert from the nodes setBackgroundInert() set, and only those.
   */
  Drupal.solo.PopupLogin.prototype.releaseBackgroundInert = function () {
    if (!this.inertedNodes) return;
    this.inertedNodes.forEach((sibling) => {
      sibling.inert = false;
    });
    this.inertedNodes = [];
  };

  /**
   * Open the login popup.
   */
  Drupal.solo.PopupLogin.prototype.open = function () {
    if (this.isOpen) return;

    if (this.settings.returnFocusOnClose) {
      this.lastFocusedElement = document.activeElement;
    }

    this.isOpen = true;

    // Remove inert attribute before opening
    Drupal.solo.setInert(this.loginBlock, false);

    // Dispatch custom event.
    this.loginBlock.dispatchEvent(
      new CustomEvent('solo:loginPopupOpen', {
        detail: { loginBlock: this.loginBlock },
        bubbles: true,
      }),
    );

    // Background is inert while the dialog is open.
    this.setBackgroundInert();

    // Close if the fixed search opens while the popup is open.
    document.addEventListener(
      'solo:searchBlockOpen',
      this.boundHandlers.searchBlockOpen,
    );

    // Apply styles based on useInlineStyles setting.
    if (this.settings.useInlineStyles) {
      // Use direct inline styles for display.
      this.loginBlock.style.display = 'block';
    } else {
      // Use class-based approach.
      this.loginBlock.classList.add('solo-popup-visible');
    }

    // Add classes and update ARIA.
    requestAnimationFrame(() => {
      this.loginBlock.classList.add('solo-is-open');
      this.updateAriaAttributes(true);
      this.setElementsTabindex(true);
      this.focusFirstInput();

      if (this.settings.announceToScreenReaders) {
        this.announce(Drupal.t('Login form opened'));
      }

      // Attach global handlers with a short delay.
      setTimeout(() => {
        if (this.boundHandlers.documentClick) {
          document.addEventListener(
            'click',
            this.boundHandlers.documentClick,
            true,
          );
          // Helper to check passive support
          const supportsPassive = (() => {
            let passive = false;
            try {
              const opts = Object.defineProperty({}, 'passive', {
                get() {
                  passive = true;
                  return undefined;
                },
              });
              window.addEventListener('testPassive', null, opts);
              window.removeEventListener('testPassive', null, opts);
            } catch (e) {
              // Passive listeners are not supported; keep the default.
            }
            return passive;
          })();

          // Then use it
          document.addEventListener(
            'touchstart',
            this.boundHandlers.documentClick,
            supportsPassive ? { capture: true, passive: true } : true,
          );
        }
        if (this.boundHandlers.escapeKey) {
          document.addEventListener('keydown', this.boundHandlers.escapeKey);
        }
        if (this.boundHandlers.focusTrap) {
          this.loginBlock.addEventListener(
            'keydown',
            this.boundHandlers.focusTrap,
          );
        }
      }, 100);

      document.body.classList.add('solo-login-popup-open');
    });
  };

  /**
   * Close the login popup.
   */
  Drupal.solo.PopupLogin.prototype.close = function (announce = true) {
    if (!this.isOpen && !this.loginBlock.classList.contains('solo-is-open'))
      return;

    this.isOpen = false;

    // Restore the background synchronously, before focus is returned to it.
    this.releaseBackgroundInert();
    if (this.boundHandlers.searchBlockOpen) {
      document.removeEventListener(
        'solo:searchBlockOpen',
        this.boundHandlers.searchBlockOpen,
      );
    }

    // CRITICAL: Remove focus from any element inside popup BEFORE hiding
    if (this.loginBlock.contains(document.activeElement)) {
      document.activeElement.blur();
    }

    // Dispatch custom event.
    this.loginBlock.dispatchEvent(
      new CustomEvent('solo:loginPopupClose', {
        detail: { loginBlock: this.loginBlock },
        bubbles: true,
      }),
    );

    // Remove classes (but NOT aria-hidden yet)
    this.loginBlock.classList.remove('solo-is-open');

    // Update aria-expanded on triggers (but NOT aria-hidden on dialog yet)
    const triggers = document.querySelectorAll(
      '[data-solo-login-popup-trigger], .solo-popup-login__trigger, .solo-popup-login__open button, .solo-popup-login__open > button',
    );
    triggers.forEach((el) => el.setAttribute('aria-expanded', 'false'));

    if (this.openButton) {
      this.openButton.setAttribute('aria-expanded', 'false');
    }
    this.closeButton.setAttribute('aria-expanded', 'false');

    this.setElementsTabindex(false);
    document.body.classList.remove('solo-login-popup-open');

    // Return focus FIRST
    if (this.settings.returnFocusOnClose && this.lastFocusedElement) {
      setTimeout(() => {
        try {
          this.lastFocusedElement.focus();
        } catch (e) {
          if (this.openButton) {
            this.openButton.focus();
          }
        }

        // THEN set aria-hidden AFTER focus has moved out
        setTimeout(() => {
          if (!this.isOpen) {
            this.loginBlock.setAttribute('aria-hidden', 'true');
            Drupal.solo.setInert(this.loginBlock, true);
          }
        }, 50);
      }, 50);
    } else {
      // If not returning focus, still delay aria-hidden slightly
      setTimeout(() => {
        if (!this.isOpen) {
          this.loginBlock.setAttribute('aria-hidden', 'true');
          Drupal.solo.setInert(this.loginBlock, true);
        }
      }, 50);
    }

    // Hide after animation.
    setTimeout(() => {
      if (!this.isOpen) {
        if (this.settings.useInlineStyles) {
          this.loginBlock.style.display = 'none';
        } else {
          this.loginBlock.classList.remove('solo-popup-visible');
        }
      }
    }, this.settings.animationDuration);

    if (announce && this.settings.announceToScreenReaders) {
      this.announce(Drupal.t('Login form closed'));
    }

    // Remove global handlers.
    if (this.boundHandlers.documentClick) {
      document.removeEventListener(
        'click',
        this.boundHandlers.documentClick,
        true,
      );
      // Must match capture setting used when adding.
      document.removeEventListener(
        'touchstart',
        this.boundHandlers.documentClick,
        { capture: true },
      );
    }
    if (this.boundHandlers.escapeKey) {
      document.removeEventListener('keydown', this.boundHandlers.escapeKey);
    }
    if (this.boundHandlers.focusTrap) {
      this.loginBlock.removeEventListener(
        'keydown',
        this.boundHandlers.focusTrap,
      );
    }
  };
  /**
   * Update ARIA attributes.
   * Note: aria-hidden on dialog is now managed separately to avoid focus conflicts.
   */
  Drupal.solo.PopupLogin.prototype.updateAriaAttributes = function (isOpen) {
    // Keep all triggers in sync.
    const triggers = document.querySelectorAll(
      '[data-solo-login-popup-trigger], .solo-popup-login__trigger, .solo-popup-login__open button, .solo-popup-login__open > button',
    );
    triggers.forEach((el) => el.setAttribute('aria-expanded', String(isOpen)));

    if (this.openButton) {
      this.openButton.setAttribute('aria-expanded', String(isOpen));
    }
    this.closeButton.setAttribute('aria-expanded', String(isOpen));

    // Only set aria-hidden when opening (removing it)
    // When closing, this is handled separately after focus moves
    if (isOpen) {
      this.loginBlock.setAttribute('aria-hidden', 'false');
    }
  };

  /**
   * Set tabindex on elements within the dialog.
   */
  Drupal.solo.PopupLogin.prototype.setElementsTabindex = function (isOpen) {
    const elements = this.loginBlock.querySelectorAll(
      'button, input, select, textarea, a[href]',
    );
    elements.forEach((element) => {
      if (isOpen) {
        const original = element.getAttribute('data-original-tabindex');
        if (original) {
          if (original === 'none') {
            element.removeAttribute('tabindex');
          } else {
            element.setAttribute('tabindex', original);
          }
          element.removeAttribute('data-original-tabindex');
        } else {
          element.removeAttribute('tabindex');
        }
      } else {
        const current = element.getAttribute('tabindex');
        element.setAttribute('data-original-tabindex', current || 'none');
        element.setAttribute('tabindex', '-1');
      }
    });
  };

  /**
   * Get focusable elements.
   */
  Drupal.solo.PopupLogin.prototype.getFocusableElements = function () {
    const selector = [
      'button:not([disabled]):not([tabindex="-1"])',
      'input:not([disabled]):not([type="hidden"]):not([tabindex="-1"])',
      'select:not([disabled]):not([tabindex="-1"])',
      'textarea:not([disabled]):not([tabindex="-1"])',
      'a[href]:not([tabindex="-1"])',
      '[tabindex]:not([tabindex="-1"])',
      '[contenteditable="true"]:not([tabindex="-1"])',
    ].join(', ');
    return Array.from(this.loginBlock.querySelectorAll(selector)).filter(
      (el) => {
        return (
          el.offsetParent !== null &&
          getComputedStyle(el).visibility !== 'hidden'
        );
      },
    );
  };

  /**
   * Focus first input (or first focusable element).
   */
  Drupal.solo.PopupLogin.prototype.focusFirstInput = function () {
    const focusableElements = this.getFocusableElements();
    const firstInput = focusableElements.find(
      (el) => el.tagName === 'INPUT' && el.type !== 'hidden',
    );

    setTimeout(() => {
      try {
        if (firstInput) {
          firstInput.focus({ preventScroll: true });
        } else if (focusableElements.length > 0) {
          focusableElements[0].focus({ preventScroll: true });
        }
      } catch (e) {
        // Ignore focus errors
      }
    }, 50);
  };

  /**
   * Destroy the popup instance.
   */
  Drupal.solo.PopupLogin.prototype.destroy = function () {
    if (this.isOpen) {
      this.close(false);
    }
    this.releaseBackgroundInert();

    // Remove event listeners specific to this instance.
    if (this.openButton && this.boundHandlers.open) {
      this.openButton.removeEventListener('click', this.boundHandlers.open);
    }
    if (this.closeButton && this.boundHandlers.close) {
      this.closeButton.removeEventListener('click', this.boundHandlers.close);
    }

    // Clean up ARIA live region if this was the last popup.
    if (
      this.liveRegion &&
      document.querySelectorAll('#solo-popup-login-block').length <= 1
    ) {
      try {
        this.liveRegion.remove();
      } catch (e) {
        // Ignore if already removed
      }
    }

    // Remove inert attribute
    if (this.loginBlock) {
      Drupal.solo.setInert(this.loginBlock, false);
    }

    // Clean up classes.
    document.body.classList.remove('solo-login-popup-open');
    if (this.loginBlock) {
      this.loginBlock.classList.remove(
        'solo-popup-css-vars',
        'solo-popup-visible',
      );
    }

    // Clear references.
    this.loginBlock = null;
    this.openButton = null;
    this.closeButton = null;
    this.context = null;
    this.boundHandlers = null;
    this.liveRegion = null;
    this.lastFocusedElement = null;
  };
})(Drupal, drupalSettings, once);
