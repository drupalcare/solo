<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Everything Solo 1 exposed must still exist in the current tree.
 *
 * WHY THIS EXISTS
 * Solo 2 must keep what Solo 1 sites and sub-themes rely on: hook classes and
 * methods, @solo/ template paths, regions, libraries and settings schema keys.
 * The lists in tests/fixtures/solo1-freeze/ are the union over the tags 1.0.35
 * to 1.0.39, rebuilt by scripts/generate-solo1-freeze.py. A removal must be a
 * decision: delete the line here in the same change, or this test is red.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
class Solo1FreezeTest extends TestCase {

  /**
   * The lists that exist, so a deleted fixture cannot pass silently.
   */
  private const LISTS = ['hooks', 'twig-paths', 'regions', 'libraries', 'schema-keys'];

  /**
   * The theme root.
   */
  private static function root(): string {
    return dirname(__DIR__, 3);
  }

  /**
   * Reads one list; the first tab-separated column is the entry.
   *
   * @return list<string>
   *   The entries.
   */
  private static function entries(string $list): array {
    $file = self::root() . '/tests/fixtures/solo1-freeze/' . $list . '.txt';
    $lines = is_file($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : FALSE;
    if ($lines === FALSE || $lines === []) {
      return [];
    }
    return array_map(static fn (string $line): string => explode("\t", $line)[0], $lines);
  }

  /**
   * Builds data provider rows, one per entry.
   *
   * @return array<string, array{string}>
   *   The entry rows keyed by entry.
   */
  private static function rows(string $list): array {
    $rows = [];
    foreach (self::entries($list) as $entry) {
      $rows[$entry] = [$entry];
    }
    return $rows;
  }

  /**
   * Every list is present and not empty, with its recorded minimum size.
   */
  public function testListsArePresent(): void {
    $minimum = ['hooks' => 41, 'twig-paths' => 52, 'regions' => 25, 'libraries' => 288, 'schema-keys' => 707];
    foreach (self::LISTS as $list) {
      $this->assertGreaterThanOrEqual($minimum[$list], count(self::entries($list)), "The $list list lost entries.");
    }
  }

  /**
   * Hook classes and methods.
   *
   * @return array<string, array{string}>
   *   Rows.
   */
  public static function hookProvider(): array {
    return self::rows('hooks');
  }

  /**
   * A frozen hook method is still declared in its class file.
   */
  #[DataProvider('hookProvider')]
  public function testHookMethodExists(string $entry): void {
    [$class, $method] = explode('::', $entry);
    $this->assertStringStartsWith('Drupal\\solo\\Hook\\', $class);
    $short = substr($class, strlen('Drupal\\solo\\Hook\\'));
    $file = self::root() . '/src/Hook/' . $short . '.php';
    $this->assertFileExists($file, "Hook class file for $class is gone.");
    $source = (string) file_get_contents($file);
    $this->assertMatchesRegularExpression('/^class\s+' . preg_quote($short, '/') . '\b/m', $source);
    $this->assertMatchesRegularExpression('/function\s+' . preg_quote($method, '/') . '\s*\(/', $source, "$entry is gone.");
  }

  /**
   * Frozen @solo/ paths.
   *
   * @return array<string, array{string}>
   *   Rows.
   */
  public static function twigPathProvider(): array {
    return self::rows('twig-paths');
  }

  /**
   * A frozen @solo/ path still resolves to a file.
   *
   * Core registers both the theme root and its templates directory for the
   * namespace; a path with ".." is resolved from templates/ (the status
   * message icons).
   */
  #[DataProvider('twigPathProvider')]
  public function testTwigPathResolves(string $entry): void {
    $this->assertStringStartsWith('@solo/', $entry);
    $relative = substr($entry, strlen('@solo/'));
    $root = self::root();
    $candidates = [
      $root . '/templates/' . $relative,
      $root . '/' . $relative,
    ];
    $found = FALSE;
    foreach ($candidates as $candidate) {
      if (is_file($candidate)) {
        $found = TRUE;
        break;
      }
    }
    $this->assertTrue($found, "$entry no longer resolves to a file.");
  }

  /**
   * Frozen regions.
   *
   * @return array<string, array{string}>
   *   Rows.
   */
  public static function regionProvider(): array {
    return self::rows('regions');
  }

  /**
   * A frozen region is still declared.
   */
  #[DataProvider('regionProvider')]
  public function testRegionExists(string $entry): void {
    $info = Yaml::parseFile(self::root() . '/solo.info.yml');
    $this->assertArrayHasKey($entry, $info['regions'] ?? [], "Region $entry is gone.");
  }

  /**
   * Frozen libraries.
   *
   * @return array<string, array{string}>
   *   Rows.
   */
  public static function libraryProvider(): array {
    return self::rows('libraries');
  }

  /**
   * A frozen library is still defined.
   */
  #[DataProvider('libraryProvider')]
  public function testLibraryExists(string $entry): void {
    static $libraries = NULL;
    $libraries ??= Yaml::parseFile(self::root() . '/solo.libraries.yml');
    $this->assertArrayHasKey($entry, $libraries, "Library $entry is gone.");
  }

  /**
   * Frozen settings schema keys.
   *
   * @return array<string, array{string}>
   *   Rows.
   */
  public static function schemaKeyProvider(): array {
    return self::rows('schema-keys');
  }

  /**
   * A frozen settings key is still in the schema.
   */
  #[DataProvider('schemaKeyProvider')]
  public function testSchemaKeyExists(string $entry): void {
    static $schema = NULL;
    $schema ??= Yaml::parseFile(self::root() . '/config/schema/solo.schema.yml');
    $this->assertArrayHasKey($entry, $schema['solo.settings']['mapping'] ?? [], "Schema key $entry is gone.");
  }

}
