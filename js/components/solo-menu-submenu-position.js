/**
 * @file
 * Solo - Enhanced with performance, accessibility, state manager, and cleanup
 *
 * Filename:     solo-menu-submenu-position.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal, drupalSettings, once) => {
  // Component name for state manager
  const COMPONENT_NAME = 'repositions';

  // Constants
  const SUBMENU_BUFFER = 30; // Buffer space for submenu positioning
  const RESIZE_DEBOUNCE_DELAY = 150; // Debounce delay for resize events
  let resizeHandler = null;

  // Register component with state manager
  if (Drupal.solo.menuState) {
    Drupal.solo.menuState.registerComponent(COMPONENT_NAME, {
      name: 'Solo Menu Repositions',
      version: '1.0',
    });
  }

  // Deprecated in 2.0, removed in the next major: the old behavior name
  // soloMenuFix reads and writes Drupal.behaviors.soloMenuSubmenuPosition.
  // It is not enumerable, so Drupal.attachBehaviors() runs it once.
  if (!Object.prototype.hasOwnProperty.call(Drupal.behaviors, 'soloMenuFix')) {
    Object.defineProperty(Drupal.behaviors, 'soloMenuFix', {
      configurable: true,
      get: () => Drupal.behaviors.soloMenuSubmenuPosition,
      set: (value) => {
        Drupal.behaviors.soloMenuSubmenuPosition = value;
      },
    });
  }

  Drupal.behaviors.soloMenuSubmenuPosition = {
    attach(context) {
      const breakpoint = Drupal.solo.getBreakpointNumber('mn');
      const windowWidth = window.innerWidth;

      // Quit if the screen is smaller than the breakpoint
      if (!Drupal.solo.isLargeLayout(windowWidth, breakpoint)) {
        return;
      }

      // Function 1: Get full window width
      // Function 1: Get full window width
      function getWindowWidth() {
        // Add state manager support with fallback
        if (Drupal.solo.menuState && Drupal.solo.menuState.getCurrentWidth) {
          return Drupal.solo.menuState.getCurrentWidth();
        }
        return window.innerWidth;
      }

      // Function 2: Get full width of the `li`
      function getLiWidth(li) {
        return li.offsetWidth;
      }

      // Function 3: Get X and Y position of the `li`
      function getLiPosition(li) {
        return li.getBoundingClientRect();
      }

      // Function 4: Get full width of the opening submenu `ul`
      function getSubmenuWidth(submenu) {
        return submenu.offsetWidth;
      }

      // Function 5: Get X and Y position of the submenu `ul`
      function getSubmenuPosition(submenu) {
        return submenu.getBoundingClientRect();
      }

      // Function 6: Get space from `li` start to the left window edge
      function getSpaceLeft(liRect) {
        return liRect.left;
      }

      // Function 7: Get space from `li` end to the right window edge
      function getSpaceRight(liRect, windowWidth) {
        return windowWidth - liRect.right;
      }

      // Function to reposition second-level submenus
      function adjustSecondLevelSubmenu(li) {
        const windowWidth = getWindowWidth();
        if (!Drupal.solo.isLargeLayout(windowWidth, breakpoint)) return;
        const submenu = li.querySelector(':scope > .solo-menu-tree__submenu');
        if (!submenu) return;
        submenu.style.visibility = 'hidden';
        const liRect = getLiPosition(li);
        const submenuWidth = getSubmenuWidth(submenu);
        const spaceLeft = getSpaceLeft(liRect);
        const spaceRight = getSpaceRight(liRect, windowWidth);
        if (submenuWidth + SUBMENU_BUFFER > spaceRight) {
          submenu.style.left = 'auto';
          submenu.style.right = '0';
        } else if (submenuWidth + SUBMENU_BUFFER > spaceLeft) {
          submenu.style.right = 'auto';
          submenu.style.left = '0';
        } else {
          submenu.style.left = '';
          submenu.style.right = '';
        }
        submenu.style.visibility = 'visible';
      }

      // Function to reposition third-level submenus
      function adjustThirdLevelSubmenu(li) {
        const windowWidth = getWindowWidth();
        if (!Drupal.solo.isLargeLayout(windowWidth, breakpoint)) return;
        const submenu = li.querySelector(':scope > .solo-menu-tree__submenu');
        if (!submenu) return;
        const parentLi = li.closest('li.solo-menu-tree__item--has-submenu');
        if (!parentLi) return;
        submenu.style.visibility = 'hidden';
        const parentRect = getLiPosition(parentLi);
        const submenuWidth = getSubmenuWidth(submenu);
        const spaceLeft = getSpaceLeft(parentRect);
        const spaceRight = getSpaceRight(parentRect, windowWidth);
        // NEW: Dynamically get 4th-level submenu width (if present)
        const fourthLevelLi = submenu.querySelector(
          'li.solo-menu-tree__item--has-submenu',
        );
        let fourthLevelWidth = 0;
        if (fourthLevelLi) {
          const fourthSubmenu = fourthLevelLi.querySelector(
            ':scope > .solo-menu-tree__submenu',
          );
          if (fourthSubmenu) {
            const originalDisplay = fourthSubmenu.style.display;
            fourthSubmenu.style.visibility = 'hidden';
            fourthSubmenu.style.display = 'block';
            fourthLevelWidth = getSubmenuWidth(fourthSubmenu);
            // Restore only if originally hidden inline
            fourthSubmenu.style.display = originalDisplay;
            fourthSubmenu.style.visibility = '';
          }
        }
        const totalNeededWidth =
          submenuWidth + fourthLevelWidth + SUBMENU_BUFFER;
        if (totalNeededWidth > spaceRight && spaceLeft > spaceRight) {
          submenu.style.left = 'auto';
          submenu.style.right = '100%';
          submenu.dataset.flipped = 'left';
          li.classList.add('solo-menu-tree__submenu--flipped-left');
          li.classList.remove('solo-menu-tree__submenu--flipped-right');
        } else if (submenuWidth + SUBMENU_BUFFER > spaceLeft) {
          submenu.style.right = 'auto';
          submenu.style.left = '100%';
          submenu.dataset.flipped = 'right';
          li.classList.add('solo-menu-tree__submenu--flipped-right');
          li.classList.remove('solo-menu-tree__submenu--flipped-left');
        } else {
          submenu.style.left = '';
          submenu.style.right = '';
        }
        submenu.style.visibility = 'visible';
      }

      function adjustFourthLevelSubmenu(li) {
        const windowWidth = getWindowWidth();
        if (!Drupal.solo.isLargeLayout(windowWidth, breakpoint)) return;
        const submenu = li.querySelector(':scope > .solo-menu-tree__submenu');
        if (!submenu) return;
        const parentLi = li
          .closest('ul.solo-menu-tree__submenu')
          .closest('li.solo-menu-tree__item--has-submenu');
        if (!parentLi) return;
        const parentSubmenu = parentLi.querySelector(
          ':scope > .solo-menu-tree__submenu',
        );
        if (!parentSubmenu) return;
        submenu.style.visibility = 'hidden';
        // Determine if parent (3rd level) is flipped left
        const isParentFlippedLeft = parentSubmenu.dataset.flipped === 'left';
        if (isParentFlippedLeft) {
          submenu.style.left = 'auto';
          submenu.style.right = '100%';
        } else {
          submenu.style.right = 'auto';
          submenu.style.left = '100%';
        }
        submenu.style.visibility = 'visible';
      }

      // Function to apply submenu fix for both hover and click
      function applySubmenuFix(menuSelector, adjustFunction) {
        once(
          'solo-menu-submenu-position',
          document.querySelectorAll(menuSelector, context),
        ).forEach((li) => {
          // Simple ARIA initialization only - don't interfere with positioning
          const button = li.querySelector(
            ':scope > button.solo-menu-tree__toggler',
          );
          const submenu = li.querySelector(':scope > .solo-menu-tree__submenu');

          if (button && submenu) {
            // Just set initial ARIA attributes if missing. Disclosure
            // buttons (W3C APG) use aria-expanded, not aria-haspopup.
            if (!button.hasAttribute('aria-expanded')) {
              button.setAttribute('aria-expanded', 'false');
            }
            if (!submenu.hasAttribute('aria-hidden')) {
              submenu.setAttribute('aria-hidden', 'true');
            }
          }

          // Keep the original event listeners EXACTLY as they are
          ['mouseenter', 'click'].forEach((eventType) => {
            li.addEventListener(eventType, function () {
              adjustFunction(this);
            });
          });

          // Add keyboard support separately - only for repositioning.
          // Do not prevent default: Enter and Space must still fire the
          // button's click, which opens the submenu when keyboard
          // navigation is off for this menu.
          li.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
              if (event.target === button) {
                adjustFunction(this);
              }
            }
          });

          // Mark element as having handlers for cleanup
          li.dataset.soloMenuHandler = 'true';
        });
      }
      // Apply fixes to second, third, and fourth-level submenus. A mega menu
      // lays out its own panels, so it is left alone (as in solo-menu.js).
      applySubmenuFix(
        '.solo-primary-menu .solo-menu-tree--primary:not(.solo-menu-tree--megamenu) > li.solo-menu-tree__item--has-submenu',
        adjustSecondLevelSubmenu,
      );
      applySubmenuFix(
        '.solo-primary-menu .solo-menu-tree--primary:not(.solo-menu-tree--megamenu) > li.solo-menu-tree__item--has-submenu > ul > li.solo-menu-tree__item--has-submenu',
        adjustThirdLevelSubmenu,
      );
      applySubmenuFix(
        '.solo-primary-menu .solo-menu-tree--primary:not(.solo-menu-tree--megamenu) > li.solo-menu-tree__item--has-submenu > ul > li.solo-menu-tree__item--has-submenu > ul > li.solo-menu-tree__item--has-submenu',
        adjustFourthLevelSubmenu,
      );

      // Function to reapply fixes on window resize
      // Function to reapply fixes on window resize
      function handleResize() {
        if (Drupal.solo.isLargeLayout(window.innerWidth, breakpoint)) {
          applySubmenuFix(
            '.solo-primary-menu .solo-menu-tree--primary:not(.solo-menu-tree--megamenu) > li.solo-menu-tree__item--has-submenu',
            adjustSecondLevelSubmenu,
          );
          applySubmenuFix(
            '.solo-primary-menu .solo-menu-tree--primary:not(.solo-menu-tree--megamenu) > li.solo-menu-tree__item--has-submenu > ul > li.solo-menu-tree__item--has-submenu',
            adjustThirdLevelSubmenu,
          );
          applySubmenuFix(
            '.solo-primary-menu .solo-menu-tree--primary:not(.solo-menu-tree--megamenu) > li.solo-menu-tree__item--has-submenu > ul > li.solo-menu-tree__item--has-submenu > ul > li.solo-menu-tree__item--has-submenu',
            adjustFourthLevelSubmenu,
          );
        }
      }

      // Store handler reference for cleanup
      resizeHandler = handleResize;

      // Listen for window resize and reapply menu fix
      window.addEventListener('resize', resizeHandler);
    },

    // Cleanup method for proper event removal
    detach(context, settings, trigger) {
      if (trigger === 'unload') {
        // Unregister from state manager
        if (Drupal.solo.menuState) {
          Drupal.solo.menuState.unregisterComponent(COMPONENT_NAME);
        }

        // Remove resize handler
        if (resizeHandler) {
          window.removeEventListener('resize', resizeHandler);
          resizeHandler = null;
        }
      }
    },
  };
})(Drupal, drupalSettings, once);
