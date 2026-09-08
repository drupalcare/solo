<?php

declare(strict_types=1);

namespace Drupal\solo\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Preprocess hooks for the Solo theme.
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
class SoloPreprocessHooks {

  use SoloIncludeLoaderTrait;

  /**
   * Constructs the hook class and guarantees the procedural includes exist.
   */
  public function __construct() {
    $this->loadSoloIncludes();
  }

  /**
   * Implements hook_preprocess_block().
   */
  #[Hook('preprocess_block')]
  public function preprocessBlock(&$variables): void {
    solo_preprocess_block($variables);
  }

  /**
   * Implements hook_preprocess_breadcrumb().
   */
  #[Hook('preprocess_breadcrumb')]
  public function preprocessBreadcrumb(&$variables): void {
    solo_preprocess_breadcrumb($variables);
  }

  /**
   * Implements hook_preprocess_comment().
   */
  #[Hook('preprocess_comment')]
  public function preprocessComment(array &$variables): void {
    solo_preprocess_comment($variables);
  }

  /**
   * Implements hook_preprocess_container().
   */
  #[Hook('preprocess_container')]
  public function preprocessContainer(array &$variables): void {
    solo_preprocess_container($variables);
  }

  /**
   * Implements hook_preprocess_field__comment().
   */
  #[Hook('preprocess_field__comment')]
  public function preprocessFieldComment(array &$variables): void {
    solo_preprocess_field__comment($variables);
  }

  /**
   * Implements hook_preprocess_field__node__created().
   */
  #[Hook('preprocess_field__node__created')]
  public function preprocessFieldNodeCreated(&$variables): void {
    solo_preprocess_field__node__created($variables);
  }

  /**
   * Implements hook_preprocess_field_multiple_value_form().
   */
  #[Hook('preprocess_field_multiple_value_form')]
  public function preprocessFieldMultipleValueForm(array &$variables): void {
    solo_preprocess_field_multiple_value_form($variables);
  }

  /**
   * Implements hook_preprocess_field().
   */
  #[Hook('preprocess_field')]
  public function preprocessField(&$variables, $hook): void {
    solo_preprocess_field($variables, $hook);
  }

  /**
   * Implements hook_preprocess_fieldset().
   */
  #[Hook('preprocess_fieldset')]
  public function preprocessFieldset(array &$variables): void {
    solo_preprocess_fieldset($variables);
  }

  /**
   * Implements hook_preprocess_filter_caption().
   */
  #[Hook('preprocess_filter_caption')]
  public function preprocessFilterCaption(&$variables): void {
    solo_preprocess_filter_caption($variables);
  }

  /**
   * Implements hook_preprocess_form_element_label().
   */
  #[Hook('preprocess_form_element_label')]
  public function preprocessFormElementLabel(array &$variables): void {
    solo_preprocess_form_element_label($variables);
  }

  /**
   * Implements hook_preprocess_form_element().
   */
  #[Hook('preprocess_form_element')]
  public function preprocessFormElement(array &$variables): void {
    solo_preprocess_form_element($variables);
  }

  /**
   * Implements hook_preprocess_form().
   */
  #[Hook('preprocess_form')]
  public function preprocessForm(array &$variables): void {
    solo_preprocess_form($variables);
  }

  /**
   * Implements hook_preprocess_html().
   */
  #[Hook('preprocess_html')]
  public function preprocessHtml(&$variables): void {
    solo_preprocess_html($variables);
  }

  /**
   * Implements hook_preprocess_input().
   */
  #[Hook('preprocess_input')]
  public function preprocessInput(array &$variables): void {
    solo_preprocess_input($variables);
  }

  /**
   * Implements hook_preprocess_maintenance_page().
   */
  #[Hook('preprocess_maintenance_page')]
  public function preprocessMaintenancePage(array &$variables): void {
    solo_preprocess_maintenance_page($variables);
  }

  /**
   * Implements hook_preprocess_menu__account().
   */
  #[Hook('preprocess_menu__account')]
  public function preprocessMenuAccount(&$variables): void {
    solo_preprocess_menu__account($variables);
  }

  /**
   * Implements hook_preprocess_menu__primary_menu().
   */
  #[Hook('preprocess_menu__primary_menu')]
  public function preprocessMenuPrimaryMenu(&$variables): void {
    solo_preprocess_menu__primary_menu($variables);
  }

  /**
   * Implements hook_preprocess_menu__primary_sidebar_menu().
   */
  #[Hook('preprocess_menu__primary_sidebar_menu')]
  public function preprocessMenuPrimarySidebarMenu(&$variables): void {
    solo_preprocess_menu__primary_sidebar_menu($variables);
  }

  /**
   * Implements hook_preprocess_menu_local_task().
   */
  #[Hook('preprocess_menu_local_task')]
  public function preprocessMenuLocalTask(array &$variables): void {
    solo_preprocess_menu_local_task($variables);
  }

  /**
   * Implements hook_preprocess_menu().
   */
  #[Hook('preprocess_menu')]
  public function preprocessMenu(&$variables): void {
    solo_preprocess_menu($variables);
  }

  /**
   * Implements hook_preprocess_node().
   */
  #[Hook('preprocess_node')]
  public function preprocessNode(array &$variables): void {
    solo_preprocess_node($variables);
  }

  /**
   * Implements hook_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(&$variables): void {
    solo_preprocess_page($variables);
  }

  /**
   * Implements hook_preprocess_search_result().
   */
  #[Hook('preprocess_search_result')]
  public function preprocessSearchResult(&$variables): void {
    solo_preprocess_search_result($variables);
  }

  /**
   * Implements hook_preprocess_views_view_table().
   */
  #[Hook('preprocess_views_view_table')]
  public function preprocessViewsViewTable(array &$variables): void {
    solo_preprocess_views_view_table($variables);
  }

}
