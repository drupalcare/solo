/**
 * @file
 * Solo Menu ARIA Cleanup Utility - FIXED VERSION
 *
 * Changes:
 * - Removed auto-run in debug mode (must be manually triggered)
 * - Added clearer console instructions
 * - Improved reporting
 */
((Drupal) => {
  'use strict';

  Drupal.solo = Drupal.solo || {};

  Drupal.solo.ariaCleanup = {
    run() {
      console.log('Solo Menu: Starting ARIA cleanup...');

      this.cleanupLiElements();
      this.ensureProperRoles();
      this.fixAriaAttributes();
      this.validateMenuStructure();

      console.log('Solo Menu: ARIA cleanup complete');
    },

    cleanupLiElements() {
      const liElements = document.querySelectorAll('.solo-menu li');
      let cleaned = 0;

      liElements.forEach(li => {
        const attributesToRemove = ['aria-haspopup', 'aria-expanded', 'aria-controls'];

        attributesToRemove.forEach(attr => {
          if (li.hasAttribute(attr)) {
            const button = li.querySelector(':scope > button.solo-menu-tree__toggler');
            if (button && !button.hasAttribute(attr)) {
              button.setAttribute(attr, li.getAttribute(attr));
            }
            li.removeAttribute(attr);
            cleaned++;
          }
        });

        // Disclosure navigation (W3C APG): list items keep their native role.
        if (li.hasAttribute('role')) {
          li.removeAttribute('role');
          cleaned++;
        }
      });

      if (cleaned > 0) {
        console.log(`Solo Menu: Cleaned ${cleaned} incorrectly placed ARIA attributes from li elements`);
      }
    },

    ensureProperRoles() {
      // Disclosure navigation (W3C APG) uses no menu roles: lists, links and
      // buttons keep their native semantics. Remove any left by old markup.
      const menuRoles = '[role="menubar"], [role="menu"], [role="menuitem"], [role="none"], [role="presentation"]';
      document.querySelectorAll('.solo-menu').forEach(menu => {
        menu.querySelectorAll(menuRoles).forEach(element => {
          element.removeAttribute('role');
        });
      });
    },

    fixAriaAttributes() {
      document.querySelectorAll('button.solo-menu-tree__toggler').forEach(button => {
        const submenu = button.nextElementSibling;

        // aria-haspopup announces a menu; a disclosure button has none.
        if (button.getAttribute('aria-haspopup') === 'true') {
          button.removeAttribute('aria-haspopup');
        }

        if (submenu && !button.hasAttribute('aria-expanded')) {
          const isExpanded = submenu.classList.contains('solo-is-open') ||
                           submenu.style.display !== 'none';
          button.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
        }

        if (submenu && submenu.id && !button.hasAttribute('aria-controls')) {
          button.setAttribute('aria-controls', submenu.id);
        }
      });

      document.querySelectorAll('.solo-menu-tree__submenu').forEach(submenu => {
        if (!submenu.hasAttribute('aria-hidden')) {
          const isHidden = !submenu.classList.contains('solo-is-open') &&
                          submenu.style.display === 'none';
          submenu.setAttribute('aria-hidden', isHidden ? 'true' : 'false');
        }
      });
    },

    validateMenuStructure() {
      const issues = [];

      document.querySelectorAll('.solo-menu-tree__submenu').forEach(submenu => {
        if (!submenu.parentElement || submenu.parentElement.tagName !== 'LI') {
          issues.push('Submenu not properly nested within LI element');
        }
      });

      document.querySelectorAll('.solo-menu li').forEach(li => {
        const hasInteractive = li.querySelector(':scope > a, :scope > button');
        const hasSubmenu = li.querySelector(':scope > ul');

        if (!hasInteractive && !hasSubmenu) {
          issues.push(`Menu item without interactive element: ${li.textContent.trim()}`);
        }
      });

      document.querySelectorAll('[aria-controls]').forEach(element => {
        const controlledId = element.getAttribute('aria-controls');
        if (controlledId && !document.getElementById(controlledId)) {
          issues.push(`aria-controls references non-existent ID: ${controlledId}`);
        }
      });

      if (issues.length > 0) {
        console.warn('Solo Menu: Structure validation issues found:', issues);
      } else {
        console.log('Solo Menu: Structure validation passed');
      }

      return issues;
    },

    generateReport() {
      const report = {
        totalMenus: document.querySelectorAll('.solo-menu').length,
        totalItems: document.querySelectorAll('.solo-menu li').length,
        totalButtons: document.querySelectorAll('.solo-menu button').length,
        totalLinks: document.querySelectorAll('.solo-menu a').length,
        ariaIssues: [],
        roleIssues: [],
        structureIssues: this.validateMenuStructure()
      };

      document.querySelectorAll('.solo-menu li[aria-haspopup], .solo-menu li[aria-expanded]').forEach(li => {
        report.ariaIssues.push(`LI element has ARIA attributes: ${li.className}`);
      });

      document.querySelectorAll('.solo-menu [role="menubar"], .solo-menu [role="menu"], .solo-menu [role="menuitem"], .solo-menu li[role]').forEach(element => {
        report.roleIssues.push(`Menu role on disclosure navigation: ${element.tagName} role="${element.getAttribute('role')}"`);
      });

      console.table(report);
      return report;
    }
  };

  // FIX: Manual execution only - no auto-run in debug mode
  // This allows users to see the broken state before fixing it

  window.soloAriaCleanup = {
    run: () => {
      console.log('%c🔧 Running ARIA Cleanup...', 'color: blue; font-weight: bold');
      Drupal.solo.ariaCleanup.run();
    },
    report: () => {
      console.log('%c📊 Generating ARIA Report...', 'color: blue; font-weight: bold');
      return Drupal.solo.ariaCleanup.generateReport();
    },
    validate: () => {
      console.log('%c✓ Validating Menu Structure...', 'color: blue; font-weight: bold');
      return Drupal.solo.ariaCleanup.validateMenuStructure();
    }
  };

  // Only show instructions in debug mode
  if (drupalSettings?.solo?.debug === true) {
    console.log('%c💡 ARIA Cleanup Commands Available:', 'color: blue; font-weight: bold; font-size: 12px');
    console.log('%c  soloAriaCleanup.report()  %c- Generate accessibility report', 'color: cyan', 'color: gray');
    console.log('%c  soloAriaCleanup.validate()%c- Validate menu structure', 'color: cyan', 'color: gray');
    console.log('%c  soloAriaCleanup.run()     %c- Fix ARIA issues (run after seeing report)', 'color: cyan', 'color: gray');
  }

})(Drupal);
