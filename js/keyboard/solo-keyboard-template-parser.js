((Drupal) => {
  'use strict';
  Drupal.solo = Drupal.solo || {};
  Drupal.solo.keyboard = Drupal.solo.keyboard || {};

  class TemplateParserModule {
    constructor(core) {
      this.core = core;
      this.menubar = core.menubar;
      this.templateInfo = core.templateInfo;
    }

    init() {
      // Ensure accessibility compliance on initialization
      this.ensureAccessibilityCompliance();
    }

    getItemStructure(element) {
      if (!element || !(element instanceof HTMLElement)) return null;

      let li;

      if (element.tagName === 'LI') {
        li = element;
      } else {
        li = element.closest('li');
      }

      if (!li) return null;

      const link = li.querySelector(':scope > a');
      const button = li.querySelector(':scope > button.solo-menu-tree__toggler');
      const nolink = li.querySelector(':scope > span.solo-menu-tree__nolink');
      const submenu = li.querySelector(':scope > ul');

      let type = 'unknown';
      if (link && button) {
        type = 'split';
      } else if (link && !button) {
        type = 'link';
      } else if (!link && button) {
        type = 'button';
      } else if (nolink) {
        type = 'nolink';
      }

      return {
        li: li,
        link: link,
        button: button,
        nolink: nolink,
        submenu: submenu,
        hasSubmenu: !!submenu,
        type: type,
        isExpanded: submenu ? submenu.classList.contains('solo-is-open') : false,
        isMenubarItem: li.classList.contains('solo-menu-tree__menubar-item'),
        isActive: li.classList.contains('is-active')
      };
    }

    getMenuLevel(element) {
      if (!element) return 0;

      let level = 0;
      let current = element.closest('ul');

      while (current) {
        if (current.classList.contains('solo-menu-tree__menubar') || current.classList.contains('solo-menu-tree__submenu')) {
          level++;
        }
        current = current.parentElement?.closest('ul');
      }

      return level;
    }

    isMenuItem(element) {
      if (!element) return false;
      return element.tagName === 'A' ||
             (element.tagName === 'BUTTON' && element.classList.contains('solo-menu-tree__toggler'));
    }

    getSubmenuDirection(element) {
      const isSidebar = this.templateInfo.isSidebar;
      const isRTL = document.documentElement.dir === 'rtl';

      if (isSidebar) {
        return isRTL ? 'left' : 'right';
      }

      return 'down';
    }

    ensureAccessibilityCompliance() {
      if (!this.menubar) return;
      // Disclosure navigation (W3C APG): links and disclosure buttons keep
      // their native roles; no menu, menubar, menuitem or none roles.
      this.menubar.querySelectorAll('a, button').forEach(item => {
        // Ensure focus-visible support
        if (!item.classList.contains('solo-menu-item')) {
          item.classList.add('solo-menu-item');
        }
      });

      // Ensure proper ARIA labels on menu regions
      if (!this.menubar.hasAttribute('aria-label')) {
        const menuLabel = this.templateInfo.isPrimary ?
          Drupal.t('Main navigation') :
          Drupal.t('Navigation menu');
        this.menubar.setAttribute('aria-label', menuLabel);
      }

      // Disclosure buttons always carry aria-expanded.
      this.menubar.querySelectorAll('button.solo-menu-tree__toggler').forEach(button => {
        if (Drupal.solo.menuState && !button.hasAttribute('aria-expanded')) {
          Drupal.solo.menuState.setAriaAttribute(button, 'aria-expanded', 'false', 'templateParser');
        }
      });

      // Ensure submenus have proper initial state
      this.menubar.querySelectorAll('ul.solo-menu-tree__submenu').forEach(submenu => {
        if (!submenu.hasAttribute('aria-hidden')) {
          const isOpen = submenu.classList.contains('solo-is-open');
          if (Drupal.solo.menuState) {
            Drupal.solo.menuState.setAriaAttribute(submenu, 'aria-hidden', isOpen ? 'false' : 'true', 'templateParser');
          }
        }
      });
    }

    validateMenuStructure() {
      const errors = [];

      // Check for proper nesting
      this.menubar.querySelectorAll('ul ul').forEach(submenu => {
        if (!submenu.parentElement || submenu.parentElement.tagName !== 'LI') {
          errors.push('Submenu not properly nested within LI element');
        }
      });

      // Check for menu items without focusable elements
      this.menubar.querySelectorAll('li').forEach(li => {
        const focusable = li.querySelector(':scope > a, :scope > button');
        if (!focusable && !li.querySelector('ul')) {
          errors.push('Menu item without focusable element');
        }
      });

      if (errors.length > 0 && window.console && console.warn) {
        console.warn('Solo Keyboard: Menu structure validation errors:', errors);
      }

      return errors.length === 0;
    }

    destroy() {
      // No cleanup needed for parser module
    }
  }

  Drupal.solo.keyboard.templateParser = TemplateParserModule;
})(Drupal);
