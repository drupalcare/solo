<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\solo_utilities\Asset\SoloLibraryDefinitions;
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
 * Solo 2 moved its PHP, its generated libraries and its settings handling into
 * solo_utilities, which Solo 2 requires. So the hooks are checked against
 * solo_utilities' #[Hook] attributes, the libraries against the full set Drupal
 * builds (solo.libraries.yml plus SoloLibraryDefinitions), and the 1.x
 * settings keys against solo_utilities' generated config map, where each one
 * is moved, kept or dropped with a reason. Regions and @solo/ paths are still
 * Solo's own files.
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
   * Hook methods of 1.x with no 2.0 implementation of that name, and why.
   */
  private const HOOK_DECISIONS = [
    // hook_modules_disabled() was removed in Drupal 8; 1.x never ran it.
    'modulesDisabled' => 'not a Drupal hook since 8.0',
    // The same attachments, added by hook_page_attachments().
    'pageAttachmentsAlter' => 'page_attachments',
  ];

  /**
   * The solo_utilities directory, found through the class loader.
   */
  private static function utilitiesRoot(): string {
    if (!class_exists(SoloLibraryDefinitions::class)) {
      throw new \RuntimeException('solo_utilities is not installed; Solo 2 requires it (composer.json).');
    }
    $file = (string) (new \ReflectionClass(SoloLibraryDefinitions::class))->getFileName();
    return dirname($file, 3);
  }

  /**
   * Every hook solo_utilities implements, normalized for comparison.
   *
   * "form_comment_form_alter" and "preprocess_field__node__created" become
   * "formcommentformalter" and "preprocessfieldnodecreated", the 1.x method
   * names in lowercase.
   *
   * @return array<string, true>
   *   Normalized hook names.
   */
  private static function utilitiesHooks(): array {
    $hooks = [];
    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::utilitiesRoot() . '/src', \FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
      if ($file->getExtension() !== 'php') {
        continue;
      }
      preg_match_all("/#\\[Hook\\('([a-z0-9_]+)'/", (string) file_get_contents($file->getPathname()), $m);
      foreach ($m[1] as $hook) {
        $hooks[str_replace('_', '', $hook)] = TRUE;
      }
    }
    return $hooks;
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
   * A frozen 1.x hook keeps its tombstone and is implemented by solo_utilities.
   */
  #[DataProvider('hookProvider')]
  public function testHookMethodExists(string $entry): void {
    static $hooks = NULL;
    $hooks ??= self::utilitiesHooks();
    // Control: the scan reads the attributes, so it must find many.
    $this->assertGreaterThan(30, count($hooks), 'The #[Hook] scan of solo_utilities found almost nothing.');
    [$class, $method] = explode('::', $entry);
    $this->assertStringStartsWith('Drupal\\solo\\Hook\\', $class);
    // The tombstone stays, so a compiled Solo 1 container can still call it
    // until the next rebuild.
    $short = substr($class, strlen('Drupal\\solo\\Hook\\'));
    $file = self::root() . '/src/Hook/' . $short . '.php';
    $this->assertFileExists($file, "Tombstone class file for $class is gone.");
    $source = (string) file_get_contents($file);
    $this->assertMatchesRegularExpression('/^(final\s+)?class\s+' . preg_quote($short, '/') . '\b/m', $source);
    $this->assertMatchesRegularExpression('/function\s+' . preg_quote($method, '/') . '\s*\(/', $source, "Tombstone $entry is gone.");
    // The behavior: a #[Hook] of the same name in solo_utilities.
    if (isset(self::HOOK_DECISIONS[$method])) {
      $replacement = self::HOOK_DECISIONS[$method];
      if (preg_match('/^[a-z_]+$/', $replacement) === 1) {
        $this->assertArrayHasKey(str_replace('_', '', $replacement), $hooks, "$entry was replaced by hook_$replacement, which is gone.");
      }
      return;
    }
    $this->assertArrayHasKey(strtolower($method), $hooks, "$entry has no #[Hook] in solo_utilities.");
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
   * A frozen library is still defined, written or built.
   *
   * Drupal's set is solo.libraries.yml plus what SoloLibraryDefinitions
   * builds from Solo's data files (static entries win, as in the PHP merge).
   */
  #[DataProvider('libraryProvider')]
  public function testLibraryExists(string $entry): void {
    static $libraries = NULL;
    static $problems = [];
    if ($libraries === NULL) {
      $libraries = Yaml::parseFile(self::root() . '/solo.libraries.yml') + (new SoloLibraryDefinitions())->build(self::root(), $problems);
    }
    $this->assertSame([], $problems, 'Solo library data has problems.');
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
   * A frozen 1.x settings key has a decided 2.0 home.
   *
   * The config map moves it to a new path, keeps it, or drops it with a
   * reason; a moved key's top-level name must exist in Solo's schema.
   */
  #[DataProvider('schemaKeyProvider')]
  public function testSchemaKeyExists(string $entry): void {
    static $rows = NULL;
    static $schema = NULL;
    if ($rows === NULL) {
      $map = Yaml::parseFile(self::utilitiesRoot() . '/config/map/solo-config-map.yml');
      $rows = array_column($map['keys'] ?? [], NULL, 'old');
      // solo.settings is typed solo_theme_settings; core's theme_settings and
      // config_object types own features, logo, favicon and _core.
      $schema = (Yaml::parseFile(self::root() . '/config/schema/solo.schema.yml')['solo_theme_settings']['mapping'] ?? [])
        + array_fill_keys(['_core', 'features', 'logo', 'favicon', 'third_party_settings'], TRUE);
      // Control: an empty mapping would make every moved key fail for the
      // wrong reason, or a reader bug pass them all.
      self::assertGreaterThan(20, count($schema), 'solo_theme_settings has almost no keys.');
    }
    $this->assertArrayHasKey($entry, $rows, "1.x key $entry has no row in solo_utilities config/map/solo-config-map.yml.");
    $row = $rows[$entry];
    if ($row['action'] === 'drop') {
      $this->assertNotEmpty($row['reason'] ?? '', "1.x key $entry is dropped without a reason.");
      return;
    }
    $this->assertNotEmpty($row['new'] ?? '', "1.x key $entry has no 2.0 path.");
    $top = explode('.', (string) $row['new'])[0];
    $this->assertArrayHasKey($top, $schema, "1.x key $entry moves to {$row['new']}, which Solo's schema does not declare.");
  }

}
