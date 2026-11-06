/**
 * @file
 * The color scheme picker: top tabs, one per category, and the selected scheme's
 * name in text. Selection itself is native radios and needs no script; without
 * this file every category shows, each with its heading.
 */
((Drupal, once) => {
  /**
   * The name a radio's label shows.
   *
   * @param {HTMLInputElement} radio
   *   A radio of the picker.
   *
   * @return {string}
   *   The name.
   */
  const schemeName = (radio) => {
    const name =
      radio.labels && radio.labels[0]
        ? radio.labels[0].querySelector('.solo-schemes__name')
        : null;
    return name ? name.textContent.trim() : '';
  };

  /**
   * Turns each category group into a tab panel with a tab above them.
   *
   * A browser lets Tab reach only the checked radio of a group, even when that
   * radio sits in a hidden panel. So a panel without the checked radio takes
   * focus itself, and an arrow key or Enter moves focus to its first radio.
   *
   * @param {HTMLElement} picker
   *   The picker's wrapper.
   *
   * @return {Function}
   *   Marks the tab and the panel that hold the checked radio.
   */
  const setUpTabs = (picker) => {
    const panels = Array.from(picker.querySelectorAll('.solo-schemes__group'));
    const tablist = document.createElement('div');
    tablist.className = 'solo-schemes__tabs';
    tablist.setAttribute('role', 'tablist');
    tablist.setAttribute('aria-label', Drupal.t('Color scheme categories'));

    const entries = panels.map((panel, index) => {
      const title = panel.querySelector('.solo-schemes__group-title');
      const tab = document.createElement('button');
      tab.type = 'button';
      tab.id = `solo-schemes-tab-${index}`;
      tab.className = 'solo-schemes__tab';
      tab.setAttribute('role', 'tab');
      tab.append(title.textContent.trim());

      const mark = document.createElement('span');
      mark.className = 'solo-schemes__tab-mark';
      mark.setAttribute('role', 'img');
      mark.setAttribute('aria-label', Drupal.t('holds the selected scheme'));
      tab.append(mark);
      tablist.append(tab);

      panel.id = panel.id || `solo-schemes-panel-${index}`;
      panel.setAttribute('role', 'tabpanel');
      panel.setAttribute('aria-labelledby', tab.id);
      tab.setAttribute('aria-controls', panel.id);
      title.hidden = true;
      return { tab, panel, mark };
    });
    panels[0].before(tablist);

    const select = (entry) => {
      entries.forEach((other) => {
        const on = other === entry;
        other.tab.setAttribute('aria-selected', on ? 'true' : 'false');
        other.tab.tabIndex = on ? 0 : -1;
        other.panel.hidden = !on;
      });
    };

    tablist.addEventListener('click', (event) => {
      const entry = entries.find(
        (candidate) => candidate.tab === event.target.closest('[role="tab"]'),
      );
      if (entry) {
        select(entry);
      }
    });

    tablist.addEventListener('keydown', (event) => {
      const index = entries.findIndex(
        (candidate) => candidate.tab === event.target.closest('[role="tab"]'),
      );
      if (index < 0) {
        return;
      }
      const rtl = window.getComputedStyle(tablist).direction === 'rtl';
      const steps = { ArrowRight: rtl ? -1 : 1, ArrowLeft: rtl ? 1 : -1 };
      let next = null;
      if (event.key in steps) {
        next = (index + steps[event.key] + entries.length) % entries.length;
      } else if (event.key === 'Home') {
        next = 0;
      } else if (event.key === 'End') {
        next = entries.length - 1;
      }
      if (next !== null) {
        event.preventDefault();
        select(entries[next]);
        entries[next].tab.focus();
      }
    });

    const enterKeys = [
      'ArrowDown',
      'ArrowUp',
      'ArrowLeft',
      'ArrowRight',
      'Enter',
      ' ',
    ];
    panels.forEach((panel) => {
      panel.addEventListener('keydown', (event) => {
        if (event.target === panel && enterKeys.includes(event.key)) {
          const radio = panel.querySelector('input[type="radio"]');
          if (radio) {
            event.preventDefault();
            radio.focus();
          }
        }
      });
    });

    const mark = () => {
      entries.forEach(({ panel, mark: tabMark }) => {
        const holds =
          panel.querySelector('input[type="radio"]:checked') !== null;
        tabMark.hidden = !holds;
        if (holds) {
          panel.removeAttribute('tabindex');
        } else {
          panel.tabIndex = 0;
        }
      });
    };
    mark();
    select(
      entries.find(({ panel }) =>
        panel.querySelector('input[type="radio"]:checked'),
      ) || entries[0],
    );
    return mark;
  };

  /**
   * Sets up the tabs and the line with the selected name.
   *
   * @param {HTMLElement} picker
   *   The picker's wrapper.
   */
  const setUpPicker = (picker) => {
    const strong = document.createElement('strong');
    const current = document.createElement('p');
    current.className = 'solo-schemes__current';
    current.append(`${Drupal.t('Selected:')} `, strong);
    picker.prepend(current);

    const mark = setUpTabs(picker);
    const update = () => {
      const checked = picker.querySelector('input[type="radio"]:checked');
      strong.textContent = checked ? schemeName(checked) : Drupal.t('None');
      mark();
    };
    picker.addEventListener('change', update);
    update();
  };

  Drupal.behaviors.soloSchemePicker = {
    attach(context) {
      once('solo-scheme-picker', '.solo-schemes', context).forEach(setUpPicker);
    },
  };
})(Drupal, once);
