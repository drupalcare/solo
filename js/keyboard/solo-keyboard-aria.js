((Drupal) => {
  'use strict';
  Drupal.solo = Drupal.solo || {};
  Drupal.solo.keyboard = Drupal.solo.keyboard || {};

  const COMPONENT_NAME = 'keyboard-aria';

  class KeyboardAriaModule {
    constructor(core) {
      this.core = core;
      this.menubar = core.menubar;
      this.observers = [];
    }

    init() {
      this.observeAriaChanges();
      this.ensureProperRoles();
    }

    ensureProperRoles() {
      // Disclosure navigation (W3C APG): no menu roles are added. The only
      // state is aria-expanded on the disclosure button, never on the <li>.
      this.menubar.querySelectorAll('li').forEach(li => {
        if (li.hasAttribute('aria-expanded')) {
          const toggler = li.querySelector(':scope > button.dropdown-toggler');
          if (toggler) {
            toggler.setAttribute('aria-expanded', li.getAttribute('aria-expanded'));
          }
          li.removeAttribute('aria-expanded');
        }
        li.removeAttribute('aria-haspopup');
      });
    }

    observeAriaChanges() {
      const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
          if (mutation.type === 'attributes' && mutation.attributeName === 'aria-expanded') {
            // FIX: Prevent redundant calls by checking if value actually changed
            const oldValue = mutation.oldValue;
            const newValue = mutation.target.getAttribute('aria-expanded');
            if (oldValue !== newValue) {
              this.syncAriaStates(mutation.target);
            }
          }
        });
      });

      this.menubar.querySelectorAll('button.dropdown-toggler').forEach(button => {
        observer.observe(button, {
          attributes: true,
          attributeFilter: ['aria-expanded'],
          attributeOldValue: true // Track old values to prevent redundant updates
        });
        this.observers.push({ element: button, observer });
      });
    }

    syncAriaStates(button) {
      const isExpanded = button.getAttribute('aria-expanded') === 'true';
      const submenu = button.nextElementSibling;

      if (submenu && submenu.tagName === 'UL') {
        // Only update if value differs from current state
        const currentHidden = submenu.getAttribute('aria-hidden');
        const newHidden = isExpanded ? 'false' : 'true';

        if (currentHidden !== newHidden) {
          if (Drupal.solo.menuState) {
            Drupal.solo.menuState.setAriaAttribute(submenu, 'aria-hidden', newHidden, COMPONENT_NAME);
          } else {
            submenu.setAttribute('aria-hidden', newHidden);
          }
        }
      }

      // FIXED: Remove aria-expanded from parent li - it should only be on the button
      const parentLi = button.closest('li');
      if (parentLi && parentLi.hasAttribute('aria-expanded')) {
        parentLi.removeAttribute('aria-expanded');
      }

      // Announce the menu state change
      this.announceMenuChange(button, isExpanded);
    }

    announceMenuChange(element, isOpen) {
      const parser = this.core.getModule('templateParser');
      const itemStructure = parser?.getItemStructure(element);

      if (itemStructure?.link || itemStructure?.button) {
        const textElement = itemStructure.link || itemStructure.button;
        const text = textElement.textContent.trim();
        if (Drupal.announce) {
          const state = isOpen ? Drupal.t('opened') : Drupal.t('closed');
          Drupal.announce(Drupal.t('@item submenu @state', {
            '@item': text,
            '@state': state
          }));
        }
      }
    }

    destroy() {
      this.observers.forEach(({ observer }) => observer.disconnect());
      this.observers = [];
    }
  }

  Drupal.solo.keyboard.aria = KeyboardAriaModule;
})(Drupal);
