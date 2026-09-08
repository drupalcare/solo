<?php

declare(strict_types=1);

namespace Drupal\solo\Hook;

/**
 * Loads Solo's procedural include files for the hook classes that delegate.
 *
 * The solo.theme file require_once's these six files, and Drupal does still
 * load a theme's .theme file. This trait does not rely on that: a hook class
 * is an autowired service and may be constructed where the theme file
 * has not been included, in which case delegating to a procedural function
 * would be a fatal "call to undefined function". require_once is a no-op once
 * a file is loaded, so calling this from every hook class constructor costs
 * nothing and removes the ordering assumption.
 *
 * @internal
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */
trait SoloIncludeLoaderTrait {

  /**
   * The include files carrying Solo's procedural hook implementations.
   */
  private const SOLO_INCLUDES = [
    '_theme_settings_helper_functions.inc',
    '_solo_preloader.inc',
    '_solo_theme_preprocess.inc',
    '_solo_theme_suggestions.inc',
    '_solo_theme_alter.inc',
    '_solo_theme_misc.inc',
  ];

  /**
   * Includes every procedural file the delegating hook methods depend on.
   */
  protected function loadSoloIncludes(): void {
    $directory = dirname(__DIR__, 2) . '/includes/';
    foreach (self::SOLO_INCLUDES as $file) {
      require_once $directory . $file;
    }
  }

}
