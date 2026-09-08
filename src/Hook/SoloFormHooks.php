<?php

declare(strict_types=1);

namespace Drupal\solo\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form alter hooks for the Solo theme.
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
class SoloFormHooks {

  use SoloIncludeLoaderTrait;

  /**
   * Constructs the hook class and guarantees the procedural includes exist.
   */
  public function __construct() {
    $this->loadSoloIncludes();
  }

  /**
   * Implements hook_form_comment_form_alter().
   */
  #[Hook('form_comment_form_alter')]
  public function formCommentFormAlter(&$form, FormStateInterface $form_state, $form_id): void {
    solo_form_comment_form_alter($form, $form_state, $form_id);
  }

  /**
   * Implements hook_form_node_form_alter().
   */
  #[Hook('form_node_form_alter')]
  public function formNodeFormAlter(array &$form, FormStateInterface $form_state, $form_id): void {
    solo_form_node_form_alter($form, $form_state, $form_id);
  }

  /**
   * Implements hook_form_node_preview_form_select_alter().
   */
  #[Hook('form_node_preview_form_select_alter')]
  public function formNodePreviewFormSelectAlter(&$form, FormStateInterface $form_state, $form_id): void {
    solo_form_node_preview_form_select_alter($form, $form_state, $form_id);
  }

  /**
   * Implements hook_form_search_block_form_alter().
   */
  #[Hook('form_search_block_form_alter')]
  public function formSearchBlockFormAlter(&$form, FormStateInterface $form_state): void {
    solo_form_search_block_form_alter($form, $form_state);
  }

  /**
   * Implements hook_form_search_form_alter().
   */
  #[Hook('form_search_form_alter')]
  public function formSearchFormAlter(&$form, FormStateInterface $form_state): void {
    solo_form_search_form_alter($form, $form_state);
  }

  /**
   * Implements hook_form_views_exposed_form_alter().
   */
  #[Hook('form_views_exposed_form_alter')]
  public function formViewsExposedFormAlter(&$form): void {
    solo_form_views_exposed_form_alter($form);
  }

}
