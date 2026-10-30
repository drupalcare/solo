((Drupal) => {
  'use strict';
  Drupal.solo = Drupal.solo || {};
  Drupal.solo.keyboard = Drupal.solo.keyboard || {};

  const COMPONENT_NAME = 'keyboard-focus';

  class KeyboardFocusModule {
    constructor(core) {
      this.core = core;
      this.menubar = core.menubar;
      this.handleFocus = this.handleFocus.bind(this);
      this.handleBlur = this.handleBlur.bind(this);
    }

    init() {
      this.bindFocusEvents();
    }

    bindFocusEvents() {
      this.core.menuItems.forEach(item => {
        item.addEventListener('focus', this.handleFocus);
        item.addEventListener('blur', this.handleBlur);
      });
    }

    handleFocus(event) {
      const item = event.target;

      // Dropdowns open on Enter or Space, never on focus alone, in every
      // template (W3C disclosure navigation; WCAG 1.4.13).
      // No roving tabindex: disclosure navigation keeps every visible link
      // and button in the natural Tab order (W3C APG).
      item.classList.add('solo-keyboard-focus');
    }

    handleBlur(event) {
      event.target.classList.remove('solo-keyboard-focus');
    }

    destroy() {
      this.core.menuItems.forEach(item => {
        item.removeEventListener('focus', this.handleFocus);
        item.removeEventListener('blur', this.handleBlur);
      });
    }
  }

  Drupal.solo.keyboard.focus = KeyboardFocusModule;
})(Drupal);
