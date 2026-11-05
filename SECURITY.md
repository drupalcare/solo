# Security policy

Solo follows the Drupal Security Team's process for handling security issues.

## Reporting a vulnerability

**Do not** report security issues in the public issue queue. Report them privately through the Drupal Security Team:

- <https://www.drupal.org/security-team/report-issue>

Please include the Solo and Solo Utilities versions, the Drupal core version, and clear steps to reproduce the problem. Background on how the team works:

- <https://www.drupal.org/security-team>
- <https://www.drupal.org/drupal-security-team/security-advisory-process-and-permissions-policy>

## Supported versions

The 2.0.x branch is the development line and requires Drupal `^11.3 || ^12` and PHP 8.3 (`solo.info.yml`). The 1.0.x branch is the line for Drupal 10. Whether a release is covered by a security advisory is decided by the Drupal Security Team on drupal.org; this file does not promise coverage.

## Where Solo accepts code from site administrators

By design, anyone who can edit Solo's theme settings can add code to every page of the site:

- **CSS Injector** emits the CSS you enter as an inline `<style>` element.
- **CSS Dynamic** and **JavaScript Dynamic** (under **Global Site Settings > Custom Code**) are saved with the settings or written to files in `sites/*/files/solo/`. With Dynamic JS enabled, Solo attaches the generated `solo-js-dynamic.js` as a same-origin `<script src>`.

Grant access to the theme settings only to roles you would trust with full administrative access.

## Content-Security-Policy

What Solo puts on a page, so a strict policy can be written for it, is documented in the README under "Content-Security-Policy": no inline JavaScript, 23 inline `style` attributes across 22 templates, one inline `<style>` element only when CSS Injector is used, and two assets attached through `html_head` that a policy must allow explicitly.

## Where the logic lives

Solo holds templates, CSS, JavaScript and settings data. Its PHP logic, including the validation of stored CSS values, paths and URLs, is in the [Solo Utilities](https://www.drupal.org/project/solo_utilities) module. A vulnerability in that code is reported the same way and fixed there.
