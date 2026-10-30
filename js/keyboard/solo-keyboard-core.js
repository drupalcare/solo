((Drupal, drupalSettings, once) => {
  'use strict';
  Drupal.solo = Drupal.solo || {};
  Drupal.solo.keyboard = Drupal.solo.keyboard || {};

  const COMPONENT_NAME = 'keyboard-core';

  class KeyboardNavigationCore {
    constructor(menuWrapper) {
      this.menuWrapper = menuWrapper;
      this.modules = new Map();
      this.templateInfo = {};
      this.breakpoint = null;

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => this.init());
      } else {
        requestAnimationFrame(() => this.init());
      }
    }

    init() {
      try {
        this.breakpoint = Drupal.solo.getBreakpointNumber ? Drupal.solo.getBreakpointNumber('mn') : 992;
        this.parseTemplate();
        if (!this.findMenuElements()) {
          console.warn('Solo Keyboard: No valid menu structure found');
          return;
        }
        this.loadModules();
        this.initializeModules();
        this.setupResizeHandler();
      } catch (error) {
        console.error('Solo Keyboard Core: Initialization error', error);
      }
    }

    parseTemplate() {
      const wrapper = this.menuWrapper;
      const classList = wrapper.classList;

      this.templateInfo = {
        menuType: this.detectMenuType(classList),
        interactionMode: this.detectInteractionMode(wrapper),
        hasMobileNav: wrapper.querySelector('.mobile-menubar-toggler-button') !== null,
        isMegamenu: wrapper.querySelector('.navigation__megamenu') !== null,
        isResponsive: classList.contains('navigation-responsive'),
        isSidebar: classList.contains('navigation-sidebar'),
        isPrimary: classList.contains('navigation-primary-responsive') || classList.contains('navigation-primary-sidebar'),
        hasKeyboardEnabled: classList.contains('solo-keyboard-enabled')
      };
    }

    detectMenuType(classList) {
      if (classList.contains('navigation-sidebar')) return 'sidebar';
      if (classList.contains('navigation-responsive')) return 'responsive';
      if (classList.contains('navigation-default')) return 'default';
      return 'unknown';
    }

    detectInteractionMode(wrapper) {
      if (wrapper.querySelector('[data-hover-toggle]') ||
          wrapper.classList.contains('navigation-responsive-hover') ||
          wrapper.classList.contains('navigation-sidebar-hover')) {
        return 'hover';
      }
      return 'click';
    }

    findMenuElements() {
      // Solo menus follow the W3C disclosure navigation pattern: plain lists
      // of links and disclosure buttons, no menu roles, natural Tab order.
      // Arrow keys are an optional enhancement on top of that.
      this.menubar = this.menuWrapper.querySelector('.navigation__menubar');

      if (!this.menubar) return false;

      this.menuItems = this.menubar.querySelectorAll('a, button');
      this.mobileNavButton = this.menuWrapper.querySelector('.mobile-menubar-toggler-button');

      return true;
    }

    loadModules() {
      const modules = ['templateParser', 'aria', 'focus', 'navigation', 'utils'];
      if (this.templateInfo.isMegamenu) modules.push('megamenu');

      modules.forEach(name => {
        if (Drupal.solo.keyboard[name]) {
          this.modules.set(name, new Drupal.solo.keyboard[name](this));
        }
      });
    }

    initializeModules() {
      this.modules.forEach((module, name) => {
        try {
          if (typeof module.init === 'function') {
            module.init();
          }
        } catch (error) {
          console.error(`Solo Keyboard: Error initializing module ${name}`, error);
        }
      });
    }

    setupResizeHandler() {
      if (Drupal.solo.menuState) {
        Drupal.solo.menuState.addResizeHandler(COMPONENT_NAME, (screenInfo) => {
          this.modules.forEach((module) => {
            if (typeof module.onResize === 'function') {
              module.onResize(screenInfo);
            }
          });
        }, 150);
      }
    }

    getModule(name) {
      return this.modules.get(name);
    }

    isSmallScreen() {
      const breakpoint = this.breakpoint || (Drupal.solo.getBreakpointNumber ? Drupal.solo.getBreakpointNumber('mn') : 992);
      return window.innerWidth <= breakpoint;
    }

    destroy() {
      this.modules.forEach((module) => {
        if (typeof module.destroy === 'function') {
          module.destroy();
        }
      });
      this.modules.clear();

      if (Drupal.solo.menuState) {
        Drupal.solo.menuState.unregisterComponent(COMPONENT_NAME);
      }
    }
  }

  Drupal.solo.keyboard.Core = KeyboardNavigationCore;

  Drupal.behaviors.soloKeyboardNavigation = {
    attach: function(context, settings) {
      const menus = once('solo-keyboard', '.solo-keyboard-enabled', context);
      menus.forEach(menu => {
        menu._soloKeyboardInstance = new KeyboardNavigationCore(menu);
      });
    },

    detach: function(context, settings, trigger) {
      if (trigger === 'unload') {
        const menus = once.filter('solo-keyboard', '.solo-keyboard-enabled', context);
        menus.forEach(menu => {
          if (menu._soloKeyboardInstance) {
            menu._soloKeyboardInstance.destroy();
            delete menu._soloKeyboardInstance;
          }
        });
      }
    }
  };
})(Drupal, drupalSettings, once);
