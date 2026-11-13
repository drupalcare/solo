# CSS class migration: Solo 1 and earlier 2.0 names to the current names

Generated from `config/map/solo-class-map.yml`, `config/map/solo-rename-map.yml` and `config/map/solo-variable-map.yml` in Solo Utilities by `scripts/class-list.py`. Do not edit by hand.

Solo renames its classes, ids, library ids, files, JavaScript names and CSS variables. A sub-theme or custom module that uses an old name stops matching until it is migrated. Solo Utilities ships a Drush command that does the renaming for you, and a Solo 1 name maps straight to its current name.

## Before you start: let Drush do the renaming

1. Save a copy of your theme (or commit it to Git).
2. Dry run, with a report written outside the web root (run from the project folder):

   ```
   drush solo:migrate-classes mytheme --stored --content --report=solo-report.md
   ```

   Nothing is written. The target is a theme machine name or a folder inside the Drupal root (`web/themes/custom/mytheme`, `web/modules/custom/my_module`). Drupal core, Solo itself and the public files folder are refused, and symbolic links are never followed.
3. Read the report. Configuration and content come first, then each file and its changed lines, then the items to check by hand.
4. Apply: `drush solo:migrate-classes mytheme --apply`. Add `--yml` to rewrite library ids in the theme `.info.yml` and `.libraries.yml` files, `--stored` to save the configuration changes, and `--content` to save id changes in content (each changed item becomes a new revision).
5. Clear the caches: `drush cr`, then check your pages.

What the command changes, by kind:

| Kind | Rewritten in | Only reported in |
|---|---|---|
| Classes and ids | `.css .scss .sass .less .twig .js` | `.theme .php .module .inc` |
| CSS variables | `.css .scss .sass .less .twig .js` | `.theme .php .module .inc` |
| Library ids | `.twig`, and `.info.yml` and `.libraries.yml` with `--yml` | `.theme .php .module .inc`, and the YAML files without `--yml` |
| JavaScript names | `.js` (data attributes and once ids also `.css .twig`) | `.theme .php .module .inc` |
| Files moved or renamed | never | every file type it reads |

`--ext=css,js` narrows the file types. Other `.yml` files, `*.min.*`, `node_modules`, `vendor`, `dist` and `.git` are never read. Old library ids keep working as deprecated aliases for one major release, so a missed id is a log line, not a broken page.

What `--stored` covers: the Solo inline CSS setting (`custom_code.inline_css`, also renamed automatically by `drush updb`), Block Class classes, Views CSS, row and field classes, and text format allowed tags with the matching CKEditor 5 settings. `--content` renames only ids, in formatted text fields and menu link attributes.

Report-only rules: a name that is one plain word, or is both a class and an id, is renamed only where the line plainly sets classes (or ids). State classes (`toggled`, `small-screen` ...) and generic words (`facebook`, `header` ...) are renamed only when chained to a Solo selector in a stylesheet; elsewhere they are reported. A renamed name with two possible new names (a split or a merge) is never rewritten, only reported.

## What is on this list

| Kind | Names |
|---|---|
| Classes: Solo 1 to the current name | 260 |
| Ids: Solo 1 to the current name | 57 |
| State classes set by Solo JavaScript (chained to a Solo selector only) | 19 |
| Generic words (chained to a Solo selector only) | 19 |
| Context-dependent classes (pick by hand) | 2 |
| Removed classes (no new element) | 5 |
| Classes renamed again while 2.0 was in development | 44 |
| Library ids | 348 |
| Files moved, renamed, split or merged | 74 |
| JavaScript behaviors | 10 |
| once() ids and data-once values | 12 |
| drupalSettings keys | 1 |
| Data attributes | 8 |
| JavaScript events | 1 |
| CSS variables (region colors) | 31 |
| Unchanged on purpose | 165 |

## Classes: Solo 1 to the current name (260)

A trailing `*` is a dynamic tail: the old prefix followed by any value becomes the new prefix with the same value.

| Solo 1 | Now |
|---|---|
| `activate-branding` | `solo-menu--has-branding` |
| `active-bottom-*` | `solo-container--bottom-active-*` |
| `active-footer-*` | `solo-container--footer-active-*` |
| `active-main-*` | `solo-container--main-active-*` |
| `active-top-*` | `solo-container--top-active-*` |
| `add-comment--after` | `solo-add-comment--after` |
| `add-comment--before` | `solo-add-comment--before` |
| `admin-context` | `solo-body--admin-context` |
| `animate-border` | `solo-container--animate-border` |
| `animate-hover` | `solo-container--animate-hover` |
| `arrow-circle-left` | `solo-icon--arrow-circle-left` |
| `arrow-circle-right` | `solo-icon--arrow-circle-right` |
| `bottom-box` | `solo-region-box--bottom` |
| `bottom-box-first` | `solo-region-box--bottom-first` |
| `bottom-box-first-inner` | `solo-region-box__inner--bottom-first` |
| `bottom-box-fourth` | `solo-region-box--bottom-fourth` |
| `bottom-box-fourth-inner` | `solo-region-box__inner--bottom-fourth` |
| `bottom-box-inner` | `solo-region-box__inner--bottom` |
| `bottom-box-second` | `solo-region-box--bottom-second` |
| `bottom-box-second-inner` | `solo-region-box__inner--bottom-second` |
| `bottom-box-third` | `solo-region-box--bottom-third` |
| `bottom-box-third-inner` | `solo-region-box__inner--bottom-third` |
| `bottom-container` | `solo-container--bottom` |
| `bottom-container-inner` | `solo-container__inner--bottom` |
| `branding-first` | `solo-branding__first` |
| `branding-second` | `solo-branding__second` |
| `branding-wrapper` | `solo-branding` |
| `breadcrumb-inner` | `solo-breadcrumb__inner` |
| `breadcrumb-menu` | `solo-breadcrumb__menu` |
| `breadcrumb-nav` | `solo-breadcrumb__nav` |
| `breadcrumb-sep` | `solo-breadcrumb__separator` |
| `breadcrumb-title` | `solo-breadcrumb__title` |
| `btn-animate` | `solo-btn-animate` |
| `button-only` | `solo-menu-tree__item--button-only` |
| `clear-all` | `solo-clear--all` |
| `comment--has-parent` | `solo-comment--has-parent` |
| `comment--heading-after` | `solo-comment--heading-after` |
| `comment--heading-first` | `solo-comment--heading-first` |
| `comment--threaded` | `solo-comment--threaded` |
| `comment__body` | `solo-comment__body` |
| `comment__footer` | `solo-comment__footer` |
| `comment__header` | `solo-comment__header` |
| `comment__header-info` | `solo-comment__header-info` |
| `comment__permalink` | `solo-comment__permalink` |
| `comment__replies-icon` | `solo-comment__replies-icon` |
| `comment__replies-icon--close` | `solo-comment__replies-icon--close` |
| `comment__replies-icon--open` | `solo-comment__replies-icon--open` |
| `comment__replies-text` | `solo-comment__replies-text` |
| `comment__replies-toggle` | `solo-comment__replies-toggle` |
| `comment__replies-wrapper` | `solo-comment__replies-wrapper` |
| `comment__title` | `solo-comment__title` |
| `comments__icon` | `solo-comments__icon` |
| `comments__label` | `solo-comments__label` |
| `copyright-first` | `solo-copyright__first` |
| `copyright-formatted` | `solo-copyright__text` |
| `copyright-inner` | `solo-copyright__inner` |
| `copyright-second` | `solo-copyright__second` |
| `core-task` | `solo-menu-local-task--core` |
| `delete-task` | `solo-menu-local-task--delete` |
| `des-spacer` | `solo-file-upload-help__spacer` |
| `dropdown-arrow` | `solo-menu-tree__arrow` |
| `dropdown-toggler` | `solo-menu-tree__toggler` |
| `dropdown-toggler-child` | `solo-menu-tree__toggler--child` |
| `dropdown-toggler-parent` | `solo-menu-tree__toggler--parent` |
| `ds-link` | `solo-menu-tree__toggler--link-disabled` |
| `element-container-wrapper` | `solo-form__element-container` |
| `en-link` | `solo-menu-tree__toggler--link-enabled` |
| `enable-legacy-animations` | `solo-body--legacy-animations` |
| `entity-ref-wrapper` | `solo-form__entity-ref` |
| `entity-select-wrapper` | `solo-form__entity-select` |
| `expand-circle-down` | `solo-icon--expand-circle-down` |
| `expand-left` | `solo-menu--expand-left` |
| `expand-right` | `solo-menu--expand-right` |
| `exposed-form` | `solo-exposed-form` |
| `external-link` | `solo-menu-tree__link--external` |
| `fade-inner` | `solo-fade-target` |
| `fixed-search-block` | `solo-fixed-search-block` |
| `fixed-search-block-inner` | `solo-fixed-search-block__inner` |
| `fixed-search-wrapper` | `solo-fixed-search-block__wrapper` |
| `footer-box` | `solo-region-box--footer` |
| `footer-box-first` | `solo-region-box--footer-first` |
| `footer-box-first-inner` | `solo-region-box__inner--footer-first` |
| `footer-box-inner` | `solo-region-box__inner--footer` |
| `footer-box-second` | `solo-region-box--footer-second` |
| `footer-box-second-inner` | `solo-region-box__inner--footer-second` |
| `footer-box-third` | `solo-region-box--footer-third` |
| `footer-box-third-inner` | `solo-region-box__inner--footer-third` |
| `footer-container` | `solo-container--footer` |
| `footer-container-inner` | `solo-container__inner--footer` |
| `footer-menu` | `solo-footer-menu__nav` |
| `footer-menu-first` | `solo-footer-menu__nav__first` |
| `footer-menu-inner` | `solo-footer-menu__nav__inner` |
| `footer-menu-second` | `solo-footer-menu__nav__second` |
| `form-group` | `solo-form__group` |
| `hamburger-icon` | `solo-hamburger` |
| `hamburger-icon-close` | `solo-hamburger--close` |
| `has-border` | `solo-container--has-border` |
| `has-sub__menu` | `solo-menu-tree__item--has-submenu` |
| `header-inner` | `solo-header__inner` |
| `highlighted-inner` | `solo-highlighted__inner` |
| `hs-icons-left` | `solo-icons-left` |
| `icon-account` | `solo-icon--account` |
| `icon-calendar` | `solo-icon--calendar` |
| `icon-comment` | `solo-icon--comment` |
| `icon-contact` | `solo-icon--contact` |
| `icon-delete` | `solo-icon--delete` |
| `icon-edit` | `solo-icon--edit` |
| `icon-revisions` | `solo-icon--revisions` |
| `icon-view` | `solo-icon--view` |
| `img--is-clickable` | `solo-image--clickable` |
| `img-placeholder` | `solo-image--placeholder` |
| `info-icon` | `solo-info-icon` |
| `input-group` | `solo-form__input-group` |
| `keyboard-button` | `solo-menu-tree__button--keyboard` |
| `li-*` | `solo-item-depth-*` |
| `link-and-button` | `solo-menu-tree__item--link-and-button` |
| `link-only` | `solo-menu-tree__item--link-only` |
| `login-block-button-close-inner` | `solo-popup-login-block__close-inner` |
| `login-block-button-open-inner` | `solo-popup-login-block__open-inner` |
| `login-button-close` | `solo-popup-login-block__close` |
| `login-button-open` | `solo-popup-login-block__open` |
| `login-popup-trigger` | `solo-popup-login-block__trigger` |
| `lone` | `solo-container--single` |
| `main-box` | `solo-region-box--main` |
| `main-box-inner` | `solo-region-box__inner--main` |
| `main-box-sides` | `solo-region-box--main-sides` |
| `main-container` | `solo-container--main` |
| `main-container-inner` | `solo-container__inner--main` |
| `main-navigation-wrapper` | `solo-menu-main` |
| `maintenance-page` | `solo-maintenance-page` |
| `marker-is-green` | `solo-mark--green` |
| `marker-is-red` | `solo-mark--red` |
| `megamenu-column` | `solo-menu-tree__megamenu-column` |
| `menu-align-*` | `solo-menu--align-*` |
| `menu-branding` | `solo-menu-branding` |
| `menu-branding-logo` | `solo-menu-branding__logo` |
| `menu-branding-site-name` | `solo-menu-branding__site-name` |
| `menu-flipped` | `solo-menu--flipped` |
| `menu-level-*` | `solo-menu-tree--level-*` |
| `menu-link` | `solo-menu__link` |
| `menu__url-title` | `solo-menu-tree__title` |
| `menu__url-title-disabled` | `solo-menu-tree__title--disabled` |
| `menu__url-title-enabled` | `solo-menu-tree__title--enabled` |
| `mobile-menubar-toggler-button` | `solo-hamburger__button` |
| `mobile-nav` | `solo-menu-main--mobile` |
| `multi` | `solo-container--multi` |
| `nav__menu-button` | `solo-menu-tree__button` |
| `nav__menu-button-*` | `solo-menu-tree__button--*` |
| `nav__menu-item` | `solo-menu-tree__item` |
| `nav__menu-item-*` | `solo-menu-tree__item--*` |
| `nav__menu-link` | `solo-menu-tree__link` |
| `nav__menu-link-*` | `solo-menu-tree__link--*` |
| `nav__menu-nolink` | `solo-menu-tree__nolink` |
| `nav__menu-nolink-*` | `solo-menu-tree__nolink--*` |
| `nav__menubar` | `solo-menu-tree__menubar` |
| `nav__menubar-item` | `solo-menu-tree__menubar-item` |
| `nav__submenu-item` | `solo-menu-tree__submenu-item` |
| `navigation-default` | `solo-menu--default` |
| `navigation-primary-responsive` | `solo-menu--primary-responsive` |
| `navigation-primary-sidebar` | `solo-menu--primary-sidebar` |
| `navigation-responsive` | `solo-menu--responsive` |
| `navigation-responsive-click` | `solo-menu--responsive-click` |
| `navigation-responsive-hover` | `solo-menu--responsive-hover` |
| `navigation-sidebar` | `solo-menu--sidebar` |
| `navigation-sidebar-click` | `solo-menu--sidebar-click` |
| `navigation-sidebar-hover` | `solo-menu--sidebar-hover` |
| `navigation-sidebar-template` | `solo-menu--sidebar-template` |
| `navigation__default` | `solo-menu-tree--default` |
| `navigation__megamenu` | `solo-menu-tree--megamenu` |
| `navigation__menubar` | `solo-menu-tree__menubar` |
| `navigation__menubar-*` | `solo-menu-tree__menubar--*` |
| `navigation__primary` | `solo-menu-tree--primary` |
| `navigation__primary_sidebar` | `solo-menu-tree--primary-sidebar` |
| `navigation__responsive` | `solo-menu-tree--responsive` |
| `navigation__responsive__click` | `solo-menu-tree--responsive-click` |
| `navigation__responsive__hover` | `solo-menu-tree--responsive-hover` |
| `navigation__sidebar` | `solo-menu-tree--sidebar` |
| `navigation__sidebar__template` | `solo-menu-tree--sidebar-template` |
| `no-link` | `solo-menu-tree__item--no-link` |
| `node__author-info` | `solo-node__author-info` |
| `page-title-inner` | `solo-page-title__inner` |
| `page-title-text` | `solo-page-title__text` |
| `page-wrapper` | `solo-page` |
| `path-update` | `solo-svg-icon__path` |
| `popup-login-block` | `solo-popup-login-block` |
| `popup-login-block-inner` | `solo-popup-login-block__inner` |
| `popup-login-description` | `solo-popup-login__description` |
| `popup-login-title` | `solo-popup-login__title` |
| `primary-menu` | `solo-primary-menu` |
| `primary-menu-inner` | `solo-primary-menu__inner` |
| `primary-sidebar-menu` | `solo-primary-sidebar-menu` |
| `primary-sidebar-menu-inner` | `solo-primary-sidebar-menu__inner` |
| `px` | `solo-body--px-widths` |
| `reading-mode` | `solo-reading-mode` |
| `region-inner` | `solo-region-box__inner` |
| `region-outer` | `solo-region-box` |
| `revisions-task` | `solo-menu-local-task--revisions` |
| `search-block-button-close-inner` | `solo-fixed-search-block__close-inner` |
| `search-button-block` | `solo-fixed-search-block__button-block` |
| `search-button-close` | `solo-fixed-search-block__close` |
| `search-button-open` | `solo-fixed-search-block__open` |
| `sidebar-box-first` | `solo-region-box--sidebar-first` |
| `sidebar-box-first-inner` | `solo-region-box__inner--sidebar-first` |
| `sidebar-box-main` | `solo-region-box--content` |
| `sidebar-box-main-inner` | `solo-region-box__inner--content` |
| `sidebar-box-second` | `solo-region-box--sidebar-second` |
| `sidebar-box-second-inner` | `solo-region-box__inner--sidebar-second` |
| `sidebar-button-close` | `solo-primary-sidebar-menu__close` |
| `sidebar-button-open` | `solo-primary-sidebar-menu__open` |
| `sidebar-hamburger-icon` | `solo-hamburger--sidebar` |
| `site-logo-img` | `solo-branding__logo-image` |
| `site-name-block` | `solo-branding__site-name` |
| `site-name-link` | `solo-branding__site-name-link` |
| `site-name-outer` | `solo-branding__second--has-name` |
| `site-slogan-outer` | `solo-branding__second--has-slogan` |
| `skip-anchor-target` | `solo-skip-target` |
| `sm-icon` | `solo-social__icon` |
| `sm-icons` | `solo-social` |
| `solo-preloader--logo_text` | `solo-preloader--logo-text` |
| `solo-preloader--progress_bar` | `solo-preloader--progress-bar` |
| `solo__bc-scroll` | `solo-page--breadcrumb-scroll` |
| `solo__fade-in` | `solo-page--fade-in` |
| `solo__inline-items` | `solo-page--inline-items` |
| `sub-mega` | `solo-menu-tree__submenu--mega` |
| `sub__mega` | `solo-menu-tree__submenu--mega` |
| `sub__menu` | `solo-menu-tree__submenu` |
| `sub__menu-*` | `solo-menu-tree__submenu--*` |
| `sub__menu-header` | `solo-menu-tree__submenu--header` |
| `submenu-flipped-left` | `solo-menu-tree__submenu--flipped-left` |
| `submenu-flipped-right` | `solo-menu-tree__submenu--flipped-right` |
| `svg-icon` | `solo-svg-icon` |
| `svg-stroke` | `solo-svg-icon--stroke` |
| `system-messages` | `solo-status-messages` |
| `system-messages-inner` | `solo-status-messages__inner` |
| `task-icon` | `solo-menu-local-task__icon` |
| `task-local` | `solo-menu-local-task` |
| `term-title` | `solo-term__title` |
| `toggler-icon` | `solo-menu-tree__toggler-icon` |
| `top-box` | `solo-region-box--top` |
| `top-box-first` | `solo-region-box--top-first` |
| `top-box-first-inner` | `solo-region-box__inner--top-first` |
| `top-box-inner` | `solo-region-box__inner--top` |
| `top-box-second` | `solo-region-box--top-second` |
| `top-box-second-inner` | `solo-region-box__inner--top-second` |
| `top-box-third` | `solo-region-box--top-third` |
| `top-box-third-inner` | `solo-region-box__inner--top-third` |
| `top-container` | `solo-container--top` |
| `top-container-inner` | `solo-container__inner--top` |
| `ul-*` | `solo-list-depth-*` |
| `url-added` | `solo-menu-tree__link--has-url` |
| `user-profile-compact` | `solo-user-profile--compact` |
| `user-profile-page` | `solo-body--user-profile` |
| `username--link` | `solo-username--link` |
| `username--plain` | `solo-username--plain` |
| `view-task` | `solo-menu-local-task--view` |
| `views-page` | `solo-body--views-page` |
| `welcome-text` | `solo-welcome-text` |
| `welcome-text-inner` | `solo-welcome-text__inner` |
| `wrapper-responsive` | `solo-table__menu-wrapper--responsive` |
| `wrapper-sticky` | `solo-table__menu-wrapper--sticky` |

## Ids: Solo 1 to the current name (57)

| Solo 1 | Now |
|---|---|
| `Layer_1` | `solo-svg-layer-1` |
| `bottom-box-first` | `solo-bottom-box-first` |
| `bottom-box-fourth` | `solo-bottom-box-fourth` |
| `bottom-box-second` | `solo-bottom-box-second` |
| `bottom-box-third` | `solo-bottom-box-third` |
| `bottom-container` | `solo-bottom-container` |
| `bottom-container-inner` | `solo-bottom-container-inner` |
| `breadcrumb` | `solo-breadcrumb` |
| `breadcrumb-inner` | `solo-breadcrumb-inner` |
| `copyright` | `solo-copyright` |
| `copyright-inner` | `solo-copyright-inner` |
| `fixed-search-block` | `solo-fixed-search-block` |
| `fixed-search-block-inner` | `solo-fixed-search-block-inner` |
| `footer-box-first` | `solo-footer-box-first` |
| `footer-box-second` | `solo-footer-box-second` |
| `footer-box-third` | `solo-footer-box-third` |
| `footer-container` | `solo-footer-container` |
| `footer-container-inner` | `solo-footer-container-inner` |
| `footer-content` | `solo-footer-content` |
| `footer-menu` | `solo-footer-menu` |
| `footer-menu-inner` | `solo-footer-menu-inner` |
| `header` | `solo-header` |
| `header-content` | `solo-header-content` |
| `header-inner` | `solo-header-inner` |
| `highlighted` | `solo-highlighted` |
| `highlighted-inner` | `solo-highlighted-inner` |
| `login-button-close` | `solo-login-button-close` |
| `login-button-open` | `solo-login-button-open` |
| `main-container` | `solo-main-container` |
| `main-container-inner` | `solo-main-container-inner` |
| `main-navigation-content` | `solo-main-navigation-content` |
| `page-title` | `solo-page-title` |
| `page-title-inner` | `solo-page-title-inner` |
| `page-wrapper` | `solo-page-wrapper` |
| `popup-login-block` | `solo-popup-login-block` |
| `popup-login-block-inner` | `solo-popup-login-block-inner` |
| `primary-menu` | `solo-primary-menu` |
| `primary-menu-inner` | `solo-primary-menu-inner` |
| `primary-sidebar-menu` | `solo-primary-sidebar-menu` |
| `primary-sidebar-menu-inner` | `solo-primary-sidebar-menu-inner` |
| `replies-*` | `solo-comment-replies-*` |
| `search-button-close` | `solo-fixed-search-block__close` |
| `search-button-open` | `solo-fixed-search-block__open` |
| `sidebar-box-first` | `solo-sidebar-box-first` |
| `sidebar-box-main` | `solo-sidebar-box-main` |
| `sidebar-box-second` | `solo-sidebar-box-second` |
| `sidebar-button-close` | `solo-menu-primary-sidebar__close` |
| `sidebar-button-open` | `solo-menu-primary-sidebar__open` |
| `system-messages` | `solo-status-messages` |
| `system-messages-inner` | `solo-system-messages-inner` |
| `top-box-first` | `solo-top-box-first` |
| `top-box-second` | `solo-top-box-second` |
| `top-box-third` | `solo-top-box-third` |
| `top-container` | `solo-top-container` |
| `top-container-inner` | `solo-top-container-inner` |
| `welcome-text` | `solo-welcome-text` |
| `welcome-text-inner` | `solo-welcome-text-inner` |

## State classes set by Solo JavaScript (chained to a Solo selector only) (19)

| Solo 1 | Now |
|---|---|
| `broken-image` | `solo-is-broken` |
| `focus-in` | `solo-is-focus-within` |
| `focus-visible` | `solo-is-focus-visible` |
| `is-done` | `solo-is-done` |
| `is-visible` | `solo-is-visible` |
| `large-screen` | `solo-is-large-screen` |
| `medium-screen` | `solo-is-medium-screen` |
| `region-l` | `solo-is-region-l` |
| `region-m` | `solo-is-region-m` |
| `region-max-*` | `solo-is-region-max-*` |
| `region-s` | `solo-is-region-s` |
| `region-xl` | `solo-is-region-xl` |
| `region-xs` | `solo-is-region-xs` |
| `region-xxl` | `solo-is-region-xxl` |
| `region-xxs` | `solo-is-region-xxs` |
| `slide-in` | `solo-is-sliding-in` |
| `slide-out` | `solo-is-sliding-out` |
| `small-screen` | `solo-is-small-screen` |
| `toggled` | `solo-is-open` |

## Generic words (chained to a Solo selector only) (19)

| Solo 1 | Now |
|---|---|
| `bluesky` | `solo-social__link--bluesky` |
| `copyright` | `solo-copyright` |
| `facebook` | `solo-social__link--facebook` |
| `header` | `solo-header` |
| `highlighted` | `solo-highlighted` |
| `instagram` | `solo-social__link--instagram` |
| `linkedin` | `solo-social__link--linkedin` |
| `mastodon` | `solo-social__link--mastodon` |
| `page-title` | `solo-page-title` |
| `pinterest` | `solo-social__link--pinterest` |
| `reddit` | `solo-social__link--reddit` |
| `rss` | `solo-social__link--rss` |
| `snapchat` | `solo-social__link--snapchat` |
| `telegram` | `solo-social__link--telegram` |
| `threads` | `solo-social__link--threads` |
| `tiktok` | `solo-social__link--tiktok` |
| `twitter` | `solo-social__link--twitter` |
| `whatsapp` | `solo-social__link--whatsapp` |
| `youtube` | `solo-social__link--youtube` |

## Context-dependent classes (pick by hand) (2)

| Solo 1 | Use |
|---|---|
| `nolink` | `solo-menu-tree__item--no-link or solo-menu-tree__nolink - printed beside no-link and nav__menu-nolink in 9 menu templates; S1 decides per element (needs checking)` |
| `section-*` | `solo-section--* - dynamic section- prefix printed once; origin needs checking in S1` |

## Removed classes (no new element) (5)

| Solo 1 | Why |
|---|---|
| `discription` | `typo selector, printed nowhere (needs checking)` |
| `node-from` | `typo selector, printed nowhere (needs checking)` |
| `svg-bottom` | `now the class solo-svg-bottom (the icon prints more than once per page, so an id repeated); change #svg-bottom to .solo-svg-bottom by hand` |
| `svg-middle` | `now the class solo-svg-middle (the icon prints more than once per page, so an id repeated); change #svg-middle to .solo-svg-middle by hand` |
| `svg-top` | `now the class solo-svg-top (the icon prints more than once per page, so an id repeated); change #svg-top to .solo-svg-top by hand` |

## Classes renamed again while 2.0 was in development (44)

A Solo 1 name above already points at the final name.

| Earlier 2.0 name | Now |
|---|---|
| `solo-comment-replies` | `solo-comment__replies` |
| `solo-file-button` | `solo-input-file__button` |
| `solo-file-custom` | `solo-input-file__custom` |
| `solo-file-name` | `solo-input-file__name` |
| `solo-file-native` | `solo-input-file__native` |
| `solo-file-upload-wrapper` | `solo-input-file__wrapper` |
| `solo-fixed-search` | `solo-fixed-search-block` |
| `solo-fixed-search__button-block` | `solo-fixed-search-block__button-block` |
| `solo-fixed-search__close` | `solo-fixed-search-block__close` |
| `solo-fixed-search__close-inner` | `solo-fixed-search-block__close-inner` |
| `solo-fixed-search__inner` | `solo-fixed-search-block__inner` |
| `solo-fixed-search__open` | `solo-fixed-search-block__open` |
| `solo-fixed-search__wrapper` | `solo-fixed-search-block__wrapper` |
| `solo-footer-nav` | `solo-footer-menu__nav` |
| `solo-footer-nav__first` | `solo-footer-menu__nav__first` |
| `solo-footer-nav__inner` | `solo-footer-menu__nav__inner` |
| `solo-footer-nav__second` | `solo-footer-menu__nav__second` |
| `solo-local-task` | `solo-menu-local-task` |
| `solo-local-task--core` | `solo-menu-local-task--core` |
| `solo-local-task--delete` | `solo-menu-local-task--delete` |
| `solo-local-task--revisions` | `solo-menu-local-task--revisions` |
| `solo-local-task--view` | `solo-menu-local-task--view` |
| `solo-local-task__icon` | `solo-menu-local-task__icon` |
| `solo-main-nav` | `solo-menu-main` |
| `solo-menu-wrapper--responsive` | `solo-table__menu-wrapper--responsive` |
| `solo-menu-wrapper--sticky` | `solo-table__menu-wrapper--sticky` |
| `solo-mobile-nav` | `solo-menu-main--mobile` |
| `solo-popup-login` | `solo-popup-login-block` |
| `solo-popup-login__close` | `solo-popup-login-block__close` |
| `solo-popup-login__close-inner` | `solo-popup-login-block__close-inner` |
| `solo-popup-login__inner` | `solo-popup-login-block__inner` |
| `solo-popup-login__open` | `solo-popup-login-block__open` |
| `solo-popup-login__open-inner` | `solo-popup-login-block__open-inner` |
| `solo-popup-login__trigger` | `solo-popup-login-block__trigger` |
| `solo-search-button-close` | `solo-fixed-search-block__close` |
| `solo-search-button-open` | `solo-fixed-search-block__open` |
| `solo-sidebar-button-close` | `solo-menu-primary-sidebar__close` |
| `solo-sidebar-button-open` | `solo-menu-primary-sidebar__open` |
| `solo-sidebar-menu__close` | `solo-primary-sidebar-menu__close` |
| `solo-sidebar-menu__open` | `solo-primary-sidebar-menu__open` |
| `solo-system-messages` | `solo-status-messages` |
| `solo-system-messages__inner` | `solo-status-messages__inner` |
| `solo-table-all` | `solo-table--all` |
| `solo-table-wrapper` | `solo-table__wrapper` |

## Library ids (348)

`alias`: the old id keeps working until the next major release and Drupal logs a deprecation. `report-only`: the old library was split, so pick the new ids that you need.

| Old id | New id | Action |
|---|---|---|
| `solo/font-awesome-4-ex` | `solo/font-awesome-7-ex` | alias |
| `solo/font-awesome-4-in` | `solo/font-awesome-7-in` | alias |
| `solo/font-awesome-5-ex` | `solo/font-awesome-7-ex` | alias |
| `solo/font-awesome-6-ex` | `solo/font-awesome-7-ex` | alias |
| `solo/solo-action-links` | `solo/component-menu-local-action` | alias |
| `solo/solo-back-to-top` | `solo/component-back-to-top` | alias |
| `solo/solo-berry-blue-dreams` | `solo/scheme-berry-blue-dreams` | alias |
| `solo/solo-blueberry-skies-quartet` | `solo/scheme-blueberry-skies-quartet` | alias |
| `solo/solo-blushing-magnolia` | `solo/scheme-blushing-magnolia` | alias |
| `solo/solo-celestial-blue-depths` | `solo/scheme-celestial-blue-depths` | alias |
| `solo/solo-charcoal-gray` | `solo/scheme-charcoal-gray` | alias |
| `solo/solo-classic-ivory-lace` | `solo/scheme-classic-ivory-lace` | alias |
| `solo/solo-color-coded-system-tabs` | `solo/component-menu-local-tasks-color-coded` | alias |
| `solo/solo-cookies` | `solo/theme-cookies` | alias |
| `solo/solo-cotton-candy-whispers` | `solo/scheme-cotton-candy-whispers` | alias |
| `solo/solo-crimson-denim-night` | `solo/scheme-crimson-denim-night` | alias |
| `solo/solo-css-dynamic` | `solo/theme-css-dynamic` | alias |
| `solo/solo-debug` | `solo/debug-menu` | alias |
| `solo/solo-deep-ocean-shadows` | `solo/scheme-deep-ocean-shadows` | alias |
| `solo/solo-deep-sea-echo` | `solo/scheme-deep-sea-echo` | alias |
| `solo/solo-denim-twilight` | `solo/scheme-denim-twilight` | alias |
| `solo/solo-dusk-shadow-fusion` | `solo/scheme-dusk-shadow-fusion` | alias |
| `solo/solo-eclipse-noir` | `solo/scheme-eclipse-noir` | alias |
| `solo/solo-enchanted-forest-zest` | `solo/scheme-enchanted-forest-zest` | alias |
| `solo/solo-ethereal-white-harmony` | `solo/scheme-ethereal-white-harmony` | alias |
| `solo/solo-fixed-search-block` | `solo/component-fixed-search-block` | alias |
| `solo/solo-flaming-sapphire` | `solo/scheme-flaming-sapphire` | alias |
| `solo/solo-form-theme-settings` | `solo/admin-form-theme-settings` | alias |
| `solo/solo-full-opacity` | `solo/base-full-opacity` | alias |
| `solo/solo-galactic-stone-hues` | `solo/scheme-galactic-stone-hues` | alias |
| `solo/solo-gentle-ivory-breeze` | `solo/scheme-gentle-ivory-breeze` | alias |
| `solo/solo-gfex-abel` | `solo/font-abel-external` | alias |
| `solo/solo-gfex-abril-fatface` | `solo/font-abril-fatface-external` | alias |
| `solo/solo-gfex-acme` | `solo/font-acme-external` | alias |
| `solo/solo-gfex-akronim` | `solo/font-akronim-external` | alias |
| `solo/solo-gfex-aladin` | `solo/font-aladin-external` | alias |
| `solo/solo-gfex-alegreya-sans` | `solo/font-alegreya-sans-external` | alias |
| `solo/solo-gfex-alegreya-sans-sc` | `solo/font-alegreya-sans-sc-external` | alias |
| `solo/solo-gfex-amita` | `solo/font-amita-external` | alias |
| `solo/solo-gfex-anton` | `solo/font-anton-external` | alias |
| `solo/solo-gfex-archivo-black` | `solo/font-archivo-black-external` | alias |
| `solo/solo-gfex-archivo-narrow` | `solo/font-archivo-narrow-external` | alias |
| `solo/solo-gfex-arimo` | `solo/font-arimo-external` | alias |
| `solo/solo-gfex-arvo` | `solo/font-arvo-external` | alias |
| `solo/solo-gfex-audiowide` | `solo/font-audiowide-external` | alias |
| `solo/solo-gfex-barlow` | `solo/font-barlow-external` | alias |
| `solo/solo-gfex-barlow-semi-condensed` | `solo/font-barlow-semi-condensed-external` | alias |
| `solo/solo-gfex-barriecito` | `solo/font-barriecito-external` | alias |
| `solo/solo-gfex-be-vietnam-pro` | `solo/font-be-vietnam-pro-external` | alias |
| `solo/solo-gfex-bebas-neue` | `solo/font-bebas-neue-external` | alias |
| `solo/solo-gfex-berkshire-swash` | `solo/font-berkshire-swash-external` | alias |
| `solo/solo-gfex-big-shoulders-stencil-display` | `solo/font-big-shoulders-stencil-display-external` | alias |
| `solo/solo-gfex-big-shoulders-stencil-text` | `solo/font-big-shoulders-stencil-text-external` | alias |
| `solo/solo-gfex-bitter` | `solo/font-bitter-external` | alias |
| `solo/solo-gfex-black-ops-one` | `solo/font-black-ops-one-external` | alias |
| `solo/solo-gfex-bree-serif` | `solo/font-bree-serif-external` | alias |
| `solo/solo-gfex-bungee-shade` | `solo/font-bungee-shade-external` | alias |
| `solo/solo-gfex-cairo` | `solo/font-cairo-external` | alias |
| `solo/solo-gfex-ceviche-one` | `solo/font-ceviche-one-external` | alias |
| `solo/solo-gfex-cinzel` | `solo/font-cinzel-external` | alias |
| `solo/solo-gfex-comfortaa` | `solo/font-comfortaa-external` | alias |
| `solo/solo-gfex-contrail-one` | `solo/font-contrail-one-external` | alias |
| `solo/solo-gfex-cookie` | `solo/font-cookie-external` | alias |
| `solo/solo-gfex-courgette` | `solo/font-courgette-external` | alias |
| `solo/solo-gfex-creepster` | `solo/font-creepster-external` | alias |
| `solo/solo-gfex-croissant-one` | `solo/font-croissant-one-external` | alias |
| `solo/solo-gfex-crushed` | `solo/font-crushed-external` | alias |
| `solo/solo-gfex-dancing-script` | `solo/font-dancing-script-external` | alias |
| `solo/solo-gfex-dm-serif-display` | `solo/font-dm-serif-display-external` | alias |
| `solo/solo-gfex-domine` | `solo/font-domine-external` | alias |
| `solo/solo-gfex-doppio-one` | `solo/font-doppio-one-external` | alias |
| `solo/solo-gfex-dosis` | `solo/font-dosis-external` | alias |
| `solo/solo-gfex-eagle-lake` | `solo/font-eagle-lake-external` | alias |
| `solo/solo-gfex-exo-2` | `solo/font-exo-2-external` | alias |
| `solo/solo-gfex-faster-one` | `solo/font-faster-one-external` | alias |
| `solo/solo-gfex-finger-paint` | `solo/font-finger-paint-external` | alias |
| `solo/solo-gfex-fira-sans` | `solo/font-fira-sans-external` | alias |
| `solo/solo-gfex-fjalla-one` | `solo/font-fjalla-one-external` | alias |
| `solo/solo-gfex-francois-one` | `solo/font-francois-one-external` | alias |
| `solo/solo-gfex-fredericka-the-great` | `solo/font-fredericka-the-great-external` | alias |
| `solo/solo-gfex-fredoka` | `solo/font-fredoka-external` | alias |
| `solo/solo-gfex-frijole` | `solo/font-frijole-external` | alias |
| `solo/solo-gfex-fugaz-one` | `solo/font-fugaz-one-external` | alias |
| `solo/solo-gfex-gilda-display` | `solo/font-gilda-display-external` | alias |
| `solo/solo-gfex-girassol` | `solo/font-girassol-external` | alias |
| `solo/solo-gfex-glegoo` | `solo/font-glegoo-external` | alias |
| `solo/solo-gfex-hammersmith-one` | `solo/font-hammersmith-one-external` | alias |
| `solo/solo-gfex-hind-siliguri` | `solo/font-hind-siliguri-external` | alias |
| `solo/solo-gfex-ibm-plex-sans` | `solo/font-ibm-plex-sans-external` | alias |
| `solo/solo-gfex-inconsolata` | `solo/font-inconsolata-external` | alias |
| `solo/solo-gfex-indie-flower` | `solo/font-indie-flower-external` | alias |
| `solo/solo-gfex-irish-grover` | `solo/font-irish-grover-external` | alias |
| `solo/solo-gfex-italiana` | `solo/font-italiana-external` | alias |
| `solo/solo-gfex-joan` | `solo/font-joan-external` | alias |
| `solo/solo-gfex-josefin-sans` | `solo/font-josefin-sans-external` | alias |
| `solo/solo-gfex-julius-sans-one` | `solo/font-julius-sans-one-external` | alias |
| `solo/solo-gfex-kalam` | `solo/font-kalam-external` | alias |
| `solo/solo-gfex-kanit` | `solo/font-kanit-external` | alias |
| `solo/solo-gfex-katibeh` | `solo/font-katibeh-external` | alias |
| `solo/solo-gfex-kaushan-script` | `solo/font-kaushan-script-external` | alias |
| `solo/solo-gfex-kavivanar` | `solo/font-kavivanar-external` | alias |
| `solo/solo-gfex-kumar-one` | `solo/font-kumar-one-external` | alias |
| `solo/solo-gfex-lato` | `solo/font-lato-external` | alias |
| `solo/solo-gfex-libre-baskerville` | `solo/font-libre-baskerville-external` | alias |
| `solo/solo-gfex-lobster-two` | `solo/font-lobster-two-external` | alias |
| `solo/solo-gfex-londrina-outline` | `solo/font-londrina-outline-external` | alias |
| `solo/solo-gfex-lora` | `solo/font-lora-external` | alias |
| `solo/solo-gfex-megrim` | `solo/font-megrim-external` | alias |
| `solo/solo-gfex-merienda` | `solo/font-merienda-external` | alias |
| `solo/solo-gfex-merriweather` | `solo/font-merriweather-external` | alias |
| `solo/solo-gfex-metal-mania` | `solo/font-metal-mania-external` | alias |
| `solo/solo-gfex-monoton` | `solo/font-monoton-external` | alias |
| `solo/solo-gfex-montserrat` | `solo/font-montserrat-external` | alias |
| `solo/solo-gfex-moo-lah-lah` | `solo/font-moo-lah-lah-external` | alias |
| `solo/solo-gfex-mukta` | `solo/font-mukta-external` | alias |
| `solo/solo-gfex-new-rocker` | `solo/font-new-rocker-external` | alias |
| `solo/solo-gfex-nosifer` | `solo/font-nosifer-external` | alias |
| `solo/solo-gfex-nunito` | `solo/font-nunito-external` | alias |
| `solo/solo-gfex-open-sans` | `solo/font-open-sans-external` | alias |
| `solo/solo-gfex-oranienbaum` | `solo/font-oranienbaum-external` | alias |
| `solo/solo-gfex-oswald` | `solo/font-oswald-external` | alias |
| `solo/solo-gfex-overlock-sc` | `solo/font-overlock-sc-external` | alias |
| `solo/solo-gfex-oxygen` | `solo/font-oxygen-external` | alias |
| `solo/solo-gfex-patrick-hand` | `solo/font-patrick-hand-external` | alias |
| `solo/solo-gfex-pirata-one` | `solo/font-pirata-one-external` | alias |
| `solo/solo-gfex-playfair-display` | `solo/font-playfair-display-external` | alias |
| `solo/solo-gfex-poiret-one` | `solo/font-poiret-one-external` | alias |
| `solo/solo-gfex-poppins` | `solo/font-poppins-external` | alias |
| `solo/solo-gfex-prata` | `solo/font-prata-external` | alias |
| `solo/solo-gfex-prompt` | `solo/font-prompt-external` | alias |
| `solo/solo-gfex-prosto-one` | `solo/font-prosto-one-external` | alias |
| `solo/solo-gfex-pt-serif` | `solo/font-pt-serif-external` | alias |
| `solo/solo-gfex-quicksand` | `solo/font-quicksand-external` | alias |
| `solo/solo-gfex-raleway` | `solo/font-raleway-external` | alias |
| `solo/solo-gfex-redressed` | `solo/font-redressed-external` | alias |
| `solo/solo-gfex-righteous` | `solo/font-righteous-external` | alias |
| `solo/solo-gfex-roboto` | `solo/font-roboto-external` | alias |
| `solo/solo-gfex-roboto-condensed` | `solo/font-roboto-condensed-external` | alias |
| `solo/solo-gfex-roboto-mono` | `solo/font-roboto-mono-external` | alias |
| `solo/solo-gfex-ruslan-display` | `solo/font-ruslan-display-external` | alias |
| `solo/solo-gfex-rye` | `solo/font-rye-external` | alias |
| `solo/solo-gfex-sancreek` | `solo/font-sancreek-external` | alias |
| `solo/solo-gfex-slackey` | `solo/font-slackey-external` | alias |
| `solo/solo-gfex-source-sans-3` | `solo/font-source-sans-3-external` | alias |
| `solo/solo-gfex-spline-sans` | `solo/font-spline-sans-external` | alias |
| `solo/solo-gfex-staatliches` | `solo/font-staatliches-external` | alias |
| `solo/solo-gfex-tapestry` | `solo/font-tapestry-external` | alias |
| `solo/solo-gfex-titillium-web` | `solo/font-titillium-web-external` | alias |
| `solo/solo-gfex-trade-winds` | `solo/font-trade-winds-external` | alias |
| `solo/solo-gfex-train-one` | `solo/font-train-one-external` | alias |
| `solo/solo-gfex-ubuntu` | `solo/font-ubuntu-external` | alias |
| `solo/solo-gfex-uncial-antiqua` | `solo/font-uncial-antiqua-external` | alias |
| `solo/solo-gfex-unica-one` | `solo/font-unica-one-external` | alias |
| `solo/solo-gfex-unifrakturmaguntia` | `solo/font-unifrakturmaguntia-external` | alias |
| `solo/solo-gfex-work-sans` | `solo/font-work-sans-external` | alias |
| `solo/solo-gfex-yanone-kaffeesatz` | `solo/font-yanone-kaffeesatz-external` | alias |
| `solo/solo-gfex-yatra-one` | `solo/font-yatra-one-external` | alias |
| `solo/solo-gfex-yeseva-one` | `solo/font-yeseva-one-external` | alias |
| `solo/solo-gfin-abel` | `solo/font-abel-local` | alias |
| `solo/solo-gfin-abril-fatface` | `solo/font-abril-fatface-local` | alias |
| `solo/solo-gfin-acme` | `solo/font-acme-local` | alias |
| `solo/solo-gfin-akronim` | `solo/font-akronim-local` | alias |
| `solo/solo-gfin-aladin` | `solo/font-aladin-local` | alias |
| `solo/solo-gfin-alegreya-sans` | `solo/font-alegreya-sans-local` | alias |
| `solo/solo-gfin-alegreya-sans-sc` | `solo/font-alegreya-sans-sc-local` | alias |
| `solo/solo-gfin-amita` | `solo/font-amita-local` | alias |
| `solo/solo-gfin-anton` | `solo/font-anton-local` | alias |
| `solo/solo-gfin-archivo-black` | `solo/font-archivo-black-local` | alias |
| `solo/solo-gfin-archivo-narrow` | `solo/font-archivo-narrow-local` | alias |
| `solo/solo-gfin-arimo` | `solo/font-arimo-local` | alias |
| `solo/solo-gfin-arvo` | `solo/font-arvo-local` | alias |
| `solo/solo-gfin-audiowide` | `solo/font-audiowide-local` | alias |
| `solo/solo-gfin-barlow` | `solo/font-barlow-local` | alias |
| `solo/solo-gfin-barlow-semi-condensed` | `solo/font-barlow-semi-condensed-local` | alias |
| `solo/solo-gfin-barriecito` | `solo/font-barriecito-local` | alias |
| `solo/solo-gfin-be-vietnam-pro` | `solo/font-be-vietnam-pro-local` | alias |
| `solo/solo-gfin-bebas-neue` | `solo/font-bebas-neue-local` | alias |
| `solo/solo-gfin-berkshire-swash` | `solo/font-berkshire-swash-local` | alias |
| `solo/solo-gfin-big-shoulders-stencil-display` | `solo/font-big-shoulders-stencil-display-local` | alias |
| `solo/solo-gfin-big-shoulders-stencil-text` | `solo/font-big-shoulders-stencil-text-local` | alias |
| `solo/solo-gfin-bitter` | `solo/font-bitter-local` | alias |
| `solo/solo-gfin-black-ops-one` | `solo/font-black-ops-one-local` | alias |
| `solo/solo-gfin-bree-serif` | `solo/font-bree-serif-local` | alias |
| `solo/solo-gfin-bungee-shade` | `solo/font-bungee-shade-local` | alias |
| `solo/solo-gfin-cairo` | `solo/font-cairo-local` | alias |
| `solo/solo-gfin-ceviche-one` | `solo/font-ceviche-one-local` | alias |
| `solo/solo-gfin-cinzel` | `solo/font-cinzel-local` | alias |
| `solo/solo-gfin-comfortaa` | `solo/font-comfortaa-local` | alias |
| `solo/solo-gfin-contrail-one` | `solo/font-contrail-one-local` | alias |
| `solo/solo-gfin-cookie` | `solo/font-cookie-local` | alias |
| `solo/solo-gfin-courgette` | `solo/font-courgette-local` | alias |
| `solo/solo-gfin-creepster` | `solo/font-creepster-local` | alias |
| `solo/solo-gfin-croissant-one` | `solo/font-croissant-one-local` | alias |
| `solo/solo-gfin-crushed` | `solo/font-crushed-local` | alias |
| `solo/solo-gfin-dancing-script` | `solo/font-dancing-script-local` | alias |
| `solo/solo-gfin-dm-serif-display` | `solo/font-dm-serif-display-local` | alias |
| `solo/solo-gfin-domine` | `solo/font-domine-local` | alias |
| `solo/solo-gfin-doppio-one` | `solo/font-doppio-one-local` | alias |
| `solo/solo-gfin-dosis` | `solo/font-dosis-local` | alias |
| `solo/solo-gfin-eagle-lake` | `solo/font-eagle-lake-local` | alias |
| `solo/solo-gfin-exo-2` | `solo/font-exo-2-local` | alias |
| `solo/solo-gfin-faster-one` | `solo/font-faster-one-local` | alias |
| `solo/solo-gfin-finger-paint` | `solo/font-finger-paint-local` | alias |
| `solo/solo-gfin-fira-sans` | `solo/font-fira-sans-local` | alias |
| `solo/solo-gfin-fjalla-one` | `solo/font-fjalla-one-local` | alias |
| `solo/solo-gfin-francois-one` | `solo/font-francois-one-local` | alias |
| `solo/solo-gfin-fredericka-the-great` | `solo/font-fredericka-the-great-local` | alias |
| `solo/solo-gfin-fredoka` | `solo/font-fredoka-local` | alias |
| `solo/solo-gfin-frijole` | `solo/font-frijole-local` | alias |
| `solo/solo-gfin-fugaz-one` | `solo/font-fugaz-one-local` | alias |
| `solo/solo-gfin-gilda-display` | `solo/font-gilda-display-local` | alias |
| `solo/solo-gfin-girassol` | `solo/font-girassol-local` | alias |
| `solo/solo-gfin-glegoo` | `solo/font-glegoo-local` | alias |
| `solo/solo-gfin-hammersmith-one` | `solo/font-hammersmith-one-local` | alias |
| `solo/solo-gfin-hind-siliguri` | `solo/font-hind-siliguri-local` | alias |
| `solo/solo-gfin-ibm-plex-sans` | `solo/font-ibm-plex-sans-local` | alias |
| `solo/solo-gfin-inconsolata` | `solo/font-inconsolata-local` | alias |
| `solo/solo-gfin-indie-flower` | `solo/font-indie-flower-local` | alias |
| `solo/solo-gfin-irish-grover` | `solo/font-irish-grover-local` | alias |
| `solo/solo-gfin-italiana` | `solo/font-italiana-local` | alias |
| `solo/solo-gfin-joan` | `solo/font-joan-local` | alias |
| `solo/solo-gfin-josefin-sans` | `solo/font-josefin-sans-local` | alias |
| `solo/solo-gfin-julius-sans-one` | `solo/font-julius-sans-one-local` | alias |
| `solo/solo-gfin-kalam` | `solo/font-kalam-local` | alias |
| `solo/solo-gfin-kanit` | `solo/font-kanit-local` | alias |
| `solo/solo-gfin-katibeh` | `solo/font-katibeh-local` | alias |
| `solo/solo-gfin-kaushan-script` | `solo/font-kaushan-script-local` | alias |
| `solo/solo-gfin-kavivanar` | `solo/font-kavivanar-local` | alias |
| `solo/solo-gfin-kumar-one` | `solo/font-kumar-one-local` | alias |
| `solo/solo-gfin-lato` | `solo/font-lato-local` | alias |
| `solo/solo-gfin-libre-baskerville` | `solo/font-libre-baskerville-local` | alias |
| `solo/solo-gfin-lobster-two` | `solo/font-lobster-two-local` | alias |
| `solo/solo-gfin-londrina-outline` | `solo/font-londrina-outline-local` | alias |
| `solo/solo-gfin-lora` | `solo/font-lora-local` | alias |
| `solo/solo-gfin-megrim` | `solo/font-megrim-local` | alias |
| `solo/solo-gfin-merienda` | `solo/font-merienda-local` | alias |
| `solo/solo-gfin-merriweather` | `solo/font-merriweather-local` | alias |
| `solo/solo-gfin-metal-mania` | `solo/font-metal-mania-local` | alias |
| `solo/solo-gfin-monoton` | `solo/font-monoton-local` | alias |
| `solo/solo-gfin-montserrat` | `solo/font-montserrat-local` | alias |
| `solo/solo-gfin-moo-lah-lah` | `solo/font-moo-lah-lah-local` | alias |
| `solo/solo-gfin-mukta` | `solo/font-mukta-local` | alias |
| `solo/solo-gfin-new-rocker` | `solo/font-new-rocker-local` | alias |
| `solo/solo-gfin-nosifer` | `solo/font-nosifer-local` | alias |
| `solo/solo-gfin-nunito` | `solo/font-nunito-local` | alias |
| `solo/solo-gfin-open-sans` | `solo/font-open-sans-local` | alias |
| `solo/solo-gfin-oranienbaum` | `solo/font-oranienbaum-local` | alias |
| `solo/solo-gfin-oswald` | `solo/font-oswald-local` | alias |
| `solo/solo-gfin-overlock-sc` | `solo/font-overlock-sc-local` | alias |
| `solo/solo-gfin-oxygen` | `solo/font-oxygen-local` | alias |
| `solo/solo-gfin-patrick-hand` | `solo/font-patrick-hand-local` | alias |
| `solo/solo-gfin-pirata-one` | `solo/font-pirata-one-local` | alias |
| `solo/solo-gfin-playfair-display` | `solo/font-playfair-display-local` | alias |
| `solo/solo-gfin-poiret-one` | `solo/font-poiret-one-local` | alias |
| `solo/solo-gfin-poppins` | `solo/font-poppins-local` | alias |
| `solo/solo-gfin-prata` | `solo/font-prata-local` | alias |
| `solo/solo-gfin-prompt` | `solo/font-prompt-local` | alias |
| `solo/solo-gfin-prosto-one` | `solo/font-prosto-one-local` | alias |
| `solo/solo-gfin-pt-serif` | `solo/font-pt-serif-local` | alias |
| `solo/solo-gfin-quicksand` | `solo/font-quicksand-local` | alias |
| `solo/solo-gfin-raleway` | `solo/font-raleway-local` | alias |
| `solo/solo-gfin-redressed` | `solo/font-redressed-local` | alias |
| `solo/solo-gfin-righteous` | `solo/font-righteous-local` | alias |
| `solo/solo-gfin-roboto` | `solo/font-roboto-local` | alias |
| `solo/solo-gfin-roboto-condensed` | `solo/font-roboto-condensed-local` | alias |
| `solo/solo-gfin-roboto-mono` | `solo/font-roboto-mono-local` | alias |
| `solo/solo-gfin-ruslan-display` | `solo/font-ruslan-display-local` | alias |
| `solo/solo-gfin-rye` | `solo/font-rye-local` | alias |
| `solo/solo-gfin-sancreek` | `solo/font-sancreek-local` | alias |
| `solo/solo-gfin-slackey` | `solo/font-slackey-local` | alias |
| `solo/solo-gfin-source-sans-3` | `solo/font-source-sans-3-local` | alias |
| `solo/solo-gfin-spline-sans` | `solo/font-spline-sans-local` | alias |
| `solo/solo-gfin-staatliches` | `solo/font-staatliches-local` | alias |
| `solo/solo-gfin-tapestry` | `solo/font-tapestry-local` | alias |
| `solo/solo-gfin-titillium-web` | `solo/font-titillium-web-local` | alias |
| `solo/solo-gfin-trade-winds` | `solo/font-trade-winds-local` | alias |
| `solo/solo-gfin-train-one` | `solo/font-train-one-local` | alias |
| `solo/solo-gfin-ubuntu` | `solo/font-ubuntu-local` | alias |
| `solo/solo-gfin-uncial-antiqua` | `solo/font-uncial-antiqua-local` | alias |
| `solo/solo-gfin-unica-one` | `solo/font-unica-one-local` | alias |
| `solo/solo-gfin-unifrakturmaguntia` | `solo/font-unifrakturmaguntia-local` | alias |
| `solo/solo-gfin-work-sans` | `solo/font-work-sans-local` | alias |
| `solo/solo-gfin-yanone-kaffeesatz` | `solo/font-yanone-kaffeesatz-local` | alias |
| `solo/solo-gfin-yatra-one` | `solo/font-yatra-one-local` | alias |
| `solo/solo-gfin-yeseva-one` | `solo/font-yeseva-one-local` | alias |
| `solo/solo-global` | `solo/bundle-global` | alias |
| `solo/solo-global-breakpoints-sb-1200` | `solo/breakpoint-page-sb-1200` | alias |
| `solo/solo-global-breakpoints-sb-1400` | `solo/breakpoint-page-sb-1400` | alias |
| `solo/solo-global-breakpoints-sb-576` | `solo/breakpoint-page-sb-576` | alias |
| `solo/solo-global-breakpoints-sb-768` | `solo/breakpoint-page-sb-768` | alias |
| `solo/solo-global-breakpoints-sb-992` | `solo/breakpoint-page-sb-992` | alias |
| `solo/solo-gunmetal-echo` | `solo/scheme-gunmetal-echo` | alias |
| `solo/solo-indigo-pulse` | `solo/scheme-indigo-pulse` | alias |
| `solo/solo-ivory-whisper` | `solo/scheme-ivory-whisper` | alias |
| `solo/solo-js-dynamic` | `solo/theme-js-dynamic` | alias |
| `solo/solo-jungle-denim-fusion` | `solo/scheme-jungle-denim-fusion` | alias |
| `solo/solo-layers` | `solo/base-layers` | alias |
| `solo/solo-lemon-lime-forest` | `solo/scheme-lemon-lime-forest` | alias |
| `solo/solo-luminous-alabaster` | `solo/scheme-luminous-alabaster` | alias |
| `solo/solo-magnolia-afternoon` | `solo/scheme-magnolia-afternoon` | alias |
| `solo/solo-menu` | `solo/bundle-menu` | alias |
| `solo/solo-menu-breakpoints-mn-1200` | `solo/breakpoint-menu-mn-1200` | alias |
| `solo/solo-menu-breakpoints-mn-1400` | `solo/breakpoint-menu-mn-1400` | alias |
| `solo/solo-menu-breakpoints-mn-576` | `solo/breakpoint-menu-mn-576` | alias |
| `solo/solo-menu-breakpoints-mn-768` | `solo/breakpoint-menu-mn-768` | alias |
| `solo/solo-menu-breakpoints-mn-992` | `solo/breakpoint-menu-mn-992` | alias |
| `solo/solo-menu-keyboard` | `solo/component-menu-keyboard` | alias |
| `solo/solo-metallic-nebula` | `solo/scheme-metallic-nebula` | alias |
| `solo/solo-midnight-denim-swirl` | `solo/scheme-midnight-denim-swirl` | alias |
| `solo/solo-midnight-garden-trio` | `solo/scheme-midnight-garden-trio` | alias |
| `solo/solo-midnight-navy-duo` | `solo/scheme-midnight-navy-duo` | alias |
| `solo/solo-midnight-whispers` | `solo/scheme-midnight-whispers` | alias |
| `solo/solo-monochrome-splash` | `solo/scheme-monochrome-splash` | alias |
| `solo/solo-mystic-ash-twilight` | `solo/scheme-mystic-ash-twilight` | alias |
| `solo/solo-nightfall-paris` | `solo/scheme-nightfall-paris` | alias |
| `solo/solo-nocturnal-velvet` | `solo/scheme-nocturnal-velvet` | alias |
| `solo/solo-node-edit-form` | `solo/layout-node-edit` | alias |
| `solo/solo-obsidian-cascade` | `solo/scheme-obsidian-cascade` | alias |
| `solo/solo-ocean-depths-mystery` | `solo/scheme-ocean-depths-mystery` | alias |
| `solo/solo-parisian-skyline` | `solo/scheme-parisian-skyline` | alias |
| `solo/solo-popup-login-block` | `solo/component-popup-login-block` | alias |
| `solo/solo-porcelain-brilliance` | `solo/scheme-porcelain-brilliance` | alias |
| `solo/solo-porpoise-dream` | `solo/scheme-porpoise-dream` | alias |
| `solo/solo-preloader` | `solo/component-preloader` | alias |
| `solo/solo-pristine-porcelain-glow` | `solo/scheme-pristine-porcelain-glow` | alias |
| `solo/solo-raspberry-nightfall` | `solo/scheme-raspberry-nightfall` | alias |
| `solo/solo-razzle-onyx-elegance` | `solo/scheme-razzle-onyx-elegance` | alias |
| `solo/solo-royal-azure-flame` | `solo/scheme-royal-azure-flame` | alias |
| `solo/solo-rustic-redwood-charm` | `solo/scheme-rustic-redwood-charm` | alias |
| `solo/solo-salmon-denim-fade` | `solo/scheme-salmon-denim-fade` | alias |
| `solo/solo-scheme-control-border` | `solo/theme-scheme-control-border` | alias |
| `solo/solo-scheme-multi-span` | `solo/theme-scheme-multi-span` | alias |
| `solo/solo-scheme-region-paint-1` | `solo/theme-scheme-region-paint-1` | alias |
| `solo/solo-scheme-region-paint-2` | `solo/theme-scheme-region-paint-2` | alias |
| `solo/solo-scheme-region-paint-3` | `solo/theme-scheme-region-paint-3` | alias |
| `solo/solo-scheme-region-rule` | `solo/theme-scheme-region-rule` | alias |
| `solo/solo-scheme-shared` | `solo/theme-scheme-shared` | alias |
| `solo/solo-search` | `solo/SPLIT: component-search-block + component-search-page` | report-only |
| `solo/solo-search-settings` | `solo/component-search-settings` | alias |
| `solo/solo-silken-shadow-whisper` | `solo/scheme-silken-shadow-whisper` | alias |
| `solo/solo-sitemap` | `solo/component-sitemap` | alias |
| `solo/solo-smokey-quartz-symphony` | `solo/scheme-smokey-quartz-symphony` | alias |
| `solo/solo-soft-silk-elegance` | `solo/scheme-soft-silk-elegance` | alias |
| `solo/solo-system-messages-dark` | `solo/component-system-messages-dark` | alias |
| `solo/solo-twilight-ash-mystique` | `solo/scheme-twilight-ash-mystique` | alias |
| `solo/solo-urban-stone-shadows` | `solo/scheme-urban-stone-shadows` | alias |
| `solo/solo-user-login-custom` | `solo/layout-user-login` | alias |
| `solo/w3-css` | `solo/base-w3-css, solo/utilities-extended-classes, solo/utilities-color-camouflage, solo/utilities-color-european-signal, solo/utilities-color-fashion, solo/utilities-color-flat-ui, solo/utilities-color-food, solo/utilities-color-ios, solo/utilities-color-metro-ui, solo/utilities-color-us-highway, solo/utilities-color-us-safety, solo/utilities-color-vivid, solo/utilities-color-win8, solo/bundle-w3-css` | report-only |

## Files moved, renamed, split or merged (74)

Report only: `libraries-override` and `libraries-extend` entries that name an old path need the new one.

| Old path | New path | What |
|---|---|---|
| `css/base/solo-hamburger.css` | `css/components/solo-menu-hamburger.css` | move |
| `css/breakpoints/solo-global-breakpoints-sb-1200.css` | `css/breakpoints/solo-page-sb-1200.css` | rename |
| `css/breakpoints/solo-global-breakpoints-sb-1400.css` | `css/breakpoints/solo-page-sb-1400.css` | rename |
| `css/breakpoints/solo-global-breakpoints-sb-576.css` | `css/breakpoints/solo-page-sb-576.css` | rename |
| `css/breakpoints/solo-global-breakpoints-sb-768.css` | `css/breakpoints/solo-page-sb-768.css` | rename |
| `css/breakpoints/solo-global-breakpoints-sb-992.css` | `css/breakpoints/solo-page-sb-992.css` | rename |
| `css/breakpoints/solo-menu-breakpoints-mn-1200.css` | `css/breakpoints/solo-menu-mn-1200.css` | rename |
| `css/breakpoints/solo-menu-breakpoints-mn-1400.css` | `css/breakpoints/solo-menu-mn-1400.css` | rename |
| `css/breakpoints/solo-menu-breakpoints-mn-576.css` | `css/breakpoints/solo-menu-mn-576.css` | rename |
| `css/breakpoints/solo-menu-breakpoints-mn-768.css` | `css/breakpoints/solo-menu-mn-768.css` | rename |
| `css/breakpoints/solo-menu-breakpoints-mn-992.css` | `css/breakpoints/solo-menu-mn-992.css` | rename |
| `css/breakpoints/solo-responsive.css` | `css/theme/solo-responsive.css` | move |
| `css/components/solo-action-links.css` | `css/components/solo-menu-local-action.css` | rename |
| `css/components/solo-color-coded-system-tabs.css` | `css/components/solo-menu-local-tasks-color-coded.css` | rename |
| `css/components/solo-comments.css` | `css/components/solo-comment.css` | rename |
| `css/components/solo-default-node.css` | `css/components/solo-node.css` | rename |
| `css/components/solo-default-views.css` | `css/components/solo-views-view.css` | rename |
| `css/components/solo-embedded-media.css` | `css/components/solo-media.css` | rename |
| `css/components/solo-file-upload.css` | `css/components/solo-input-file.css` | merge |
| `css/components/solo-file.css` | `css/components/solo-input-file.css` | rename |
| `css/components/solo-forms.css` | `css/components/solo-form.css` | split |
| `css/components/solo-menu-keyboard.css` | `removed (it was empty; nothing replaces it)` | remove |
| `css/components/solo-node-form.css` | `css/components/solo-node-edit-form.css` | rename |
| `css/components/solo-system-admin.css` | `css/admin/solo-admin-system.css` | rename |
| `css/components/solo-system-messages-dark.css` | `css/components/solo-status-messages-dark.css` | rename |
| `css/components/solo-system-messages.css` | `css/components/solo-status-messages.css` | rename |
| `css/components/solo-system-tabs.css` | `css/components/solo-menu-local-tasks.css` | rename |
| `css/components/solo-tables.css` | `css/components/solo-table.css` | rename |
| `css/components/solo-teaser-mode.css` | `css/components/solo-node-teaser.css` | rename |
| `css/components/solo-text-content.css` | `css/components/solo-text-format.css` | rename |
| `css/components/solo-user-login.css` | `css/components/solo-form-user-login.css` | rename |
| `css/components/solo-user-menu.css` | `css/components/solo-menu-account.css` | rename |
| `css/components/solo-user-profile.css` | `css/components/solo-user.css` | rename |
| `css/layout/solo-layout-breakpoints-sb-1200.css` | `css/layout/solo-page-sb-1200.css` | rename |
| `css/layout/solo-layout-breakpoints-sb-1400.css` | `css/layout/solo-page-sb-1400.css` | rename |
| `css/layout/solo-layout-breakpoints-sb-576.css` | `css/layout/solo-page-sb-576.css` | rename |
| `css/layout/solo-layout-breakpoints-sb-768.css` | `css/layout/solo-page-sb-768.css` | rename |
| `css/layout/solo-layout-breakpoints-sb-992.css` | `css/layout/solo-page-sb-992.css` | rename |
| `css/layout/solo-layout-node-edit.css` | `css/layout/solo-node-edit.css` | rename |
| `css/layout/solo-layout-user-login.css` | `css/layout/solo-user-login.css` | rename |
| `css/theme/solo-form-theme-settings.css` | `css/admin/solo-form-theme-settings.css` | move |
| `css/theme/solo-menu-side.css` | `css/theme/solo-menu-primary-sidebar.css` | rename |
| `css/w3-css/w3css-color-libraries-camouflage.css` | `css/utilities/solo-color-camouflage.css` | rename |
| `css/w3-css/w3css-color-libraries-european-signal.css` | `css/utilities/solo-color-european-signal.css` | rename |
| `css/w3-css/w3css-color-libraries-fashion.css` | `css/utilities/solo-color-fashion.css` | rename |
| `css/w3-css/w3css-color-libraries-flat-ui.css` | `css/utilities/solo-color-flat-ui.css` | rename |
| `css/w3-css/w3css-color-libraries-food.css` | `css/utilities/solo-color-food.css` | rename |
| `css/w3-css/w3css-color-libraries-ios.css` | `css/utilities/solo-color-ios.css` | rename |
| `css/w3-css/w3css-color-libraries-metro-ui.css` | `css/utilities/solo-color-metro-ui.css` | rename |
| `css/w3-css/w3css-color-libraries-us-highway.css` | `css/utilities/solo-color-us-highway.css` | rename |
| `css/w3-css/w3css-color-libraries-us-safety.css` | `css/utilities/solo-color-us-safety.css` | rename |
| `css/w3-css/w3css-color-libraries-vivid.css` | `css/utilities/solo-color-vivid.css` | rename |
| `css/w3-css/w3css-color-libraries-win8.css` | `css/utilities/solo-color-win8.css` | rename |
| `css/w3-css/w3css-extended-classes.css` | `css/utilities/solo-extended-classes.css` | rename |
| `fonts/font-awesome.4.7.0.css` | `fonts/font-awesome-7.0.1.css` | replace |
| `js/base/solo-color-picker.js` | `js/admin/solo-color-picker.js` | move-rename |
| `js/base/solo-form-scheme-picker.js` | `js/admin/solo-form-scheme-picker.js` | move-rename |
| `js/base/solo-form-theme-settings.js` | `js/admin/solo-form-theme-settings.js` | move-rename |
| `js/components/solo-scripts.js` | `SPLIT into one file per template (image, skip-link, footer-menu, file-input, region size, details): see behaviors table` | split |
| `js/components/solo-search-settings.js` | `js/components/solo-search-block.js` | move-rename |
| `js/components/solo-utils.js` | `SPLIT: file upload (input-file), search animation (search-block), details animation, menu depth (menu)` | split |
| `js/keyboard/solo-keyboard-aria.js` | `js/components/solo-menu-keyboard-aria.js` | move-rename |
| `js/keyboard/solo-keyboard-core.js` | `js/components/solo-menu-keyboard-core.js` | move-rename |
| `js/keyboard/solo-keyboard-focus.js` | `js/components/solo-menu-keyboard-focus.js` | move-rename |
| `js/keyboard/solo-keyboard-megamenu.js` | `js/components/solo-menu-keyboard-megamenu.js` | move-rename |
| `js/keyboard/solo-keyboard-navigation.js` | `js/components/solo-menu-keyboard-navigation.js` | move-rename |
| `js/keyboard/solo-keyboard-template-parser.js` | `js/components/solo-menu-keyboard-template-parser.js` | move-rename |
| `js/keyboard/solo-keyboard-utils.js` | `js/components/solo-menu-keyboard-utils.js` | move-rename |
| `js/menu/solo-menu-mobile.js` | `js/components/solo-menu-mobile.js` | move-rename |
| `js/menu/solo-menu-repositions-submenus.js` | `js/components/solo-menu-submenu-position.js` | move-rename |
| `js/menu/solo-menu-scripts.js` | `js/components/solo-menu-main.js` | move-rename |
| `js/menu/solo-menu-side.js` | `js/components/solo-menu-primary-sidebar.js` | move-rename |
| `js/menu/solo-menu-state-manager.js` | `js/components/solo-menu-state-manager.js` | move-rename |
| `js/menu/solo-menu.js` | `js/components/solo-menu.js` | move-rename |

## JavaScript behaviors (10)

`alias`: the old name keeps working for one major release. `report-only`: pick the new name by hand.

| Old | New | Action |
|---|---|---|
| `Drupal.behaviors.commentRepliesToggle` | `Drupal.behaviors.soloComments` | alias |
| `Drupal.behaviors.globalMenu` | `Drupal.behaviors.soloMenuMain` | alias |
| `Drupal.behaviors.menuAction` | `Drupal.behaviors.soloMenu` | alias |
| `Drupal.behaviors.mobileMenu` | `Drupal.behaviors.soloMenuMobile` | alias |
| `Drupal.behaviors.ColorSchemesRules` | `Drupal.behaviors.soloUtilitiesColorSchemesRules` | alias |
| `Drupal.behaviors.soloTheme` | `Drupal.behaviors.SPLIT (soloImage, soloSkipLink, soloFooterMenu, soloRegionSize, ...)` | report-only |
| `Drupal.behaviors.soloFileUpload / soloMenuDepth / soloSearchAnimation` | `Drupal.behaviors.soloInputFile / soloMenu... / soloSearchBlock (move out of solo-utils.js)` | report-only |
| `Drupal.behaviors.soloPrimarySideMenu` | `Drupal.behaviors.soloMenuPrimarySidebar` | alias |
| `Drupal.behaviors.soloMenuFix` | `Drupal.behaviors.soloMenuSubmenuPosition` | alias |
| `Drupal.behaviors.soloSearchBlock` | `Drupal.behaviors.soloSearchBlock (file moves to solo-search-block.js)` | report-only |

## once() ids and data-once values (12)

| Old | New | Action |
|---|---|---|
| `comment-replies-toggle` | `solo-comment-replies-toggle` | rewrite |
| `soloDetailsAnimation` | `solo-details-animation` | rewrite |
| `soloHamburgerInit` | `solo-menu-hamburger-init` | rewrite |
| `soloHamburgerLateInit` | `solo-menu-hamburger-late-init` | rewrite |
| `soloMenuDepth` | `solo-menu-depth` | rewrite |
| `soloMenuFix` | `solo-menu-submenu-position` | rewrite |
| `solo-menu-init` | `solo-menu-main` | rewrite |
| `solo-sidebar-button` | `solo-menu-primary-sidebar-button` | rewrite |
| `solo-sidebar-init` | `solo-menu-primary-sidebar` | rewrite |
| `solo-search-block-init` | `solo-fixed-search-block` | rewrite |
| `solo-search-close-btn` | `solo-fixed-search-block-close` | rewrite |
| `solo-search-open-btn` | `solo-fixed-search-block-open` | rewrite |

## drupalSettings keys (1)

| Old | New | Action |
|---|---|---|
| `drupalSettings.solo.debug_verbosity` | `drupalSettings.solo.debugVerbosity` | alias |

## Data attributes (8)

| Old | New | Action |
|---|---|---|
| `data-comment-id` | `data-solo-comment-id` | alias |
| `data-force-show` | `data-solo-preloader-force-show` | alias |
| `data-hide-on` | `data-solo-preloader-hide-on` | alias |
| `data-max-display` | `data-solo-preloader-max-display` | alias |
| `data-hover-added` | `data-solo-hover-added` | alias |
| `data-hover-toggle` | `data-solo-hover-toggle` | alias |
| `data-original-tabindex` | `data-solo-original-tabindex` | alias |
| `data-default-value` | `data-solo-default-value` | alias |

## JavaScript events (1)

| Old | New | Action |
|---|---|---|
| `solo-aria-change` | `solo:ariaChange` | alias |

## CSS variables (region colors) (31)

An old name still reads the new one (a deprecated archive file in Solo). A rule that sets an old name no longer changes anything: rewrite it.

| Old | New |
|---|---|
| `--r-bg` | `--r-background` |
| `--r-tx` | `--r-text` |
| `--r-h1` | `--r-heading` |
| `--r-lk` | `--r-link` |
| `--r-lk-h` | `--r-link-hover` |
| `--r-br` | `--r-border` |
| `--r-bg-fr` | `--r-form-background` |
| `--r-tx-lk` | `--r-menu-text` |
| `--r-tx-lk-h` | `--r-menu-text-hover` |
| `--r-menu-tx-h` | `--r-menu-text-hover` |
| `--r-bg-lk` | `--r-menu-background` |
| `--r-bg-lk-h` | `--r-menu-background-hover` |
| `--r-menu-bg-h` | `--r-menu-background-hover` |
| `--r-tx-bt` | `--r-button-text` |
| `--r-tx-bt-h` | `--r-button-text-hover` |
| `--r-bg-bt` | `--r-button-background` |
| `--r-bg-bt-h` | `--r-button-background-hover` |
| `--r-header-bg` | `--solo-accent-background` |
| `--r-header-tx` | `--solo-accent-text` |
| `--r-br-b` | `--solo-border-default` |
| `--r-bg-dark` | `--solo-tone-background-dark` |
| `--r-tx-dark` | `--solo-tone-text-dark` |
| `--r-br-dark` | `--solo-tone-border-dark` |
| `--r-bg-light` | `--solo-tone-background-light` |
| `--r-tx-light` | `--solo-tone-text-light` |
| `--r-br-light` | `--solo-tone-border-light` |
| `--r-sc-h1` | `--r-heading` |
| `--r-sc-lk` | `--r-link` |
| `--r-sc-lk-h` | `--r-link-hover` |
| `--r-sc-tx-bt` | `--r-button-text` |
| `--r-sc-tx-bt-h` | `--r-button-text-hover` |

## Unchanged on purpose (165)

Font and animation value classes are public names and keep their Solo 1 spelling.

| Name |
|---|
| `abel` |
| `abril-fatface` |
| `acme` |
| `akronim` |
| `aladin` |
| `alegreya-aans-sc` |
| `alegreya-sans` |
| `amita` |
| `animate-solo-aurora-wave` |
| `animate-solo-cosmic-drift` |
| `animate-solo-dreamy-bloom` |
| `animate-solo-echo-pulse` |
| `animate-solo-electric-surge` |
| `animate-solo-gliding-letters` |
| `animate-solo-gravity-defy` |
| `animate-solo-holographic-spin` |
| `animate-solo-moonlight-dance` |
| `animate-solo-mystic-fade` |
| `animate-solo-phoenix-rise` |
| `animate-solo-quantum-leap` |
| `animate-solo-rhythmic-slide` |
| `animate-solo-sapphire-zoom` |
| `animate-solo-serene-float` |
| `animate-solo-shimmering-wave` |
| `animate-solo-solar-flare` |
| `animate-solo-starry-glide` |
| `animate-solo-swirling-entrance` |
| `animate-solo-timeless-flip` |
| `animate-solo-velvet-slide` |
| `animate-solo-whisper-fade` |
| `animate-solo-whispering-shadows` |
| `animate-solo-zen-rotation` |
| `anton` |
| `archivo-black` |
| `archivo-narrow` |
| `arimo` |
| `arvo` |
| `audiowide` |
| `barlow` |
| `barlow-semi-condensed` |
| `barriecito` |
| `be-vietnam-pro` |
| `bebas-neue` |
| `berkshire-swash` |
| `big-shoulders-stencil-display` |
| `big-shoulders-stencil-text` |
| `bitter` |
| `black-ops-one` |
| `bree-serif` |
| `bungee-shade` |
| `cairo` |
| `ceviche-one` |
| `cinzel` |
| `comfortaa` |
| `contrail-one` |
| `cookie` |
| `courgette` |
| `creepster` |
| `croissant-one` |
| `crushed` |
| `dancing-script` |
| `dis-all-animations` |
| `dis-anim-link` |
| `dis-anim-submit` |
| `dis-border` |
| `dis-border-radius` |
| `dis-box-shadow` |
| `dis-icon-colors` |
| `dis-margin` |
| `dis-opacity` |
| `dis-padding` |
| `dis-text-transform` |
| `dm-serif-display` |
| `domine` |
| `doppio-one` |
| `dosis` |
| `eagle-lake` |
| `exo-2` |
| `faster-one` |
| `finger-paint` |
| `fira-sans` |
| `fjalla-one` |
| `francois-one` |
| `fredericka-the-great` |
| `fredoka` |
| `frijole` |
| `fugaz-one` |
| `gilda-display` |
| `girassol` |
| `glegoo` |
| `hammersmith-one` |
| `hind-siliguri` |
| `ibm-plex-sans` |
| `image-auto` |
| `inconsolata` |
| `indie-flower` |
| `inline-radio-checkbox` |
| `irish-grover` |
| `italiana` |
| `joan` |
| `josefin-sans` |
| `julius-sans-one` |
| `kalam` |
| `kanit` |
| `katibeh` |
| `kaushan-script` |
| `kavivanar` |
| `kumar-one` |
| `lato` |
| `libre-baskerville` |
| `lobster-two` |
| `londrina-outline` |
| `lora` |
| `megrim` |
| `merienda` |
| `merriweather` |
| `metal-mania` |
| `monoton` |
| `montserrat` |
| `moo-lah-lah` |
| `mukta` |
| `new-rocker` |
| `nosifer` |
| `nunito` |
| `open-sans` |
| `oranienbaum` |
| `oswald` |
| `overlock-sc` |
| `oxygen` |
| `patrick-hand` |
| `pirata-one` |
| `playfair-display` |
| `poiret-one` |
| `poppins` |
| `prata` |
| `prompt` |
| `prosto-one` |
| `pt-serif` |
| `quicksand` |
| `raleway` |
| `redressed` |
| `righteous` |
| `roboto` |
| `roboto-condensed` |
| `roboto-mono` |
| `ruslan-display` |
| `rye` |
| `sancreek` |
| `slackey` |
| `source-sans-3` |
| `source-sans-pro` |
| `spline-sans` |
| `staatliches` |
| `tapestry` |
| `titillium-web` |
| `trade-winds` |
| `train-one` |
| `ubuntu` |
| `uncial-antiqua` |
| `unica-one` |
| `unifrakturmaguntia` |
| `work-sans` |
| `yanone-kaffeesatz` |
| `yatra-one` |
| `yeseva-one` |
