<?php

declare(strict_types=1);

namespace Drupal\solo\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Theme suggestion hooks for the Solo theme.
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
class SoloSuggestionsHooks {

  use SoloIncludeLoaderTrait;

  /**
   * Constructs the hook class and guarantees the procedural includes exist.
   */
  public function __construct() {
    $this->loadSoloIncludes();
  }

  /**
   * Implements hook_theme_suggestions_block_alter().
   */
  #[Hook('theme_suggestions_block_alter')]
  public function themeSuggestionsBlockAlter(array &$suggestions, array $variables): void {
    solo_theme_suggestions_block_alter($suggestions, $variables);
  }

  /**
   * Implements hook_theme_suggestions_form_alter().
   */
  #[Hook('theme_suggestions_form_alter')]
  public function themeSuggestionsFormAlter(array &$suggestions, array $variables): void {
    solo_theme_suggestions_form_alter($suggestions, $variables);
  }

  /**
   * Implements hook_theme_suggestions_menu_alter().
   */
  #[Hook('theme_suggestions_menu_alter')]
  public function themeSuggestionsMenuAlter(array &$suggestions, array $variables): void {
    solo_theme_suggestions_menu_alter($suggestions, $variables);
  }

  /**
   * Implements hook_theme_suggestions_node_edit_form_alter().
   */
  #[Hook('theme_suggestions_node_edit_form_alter')]
  public function themeSuggestionsNodeEditFormAlter(array &$suggestions, array $variables): void {
    solo_theme_suggestions_node_edit_form_alter($suggestions, $variables);
  }

  /**
   * Implements hook_theme_suggestions_page_alter().
   */
  #[Hook('theme_suggestions_page_alter')]
  public function themeSuggestionsPageAlter(array &$suggestions, array $variables): void {
    solo_theme_suggestions_page_alter($suggestions, $variables);
  }

  /**
   * Implements hook_theme_suggestions_user_alter().
   */
  #[Hook('theme_suggestions_user_alter')]
  public function themeSuggestionsUserAlter(array &$suggestions, array $variables): void {
    solo_theme_suggestions_user_alter($suggestions, $variables);
  }

}
