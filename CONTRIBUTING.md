# Contributing to Solo

Thanks for helping. Solo is a Drupal theme used as the base of many sites, and many of those sites have a sub-theme that selects its class names, libraries and settings. A change here reaches all of them.

Read [AGENTS.md](AGENTS.md) first. It lists where each fact lives and what must not change.

## Where to work

- **Issues and merge requests:** the [Solo issue queue](https://www.drupal.org/project/issues/solo) on drupal.org. Source: <https://git.drupalcode.org/project/solo>.
- **Security problems:** do not post them in the queue. See [SECURITY.md](SECURITY.md).
- **Logic problems** (preprocess, theme suggestions, the theme settings form, generated CSS, Drush commands, the upgrade page) belong to the [Solo Utilities issue queue](https://www.drupal.org/project/issues/solo_utilities). Solo holds no PHP logic.

## Requirements for a change

- Target Drupal `^11.3 || ^12` and PHP 8.3, as declared in `solo.info.yml`.
- Follow Drupal coding standards (`Drupal` and `DrupalPractice`) for PHP, and the project's existing style for CSS, JavaScript and Twig.
- Do not add PHP logic to the theme. Add it to Solo Utilities.
- Keep every class and id on the `solo-` BEM pattern in `docs/CLASS-MIGRATION.md`. A rename needs an entry in `CHANGELOG.md` and a change to the map in Solo Utilities.
- Keep Solo free of inline JavaScript and of new inline `style` attributes; the README documents what a strict Content-Security-Policy must allow, and a change that alters it updates that section.
- Menus follow the W3C disclosure navigation pattern and focus is always visible. A change must not remove either.
- Make strings translatable. Use American English in code comments, docs and user-facing text.
- An update function's docblock is one summary line; put the explanation in `//` comments inside the function.
- If a change alters a setting, a requirement or the upgrade route, update `README.md` and `CHANGELOG.md` in the same change.

## Local checks

Run these from a Drupal project that has Solo and Solo Utilities installed:

```bash
vendor/bin/phpcs --standard=web/themes/contrib/solo/phpcs.xml.dist web/themes/contrib/solo
vendor/bin/phpstan analyse -c web/themes/contrib/solo/phpstan.neon
```

The GitLab pipeline in `.gitlab-ci.yml` runs the project's tests against current core, the previous minor and the next major. A change that adds behavior comes with a test that fails without it.

## Merge request checklist

- [ ] The change matches the issue and nothing else.
- [ ] PHPCS and PHPStan pass with the files in this repository.
- [ ] No class, id, library, region or setting name changed without a `CHANGELOG.md` entry.
- [ ] The README Content-Security-Policy section still matches the templates.
- [ ] `README.md` and `CHANGELOG.md` are updated where behavior changed.
- [ ] The pipeline is green.
