<?php

declare(strict_types=1);

namespace Drupal\solo\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Library, page attachment and module lifecycle hooks for Solo.
 *
 * WHY THESE CLASSES EXIST. Drupal 12 discovers a theme's hooks in exactly two
 * places: a class in the Drupal\solo\Hook namespace carrying #[Hook]
 * attributes, and procedural functions in solo.theme / theme-settings.php.
 * Solo declared 0 functions in solo.theme and 40 hook functions across
 * includes/*.inc, which are require_once'd but never scanned — so 38 of the 40
 * were dead and the site served HTTP 500. Measured 2026-09-07, fixed
 * 2026-09-08.
 *
 * Each method delegates to the procedural function that already implements it.
 * That is deliberate: this commit changes how the hooks are DISCOVERED, and
 * changes no behaviour. Moving the bodies in is a separate, reviewable step.
 *
 * @see \Drupal\solo\Hook\SoloIncludeLoaderTrait
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */
class SoloMiscHooks {

  use SoloIncludeLoaderTrait;

  /**
   * Constructs the hook class and guarantees the procedural includes exist.
   */
  public function __construct() {
    $this->loadSoloIncludes();
  }

  /**
   * Implements hook_library_info_alter().
   */
  #[Hook('library_info_alter')]
  public function libraryInfoAlter(array &$libraries, string $extension): void {
    solo_library_info_alter($libraries, $extension);
  }

  /**
   * Implements hook_modules_disabled().
   */
  #[Hook('modules_disabled')]
  public function modulesDisabled(array $modules): void {
    solo_modules_disabled($modules);
  }

  /**
   * Implements hook_page_attachments_alter().
   */
  #[Hook('page_attachments_alter')]
  public function pageAttachmentsAlter(array &$attachments): void {
    solo_page_attachments_alter($attachments);
  }

}
