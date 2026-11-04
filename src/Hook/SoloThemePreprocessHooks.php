<?php

declare(strict_types=1);

namespace Drupal\solo\Hook;

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
final class SoloThemePreprocessHooks {

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessHtml(&$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessPage(&$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessNode(array &$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessBreadcrumb(&$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessField(&$variables, $hook): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessFieldNodeCreated(&$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessFilterCaption(&$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessSearchResult(&$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public function preprocessComment(array &$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessBlock(&$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessMenu(&$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessMenuPrimaryMenu(&$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessMenuPrimarySidebarMenu(&$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessMenuAccount(&$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessFormElement(array &$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessFormElementLabel(array &$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public function preprocessInput(array &$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessMenuLocalTask(array &$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessFieldComment(array &$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessMaintenancePage(array &$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessViewsViewTable(array &$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessFieldset(array &$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessForm(array &$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessContainer(array &$variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function preprocessFieldMultipleValueForm(array &$variables): void {
  }

}
