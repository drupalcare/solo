# W3.CSS in Solo

## Upstream

W3.CSS 4.15, December 2020, by Jan Egil and Borge Refsnes (https://www.w3schools.com/w3css/).
Solo's copy is `w3.css` in this folder (library `solo/base-w3-css`, weight -202). It is
edited by hand in the Solo cascade layers; there is no build step and no untouched upstream
copy in Solo.

The W3.CSS rules that must beat unlayered core, contrib, Paragraphs Bundles or VVJ rules are
not in this file: they are in `css/base/solo-host-overrides.css`, section
"from css/w3-css/w3.css". That includes every animation binding (`.w3-spin`,
`.w3-animate-*`), the column widths, the modal widths and the padding of most components.

Every `w3-*` class keeps its name and meaning. Class names follow W3CSS Theme's (the
owner-approved rename standard): the same class does the same thing in both themes.

## Modern rebuild (2026-11-10)

Ported from W3CSS Theme's rebuild of the same date (`d8w3css/css/w3-css/SOURCE.md`,
"Modern rebuild"), which holds the browser target and the support source for every feature:
Baseline widely available plus the current and previous major of Chrome, Edge, Firefox,
Safari and iOS Safari on 2026-11-10, checked against web-features 3.45.0 (MDN
browser-compat-data). Solo uses no feature that rebuild did not check.

### Removed

Only Internet Explorer, old Edge or old Firefox needed these: `-ms-text-size-adjust`,
`article ... section { display: block }`, `audio, canvas, progress, video
{ display: inline-block }`, `template`, `a { background-color }`, `dfn`,
`img { border-style }`, `button, input { overflow: visible }`, `textarea { overflow }`,
checkbox and radio padding, `::-moz-focus-inner`, `:-moz-focusring`, `-ms-transform`
(five rules), `-khtml-`, `-moz-` and `-ms-user-select`.

Removed because `css/base/solo-normalize.css` owns the same rule (one owner per rule). That
file loads after this one in the same layer, so these copies never applied and removing them
changes nothing: box-sizing, `-webkit-text-size-adjust`, body margin, `[hidden]`, `summary`,
`progress`, `abbr[title]`, `b`/`strong`, `small`, `sub`/`sup`, `hr` box and overflow,
`code` font-size, form-control margin and text-transform, `-webkit-appearance` on buttons
and search fields, `legend`, the number spinner, the search decoration and
`::-webkit-file-upload-button`.

Kept on purpose: `-webkit-touch-callout` and `-webkit-user-select` on buttons (Safari needs
the prefix), the float grid, the float bar and the clearfix pseudo-elements (see Held).

### Skipped, and why

These are in W3CSS Theme's rebuild but Solo's own CSS owns the rule, and adding them here
would either duplicate it or change how Solo looks:

- Focus ring (`:focus-visible` outline in the locked layer): Solo draws its own two-color
  ring in `solo-normalize.css` and `solo-host-overrides.css`. Only the keyboard access to
  hover content was ported (below).
- `[hidden]:not([hidden="until-found"])`: `solo-normalize.css` owns `[hidden]`. Until it
  changes, `hidden="until-found"` content stays hidden from find-in-page in Solo.
- `legend` back to the browser default: `solo-normalize.css` and `solo-host-overrides.css`
  still set `display: table`.
- `img` and `video` `max-width`: already in `solo-normalize.css`; only `picture`, `canvas`
  and `svg` were added here.
- `hr { color: inherit }` and the `fieldset` rule: Solo paints `hr` from `--r-border`, and
  `fieldset` lives in `solo-host-overrides.css`.

### Visible changes (check on a site)

| Class or element | Before | After | Why |
|---|---|---|---|
| `picture`, `canvas`, `svg` | could overflow their column | `max-width: 100%` (zero specificity, any class wins) | media never break a layout; `img` and `video` already had it |
| `h1` to `h6` | browser line breaks | `text-wrap: balance` | even line lengths in headings |
| `pre` and code with tab characters | tab width 8 | `tab-size: 4` | readable code |
| `.w3-top`, `.w3-bottom` | `width: 100%` from the static left edge | `inset-inline: 0` | a padded parent no longer pushes the bar sideways and past the right edge |
| `.w3-modal-content` | a fixed width could exceed a narrow window | `max-width: 100%` | never wider than the modal |
| `.w3-animate-zoom`, `.w3-spin` on a `.w3-display-*` box | the animation replaced the centering transform, so the box jumped off center | `scale` and `rotate` properties, centering kept | combined classes work |
| `.w3-animate-top/left/right/bottom` | animated `inset-*` | animate `translate` | same look; no layout work per frame, and a site `top` or `left` on the box is not overwritten while it runs. Left and right still slide from the start and end side in right-to-left pages (`--solo-w3-inline-dir`) |
| `.w3-code`, `.w3-codespan` | `Consolas, "courier new"` | adds a `monospace` fallback | systems with neither font showed a serif font |
| `::file-selector-button` | Firefox used the system font | `appearance: button; font: inherit` | the standard name; Chrome and Safari already had it through the old `::-webkit-` name |
| `.w3-dropdown-hover`, `.w3-tooltip`, `.w3-display-container` | content showed on mouse hover only | also shows while keyboard focus is inside | keyboard users reach the same content (WCAG 2.1.1) |
| `.w3-spin`, `.w3-animate-*`, `.w3-main`, `#main`, `.w3-animate-input` | moved for every visitor | no animation or transition when the visitor asks for reduced motion | WCAG 2.3.3 |
| `input[type=button/reset/submit]` in Firefox | 1px dotted focus outline | Solo's focus ring | the `-moz-focusring` rule was for old Firefox |

### Held for the owner (not built)

1. `.w3-row` to flex for every row: a heading or text inside a row would sit beside the
   columns.
2. `.w3-bar` to flex: several `.w3-right` items would swap order (float right lays out right
   to left, flex keeps source order).
3. Clearfix to `display: flow-root`: every container would narrow beside an outside float.
4. `.w3-display-*` centering on the `translate` property: w3css_paragraphs sets the same
   `transform: translate()` unlayered, and the two would add up.

### New classes

All in `solo.utilities`, spacing from Solo's size tokens (`--solo-px8`, `--solo-px16`,
`--solo-px32`), no colors.

| Class | What it does | Example |
|---|---|---|
| `w3-flex-column` | flex, one under the other | `<div class="w3-flex-column w3-gap-small">` |
| `w3-items-center` | align flex or grid items on the cross axis center | `<div class="w3-cluster w3-items-center">` |
| `w3-justify-center` | center items on the main axis | `<div class="w3-flex-column w3-justify-center">` |
| `w3-justify-between` | first and last item at the edges | `<div class="w3-cluster w3-justify-between">` |
| `w3-gap-small`, `w3-gap-large` | gap `--solo-px8`, `--solo-px32` | `<div class="w3-stack w3-gap-large">` |
| `w3-stack` | vertical stack with an even gap (`--w3-stack-gap`, default `--solo-px16`) | `<div class="w3-stack"><p>..</p><p>..</p></div>` |
| `w3-cluster` | wrapping row with an even gap (`--w3-cluster-gap`, default `--solo-px8`) | `<div class="w3-cluster"><a class="w3-tag">..</a></div>` |
| `w3-place-center` | content exactly in the middle | `<div class="w3-place-center w3-screen-height">` |
| `w3-ratio-square`, `w3-ratio-photo`, `w3-ratio-video` | 1:1, 4:3, 16:9 box | `<div class="w3-ratio-video"><iframe class="w3-cover">` |
| `w3-cover` | fill the box and crop | `<img class="w3-cover" ...>` |
| `w3-screen-height` | at least the visible screen height (`100vh`, then `100dvh`) | `<section class="w3-screen-height">` |
| `w3-truncate` | one line with an ellipsis | `<p class="w3-truncate">` |
| `w3-query-container` | the box answers `@container` rules | `<div class="w3-query-container"><div class="w3-card">` |
| `w3-snap-row` | sideways scrolling row that stops on each child | `<div class="w3-snap-row"><div class="w3-card">` |

### Flex and grid classes (owner: "same thing if you are missing something build it", 2026-11-10)

Same names, values and breakpoint as W3CSS Theme's (`d8w3css/css/base/w3css-drupal.css`), in
`solo.utilities`. The only difference: the gap reads `--solo-px16` where W3CSS Theme reads
`--w3-space-4`. `w3-gap` comes before `w3-gap-small` and `w3-gap-large` in the file, so those
win when both are on one element.

| Class | What it does | Example |
|---|---|---|
| `w3-flex` | `display: flex` | `<div class="w3-flex w3-gap">` |
| `w3-flex-wrap` | `flex-wrap: wrap` | `<div class="w3-flex w3-flex-wrap w3-gap">` |
| `w3-grid` | `display: grid` (set the columns yourself) | `<div class="w3-grid w3-gap">` |
| `w3-grid-2`, `w3-grid-3`, `w3-grid-4` | one column, then 2, 3 or 4 equal columns from 601px | `<div class="w3-grid-3 w3-gap">` |
| `w3-grid-auto` | as many columns as fit, each at least `--w3-grid-min` (default `16rem`), never wider than the box | `<div class="w3-grid-auto w3-gap" style="--w3-grid-min: 12rem">` |
| `w3-gap` | gap `--solo-px16` | `<div class="w3-grid-2 w3-gap">` |
