# Solo theme: agent brief

**Who this is for:** whoever works the Solo theme next, human or agent, with no memory of how it got this way.

**Scope:** the `solo` theme and its starter sub-theme `solo_subtheme/`. The Solo Utilities module (`drupal/solo_utilities`) is a separate project and repository. It holds all of Solo's PHP, so a logic change is made there, not here.

## Before you change anything

1. **Solo 2 keeps templates, CSS, JavaScript and settings data in the theme and no logic.** The PHP in this repository is `solo.post_update.php` and the five classes in `src/Hook/`. Those classes are tombstones: a compiled container from Solo 1 still names them, so they exist, do nothing, take no constructor argument and carry no hook attribute. Do not add logic to them.
2. **Solo requires Solo Utilities** (`solo.info.yml` `dependencies`, `composer.json` `^2.0`). A theme feature that needs PHP is a Solo Utilities change plus a theme template, library or setting here.
3. **Read the template or library you are about to edit.** Only `solo-global` and `solo-menu` are written in `solo.libraries.yml`; every other `solo/*` library is built by solo_utilities (`src/Asset/SoloLibraryDefinitions.php`) from `fonts/solo-fonts.json`, `schemes/*.yml`, `scripts/breakpoints/breakpoints.json` and `libraries/solo-components.json` (see `libraries/README.md`). Library names are used by sub-themes: never rename one.

## Where facts live (one owner each)

| Fact | Lives in |
|---|---|
| Supported core and PHP, regions, dependencies | `solo.info.yml` and `composer.json` (`^11.3 \|\| ^12`, PHP 8.3). CI jobs in `.gitlab-ci.yml` follow them. |
| Default settings and settings schema | `config/install/solo.settings.yml`, `config/schema/solo.schema.yml` |
| Old and new CSS class and id names | `docs/CLASS-MIGRATION.md`, generated from `solo_utilities/config/map/solo-class-map.yml` in Solo Utilities. Do not edit it by hand. |
| Breakpoints | `solo.breakpoints.yml` |
| Libraries | `solo.libraries.yml` (2 static) + `libraries/README.md` (the generated set; `python3 -I scripts/solo_libraries.py --dump`) |
| Templates | `templates/` (admin, block, content, content-edit, dataset, field, form, layout, misc, navigation, user, views) |
| Starter sub-theme | `solo_subtheme/`, copied and renamed by `drush solo:subtheme` from Solo Utilities |
| Theme settings form, generated CSS, preprocess, suggestions | Solo Utilities |

## What must not change

- **Class and id names** follow the `solo-` BEM pattern listed in `docs/CLASS-MIGRATION.md`. Renaming one breaks every sub-theme that selects it. A rename is an upgrade-note entry and a change to the map in Solo Utilities.
- **Library names, region machine names and setting names.** Sites and sub-themes store and select them. Settings migrate by name from a map in Solo Utilities.
- **The tombstone classes in `src/Hook/`** and the `solo_removed_post_updates()` entry in `solo.post_update.php`. Both protect sites that update from Solo 1.
- **The Content-Security-Policy facts in the README.** A change that adds an inline script, an `on*` attribute, `eval()` or a new inline `style` attribute changes what a strict policy must allow. Update the README section in the same commit.
- **Update functions:** the docblock of a `hook_update_N()` or `hook_post_update_NAME()` is one summary line. Drupal prints the whole docblock in the `drush updatedb` table. Explanations go in `//` comments inside the function.

## Commands that check a change

Run from a Drupal project that has Solo and Solo Utilities installed.

```bash
vendor/bin/phpcs --standard=web/themes/contrib/solo/phpcs.xml.dist web/themes/contrib/solo
vendor/bin/phpstan analyse -c web/themes/contrib/solo/phpstan.neon
```

The drupal.org GitLab pipeline (`.gitlab-ci.yml`) runs the project's tests against current core, the previous minor and the next major. A pipeline that is red on the commit you release is a blocker.

## Reporting a problem

Public bugs go to the [issue queue](https://www.drupal.org/project/issues/solo). Security problems do not: see [SECURITY.md](SECURITY.md).
