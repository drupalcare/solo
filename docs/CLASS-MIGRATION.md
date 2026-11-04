# CSS class migration: Solo 1 to Solo 2

Generated from `solo_utilities/config/map/solo-class-map.yml` by `planning/spike-tools/bem-themes/class-migration-doc.py`. Do not edit by hand.

Solo 2 renames its classes and ids to BEM names with the `solo-` prefix. A sub-theme or custom module that selects the old names stops matching until it is migrated. Solo Utilities ships a Drush command that does the renaming for you.

## Before you start: let Drush do the renaming

1. Save a copy of your theme (or commit it to Git).
2. Dry run, with a report written outside the web root (run from the project folder):

   ```
   drush solo:migrate-classes mytheme --stored --content --report=solo-report.md
   ```

   Nothing is written. The target is a theme machine name or a folder inside the Drupal root (`web/themes/custom/mytheme`, `web/modules/custom/my_module`). Drupal core, Solo itself and the public files folder are refused, and symbolic links are never followed.
3. Read the report. Configuration and content come first, then each file and its changed lines, then the items to check by hand.
4. Apply: `drush solo:migrate-classes mytheme --apply`. Add `--stored` to save the configuration changes, and `--content` to save id changes in content (each changed item becomes a new revision).
5. Clear the caches: `drush cr`, then check your pages.

What the command changes: `.css .scss .sass .less .twig .js` files are rewritten; `.theme .php .module .inc` files are only reported (change the PHP by hand). `--ext=css,js` narrows the file types. `.yml` and `*.min.*` files, `node_modules`, `vendor`, `dist` and `.git` are never read.

What `--stored` covers: the Solo inline CSS setting (`custom_code.inline_css`, also renamed automatically by `drush updb`), Block Class classes, Views CSS, row and field classes, and text format allowed tags with the matching CKEditor 5 settings. `--content` renames only ids, in formatted text fields and menu link attributes.

Report-only rules: a name that is one plain word, or is both a class and an id, is renamed only where the line plainly sets classes (or ids). State classes (`toggled`, `small-screen` ...) and generic words (`facebook`, `header` ...) are renamed only when chained to a Solo selector in a stylesheet; elsewhere they are reported.

## Classes (260)

| Solo 1 | Solo 2 |
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
| `core-task` | `solo-local-task--core` |
| `delete-task` | `solo-local-task--delete` |
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
| `fixed-search-block` | `solo-fixed-search` |
| `fixed-search-block-inner` | `solo-fixed-search__inner` |
| `fixed-search-wrapper` | `solo-fixed-search__wrapper` |
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
| `footer-menu` | `solo-footer-nav` |
| `footer-menu-first` | `solo-footer-nav__first` |
| `footer-menu-inner` | `solo-footer-nav__inner` |
| `footer-menu-second` | `solo-footer-nav__second` |
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
| `login-block-button-close-inner` | `solo-popup-login__close-inner` |
| `login-block-button-open-inner` | `solo-popup-login__open-inner` |
| `login-button-close` | `solo-popup-login__close` |
| `login-button-open` | `solo-popup-login__open` |
| `login-popup-trigger` | `solo-popup-login__trigger` |
| `lone` | `solo-container--single` |
| `main-box` | `solo-region-box--main` |
| `main-box-inner` | `solo-region-box__inner--main` |
| `main-box-sides` | `solo-region-box--main-sides` |
| `main-container` | `solo-container--main` |
| `main-container-inner` | `solo-container__inner--main` |
| `main-navigation-wrapper` | `solo-main-nav` |
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
| `mobile-nav` | `solo-mobile-nav` |
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
| `popup-login-block` | `solo-popup-login` |
| `popup-login-block-inner` | `solo-popup-login__inner` |
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
| `revisions-task` | `solo-local-task--revisions` |
| `search-block-button-close-inner` | `solo-fixed-search__close-inner` |
| `search-button-block` | `solo-fixed-search__button-block` |
| `search-button-close` | `solo-fixed-search__close` |
| `search-button-open` | `solo-fixed-search__open` |
| `sidebar-box-first` | `solo-region-box--sidebar-first` |
| `sidebar-box-first-inner` | `solo-region-box__inner--sidebar-first` |
| `sidebar-box-main` | `solo-region-box--content` |
| `sidebar-box-main-inner` | `solo-region-box__inner--content` |
| `sidebar-box-second` | `solo-region-box--sidebar-second` |
| `sidebar-box-second-inner` | `solo-region-box__inner--sidebar-second` |
| `sidebar-button-close` | `solo-sidebar-menu__close` |
| `sidebar-button-open` | `solo-sidebar-menu__open` |
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
| `system-messages` | `solo-system-messages` |
| `system-messages-inner` | `solo-system-messages__inner` |
| `task-icon` | `solo-local-task__icon` |
| `task-local` | `solo-local-task` |
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
| `view-task` | `solo-local-task--view` |
| `views-page` | `solo-body--views-page` |
| `welcome-text` | `solo-welcome-text` |
| `welcome-text-inner` | `solo-welcome-text__inner` |
| `wrapper-responsive` | `solo-menu-wrapper--responsive` |
| `wrapper-sticky` | `solo-menu-wrapper--sticky` |

## Ids (60)

| Solo 1 | Solo 2 |
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
| `search-button-close` | `solo-search-button-close` |
| `search-button-open` | `solo-search-button-open` |
| `sidebar-box-first` | `solo-sidebar-box-first` |
| `sidebar-box-main` | `solo-sidebar-box-main` |
| `sidebar-box-second` | `solo-sidebar-box-second` |
| `sidebar-button-close` | `solo-sidebar-button-close` |
| `sidebar-button-open` | `solo-sidebar-button-open` |
| `svg-bottom` | `solo-svg-bottom` |
| `svg-middle` | `solo-svg-middle` |
| `svg-top` | `solo-svg-top` |
| `system-messages` | `solo-system-messages` |
| `system-messages-inner` | `solo-system-messages-inner` |
| `top-box-first` | `solo-top-box-first` |
| `top-box-second` | `solo-top-box-second` |
| `top-box-third` | `solo-top-box-third` |
| `top-container` | `solo-top-container` |
| `top-container-inner` | `solo-top-container-inner` |
| `welcome-text` | `solo-welcome-text` |
| `welcome-text-inner` | `solo-welcome-text-inner` |

## State classes (set by Solo JavaScript) (19)

| Solo 1 | Solo 2 |
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

| Solo 1 | Solo 2 |
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

## Context-dependent (pick by hand) (2)

| Solo 1 | Solo 2 |
|---|---|
| `nolink` | `solo-menu-tree__item--no-link` or `solo-menu-tree__nolink` - printed beside no-link and nav__menu-nolink in 9 menu templates; S1 decides per element (needs checking) |
| `section-*` | `solo-section--*` - dynamic section- prefix printed once; origin needs checking in S1 |

## Removed (no Solo 2 element) (2)

| Solo 1 | Why |
|---|---|
| `discription` | typo selector, printed nowhere (needs checking) |
| `node-from` | typo selector, printed nowhere (needs checking) |

## Unchanged on purpose (165)

Font and animation value classes are public names and keep their Solo 1 spelling: `abel`, `abril-fatface`, `acme`, `akronim`, `aladin`, `alegreya-aans-sc`, `alegreya-sans`, `amita`, `animate-solo-aurora-wave`, `animate-solo-cosmic-drift`, `animate-solo-dreamy-bloom`, `animate-solo-echo-pulse`, `animate-solo-electric-surge`, `animate-solo-gliding-letters`, `animate-solo-gravity-defy`, `animate-solo-holographic-spin`, `animate-solo-moonlight-dance`, `animate-solo-mystic-fade`, `animate-solo-phoenix-rise`, `animate-solo-quantum-leap`, `animate-solo-rhythmic-slide`, `animate-solo-sapphire-zoom`, `animate-solo-serene-float`, `animate-solo-shimmering-wave`, `animate-solo-solar-flare`, `animate-solo-starry-glide`, `animate-solo-swirling-entrance`, `animate-solo-timeless-flip`, `animate-solo-velvet-slide`, `animate-solo-whisper-fade`, `animate-solo-whispering-shadows`, `animate-solo-zen-rotation`, `anton`, `archivo-black`, `archivo-narrow`, `arimo`, `arvo`, `audiowide`, `barlow`, `barlow-semi-condensed`, `barriecito`, `be-vietnam-pro`, `bebas-neue`, `berkshire-swash`, `big-shoulders-stencil-display`, `big-shoulders-stencil-text`, `bitter`, `black-ops-one`, `bree-serif`, `bungee-shade`, `cairo`, `ceviche-one`, `cinzel`, `comfortaa`, `contrail-one`, `cookie`, `courgette`, `creepster`, `croissant-one`, `crushed`, `dancing-script`, `dis-all-animations`, `dis-anim-link`, `dis-anim-submit`, `dis-border`, `dis-border-radius`, `dis-box-shadow`, `dis-icon-colors`, `dis-margin`, `dis-opacity`, `dis-padding`, `dis-text-transform`, `dm-serif-display`, `domine`, `doppio-one`, `dosis`, `eagle-lake`, `exo-2`, `faster-one`, `finger-paint`, `fira-sans`, `fjalla-one`, `francois-one`, `fredericka-the-great`, `fredoka`, `frijole`, `fugaz-one`, `gilda-display`, `girassol`, `glegoo`, `hammersmith-one`, `hind-siliguri`, `ibm-plex-sans`, `image-auto`, `inconsolata`, `indie-flower`, `inline-radio-checkbox`, `irish-grover`, `italiana`, `joan`, `josefin-sans`, `julius-sans-one`, `kalam`, `kanit`, `katibeh`, `kaushan-script`, `kavivanar`, `kumar-one`, `lato`, `libre-baskerville`, `lobster-two`, `londrina-outline`, `lora`, `megrim`, `merienda`, `merriweather`, `metal-mania`, `monoton`, `montserrat`, `moo-lah-lah`, `mukta`, `new-rocker`, `nosifer`, `nunito`, `open-sans`, `oranienbaum`, `oswald`, `overlock-sc`, `oxygen`, `patrick-hand`, `pirata-one`, `playfair-display`, `poiret-one`, `poppins`, `prata`, `prompt`, `prosto-one`, `pt-serif`, `quicksand`, `raleway`, `redressed`, `righteous`, `roboto`, `roboto-condensed`, `roboto-mono`, `ruslan-display`, `rye`, `sancreek`, `slackey`, `source-sans-3`, `source-sans-pro`, `spline-sans`, `staatliches`, `tapestry`, `titillium-web`, `trade-winds`, `train-one`, `ubuntu`, `uncial-antiqua`, `unica-one`, `unifrakturmaguntia`, `work-sans`, `yanone-kaffeesatz`, `yatra-one`, `yeseva-one`.

A trailing `*` is a dynamic tail: the old prefix followed by any value becomes the new prefix with the same value.
