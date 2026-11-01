<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/SoloCharacterizationBase.php';

/**
 * Records what Solo's pure helpers return today (conversion rule 1).
 *
 * Covers: _solo_sanitize_hex_for_css, _solo_minify_inline_css,
 * _solo_ensure_class_array, _solo_hex_to_rgb, _get_which_main_col,
 * _create_region_layout_radio_two, _set_site_name, _maintenance_library,
 * _set_logo_alt, _process_system_menu_block, _process_system_branding_block.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * Each test runs in its own process: other Solo tests may already have loaded
 * includes/ from another path (a site's copy), and a function cannot be
 * declared twice.
 *
 * @group solo
 */
#[Group('solo')]
#[RunTestsInSeparateProcesses]
class PureUtilitiesCharacterizationTest extends SoloCharacterizationBase {

  /**
   * Hex values and what the sanitizer answers.
   *
   * @return array<string, array{mixed, string}>
   *   Input and output.
   */
  public static function hexProvider(): array {
    return [
      'valid lower' => ['#aabbcc', '#aabbcc'],
      'valid upper kept as is' => ['#AABBCC', '#AABBCC'],
      'surrounding whitespace trimmed' => ["  #aabbcc\n", '#aabbcc'],
      'NULL' => [NULL, ''],
      'empty string' => ['', ''],
      'three digits refused' => ['#abc', ''],
      'eight digits refused' => ['#aabbccdd', ''],
      'no hash refused' => ['aabbcc', ''],
      'non hex digit refused' => ['#aabbcg', ''],
      'declaration injection refused' => ['#aabbcc;background:url(x)', ''],
      'brace injection refused' => ['#aabbcc}body{', ''],
      'integer refused' => [123456, ''],
      'zero string refused' => ['0', ''],
    ];
  }

  /**
   * The sanitizer accepts only a six-digit hex colour.
   */
  #[DataProvider('hexProvider')]
  public function testSanitizeHex(mixed $input, string $expected): void {
    $this->assertSame($expected, _solo_sanitize_hex_for_css($input));
  }

  /**
   * CSS minifier input and output, including a quirk.
   *
   * @return array<string, array{string, string}>
   *   Input and output.
   */
  public static function cssProvider(): array {
    return [
      'whitespace around braces and semicolons' => ["a { color: red ; }", 'a{color:red;}'],
      'comment removed' => ["/* x */ a{b:c}", 'a{b:c}'],
      'quoted string kept intact' => ['a::before{content:"  a  /* k */ "}', 'a::before{content:"  a  /* k */ "}'],
      'single quoted string kept' => ["a{content:'a   b'}", "a{content:'a   b'}"],
      'charset removed' => ['@charset "UTF-8";a{b:c}', 'a{b:c}'],
      'descendant combinator keeps one space' => ["a    b {c:d}", 'a    b{c:d}'],
      // Runs of spaces INSIDE a selector are not collapsed (recorded).
      'oddity: space before a colon survives' => ['a{color : red}', 'a{color :red}'],
      'newline between rules' => ["a{b:c}\n\nd{e:f}", 'a{b:c}d{e:f}'],
      'empty' => ['', ''],
      'spaces before closing paren' => ['a{b:calc(1px + 2px )}', 'a{b:calc(1px + 2px)}'],
    ];
  }

  /**
   * The inline CSS minifier output is recorded as it is.
   */
  #[DataProvider('cssProvider')]
  public function testMinifyInlineCss(string $input, string $expected): void {
    $this->assertSame($expected, _solo_minify_inline_css($input));
  }

  /**
   * A missing class key becomes an empty array.
   */
  public function testEnsureClassArrayCreatesMissingKey(): void {
    $variables = [];
    _solo_ensure_class_array($variables);
    $this->assertSame(['attributes' => ['class' => []]], $variables);
  }

  /**
   * A class string is wrapped in an array, a list is left alone.
   */
  public function testEnsureClassArrayWrapsStringAndKeepsArray(): void {
    $variables = ['attributes' => ['class' => 'foo bar']];
    _solo_ensure_class_array($variables);
    $this->assertSame(['foo bar'], $variables['attributes']['class']);
    _solo_ensure_class_array($variables);
    $this->assertSame(['foo bar'], $variables['attributes']['class']);
  }

  /**
   * A dotted path creates the nested class array, '#' trimmed from each part.
   */
  public function testEnsureClassArrayNestedPath(): void {
    $variables = [];
    _solo_ensure_class_array($variables, 'label.#attributes');
    $this->assertSame(['label' => ['attributes' => ['class' => []]]], $variables);

    $variables = ['label' => ['attributes' => ['class' => 'x']]];
    _solo_ensure_class_array($variables, 'label.#attributes');
    $this->assertSame(['x'], $variables['label']['attributes']['class']);
  }

  /**
   * ODDITY: a path with no dot that is not 'attributes' is ignored.
   */
  public function testEnsureClassArrayDotlessCustomPathIsNoOp(): void {
    $variables = [];
    _solo_ensure_class_array($variables, 'title_attributes');
    $this->assertSame([], $variables);
  }

  /**
   * Hex to RGB, including the short and empty input quirks.
   */
  public function testHexToRgb(): void {
    $orange = ['r' => 255, 'g' => 128, 'b' => 0];
    $this->assertSame($orange, _solo_hex_to_rgb('#ff8000'));
    $this->assertSame($orange, _solo_hex_to_rgb('ff8000'));
    // ODDITY: a three digit value is not expanded; it is read byte by byte.
    $this->assertSame(['r' => 255, 'g' => 15, 'b' => 0], _solo_hex_to_rgb('#fff'));
    $this->assertSame(['r' => 0, 'g' => 0, 'b' => 0], _solo_hex_to_rgb(''));
  }

  /**
   * The default main column depends on which sidebars hold content.
   */
  public function testWhichMainCol(): void {
    $this->assertSame('solo-col-2-1', _get_which_main_col(NULL));
    $this->assertSame('solo-col-2-1', _get_which_main_col(['page' => ['sidebar_second' => ['x']]]));
    $this->assertSame('solo-col-1-2', _get_which_main_col(['page' => ['sidebar_first' => ['x']]]));
    $both = ['page' => ['sidebar_first' => ['x'], 'sidebar_second' => ['y']]];
    $this->assertSame('solo-col-1-1', _get_which_main_col($both));
  }

  /**
   * The two column select: default, stored override and the NULL variables.
   */
  public function testCreateRegionLayoutRadioTwo(): void {
    // The section files call this with $variables never set (NULL).
    $variables = NULL;
    $main = _create_region_layout_radio_two('main', $variables);
    $this->assertSame('select', $main['#type']);
    $this->assertSame('solo-col-2-1', $main['#default_value']);
    $this->assertSame([
      'solo-col-1-1', 'solo-col-2-1', 'solo-col-3-1', 'solo-col-4-1',
      'solo-col-1-2', 'solo-col-1-3', 'solo-col-1-4',
    ], array_keys($main['#options']));

    $top = _create_region_layout_radio_two('top', $variables);
    $this->assertSame('solo-col-1-1', $top['#default_value']);

    $this->settings['top_2col'] = 'solo-col-3-1';
    $this->assertSame('solo-col-3-1', _create_region_layout_radio_two('top', $variables)['#default_value']);

    // ODDITY: only NULL falls back; an empty stored string wins.
    $this->settings['top_2col'] = '';
    $this->assertSame('', _create_region_layout_radio_two('top', $variables)['#default_value']);
  }

  /**
   * The title text of the select is a placeholder-built translatable string.
   */
  public function testCreateRegionLayoutRadioTwoTitle(): void {
    $variables = NULL;
    $title = _create_region_layout_radio_two('footer', $variables)['#title'];
    $this->assertSame("The two columns' settings for @region region", $title->getUntranslatedString());
    $this->assertSame(['@region' => 'footer'], $title->getArguments());
  }

  /**
   * The site name is blanked unless the database is active.
   */
  public function testSetSiteName(): void {
    $variables = ['site_name' => 'Shop'];
    _set_site_name($variables);
    $this->assertSame('', $variables['site_name']);

    $variables = ['site_name' => 'Shop', 'db_is_active' => FALSE];
    _set_site_name($variables);
    $this->assertSame('', $variables['site_name']);

    $variables = ['site_name' => 'Shop', 'db_is_active' => TRUE];
    _set_site_name($variables);
    $this->assertSame('Shop', $variables['site_name']);

    // ODDITY: creates the key when it was absent.
    $variables = [];
    _set_site_name($variables);
    $this->assertSame(['site_name' => ''], $variables);
  }

  /**
   * The maintenance library is appended, never replacing earlier ones.
   */
  public function testMaintenanceLibrary(): void {
    $variables = ['#attached' => ['library' => ['core/drupal']]];
    _maintenance_library($variables);
    $this->assertSame(['core/drupal', 'solo/templates/layout/maintenance_page'], $variables['#attached']['library']);
  }

  /**
   * The logo alt text falls back to 'Home' on any empty value.
   */
  public function testSetLogoAlt(): void {
    $variables = [];
    _set_logo_alt($variables);
    $this->assertSame('Home', $variables['logo_alt']);

    $this->settings['header_logo_image_alt'] = 'Acme';
    _set_logo_alt($variables);
    $this->assertSame('Acme', $variables['logo_alt']);

    // ODDITY: the string '0' is falsy, so it also becomes 'Home'.
    $this->settings['header_logo_image_alt'] = '0';
    _set_logo_alt($variables);
    $this->assertSame('Home', $variables['logo_alt']);
  }

  /**
   * Only system_menu_block blocks get the region attribute.
   */
  public function testProcessSystemMenuBlock(): void {
    $block = new class() {

      /**
       * Region stub.
       */
      public function getRegion(): string {
        return 'header';
      }

    };
    $variables = ['base_plugin_id' => 'system_menu_block'];
    _process_system_menu_block($variables, $block);
    $this->assertSame('header', $variables['content']['#attributes']['region']);

    $variables = ['base_plugin_id' => 'other'];
    _process_system_menu_block($variables, $block);
    $this->assertSame(['base_plugin_id' => 'other'], $variables);
  }

  /**
   * ODDITY: the branding block processor never changes anything.
   */
  public function testProcessSystemBrandingBlockDoesNothing(): void {
    $variables = ['base_plugin_id' => 'system_branding_block', 'x' => 1];
    _process_system_branding_block($variables);
    $this->assertSame(['base_plugin_id' => 'system_branding_block', 'x' => 1], $variables);
  }

}
