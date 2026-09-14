<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Tests that every library the theme attaches is actually declared.
 *
 * Attaching a library that does not exist is a SILENT no-op in Drupal. It is
 * not an exception, it is not a warning, and nothing reaches watchdog - the
 * asset simply never loads and the feature it powers quietly does nothing.
 *
 * Measured on sts.local 2026-09-13: _load_enable_toggleable_radios() attached
 * 'solo/solo-toggleable-radios' on every page request, and no such library had
 * existed since commit 5aa0d22 removed it in February 2025. Turning the
 * setting on produced HTTP 200, the stylesheet absent from the page, and an
 * empty watchdog. The library, its form element and its JavaScript were all
 * deleted in that commit; the loader and the CSS file were missed.
 *
 * This test reads the loaders and the library declarations as text rather than
 * booting Drupal, so it stays a unit test and runs in milliseconds.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */
#[Group('solo')]
class AttachedLibrariesExistTest extends TestCase {

  /**
   * The theme root.
   */
  protected function themePath(): string {
    return dirname(__DIR__, 3);
  }

  /**
   * Every 'solo/<name>' this theme attaches must be declared as a library.
   */
  public function testEveryAttachedLibraryIsDeclared(): void {
    $theme = $this->themePath();

    $libraries = file_get_contents($theme . '/solo.libraries.yml');
    $this->assertIsString($libraries, 'solo.libraries.yml is unreadable.');
    preg_match_all('/^([a-z0-9][a-z0-9._-]*)\s*:\s*$/m', $libraries, $m);
    $declared = array_flip($m[1]);
    $this->assertNotEmpty($declared, 'No library names parsed - the parser is wrong, not the theme.');

    $attached = [];
    foreach ($this->sourceFiles() as $file) {
      $code = (string) file_get_contents($file);
      // COMPLETE literals only. The trailing ';' is what makes this safe:
      // the theme also builds names by concatenation, for example
      // 'solo/solo-global-breakpoints-' . $g_point and 'solo/solo-gfex' .
      // substr($preloader_font, 4). Matching those would capture the prefix
      // and report a library that was never attached under that name. A
      // concatenated name cannot be resolved statically, so this test does
      // not pretend to check it.
      preg_match_all(
        "/\\[['\"]library['\"]\\]\\[\\]\\s*=\\s*'solo\\/([a-z0-9._-]+)'\\s*;/i",
        $code,
        $found
      );
      foreach ($found[1] as $name) {
        $attached[$name][] = str_replace($theme . '/', '', $file);
      }
    }
    $this->assertNotEmpty(
      $attached,
      'No literal library attachments found at all - the parser is wrong, not the theme.'
    );

    $missing = [];
    foreach ($attached as $name => $files) {
      if (!isset($declared[$name])) {
        $missing[] = sprintf("solo/%s attached in %s", $name, implode(', ', array_unique($files)));
      }
    }

    $this->assertSame([], $missing, sprintf(
      "%d attached librar%s not declared in solo.libraries.yml. Drupal ignores "
      . "these silently - the feature never loads and nothing is logged:\n  - %s",
      count($missing),
      count($missing) === 1 ? 'y is' : 'ies are',
      implode("\n  - ", $missing)
    ));
  }

  /**
   * Every CSS file a library points at must exist on disk.
   */
  public function testEveryDeclaredCssFileExists(): void {
    $theme = $this->themePath();
    $lines = file($theme . '/solo.libraries.yml', FILE_IGNORE_NEW_LINES) ?: [];

    $missing = [];
    $checked = 0;
    foreach ($lines as $n => $line) {
      // Asset keys are indented under css:/js:; a bare theme-relative path.
      if (!preg_match('/^\s{4,}([^\s:][^:]*\.(?:css|js))\s*:/', $line, $m)) {
        continue;
      }
      $path = trim($m[1]);
      // External URLs and stream wrappers are not files in this theme.
      if (str_contains($path, '://') || str_starts_with($path, '//')) {
        continue;
      }
      $checked++;
      if (!file_exists($theme . '/' . $path)) {
        $missing[] = sprintf('%s (solo.libraries.yml line %d)', $path, $n + 1);
      }
    }

    $this->assertGreaterThan(
      100,
      $checked,
      'Parsed suspiciously few asset paths - the parser is wrong, not the theme.'
    );
    $this->assertSame([], $missing, sprintf(
      "%d asset(s) declared in solo.libraries.yml do not exist:\n  - %s",
      count($missing),
      implode("\n  - ", $missing)
    ));
  }

  /**
   * No stylesheet under css/ should be unreachable.
   *
   * A CSS file that no library and no info.yml key names can never load. It is
   * dead weight in the release and, worse, it reads as live code: four such
   * files totalling 30KB were found in this theme, one of them still carrying
   * a "linear-gradient(45deg, red 0 50%, blue 50% 100%)" debug line that was
   * immediately overridden on the next line.
   *
   * Two sources count as a reference, not just libraries:
   * - solo.libraries.yml, the normal case.
   * - solo.info.yml, which names the CKEditor stylesheets under
   *   ckeditor_stylesheets / ckeditor5-stylesheets.
   * The starter subtheme is skipped: it ships its own libraries file.
   */
  public function testNoOrphanedStylesheets(): void {
    $theme = $this->themePath();
    $haystack = (string) file_get_contents($theme . '/solo.libraries.yml')
      . (string) file_get_contents($theme . '/solo.info.yml');

    $orphans = [];
    $checked = 0;
    foreach (glob($theme . '/css/**/*.css') ?: [] as $file) {
      $relative = str_replace($theme . '/', '', $file);
      $checked++;
      if (!str_contains($haystack, $relative) && !str_contains($haystack, basename($file))) {
        $orphans[] = sprintf('%s (%d bytes)', $relative, filesize($file));
      }
    }

    $this->assertGreaterThan(
      50,
      $checked,
      'Parsed suspiciously few stylesheets - the glob is wrong, not the theme.'
    );
    $this->assertSame([], $orphans, sprintf(
      "%d stylesheet(s) under css/ are named by neither solo.libraries.yml nor "
      . "solo.info.yml, so nothing can ever load them:\n  - %s",
      count($orphans),
      implode("\n  - ", $orphans)
    ));
  }

  /**
   * Returns the PHP sources that can attach a library.
   *
   * @return list<string>
   *   Absolute file paths.
   */
  protected function sourceFiles(): array {
    $theme = $this->themePath();
    $out = [];
    foreach (['includes', 'src'] as $dir) {
      $base = $theme . '/' . $dir;
      if (!is_dir($base)) {
        continue;
      }
      $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base));
      foreach ($it as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php', 'inc'], TRUE)) {
          $out[] = $file->getPathname();
        }
      }
    }
    foreach (glob($theme . '/*.theme') ?: [] as $f) {
      $out[] = $f;
    }
    sort($out);
    return $out;
  }

}
