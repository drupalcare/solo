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
final class SoloThemeSettingsHooks {

  /**
   * Does nothing; see the class comment.
   */
  public function formSystemThemeSettingsAlter(&$form, FormStateInterface $form_state): void {
  }

}
