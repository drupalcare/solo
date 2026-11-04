<?php

/**
 * @file
 * Post update functions for the Solo theme.
 */

declare(strict_types=1);

/**
 * Implements hook_removed_post_updates().
 */
function solo_removed_post_updates(): array {
  // Solo 2 is reached from Solo 1.0.35 or later, which already ran this. The
  // settings migrator also reads every shape it could leave behind.
  return [
    'solo_post_update_schema_compliance' => '1.0.32',
  ];
}

/**
 * Installs the Solo Utilities module, which now holds all of Solo's logic.
 */
function solo_post_update_install_solo_utilities(): string {
  // On a 1.x site the module may be absent, so only the theme can install it.
  // Core marks a newly installed module's post-updates as already run, so its
  // hook_modules_installed migrates the settings of every Solo-based theme
  // here. On failure that hook re-opens the module's own post-update and
  // throws, which keeps this update red, and the next run retries through it.
  // A site that already has the module gets the migration from that
  // post-update, which runs after this one.
  if (\Drupal::moduleHandler()->moduleExists('solo_utilities')) {
    return 'Solo Utilities was already enabled; its own update migrates the Solo settings.';
  }
  \Drupal::service('module_installer')->install(['solo_utilities']);
  return 'Solo Utilities enabled and the Solo settings migrated.';
}
