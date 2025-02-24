/**
 * @file
 * Solo
 *
 * Filename:     solo-menu-repositions-submenus.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal, drupalSettings, once) => {
  'use strict';

  Drupal.behaviors.soloMenuFix = {
    attach: function (context) {
      const breakpoint = Drupal.solo.getBreakpointNumber('mn'); // Get the menu breakpoint

      // Function 1: Get full window width
      function getWindowWidth() {
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
        if (windowWidth < breakpoint) return; // Only run on large screens

        const submenu = li.querySelector(':scope > .sub__menu');
        if (!submenu) return;

        const liRect = getLiPosition(li);
        const submenuWidth = getSubmenuWidth(submenu);
        const spaceLeft = getSpaceLeft(liRect);
        const spaceRight = getSpaceRight(liRect, windowWidth);

// console.log("Parent <li> Position:", liRect);
// console.log("Submenu Width:", submenuWidth);
// console.log("Space Left:", spaceLeft);
// console.log("Space Right:", spaceRight);

        // Adjust positioning for second-level submenu
        if (submenuWidth + 30 > spaceRight) {
          console.log("First option");
          submenu.style.left = 'auto';
          submenu.style.right = '0'; // Align with li's start
        } else if (submenuWidth + 30 > spaceLeft) {
          console.log("Second option");
          submenu.style.right = 'auto';
          submenu.style.left = '0'; // Align with li's end
        } else {
          console.log("nothing");
          submenu.style.left = '';
          submenu.style.right = '';
        }
      }

      // Function to reposition third-level submenus based on **parent li** position
      function adjustThirdLevelSubmenu(li) {
        const windowWidth = getWindowWidth();
        if (windowWidth < breakpoint) return; // Only run on large screens

        const submenu = li.querySelector(':scope > .sub__menu');
        if (!submenu) return;

        const parentLi = li.closest('li.has-sub__menu'); // Get the direct parent li
        if (!parentLi) return;

        const parentRect = getLiPosition(parentLi);
        const submenuWidth = getSubmenuWidth(submenu);
        const spaceLeft = getSpaceLeft(parentRect);
        const spaceRight = getSpaceRight(parentRect, windowWidth);


        // Adjust positioning for third-level submenu
        if (submenuWidth + 30 > spaceRight) {
          submenu.style.left = 'auto';
          submenu.style.right = '100%'; // Place before parent `li`
        } else if (submenuWidth + 30 > spaceLeft) {
          submenu.style.right = 'auto';
          submenu.style.left = '100%'; // Place after parent `li`
        } else {
          submenu.style.left = '';
          submenu.style.right = '';
        }
      }

      // Function to apply the submenu fix
      function applySubmenuFix(menuSelector, adjustFunction) {
        document.querySelectorAll(menuSelector, context).forEach((li) => {
          li.addEventListener('click', function (event) {
            event.stopPropagation(); // Prevent event bubbling
            adjustFunction(this);
          });
        });
      }

      // Apply fixes to second and third-level submenus
      applySubmenuFix('.navigation__primary > li.has-sub__menu', adjustSecondLevelSubmenu);
      applySubmenuFix('.navigation__primary > li.has-sub__menu ul li.has-sub__menu', adjustThirdLevelSubmenu);
    }
  };
})(Drupal, drupalSettings, once);





