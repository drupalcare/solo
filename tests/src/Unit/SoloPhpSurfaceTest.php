<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Solo 2 ships no PHP logic: only tombstones for Solo 1 containers.
 *
 * WHY THIS EXISTS
 * Solo 1 kept its logic in solo.theme, includes/*.inc and src/Hook classes,
 * and four guards protected that layout: solo.theme had to stay convertible
 * (#[ExtensionFileIsConverted]), hook methods had to load the includes, the
 * procedural hooks needed attribute twins, and hook classes could not be
 * fetched from the container below Drupal 11.3 (#3628961). Solo 2 moved every
 * line of that logic into Solo Utilities, so those subjects are gone.
 *
 * What remains must stay inert. A Solo 1 site's compiled container still names
 * the old hook classes until its next rebuild, so each class stays as an empty
 * tombstone. If one ever gained a #[Hook] attribute, a constructor or a body,
 * Solo would run logic of its own again, next to the copy in Solo Utilities.
 *
 * WHAT THIS ENFORCES
 * 1. No solo.theme and no includes/ folder.
 * 2. Every PHP file under src/ is a tombstone in src/Hook (the starterkit
 *    class lives in solo_subtheme/src and is not scanned).
 * 3. A tombstone is a final, @internal class with no #[Hook], no
 *    constructor, no \Drupal call and only empty methods.
 * The checker is proven on seeded sources in both directions.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
class SoloPhpSurfaceTest extends TestCase {

  /**
   * The theme root.
   */
  private static function root(): string {
    return dirname(__DIR__, 3);
  }

  /**
   * Solo 1's procedural files are gone.
   */
  public function testNoThemeFileOrIncludes(): void {
    $this->assertFileDoesNotExist(self::root() . '/solo.theme');
    $this->assertDirectoryDoesNotExist(self::root() . '/includes');
  }

  /**
   * Every class under src/ is an inert tombstone in src/Hook.
   */
  public function testEverySourceFileIsTombstone(): void {
    $src = self::root() . '/src';
    $this->assertDirectoryExists($src);
    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \FilesystemIterator::SKIP_DOTS));
    $problems = [];
    $checked = 0;
    foreach ($iterator as $file) {
      if ($file->getExtension() !== 'php') {
        continue;
      }
      $relative = substr($file->getPathname(), strlen($src) + 1);
      if (!str_starts_with($relative, 'Hook/')) {
        $problems[] = "$relative is outside src/Hook; Solo 2 keeps its logic in Solo Utilities.";
        continue;
      }
      foreach (self::tombstoneProblems((string) file_get_contents($file->getPathname())) as $problem) {
        $problems[] = "$relative: $problem";
      }
      $checked++;
    }
    // Control: the five Solo 1 hook classes are all read.
    $this->assertGreaterThanOrEqual(5, $checked, 'The scan read almost no tombstones.');
    $this->assertSame([], $problems);
  }

  /**
   * The checker passes an inert tombstone and flags each kind of live code.
   */
  public function testCheckerCatchesLiveCode(): void {
    $good = "<?php\n/**\n * Tombstone.\n *\n * @internal\n */\nfinal class A {\n\n  /**\n   * Nothing.\n   */\n  public function b(array &\$x): void {\n  }\n\n}\n";
    $this->assertSame([], self::tombstoneProblems($good));

    $this->assertNotSame([], self::tombstoneProblems(str_replace('final class', 'class', $good)), 'Not final.');
    $this->assertNotSame([], self::tombstoneProblems(str_replace(' * @internal', ' * @api', $good)), 'Not internal.');
    $this->assertNotSame([], self::tombstoneProblems(str_replace('  public function b', "  #[Hook('page_attachments')]\n  public function b", $good)), 'A hook attribute.');
    $this->assertNotSame([], self::tombstoneProblems(str_replace("void {\n  }", "void {\n    \$x[] = 1;\n  }", $good)), 'A method body.');
    $this->assertNotSame([], self::tombstoneProblems(str_replace("void {\n  }", "void {\n  }\n\n  public function __construct() {\n  }", $good)), 'A constructor.');
    $this->assertNotSame([], self::tombstoneProblems(str_replace("void {\n  }", "void {\n    \\Drupal::service('x');\n  }", $good)), 'A container call.');
  }

  /**
   * Lists why a source is not an inert tombstone.
   *
   * @return list<string>
   *   Problems; empty when the source is a tombstone.
   */
  private static function tombstoneProblems(string $source): array {
    $problems = [];
    if (preg_match('/^final\s+class\s+\w+/m', $source) !== 1) {
      $problems[] = 'not a final class';
    }
    if (!str_contains($source, '@internal')) {
      $problems[] = 'not marked @internal';
    }
    if (preg_match('/#\[\s*(\\\\?Drupal\\\\Core\\\\Hook\\\\Attribute\\\\)?(Legacy)?Hook\b/', $source) === 1) {
      $problems[] = 'carries a hook attribute';
    }
    if (preg_match('/function\s+__construct\s*\(/', $source) === 1) {
      $problems[] = 'has a constructor';
    }
    if (str_contains($source, '\\Drupal::')) {
      $problems[] = 'calls \\Drupal::';
    }
    // Every method body is empty: "{" then only whitespace then "}".
    preg_match_all('/function\s+\w+\s*\([^)]*\)\s*(?::\s*[\w\\\\?|]+)?\s*\{(.*?)\n  \}/s', $source, $bodies);
    foreach ($bodies[1] as $body) {
      if (trim($body) !== '') {
        $problems[] = 'a method has a body';
        break;
      }
    }
    return $problems;
  }

}
