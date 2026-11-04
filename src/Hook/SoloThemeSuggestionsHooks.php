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
final class SoloThemeSuggestionsHooks {

  /**
   * Does nothing; see the class comment.
   */
  public static function themeSuggestionsFormAlter(array &$suggestions, array $variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function themeSuggestionsBlockAlter(array &$suggestions, array $variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function themeSuggestionsMenuAlter(array &$suggestions, array $variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function themeSuggestionsPageAlter(array &$suggestions, array $variables): void {
  }

  /**
   * Does nothing; see the class comment.
   */
  public static function themeSuggestionsUserAlter(array &$suggestions, array $variables): void {
  }

}
