<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * The logo path check must refuse script and data URLs.
 *
 * WHY THIS EXISTS
 * _solo_is_safe_logo_path() accepted any string with no slash as "a simple
 * filename". "javascript:x" has no slash, so it passed and was later printed
 * as a logo URL. A colon now disqualifies a bare name.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
class LogoPathValidatorTest extends TestCase {

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    require_once dirname(__DIR__, 3) . '/includes/_solo_theme_settings.inc';
  }

  /**
   * Paths and whether the check should accept each.
   *
   * @return array<string, array{string, bool}>
   *   The path and the expected answer.
   */
  public static function pathProvider(): array {
    return [
      'empty' => ['', TRUE],
      'bare filename' => ['logo.svg', TRUE],
      'root relative' => ['/sites/default/files/logo.svg', TRUE],
      'public stream' => ['public://logo.svg', TRUE],
      'theme relative' => ['themes/contrib/solo/logo.svg', TRUE],
      'https url' => ['https://example.com/logo.svg', TRUE],
      'http url' => ['http://example.com/logo.svg', TRUE],
      'javascript scheme, no slash' => ['javascript:x', FALSE],
      'javascript call' => ['javascript:alert(1)', FALSE],
      'javascript upper case' => ['JavaScript:x', FALSE],
      'javascript padded' => ['  javascript:x', FALSE],
      'data scheme' => ['data:text/html,x', FALSE],
      'vbscript' => ['vbscript:x', FALSE],
      'ftp url' => ['ftp://example.com/logo.svg', FALSE],
      'private stream' => ['private://logo.svg', FALSE],
      'javascript with tab' => ["java\tscript:x", FALSE],
    ];
  }

  /**
   * Each path gets the expected answer.
   *
   * The docblock tag is for PHPUnit 9 (Drupal 10), which does not read the
   * attribute.
   *
   * @dataProvider pathProvider
   */
  #[DataProvider('pathProvider')]
  public function testPath(string $path, bool $expected): void {
    $this->assertSame($expected, _solo_is_safe_logo_path($path));
  }

}
