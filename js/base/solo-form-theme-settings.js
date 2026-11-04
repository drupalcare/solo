/**
 * @file
 * Solo
 *
 * Filename:     solo-form-theme-settings.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */
((Drupal, once) => {
  "use strict";

  // What core marks as a field that failed validation, in any admin theme.
  // Not ".error" alone: some field labels still carry that class.
  const errorSelector = ".form-item--error, .form-item__error-message, [aria-invalid=\"true\"]";

  /**
   * The element the URL's fragment names, if any.
   *
   * @return {HTMLElement|null}
   *   The target.
   */
  const hashTarget = () => {
    const hash = window.location.hash.slice(1);
    if (!hash) {
      return null;
    }
    try {
      return document.getElementById(decodeURIComponent(hash));
    }
    catch {
      return document.getElementById(hash);
    }
  };

  /**
   * Tells whether core has a reason to show a given part of this section.
   *
   * Core picks the tab that holds a validation error or the URL's target.
   * Script must not undo that choice.
   *
   * @param {HTMLDetailsElement} section
   *   The top-level section.
   *
   * @return {boolean}
   *   TRUE when the section holds an error or a target inside it.
   */
  const coreNeedsSection = (section) => {
    if (section.querySelector(errorSelector)) {
      return true;
    }
    const target = hashTarget();
    return target !== null && target !== section && section.contains(target);
  };

  /**
   * A section's own title, without the summary core or a theme adds beside it.
   *
   * @param {HTMLDetailsElement} section
   *   A details element.
   *
   * @return {string}
   *   The title.
   */
  const summaryTitle = (section) => {
    const summary = section.querySelector(":scope > summary");
    if (!summary) {
      return "";
    }
    const copy = summary.cloneNode(true);
    copy.querySelectorAll(".summary, .claro-details__summary-summary, .visually-hidden").forEach((extra) => extra.remove());
    return copy.textContent.trim();
  };

  /**
   * Lists details other modules added at the top of the form in an Other group.
   *
   * @param {HTMLFormElement} form
   *   The theme settings form.
   * @param {HTMLElement} before
   *   The navigation element the group goes before.
   */
  const addOtherGroup = (form, before) => {
    const others = Array.from(form.querySelectorAll(":scope > details:not([data-solo-section]), :scope > .solo-sections > details:not([data-solo-section])"));
    if (!others.length) {
      return;
    }
    const template = form.querySelector("template[data-solo-icon=\"other\"]");
    const icon = () => (template ? template.content.cloneNode(true) : "");
    const heading = document.createElement("h2");
    heading.className = "solo-nav__group";
    heading.append(icon(), Drupal.t("Other"));
    const list = document.createElement("ul");
    list.className = "solo-nav__list";
    others.forEach((section, index) => {
      section.id = section.id || `solo-other-section-${index}`;
      section.setAttribute("data-solo-section", "other");
      const name = document.createElement("span");
      name.className = "solo-nav__name";
      name.textContent = summaryTitle(section);
      const link = document.createElement("a");
      link.className = "solo-nav__link";
      link.href = `#${section.id}`;
      link.append(icon(), name);
      const item = document.createElement("li");
      item.className = "solo-nav__item";
      item.append(link);
      list.append(item);
    });
    before.before(heading, list);
  };

  /**
   * Turns the navigation on: one section shows, chosen from the left column.
   *
   * Without this every section stays open in source order and the links jump.
   *
   * @param {HTMLFormElement} form
   *   The theme settings form.
   */
  const setUpNavigation = (form) => {
    const nav = form.querySelector(".solo-nav");
    const menu = nav ? nav.querySelector(".solo-nav__menu") : null;
    if (!menu) {
      return;
    }
    const toggle = menu.querySelector(":scope > summary");
    const currentName = menu.querySelector(".solo-nav__current");
    const note = menu.querySelector(".solo-nav__note");
    addOtherGroup(form, note);

    // Each link and the section it names, with a heading to move focus to. The
    // summary is hidden in this mode, so the heading carries the title.
    const entries = [];
    nav.querySelectorAll(".solo-nav__link").forEach((link) => {
      const section = document.getElementById(link.getAttribute("href").slice(1));
      if (!section || !form.contains(section)) {
        return;
      }
      const heading = document.createElement("h2");
      heading.className = "solo-section-title";
      heading.tabIndex = -1;
      heading.textContent = link.querySelector(".solo-nav__name").textContent.trim();
      const summary = section.querySelector(":scope > summary");
      if (summary) {
        summary.after(heading);
      }
      else {
        section.prepend(heading);
      }
      entries.push({ link, section, heading });
    });
    if (!entries.length) {
      return;
    }

    const entryHolding = (element) => entries.find((entry) => element && entry.section.contains(element)) || null;
    const entryWithId = (id) => entries.find((entry) => entry.section.id === id) || null;

    const storageKey = `solo.settings.section:${window.location.pathname}`;
    const readStored = () => {
      try {
        return window.sessionStorage.getItem(storageKey);
      }
      catch {
        return null;
      }
    };
    const writeStored = (id) => {
      try {
        window.sessionStorage.setItem(storageKey, id);
      }
      catch {
        // Private modes may refuse storage. The URL still holds the selection.
      }
    };

    // CSS alone decides when the menu is compact: it shows the toggle then.
    let compact = null;
    const syncMenu = () => {
      const now = window.getComputedStyle(toggle).display !== "none";
      if (now !== compact) {
        compact = now;
        menu.open = !now;
      }
    };

    let current = null;
    const select = (entry, moveFocus) => {
      current = entry;
      entries.forEach((other) => {
        other.section.hidden = other !== entry;
        other.link.removeAttribute("aria-current");
      });
      entry.section.open = true;
      entry.link.setAttribute("aria-current", "location");
      currentName.textContent = entry.heading.textContent;
      writeStored(entry.section.id);
      if (compact) {
        menu.open = false;
      }
      if (moveFocus) {
        entry.heading.focus();
      }
    };

    // A nav item says so, in words, when its section holds a validation error.
    const withErrors = entries.filter((entry) => entry.section.querySelector(errorSelector));
    withErrors.forEach((entry) => {
      const mark = document.createElement("small");
      mark.className = "solo-nav__error";
      mark.textContent = Drupal.t("Has an error");
      entry.link.append(mark);
    });

    form.classList.add("solo-nav-active");
    syncMenu();
    new ResizeObserver(syncMenu).observe(form);

    const target = hashTarget();
    const targetEntry = entryHolding(target);
    let initial = targetEntry || entryWithId(readStored()) || entries[0];
    if (withErrors.length) {
      initial = withErrors.includes(targetEntry) ? targetEntry : withErrors[0];
    }
    select(initial, false);
    if (target && targetEntry === initial) {
      target.scrollIntoView();
    }

    // Links are handled here so history gets one entry and focus lands on the
    // heading. A modified click keeps its native meaning, such as a new tab.
    nav.addEventListener("click", (event) => {
      const link = event.target.closest(".solo-nav__link");
      if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
        return;
      }
      const entry = entries.find((candidate) => candidate.link === link);
      if (!entry) {
        return;
      }
      event.preventDefault();
      const hash = link.getAttribute("href");
      if (window.location.hash !== hash) {
        window.history.pushState(null, "", hash);
      }
      // Moving to another section starts on its first tab, unless core chose a
      // tab for an error. The click goes through core's own handler.
      if (entry !== current && !coreNeedsSection(entry.section) && !entry.section.querySelector(":is(input, select, textarea):invalid")) {
        const firstLink = entry.section.querySelector(".vertical-tabs .vertical-tabs__menu-link");
        if (firstLink && !firstLink.closest(".is-selected")) {
          firstLink.click();
        }
      }
      select(entry, true);
    });

    // Back, forward, a typed fragment, or a link to a field inside a section.
    // Core picks the tab and focuses a field; a section only has to be shown.
    window.addEventListener("hashchange", () => {
      const changed = hashTarget();
      const entry = window.location.hash ? entryHolding(changed) : initial;
      if (!entry) {
        return;
      }
      const onSection = !changed || changed === entry.section;
      if (entry !== current || onSection) {
        select(entry, onSection);
      }
      if (!onSection) {
        changed.scrollIntoView({ block: "center" });
      }
    });

    // The browser cannot report an invalid field in a hidden section or tab, and
    // would block the submit in silence. It reports the first, so show that one.
    let reporting = false;
    form.addEventListener("invalid", (event) => {
      if (reporting) {
        return;
      }
      reporting = true;
      window.setTimeout(() => {
        reporting = false;
      }, 0);
      const entry = entryHolding(event.target);
      if (!entry) {
        return;
      }
      if (entry !== current) {
        select(entry, false);
      }
      const pane = event.target.closest(".vertical-tabs__pane");
      if (pane && pane.style.display === "none") {
        const tabLink = entry.section.querySelector(`.vertical-tabs__menu-link[href="#${CSS.escape(pane.id)}"]`);
        if (tabLink) {
          tabLink.click();
        }
      }
    }, true);
  };

  // Google Material Icons "content_copy" and "check", trimmed to their edges.
  const ICONS = {
    copy: {
      viewBox: '2 1 19 22',
      path: 'M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z',
    },
    check: {
      viewBox: '3.41 5.59 17.59 13.41',
      path: 'M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z',
    },
  };

  /**
   * Builds a decorative icon.
   *
   * @param {string} name
   *   A key of ICONS.
   *
   * @return {SVGSVGElement}
   *   The icon.
   */
  const icon = (name) => {
    const namespace = 'http://www.w3.org/2000/svg';
    const svg = document.createElementNS(namespace, 'svg');
    svg.setAttribute('viewBox', ICONS[name].viewBox);
    svg.setAttribute('aria-hidden', 'true');
    svg.setAttribute('focusable', 'false');
    const path = document.createElementNS(namespace, 'path');
    path.setAttribute('d', ICONS[name].path);
    svg.append(path);
    return svg;
  };

  /**
   * Copies text with the older browser command.
   *
   * The Clipboard API exists only on HTTPS and localhost, and many sites are
   * edited over plain HTTP.
   *
   * @param {string} text
   *   The text to copy.
   *
   * @return {boolean}
   *   Whether the browser reports that it copied.
   */
  const copyWithCommand = (text) => {
    const active = document.activeElement;
    const field = document.createElement('textarea');
    field.value = text;
    field.setAttribute('readonly', '');
    field.setAttribute('aria-hidden', 'true');
    field.style.position = 'fixed';
    field.style.insetBlockStart = '-100px';
    document.body.append(field);
    field.select();
    let copied = false;
    try {
      copied = document.execCommand('copy');
    } catch (error) {
      copied = false;
    }
    field.remove();
    if (active) {
      active.focus();
    }
    return copied;
  };

  /**
   * Copies text to the clipboard.
   *
   * @param {string} text
   *   The text to copy.
   *
   * @return {Promise<boolean>}
   *   Whether the text was copied.
   */
  const copyText = async (text) => {
    if (navigator.clipboard && window.isSecureContext) {
      try {
        await navigator.clipboard.writeText(text);
        return true;
      } catch (error) {
        return copyWithCommand(text);
      }
    }
    return copyWithCommand(text);
  };

  /**
   * Adds a copy button beside one class name.
   *
   * @param {HTMLElement} code
   *   The element that holds the class name.
   */
  const addCopyButton = (code) => {
    const name = code.textContent;
    const chip = document.createElement('span');
    chip.className = 'solo-classes__chip';
    code.before(chip);

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'solo-classes__copy';
    button.setAttribute('aria-label', Drupal.t('Copy class @name', { '@name': name }));
    button.append(icon('copy'));
    chip.append(code, button);

    let timer = null;
    button.addEventListener('click', async () => {
      const copied = await copyText(name);
      const message = copied ? Drupal.t('Copied') : Drupal.t('Copy failed');
      clearTimeout(timer);
      button.classList.toggle('solo-is-done', copied);
      button.replaceChildren(...(copied ? [icon('check')] : []), message);
      Drupal.announce(copied ? Drupal.t('@name copied', { '@name': name }) : message);
      timer = setTimeout(() => {
        button.classList.remove('solo-is-done');
        button.replaceChildren(icon('copy'));
      }, 1500);
    });
  };

  Drupal.behaviors.soloFormThemeSettings = {
    attach: function(context) {
      once('solo-classes-copy', '.solo-classes__chips > code, #edit-settings-page-wrapper li > code', context).forEach(addCopyButton);
      once("solo-form-theme-settings", "form.system-theme-settings", context).forEach((form) => {
        setUpNavigation(form);
      });

    }
  };
})(Drupal, once);
