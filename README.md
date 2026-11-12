# Solo - W3CSS Theme

Solo is the third generation of the W3CSS Theme and has been thoroughly
revamped. This version does not rely on jQuery, making the inclusion of
the w3.css library optional.

- Project page: [drupal.org/project/solo](https://www.drupal.org/project/solo)
- Issue queue: [drupal.org/project/issues/solo](https://www.drupal.org/project/issues/solo)

Should you wish to construct a more complex website, consider using
the [Paragraphs Bundles](https://www.drupal.org/project/paragraphs_bundles)
module. This module equips you with strong capabilities to create complex
components such as 3D cards, slideshows, 3D carousel, tabs, cards,
accordions, and more.


## Table of contents

- Overview
- Requirements
- Installation
- Configuration
- Usage
- Troubleshooting
- Changelog
- Maintainers
- License


## Overview


### 24 regions

The theme offers 24 distinct regions, each with its own unique settings,
allowing for a high degree of customization and control over the look and
feel of your website. The regions in this theme are designed to accommodate
a wide variety of content types and layouts.


### Intelligent design

If a region is not being used, its settings will be hidden. This helps to
keep the theme's interface clean and uncluttered, making it easier for you
to find and adjust the settings you need.


### Responsive design

Solo theme is fully responsive, ensuring your website will look and function
perfectly on all devices, including desktops, tablets, and smartphones.


### Five breakpoints

The Solo theme provides 5 breakpoints for the site and 5 for the menu,
enabling you to display the most optimal layout to the user.


### Three-level main menu and megamenu

Includes a three-level main menu, enabling you to create a complex navigation
structure with ease. Hoverable or clickable.


### Sidebar main menu

In addition to the three-level main menu, the theme also includes a sidebar
main menu, providing an additional navigation option.


### Customized colors

Customize 15 color fields per region to align the colors with your brand
identity.

Solo uses CSS custom properties for region-specific theming, providing granular
control while keeping layouts consistent. These are the region variables:

- `--r-background`: Region background color. Sets the overall background color of the
  region wrapper.
- `--r-text`: Region text color. Sets the default text color for all paragraph and
  inline text within the region.
- `--r-heading`: Region heading color. Controls the color of headings (h1, h2, h3)
  within the region.
- `--r-link`: Region link color. Sets the color of links (`<a>`) in their normal,
  non-hover state.
- `--r-link-hover`: Region link hover color. Sets the color of links when hovered or
  focused.
- `--r-border`: Region border color. Sets the border color of the region.
- `--r-form-background`: Form background color within the region. Targets backgrounds of
  forms such as login and search blocks.
- `--r-menu-text`: Menu link text color within the region.
- `--r-menu-text-hover`: Menu link hover text color within the region.
- `--r-menu-background`: Menu link background color in the region (normal state).
- `--r-menu-background-hover`: Menu link background hover color in the region.
- `--r-button-text`: Button text color within the region (normal state).
- `--r-button-text-hover`: Button text hover color within the region.
- `--r-button-background`: Button background color within the region (normal state).
- `--r-button-background-hover`: Button background hover color within the region.


### Customized Layout

Customize your website's layout with seven styles for two-column layouts,
three styles for three-column layouts, and four styles for four-column layouts.


### Preloader, back to top, menu template assignment, and formatted copyright

Features added for loading experience, scroll behavior, menu presentation, and
footer content.


#### Preloader (global settings)

Load-based preloader: displays while the page is loading and hides when ready.
No minimum display time—fast loads show it briefly; slow loads keep it until
complete. Configurable **max display time** (3–60 seconds) acts as a fallback
only if the load event never fires. **Visibility:** enable/disable, **hide
trigger** (“Page appears” via `DOMContentLoaded`; default, or “Page fully
loaded” via `window.load`), max display (sec), “Force show for testing” (30s),
disable on admin routes, disable for authenticated users, and **path
include/exclude rules** (one path per line; prefix with minus to exclude, e.g.
`-/contact`; list paths without prefix to show only on those paths).
**Appearance:** style (spinner, progress bar, text, logo, or logo + text).
**Transition effect** (fade, slide up/down/left/right, zoom out, blur) controls
how the preloader disappears. Spinner: modern thin ring with optional percentage
inside. Progress bar: centered rounded bar (320px max-width) with animated fill
and percentage label. Text/logo: optional text, font, animation effect, font
size; logo can **use theme logo** or a custom path (public://, themes/..., or
filename). Background color with **separate opacity** (0–100%, applied only to
background, not content) and text color. Library and CSS are attached only when
the preloader is enabled (scoped under `html.solo-preloader-enabled`). Shows
**instantly on link click** (capture phase) and hides when the new page is
ready. Critical CSS in head; respects reduced motion.


#### Back to top (global settings)

Accessible back-to-top button under Global Settings: enable/disable, visibility
(admin routes, authenticated users, small screens), scroll distance (200–800
px),
position (bottom-right or bottom-left), style (solid/outline), optional colors,
and icon (arrow-up, chevron-up, arrow-minimal). Styling uses **CSS variables**
only (no inline layout); scroll threshold is set as a variable and applied in
CSS. Uses proper button semantics and respects reduced motion.


#### Menu template assignment (global settings)

Under Global Settings, assign any of Solo's responsive menu Twig templates to
any
menu (primary, footer, or custom). Control how each menu is rendered—e.g. use
the
main menu in the header with a specific responsive template—without custom code.


#### Formatted copyright and credit

Option to use a single formatted (rich-text) field for copyright and credit in
the
footer. Enter all copyright, legal text, links, and ICP in one field with a
chosen
text format. When off, the four separate copyright/credit fields remain
available.


### Conclusion

Solo is perfect for those who want a high degree of control over their
website's design, but also value simplicity and ease of use. Whether you are
creating a simple blog or a complex corporate website, Solo theme will provide
you with the tools you need to create an effective online presence.


### Solo regions

The Solo theme encompasses two types of regions: **Single and Grouped regions**.


#### [1] Single regions

The theme consists of the following single regions:

1. Popup Login Block
1. Fixed Search Block
1. Header
1. Primary Sidebar Menu
1. Primary Menu
1. Welcome Text
1. Highlighted
1. System Messages
1. Page Title
1. Breadcrumb
1. Footer Menu Container

All single regions are visible on all site pages, except 'Page Title' (not
visible on the homepage) and 'Welcome Text' (visible on the homepage only).


#### [2] Grouped regions

The theme also includes the following grouped regions:

| TOP         | MAIN         | BOTTOM        | FOOTER        |
|-------------|--------------|---------------|---------------|
| 1. Top 1    | 1. Sidebar L | 1. Bottom 1   | 1. Footer 1   |
| 2. Top 2    | 2. Content   | 2. Bottom 2   | 2. Footer 2   |
| 3. Top 3    | 3. Sidebar R | 3. Bottom 3   | 3. Footer 3   |
|             |              | 4. Bottom 4   |               |


### Theme architecture

The `div` element with the class `page-wrapper` wraps around 24 distinct
regions.
Both the `page-wrapper` and each of these regions can have up to 15 different
color inputs applied to them. The color inputs can be categorized as follows:


#### Color categories

- **General**
  - Background color
  - Text color
  - Border color
  - HTML heading color (h1, h2, h3)
  - Background color for Form Input Field

- **Links**
  - Text link color
  - Text link hover color

- **Menus**
  - Text menu link color
  - Text menu link hover color
  - Background menu link color
  - Background menu link hover color

- **Buttons**
  - Text color for buttons
  - Text hover color for buttons
  - Background button color
  - Background button hover color

**Note:** The construction of the 15 colors is as follows: First, apply global
colors to the page wrapper. Then, for each region, apply specific colors. Note
that the colors of the regions will override the global colors.


## Requirements

- **Drupal core** `^11.3 || ^12` and **PHP** 8.3 or later (`solo.info.yml`,
  `composer.json`).
- **[Solo Utilities](https://www.drupal.org/project/solo_utilities)** `^2.0`:
  the module that holds Solo's logic. Solo 2 requires it, and Composer installs
  it with the theme.


## Installation


### Install, and when you need a sub-theme

Install Solo with `composer require drupal/solo`, which also brings the
[Solo Utilities](https://www.drupal.org/project/solo_utilities) module that
holds Solo's logic, then click **Install and set as default** under
**Appearance**. No sub-theme is needed for most
sites:

- **Everything you set in the theme settings** (colors, layout, menus,
  regions) is stored in your site's configuration, so updating Solo never
  overwrites it.
- **Your own CSS and JavaScript** go in the theme settings too, under
  **Global Site Settings > Custom Code**: *CSS Injector*, *CSS Dynamic* and
  *JavaScript Dynamic*. They are saved with your settings, or to files in
  `sites/*/files/solo/`, never inside Solo's folder.

**Create a sub-theme when you need to override Twig templates.** Never
edit Solo's own files: the next update replaces them.

The quickest way is one Drush command from Solo Utilities (Drush 12 or
later). It copies the starter to
`themes/custom/my_theme` and renames everything for you:

```
drush solo:subtheme my_theme
```

Or do it by hand from the starter Solo ships, the `solo_subtheme` folder:

1. Copy it to `themes/custom/my_theme` and rename it as described in
   `solo_subtheme/README.md`. Every `solo_subtheme` must become your
   theme's machine name.
1. Clear the cache (`drush cr`), then install your sub-theme and set it as
   the default theme under **Appearance**. Drupal installs Solo with it
   as its base theme.

Full guide:
[Solo how-to: install Solo
theme](https://www.drupal.org/docs/extending-drupal/themes/contributed-themes/solo/solo-how-to#install-solo-theme).


### Upgrading from Solo 1

**Sub-theme owners: this release renames Solo's CSS classes and element IDs, so
your sub-theme's CSS, Twig and JavaScript break until you migrate them.** Run
`drush solo:migrate-classes my_theme` first. It is a dry run and changes
nothing. Read the list, then run it again with `--apply`. `drush updatedb` also
rewrites the inline CSS saved in Solo's settings. The full list of old and new
names is in `docs/CLASS-MIGRATION.md` (also `docs/class-migration.html`, which opens offline). The command also renames library ids (`--yml` for your `.info.yml` and `.libraries.yml`), JavaScript names and region color variables, and reports moved files.

Solo 2 keeps templates, CSS, JavaScript and settings data in the theme and moves
all PHP into the required Solo Utilities module. Setting names change, and the
update migrates them for Solo and every sub-theme.

**Before you start:** be on Solo 1.0.35 or later and Drupal 11.3 or later, and
run `drush solo:preflight` to list what your own code must change. If you upload
code by hand, add the `solo_utilities` folder too, or every database update
stops at the requirements check. Take a database snapshot.

**The route, in this order:**

```
drush maintenance:mode 1     # older Drush: drush state:set system.maintenance_mode 1
composer update drupal/solo drupal/solo_utilities --with-dependencies
drush updatedb
drush config:export
drush maintenance:mode 0
```

Keep maintenance mode on until `drush updatedb` finishes without an error.
Between
the new code landing and the update, a page that is not already cached can fail
with a 500 error, because compiled templates and services still expect Solo 1.
No
design can close that gap; the route hides it. If the update names a theme it
could
not migrate, that theme is left unchanged and shows default settings until you
fix
it and run `drush updatedb` again.

**Run `drush config:export`.** Solo now declares the module as a dependency, so
an
old export would uninstall it on the next import, which drops its node width
table.
A stale export holding old setting names is converted on import, but your first
export after the update shows a whole-file difference once.

**To roll back:** restore the database snapshot together with the old code, then
run
`drush cr`. With no snapshot, run `drush solo:settings-restore` **before**
putting
the old code back, and restore `config/sync` from version control by hand. That
path
loses settings saved after the migration. Solo 1 cannot read the new names, so
rolling back code alone shows default settings. Never uninstall Solo Utilities
as a
rollback: uninstalling clears the width settings.

**Four things the update cannot change for you.** Each makes no error, it just
stops
working. `drush solo:preflight` lists them by file and line:

1. Code reading a setting directly, such as
   `\Drupal::config('my_theme.settings')->get('old_name')`, or a Twig config
   function
   from a contributed module. It now returns NULL.
1. A sub-theme's own settings form alter aimed at an old element path. It stops
   matching.
1. Sub-theme code that writes `->set('old_name', ...)`. It creates a key nothing
   reads.
1. An override in `settings.php`, such as
   `$config['my_theme.settings']['old_name']`.
   It stops applying.

Sub-theme code calling Solo 1's PHP helper functions or classes must change as
well:
they are gone, and `drush solo:preflight` lists each call and each use of the
classes `Drupal\solo\SocialIconColor` and `Drupal\solo\SoloIncludes`.
Font files under `fonts/font-files/` moved: a sub-theme `@font-face` pointing at
them should attach the `solo/font-<name>-local` library instead. `theme_get_setting('old_name')` keeps working only while the
optional
`solo_utilities_compat` sub-module is on.


## Configuration

Everything you set in the theme settings is stored in your site's configuration,
so updating Solo never overwrites it.



#### Library ids renamed in Solo 2

Every Solo library id now reads `<layer>-<name>`: `solo/solo-global` is `solo/bundle-global`, `solo/solo-menu` is `solo/bundle-menu`, `solo/solo-layers` is `solo/base-layers`, component libraries are `solo/component-...`, color schemes `solo/scheme-<name>`, fonts `solo/font-<slug>-external` and `solo/font-<slug>-local`, breakpoints `solo/breakpoint-page-sb-<px>` and `solo/breakpoint-menu-mn-<px>`. Each old id still works until Solo 3.0.0 as a deprecated alias that depends on the new library. A `libraries-override` or `libraries-extend` entry on an old id no longer changes any file, so switch it to the new id. `drush solo:preflight` lists those entries. Saved settings keep their values.

#### Class and JavaScript names renamed in Solo 2

Solo's own class names now follow the template that prints them. Until the next
major release each old class still works as a second selector in Solo's CSS and
JavaScript, so a sub-theme template or content that prints an old class keeps
its look and behavior. Update to the new name when you can.

| Old name | New name | Kind |
| --- | --- | --- |
| `solo-file-button` | `solo-input-file__button` | class |
| `solo-file-custom` | `solo-input-file__custom` | class |
| `solo-file-name` | `solo-input-file__name` | class |
| `solo-file-native` | `solo-input-file__native` | class |
| `solo-file-upload-wrapper` | `solo-input-file__wrapper` | class |
| `solo-fixed-search` | `solo-fixed-search-block` | class |
| `solo-fixed-search__button-block` | `solo-fixed-search-block__button-block` | class |
| `solo-fixed-search__close` | `solo-fixed-search-block__close` | class |
| `solo-fixed-search__close-inner` | `solo-fixed-search-block__close-inner` | class |
| `solo-fixed-search__inner` | `solo-fixed-search-block__inner` | class |
| `solo-fixed-search__open` | `solo-fixed-search-block__open` | class |
| `solo-fixed-search__wrapper` | `solo-fixed-search-block__wrapper` | class |
| `solo-footer-nav` | `solo-footer-menu__nav` | class |
| `solo-footer-nav__first` | `solo-footer-menu__nav__first` | class |
| `solo-footer-nav__inner` | `solo-footer-menu__nav__inner` | class |
| `solo-footer-nav__second` | `solo-footer-menu__nav__second` | class |
| `solo-local-task` | `solo-menu-local-task` | class |
| `solo-local-task--core` | `solo-menu-local-task--core` | class |
| `solo-local-task--delete` | `solo-menu-local-task--delete` | class |
| `solo-local-task--revisions` | `solo-menu-local-task--revisions` | class |
| `solo-local-task--view` | `solo-menu-local-task--view` | class |
| `solo-local-task__icon` | `solo-menu-local-task__icon` | class |
| `solo-main-nav` | `solo-menu-main` | class |
| `solo-menu-wrapper--responsive` | `solo-table__menu-wrapper--responsive` | class |
| `solo-menu-wrapper--sticky` | `solo-table__menu-wrapper--sticky` | class |
| `solo-mobile-nav` | `solo-menu-main--mobile` | class |
| `solo-popup-login` | `solo-popup-login-block` | class |
| `solo-popup-login__close` | `solo-popup-login-block__close` | class |
| `solo-popup-login__close-inner` | `solo-popup-login-block__close-inner` | class |
| `solo-popup-login__inner` | `solo-popup-login-block__inner` | class |
| `solo-popup-login__open` | `solo-popup-login-block__open` | class |
| `solo-popup-login__open-inner` | `solo-popup-login-block__open-inner` | class |
| `solo-search-button-close` | `solo-fixed-search-block__close` | id |
| `solo-search-button-open` | `solo-fixed-search-block__open` | id |
| `solo-sidebar-button-close` | `solo-menu-primary-sidebar__close` | id |
| `solo-sidebar-button-open` | `solo-menu-primary-sidebar__open` | id |
| `solo-sidebar-menu__close` | `solo-primary-sidebar-menu__close` | class |
| `solo-sidebar-menu__open` | `solo-primary-sidebar-menu__open` | class |
| `solo-system-messages` | `solo-status-messages` | class and id |
| `solo-system-messages__inner` | `solo-status-messages__inner` | class |
| `solo-table-all` | `solo-table--all` | class |
| `solo-table-wrapper` | `solo-table__wrapper` | class |

Public JavaScript names were renamed the same way. Each old behavior name stays
as an alias of the new one, the old settings key and event are still sent, and
the old data attributes are still read, all until the next major release.

| Kind | Old name | New name |
| --- | --- | --- |
| Behavior | `Drupal.behaviors.commentRepliesToggle` | `Drupal.behaviors.soloComments` |
| Behavior | `Drupal.behaviors.globalMenu` | `Drupal.behaviors.soloMenuMain` |
| Behavior | `Drupal.behaviors.menuAction` | `Drupal.behaviors.soloMenu` |
| Behavior | `Drupal.behaviors.mobileMenu` | `Drupal.behaviors.soloMenuMobile` |
| Behavior | `Drupal.behaviors.soloPrimarySideMenu` | `Drupal.behaviors.soloMenuPrimarySidebar` |
| Behavior | `Drupal.behaviors.soloMenuFix` | `Drupal.behaviors.soloMenuSubmenuPosition` |
| Behavior | `Drupal.behaviors.soloFileUpload` | `Drupal.behaviors.soloInputFile` |
| Behavior (Solo Utilities) | `Drupal.behaviors.ColorSchemesRules` | `Drupal.behaviors.soloUtilitiesColorSchemesRules` |
| drupalSettings key | `drupalSettings.solo.debug_verbosity` | `drupalSettings.solo.debugVerbosity` |
| Event | `solo-aria-change` | `solo:ariaChange` |
| Data attribute | `data-comment-id` | `data-solo-comment-id` |
| Data attribute | `data-force-show` | `data-solo-preloader-force-show` |
| Data attribute | `data-hide-on` | `data-solo-preloader-hide-on` |
| Data attribute | `data-max-display` | `data-solo-preloader-max-display` |
| Data attribute | `data-hover-toggle` | `data-solo-hover-toggle` |
| Data attribute (Solo Utilities form) | `data-default-value` | `data-solo-default-value` |

`once()` ids and the internal `data-hover-added` and `data-original-tabindex`
markers were renamed without an alias (`data-solo-hover-added`,
`data-solo-original-tabindex`): JavaScript that selects
`[data-once~="old-id"]` must use the new id.

#### Region color variables renamed in Solo 2

Region colors use the same 15 `--r-<role>` names in every theme: `--r-background`, `--r-text`, `--r-heading`, `--r-link`, `--r-link-hover`, `--r-border`, `--r-form-background`, `--r-menu-text`, `--r-menu-text-hover`, `--r-menu-background`, `--r-menu-background-hover`, `--r-button-text`, `--r-button-text-hover`, `--r-button-background` and `--r-button-background-hover`. Each is a typed color, so a value that is not a color is ignored. A gradient goes in `--r-background-image`; `--r-background` holds only a color. Until the next major release the old names below still work as readers: CSS that reads `var(--r-tx)` keeps its color. A rule that sets an old name changes nothing, so rewrite it to the new name. Saved settings keep their values.

<!-- region-vars:start -->

| Old name | New name |
| --- | --- |
| `--r-bg` | `--r-background` |
| `--r-bg-bt` | `--r-button-background` |
| `--r-bg-bt-h` | `--r-button-background-hover` |
| `--r-bg-dark` | `--solo-tone-background-dark` |
| `--r-bg-fr` | `--r-form-background` |
| `--r-bg-light` | `--solo-tone-background-light` |
| `--r-bg-lk` | `--r-menu-background` |
| `--r-bg-lk-h` | `--r-menu-background-hover` |
| `--r-br` | `--r-border` |
| `--r-br-b` | `--solo-border-default` |
| `--r-br-dark` | `--solo-tone-border-dark` |
| `--r-br-light` | `--solo-tone-border-light` |
| `--r-h1` | `--r-heading` |
| `--r-header-bg` | `--solo-accent-background` |
| `--r-header-tx` | `--solo-accent-text` |
| `--r-lk` | `--r-link` |
| `--r-lk-h` | `--r-link-hover` |
| `--r-menu-bg-h` | `--r-menu-background-hover` |
| `--r-menu-tx-h` | `--r-menu-text-hover` |
| `--r-sc-h1` | `--r-heading` |
| `--r-sc-lk` | `--r-link` |
| `--r-sc-lk-h` | `--r-link-hover` |
| `--r-sc-tx-bt` | `--r-button-text` |
| `--r-sc-tx-bt-h` | `--r-button-text-hover` |
| `--r-tx` | `--r-text` |
| `--r-tx-bt` | `--r-button-text` |
| `--r-tx-bt-h` | `--r-button-text-hover` |
| `--r-tx-dark` | `--solo-tone-text-dark` |
| `--r-tx-light` | `--solo-tone-text-light` |
| `--r-tx-lk` | `--r-menu-text` |
| `--r-tx-lk-h` | `--r-menu-text-hover` |

<!-- region-vars:end -->

### Blueprint

The blueprint for the Solo theme regions is a structured layout that defines
the various sections of a web page. A brief description of each region follows:

- `highlighted`: This block is for featured or important content.
- `popup_login_block`: This is a block that pops up for user login.
- `fixed_search_block`: This block is for the search bar, fixed in place.
- `header`: The topmost part of the webpage, usually with the logo and
navigation.
- `primary_sidebar_menu`: The main sidebar menu, on the left or right of the
page.
- `primary_menu`: The main navigation menu, usually in the header.
- `welcome_text`: A text block welcoming the user to the website.
- `top_first`, `top_second`, `top_third`: Container blocks at the top of the
page.
- `system_messages`: This block displays system messages to the user.
- `page_title`: Displays the title of the current page.
- `breadcrumb`: Navigation links showing the user's path to the current page.
- `sidebar_first` (Left Sidebar), `sidebar_second` (Right Sidebar).
- `content`: The main content area of the webpage.
- `bottom_first`, `bottom_second`, `bottom_third`, `bottom_fourth`: Bottom
blocks.
- `footer_first`, `footer_second`, `footer_third`: Footer container blocks.
- `footer_menu`: The navigation menu in the footer.

Each of these regions can contain various types of content, such as text,
images, links, and more, depending on the needs of the website.

This blueprint is a starting point and guide for developers, designers,
and other site builders involved in the development process.


### Global site settings

- Solo theme provides features to adjust website width and breakpoints.
- It allows altering spaces between regions and modifying layout settings.
- Solo theme enables font size modifications and custom data formats.
- Ability to import Google fonts and add custom CSS to the header.
- Modify login, register, and password pages; change header and menu order.
- Offers animation features and layout changes for multiple value fields.
- Apply reading mode to content types, setting max width for content regions.
- **Preloader:** Load-based preloader (no minimum time; configurable max display
  fallback). Hide trigger: “Page appears” (DOM ready, default) or “Page fully
  loaded”. Path include/exclude rules. Styles: spinner (with optional %),
  progress bar, text, logo, logo+text. Transition effects: fade, slide
  up/down/left/right, zoom out, blur. Background opacity (0–100%, bg only).
  Optional theme or custom logo, text/font/effects, colors. Instant show on link
  click; scoped CSS; “Force show for testing” 30s; respects reduced motion.
- **Back to top:** Accessible button; visibility, scroll distance (200–800 px),
  position, style (solid/outline), colors, icon. CSS variables only (no inline
  layout).
- **Menu template assignment:** Assign any responsive menu Twig template to any
  menu (primary, footer, or custom) so each menu can use a different template.
- **Formatted copyright and credit:** Option to use one rich-text field for all
  copyright and credit in the footer; when off, the four separate fields are
  used.


### Libraries and fonts settings

- Feature to upload and use the w3.css library locally.
- Modify the font family of the entire website with Google fonts.
- Alter font family of h1, h2, h3 with Google fonts selections.
- Offers 25 special Google fonts for application to HTML tags.


### Page wrapper settings

- Input field for predefined CSS classes from Solo or third-party libraries.
- 15 color input fields for global use across the site.


### Predefined color scheme settings

- Dropdown select list with 50 predefined Color Scheme Themes for the site.


### Social media links settings

- Options for social media icon sizes (Small, Medium, Large).
- Input color field to change social media icon colors.
- Checkbox to show or hide social icons.
- Eight social text fields and one RSS field.


### Single regions settings


#### Highlighted settings

- Change visibility options for the highlighted block.
- Text input for predefined CSS classes.
- 15 color input fields for this region.


#### Popup login block settings

- Input field for predefined CSS classes.
- Checkbox for popup block login feature.
- Text field to customize login wording.
- 15 color input fields for this region.


#### Fixed search block settings

- Input field for predefined CSS classes.
- 15 color input fields for this region.


### Theme settings documentation


#### Header settings

- Text input for predefined CSS classes from Solo or third-party libraries.
- Checkbox to center Site's Name, Slogan, and Logo.
- Checkbox to adjust positioning of Search Icon, User Menu, Sidebar Menu, and
  Sidebar Hamburger to the left.
- Dropdown for 24 predefined CSS text animations for the Site name.
- 15 color input fields for this region.


#### Primary sidebar menu settings

- Text input for predefined CSS classes.
- Checkbox to apply borders to menu items.
- 15 color input fields for this region.


#### Primary menu settings

- Text input for predefined CSS classes.
- Checkbox for hover-to-show main menu dropdowns.
- Checkbox to apply borders to menu items.
- Checkbox for even space distribution between menu items.
- 15 color input fields for this region.
- Checkbox for Mega Menu transformation with additional fields:
  - Mega Menu Layout options (2-4 columns with various percentages).
  - Mega Menu Header checkbox for second level headers.


#### Welcome settings

- Text input for predefined CSS classes.
- 15 color input fields for this region.


#### System message settings

- Text input for predefined CSS classes.
- 15 color input fields for this region.


#### Page title settings

- Text input for predefined CSS classes.
- Dropdown for 24 predefined CSS text animations for the page title.
- 15 color input fields for this region.


#### Breadcrumb settings

- Text input for predefined CSS classes.
- Checkbox to hide page title from the breadcrumb.
- 15 color input fields for this region.


#### Footer menu settings

- Text input for predefined CSS classes.
- Dropdown for 24 predefined CSS text animations for the page title.
- 15 color input fields for this region.


#### Copyright and credit settings

- Text input for predefined CSS classes.
- 15 color input fields for this region.
- Text field for dynamic copyright year.
- Checkbox to show or hide credit.
- **Formatted copyright and credit:** When enabled, one rich-text field is used
  for all copyright and credit; when off, the four separate fields above are
  used. See *Formatted Copyright and Credit* above for details.


#### Grouped regions

Regions grouped together are enclosed within a div. While the region wrapper
has its own settings, individual regions maintain their own settings.


##### The (TOP, MAIN, BOTTOM, FOOTER) region wrapper settings

- Two, three, and four columns settings with various percentage distributions.
- Change visibility options (Visible on all pages, Home Page Only, All Pages
Except Home Page).
- Checkbox for animation on individual region's border (Not for Main).
- Checkbox to apply borders to each individual region.
- Radio field for round corners on each individual region.
- Text input for predefined CSS classes.
- 15 color input fields for this region.


##### Individual region settings

- Text input for predefined CSS classes.
- 15 color input fields for this region.


### Content-Security-Policy

Measured, so that a site running a strict CSP knows what this theme puts on a
page:

- **No inline JavaScript.** No `<script>` blocks, no `on*` event handler
  attributes, no `eval()` or `new Function()`. Nothing here requires
  `script-src 'unsafe-inline'`.
- **23 inline `style="..."` attributes** across 22 templates. These need
  `style-src-attr 'unsafe-inline'`. A nonce cannot authorize a style
  *attribute*; nonces apply only to `<script>` and `<style>` elements.
- **One inline `<style>` element**, and only when you use it: the
  **CSS injector** theme setting emits whatever CSS you put in it. Leave that
  setting empty and no inline `<style>` is produced at all. This is the one
  place where a nonce would be the right fix.

Two assets are attached through `html_head` rather than the Libraries API, so
the [Content-Security-Policy module](https://www.drupal.org/project/csp) will
not discover them automatically and your policy has to allow them explicitly:

| Asset | When | Needs |
| --- | --- | --- |
| Generated `solo-js-dynamic.js`, as `<script src="…">` | *Dynamic JS* setting enabled | `script-src 'self'` |
| Google Font style sheet `<link>` | *Import Google Font* setting used | `style-src` + `font-src` for `fonts.googleapis.com` / `fonts.gstatic.com` |

The script tag is an external same-origin reference, not inline code; it is
attached this way because the locale module rejects stream-wrapper URIs for
library JS assets.


## Usage

- **Theme settings** (colors, layout, menus, regions): see
  [Configuration](#configuration).
- **Your own CSS and JavaScript:** **Global Site Settings > Custom Code**, as
  described in [Installation](#installation).
- **Create a sub-theme:** `drush solo:subtheme my_theme` (Solo Utilities, Drush
  12 or later).
- **Check a Solo 1 sub-theme for Solo 2:** `drush solo:preflight`.
- **Rename Solo 1 classes and ids:** `drush solo:migrate-classes my_theme`. The
  list of old and new names is in `docs/CLASS-MIGRATION.md`.
- **Go back to the earlier settings after an upgrade:** `drush
  solo:settings-restore`, or the **Restore earlier settings** button on
  **Configuration > System > Solo upgrade**.


## Troubleshooting

- **A page returns a 500 error right after the code update.** Compiled templates
  and services still expect Solo 1 until `drush updatedb` finishes. Keep
  maintenance mode on until it does. See "Upgrading from Solo 1".
- **The site shows default settings after an update or a code rollback.** Solo 1
  cannot read the new setting names. If the update named a theme it could not
  migrate, fix it and run `drush updatedb` again; to go back, follow the
  rollback steps in "Upgrading from Solo 1".
- **A sub-theme's CSS, Twig or JavaScript stopped matching.** Solo 2 renames its
  classes and ids. Run `drush solo:migrate-classes my_theme` (a dry run until
  you add `--apply`).
- **Custom code reading a setting returns NULL.** See "Four things the update
  cannot change for you" under "Upgrading from Solo 1". `drush solo:preflight`
  lists each by file and line.
- **A strict Content-Security-Policy blocks Solo's dynamic script or the Google
  font.** See [Content-Security-Policy](#content-security-policy).
- Report anything else in the [issue
  queue](https://www.drupal.org/project/issues/solo).


## Changelog

See [CHANGELOG.md](./CHANGELOG.md).


## Maintainers

- Alaa Haddad - [flashwebcenter](https://www.drupal.org/u/flashwebcenter)


## License

GPL-2.0-or-later. See [LICENSE.txt](./LICENSE.txt).
