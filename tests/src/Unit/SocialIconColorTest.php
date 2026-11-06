<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\solo_utilities\Security\CssValueValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Tests the social icon color rules.
 *
 * The setting is printed into a style attribute, so a bare custom property
 * name ("--solo-fb" rather than "var(--solo-fb)") makes CSS discard the whole
 * declaration and the icons render gray, with nothing logged. In Solo 1 the
 * rules lived in Drupal\solo\SocialIconColor. In Solo 2 they live in Solo
 * Utilities: CssValueValidator decides what a color value is, and
 * SettingsFormValidator::validateSocialIconColor() turns a bare name into the
 * "enter var(--name)" error. This test pins the validator half.
 *
 * Two intended differences from 1.x: 4 and 8 digit hex, rgb(), rgba(),
 * transparent and currentcolor are now accepted (the validator is shared with
 * every other color setting), and an empty value is skipped by the form
 * validator before it reaches here, so the validator answers NULL for it.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[CoversClass(CssValueValidator::class)]
#[Group('solo')]
class SocialIconColorTest extends TestCase {

  /**
   * Values that are a bare custom property name.
   *
   * @return array<string, array{string}>
   *   Test cases.
   */
  public static function bareCustomProperties(): array {
    return [
      'short name' => ['--fb'],
      'theme name' => ['--solo-fb'],
      'underscores' => ['--solo_fb_2'],
      'leading and trailing space' => ['  --solo-fb  '],
    ];
  }

  /**
   * Values that are NOT a bare custom property name.
   *
   * @return array<string, array{string}>
   *   Test cases.
   */
  public static function notBareCustomProperties(): array {
    return [
      'empty' => [''],
      'hex' => ['#1877f2'],
      'short hex' => ['#abc'],
      'already wrapped' => ['var(--solo-fb)'],
      'single dash' => ['-solo-fb'],
      'plain word' => ['red'],
    ];
  }

  /**
   * A bare custom property name is recognized, and is never a color value.
   *
   * @dataProvider bareCustomProperties
   */
  #[DataProvider('bareCustomProperties')]
  public function testBareCustomPropertyIsDetected(string $value): void {
    $validator = new CssValueValidator();
    $this->assertSame(trim($value), $validator->customPropertyName($value));
    $this->assertNull($validator->color($value), 'A bare custom property name is never an acceptable value.');
  }

  /**
   * Anything else is not mistaken for a bare custom property name.
   *
   * @dataProvider notBareCustomProperties
   */
  #[DataProvider('notBareCustomProperties')]
  public function testOtherValuesAreNotBareCustomProperties(string $value): void {
    $this->assertNull((new CssValueValidator())->customPropertyName($value));
  }

  /**
   * The values the setting accepts come back unchanged.
   */
  public function testAcceptedValues(): void {
    $validator = new CssValueValidator();
    $this->assertSame('#1877f2', $validator->color('#1877f2'));
    $this->assertSame('#ABC', $validator->color('#ABC'));
    $this->assertSame('var(--solo-fb)', $validator->color('var(--solo-fb)'));
    $this->assertSame('var( --solo-fb )', $validator->color('var( --solo-fb )'), 'Inner spacing is tolerated.');
    $this->assertSame('#1877f2', $validator->color('  #1877f2  '), 'Outer whitespace is trimmed.');
    // New in 2.0: the shared validator also accepts these.
    $this->assertSame('rgb(24, 119, 242)', $validator->color('rgb(24, 119, 242)'));
    $this->assertSame('transparent', $validator->color('TRANSPARENT'));
  }

  /**
   * The values the setting refuses.
   */
  public function testRefusedValues(): void {
    $validator = new CssValueValidator();
    $this->assertNull($validator->color(''), 'Empty is skipped by the form validator; nothing is emitted for it.');
    $this->assertNull($validator->color('--solo-fb'), 'The bug this exists for.');
    $this->assertNull($validator->color('#12345'), 'Five hex digits is not a color.');
    $this->assertNull($validator->color('red'), 'Named colors are out of scope here.');
    $this->assertNull($validator->color('var(--a); background:url(x)'), 'No trailing CSS.');
    $this->assertNull($validator->color('"><script>alert(1)</script>'), 'No markup.');
    $this->assertNull($validator->color('rgb(300, 0, 0)'), 'A channel above 255 is refused.');
    $this->assertNull($validator->color("#fff\n;x"), 'Anything after a newline is refused, not trimmed away.');
    $this->assertNull($validator->color(['#fff']), 'A non-string is refused.');
  }

}
