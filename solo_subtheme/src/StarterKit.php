<?php

declare(strict_types=1);

namespace Drupal\solo_subtheme;

use Drupal\Core\Theme\StarterKitInterface;

/**
 * Finishes the rename that core's theme generator starts.
 *
 * Core replaces the machine name, the label and their camel, Pascal and title
 * forms. The starter also spells its name in kebab case (the CSS and JS file
 * names and the library key) and as "Solo sub-theme" in prose. Core does not
 * know those spellings, so they are replaced here, and the README that
 * explains renaming by hand is removed because the rename has been done.
 */
final class StarterKit implements StarterKitInterface {

  /**
   * {@inheritdoc}
   */
  public static function postProcess(string $working_dir, string $machine_name, string $theme_name): void {
    $replacements = [
      'solo-subtheme' => str_replace('_', '-', $machine_name),
      'Solo sub-theme' => $theme_name,
    ];

    $readme = $working_dir . '/README.md';
    if (is_file($readme)) {
      unlink($readme);
    }

    $paths = [];
    $files = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($working_dir, \FilesystemIterator::SKIP_DOTS),
    );
    foreach ($files as $file) {
      if ($file instanceof \SplFileInfo && $file->isFile()) {
        $paths[] = $file->getPathname();
      }
    }

    foreach ($paths as $path) {
      $contents = file_get_contents($path);
      if ($contents === FALSE) {
        throw new \RuntimeException(sprintf('Cannot read %s.', $path));
      }
      $renamed = strtr($contents, $replacements);
      if ($renamed !== $contents) {
        file_put_contents($path, $renamed);
      }
      $basename = strtr(basename($path), $replacements);
      if ($basename !== basename($path)) {
        rename($path, dirname($path) . '/' . $basename);
      }
    }
  }

}
