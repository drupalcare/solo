<?php

declare(strict_types=1);

namespace Drupal\solo\Hook;

use Drupal\Core\Form\FormStateInterface;

/**
 * Tombstone: a site's compiled container from Solo 1 names this class.
 *
 * Between a code update and the next rebuild that container still calls these
 * methods. They do nothing, take no constructor argument and carry no hook
 * attribute, so a rebuilt site never calls them. All of Solo's logic is in the
 * Solo Utilities module.
 *
 * @internal
 */
final class SoloThemeAlterHooks {

  /**
   * Does nothing; see the class comment.
   */
  public static function formNodePreviewFormSelectAlter(&$form, FormStateInterface $form_state, $form_id): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function formNodeFormAlter(array &$form, FormStateInterface $form_state, $form_id): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function themeSuggestionsNodeEditFormAlter(array &$suggestions, array $variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public function formCommentFormAlter(&$form, FormStateInterface $form_state, $form_id): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function formViewsExposedFormAlter(&$form, FormStateInterface $form_state): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public function formSearchFormAlter(&$form, FormStateInterface $form_state): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public function formSearchBlockFormAlter(&$form, FormStateInterface $form_state): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function libraryInfoAlter(array &$libraries, string $extension): void {
  }

}
