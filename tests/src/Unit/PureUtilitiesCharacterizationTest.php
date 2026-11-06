<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Extension\ThemeSettingsProvider;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\block\BlockInterface;
use Drupal\solo_utilities\Context\RequestContext;
use Drupal\solo_utilities\Css\ThemeCssBuilder;
use Drupal\solo_utilities\Feature\BlockBranding;
use Drupal\solo_utilities\Feature\MaintenancePage;
use Drupal\solo_utilities\Form\SettingsElementFactory;
use Drupal\solo_utilities\Form\SettingsOptions;
use Drupal\solo_utilities\Layout\LayoutResolver;
use Drupal\solo_utilities\Render\AttributeHelper;
use Drupal\solo_utilities\Security\CssValueValidator;
use Drupal\solo_utilities\Settings\BoundThemeSettings;
use Drupal\solo_utilities\Settings\SchemaBounds;
use Drupal\solo_utilities\Settings\SettingsCatalog;
use Drupal\solo_utilities\Theme\SoloThemeDetectionInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/SoloCharacterizationBase.php';

/**
 * Records what Solo's pure helpers returned in 1.x, against the 2.0 classes.
 *
 * Each case ran against a procedural helper in Solo 1 (_solo_sanitize_hex_for_css,
 * _solo_minify_inline_css, _solo_ensure_class_array, _solo_hex_to_rgb,
 * _get_which_main_col, _create_region_layout_radio_two, _set_site_name,
 * _set_logo_alt, _process_system_menu_block, _process_system_branding_block).
 * Solo 2 holds that logic in Solo Utilities classes. A case whose 2.0 answer
 * differs on purpose carries a `//` comment quoting the reason.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
class PureUtilitiesCharacterizationTest extends SoloCharacterizationBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // The select factory builds translatable strings through \Drupal::.
    $this->container->set('string_translation', $this->createStub(TranslationInterface::class));
  }

  /**
   * Builds the settings object the 2.0 classes read, backed by $this->settings.
   */
  private function bound(): BoundThemeSettings {
    $catalog = (new \ReflectionClass(SettingsCatalog::class))->newInstanceWithoutConstructor();
    // No shipped defaults: only the stored values count.
    (new \ReflectionProperty(SettingsCatalog::class, 'defaults'))->setValue($catalog, []);
    return new BoundThemeSettings($this->container->get(ThemeSettingsProvider::class), 'solo', $catalog);
  }

  /**
   * Hex values and what the validator answers (NULL becomes '').
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
      // CHANGED ON PURPOSE: CssValueValidator::hexColor() docblock: "A hex color of 3, 4, 6 or 8 digits.".
      'three digits accepted in 2.0' => ['#abc', '#abc'],
      // CHANGED ON PURPOSE: same docblock, 8 digits are accepted.
      'eight digits accepted in 2.0' => ['#aabbccdd', '#aabbccdd'],
      'no hash refused' => ['aabbcc', ''],
      'non hex digit refused' => ['#aabbcg', ''],
      'declaration injection refused' => ['#aabbcc;background:url(x)', ''],
      'brace injection refused' => ['#aabbcc}body{', ''],
      'integer refused' => [123456, ''],
      'zero string refused' => ['0', ''],
    ];
  }

  /**
   * The validator accepts only a hex color.
   */
  #[DataProvider('hexProvider')]
  public function testSanitizeHex(mixed $input, string $expected): void {
    $this->assertSame($expected, (new CssValueValidator())->hexColor($input) ?? '');
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
    // minify() uses no service, so the builder is made without its arguments.
    $builder = (new \ReflectionClass(ThemeCssBuilder::class))->newInstanceWithoutConstructor();
    $this->assertSame($expected, $builder->minify($input));
  }

  /**
   * A missing class key becomes an empty array.
   */
  public function testEnsureClassArrayCreatesMissingKey(): void {
    $variables = [];
    (new AttributeHelper())->ensureClassArray($variables);
    $this->assertSame(['attributes' => ['class' => []]], $variables);
  }

  /**
   * A class string becomes a list, a list is left alone.
   */
  public function testEnsureClassArrayWrapsStringAndKeepsArray(): void {
    $helper = new AttributeHelper();
    $variables = ['attributes' => ['class' => 'foo bar']];
    $helper->ensureClassArray($variables);
    // CHANGED ON PURPOSE: AttributeHelper::classList() docblock: "Normalizes
    // any stored class value to a list of single class names." 1.x kept
    // ['foo bar'].
    $this->assertSame(['foo', 'bar'], $variables['attributes']['class']);
    $helper->ensureClassArray($variables);
    $this->assertSame(['foo', 'bar'], $variables['attributes']['class']);
  }

  /**
   * A nested element is reached by passing that element and its key.
   */
  public function testEnsureClassArrayNestedPath(): void {
    $helper = new AttributeHelper();
    // CHANGED ON PURPOSE: AttributeHelper docblock: "To reach a nested element,
    // pass that element: addClasses($variables['label'], 'x', '#attributes')".
    // The dotted path 'label.#attributes' of 1.x is gone and the key keeps its
    // '#'.
    $variables = ['label' => []];
    $helper->ensureClassArray($variables['label'], '#attributes');
    $this->assertSame(['label' => ['#attributes' => ['class' => []]]], $variables);

    $variables = ['label' => ['#attributes' => ['class' => 'x']]];
    $helper->ensureClassArray($variables['label'], '#attributes');
    $this->assertSame(['x'], $variables['label']['#attributes']['class']);
  }

  /**
   * A custom attributes key is now honored.
   */
  public function testEnsureClassArrayCustomKey(): void {
    $variables = [];
    // CHANGED ON PURPOSE: ensureClassArray() docblock: "$key The key of the
    // attributes under $parent". 1.x ignored a dotless path other than
    // 'attributes'.
    (new AttributeHelper())->ensureClassArray($variables, 'title_attributes');
    $this->assertSame(['title_attributes' => ['class' => []]], $variables);
  }

  /**
   * Hex to RGB. The result is a string now, and a short hex is expanded.
   */
  public function testHexToRgb(): void {
    $validator = new CssValueValidator();
    // CHANGED ON PURPOSE: rgbTriplet() returns an "r, g, b" string, where 1.x
    // returned an array; every CssValueValidator method "returns the safe form
    // of the value, or NULL" (class docblock).
    $this->assertSame('255, 128, 0', $validator->rgbTriplet('#ff8000'));
    // CHANGED ON PURPOSE: rgbTriplet() docblock: "1.x turned #fff into
    // 255,15,0 ... A short hex is expanded first here.".
    $this->assertSame('255, 255, 255', $validator->rgbTriplet('#fff'));
    // CHANGED ON PURPOSE: class docblock "Every method returns the safe form of
    // the value, or NULL"; 1.x answered 0,0,0 for an empty value.
    $this->assertNull($validator->rgbTriplet(''));
  }

  /**
   * A hex without its '#' was read by 1.x.
   */
  public function testHexToRgbWithoutHash(): void {
    // 1.x read 'ff8000' as 255, 128, 0; rgbTriplet() accepts it again.
    $this->assertSame('255, 128, 0', (new CssValueValidator())->rgbTriplet('ff8000'));
  }

  /**
   * The default main column depends on which sidebars hold content.
   */
  public function testWhichMainCol(): void {
    $resolver = new LayoutResolver($this->createStub(EntityTypeManagerInterface::class));
    $settings = $this->bound();
    // CHANGED ON PURPOSE: LayoutResolver::columnLayout() docblock: "The class,
    // or NULL for a group with nothing in it." 1.x answered solo-col-2-1 for
    // no page; that default now lives in SettingsElementFactory (see the
    // radio test below).
    $this->assertNull($resolver->columnLayout($settings, 'main', [], NULL));
    // The content region is always filled on a real page; 1.x assumed it.
    $second_only = ['content' => ['c'], 'sidebar_second' => ['x']];
    $this->assertSame('solo-col-2-1', $resolver->columnLayout($settings, 'main', $second_only, NULL));
    $first_only = ['content' => ['c'], 'sidebar_first' => ['x']];
    $this->assertSame('solo-col-1-2', $resolver->columnLayout($settings, 'main', $first_only, NULL));
    // Two sidebars and no content region: the two-column default, as in 1.x.
    $both = ['sidebar_first' => ['x'], 'sidebar_second' => ['y']];
    $this->assertSame('solo-col-1-1', $resolver->columnLayout($settings, 'main', $both, NULL));
  }

  /**
   * The select factory under test, built the way the container builds it.
   */
  private function elements(): SettingsElementFactory {
    $options = new SettingsOptions(
      $this->createStub(EntityTypeManagerInterface::class),
      $this->createStub(ThemeExtensionList::class),
      '',
      $this->createStub(SoloThemeDetectionInterface::class),
    );
    $bounds = (new \ReflectionClass(SchemaBounds::class))->newInstanceWithoutConstructor();
    return new SettingsElementFactory($options, $bounds);
  }

  /**
   * The two column select: default, stored override and the NULL variables.
   */
  public function testCreateRegionLayoutRadioTwo(): void {
    $elements = $this->elements();
    $main = $elements->regionColumns($this->bound(), 'main', 2);
    $this->assertSame('select', $main['#type']);
    $this->assertSame('solo-col-2-1', $main['#default_value']);
    $this->assertSame([
      'solo-col-1-1', 'solo-col-2-1', 'solo-col-3-1', 'solo-col-4-1',
      'solo-col-1-2', 'solo-col-1-3', 'solo-col-1-4',
    ], array_keys($main['#options']));

    $top = $elements->regionColumns($this->bound(), 'top', 2);
    $this->assertSame('solo-col-1-1', $top['#default_value']);

    // CHANGED ON PURPOSE: the setting is stored as region_groups.top.columns_2;
    // 1.x called it top_2col (CHANGELOG: "Settings are migrated from the Solo 1
    // names to the Solo 2 names").
    $this->settings['region_groups.top.columns_2'] = 'solo-col-3-1';
    $this->assertSame('solo-col-3-1', $elements->regionColumns($this->bound(), 'top', 2)['#default_value']);

    // ODDITY: only NULL falls back; an empty stored string wins.
    $this->settings['region_groups.top.columns_2'] = '';
    $this->assertSame('', $elements->regionColumns($this->bound(), 'top', 2)['#default_value']);
  }

  /**
   * The title text of the select is a placeholder-built translatable string.
   */
  public function testCreateRegionLayoutRadioTwoTitle(): void {
    $title = $this->elements()->regionColumns($this->bound(), 'footer', 2)['#title'];
    $this->assertSame("The two columns' settings for @region region", $title->getUntranslatedString());
    $this->assertSame(['@region' => 'footer'], $title->getArguments());
  }

  /**
   * The site name is blanked unless the database is active.
   */
  public function testSetSiteName(): void {
    $page = new MaintenancePage();
    $variables = ['site_name' => 'Shop'];
    $page->preprocess($variables);
    $this->assertSame('', $variables['site_name']);

    $variables = ['site_name' => 'Shop', 'db_is_active' => FALSE];
    $page->preprocess($variables);
    $this->assertSame('', $variables['site_name']);

    $variables = ['site_name' => 'Shop', 'db_is_active' => TRUE];
    $page->preprocess($variables);
    $this->assertSame('Shop', $variables['site_name']);

    // ODDITY: creates the key when it was absent.
    $variables = [];
    $page->preprocess($variables);
    $this->assertSame(['site_name' => ''], $variables);
  }

  /**
   * Builds the block preprocessor with a block of the given region.
   */
  private function branding(): BlockBranding {
    $block = $this->createStub(BlockInterface::class);
    $block->method('getRegion')->willReturn('header');
    $storage = $this->createStub(EntityStorageInterface::class);
    $storage->method('load')->willReturn($block);
    $manager = $this->createStub(EntityTypeManagerInterface::class);
    $manager->method('getStorage')->willReturn($storage);
    // The request context is read only for the page title block.
    $context = (new \ReflectionClass(RequestContext::class))->newInstanceWithoutConstructor();
    return new BlockBranding($context, $this->createStub(SoloThemeDetectionInterface::class), $manager);
  }

  /**
   * The logo alt text falls back to 'Home' on any empty value.
   */
  public function testSetLogoAlt(): void {
    $branding = $this->branding();
    $variables = ['elements' => ['#id' => 'solo_branding']];
    $branding->preprocess($variables, $this->bound());
    $this->assertSame('Home', $variables['logo_alt']);

    $this->settings['header.logo_alt'] = 'Acme';
    $branding->preprocess($variables, $this->bound());
    $this->assertSame('Acme', $variables['logo_alt']);

    // ODDITY: the string '0' is falsy, so it also becomes 'Home'.
    $this->settings['header.logo_alt'] = '0';
    $branding->preprocess($variables, $this->bound());
    $this->assertSame('Home', $variables['logo_alt']);

    // CHANGED ON PURPOSE: BlockBranding::preprocess() comment: "A block placed
    // by Layout Builder has no id". With no block entity the alt is not set;
    // 1.x set it on every call.
    $variables = [];
    $branding->preprocess($variables, $this->bound());
    $this->assertArrayNotHasKey('logo_alt', $variables);
  }

  /**
   * Only system_menu_block blocks get the region attribute.
   */
  public function testProcessSystemMenuBlock(): void {
    $branding = $this->branding();
    $variables = ['base_plugin_id' => 'system_menu_block', 'elements' => ['#id' => 'main_menu']];
    $branding->preprocess($variables, $this->bound());
    $this->assertSame('header', $variables['content']['#attributes']['region']);

    // CHANGED ON PURPOSE: the block preprocess is one class now ("Prepares
    // block templates: logo, logo text, menu region and page title flag"), so
    // another block gets the logo variables; it still gets no region.
    $variables = ['base_plugin_id' => 'other', 'elements' => ['#id' => 'other_block']];
    $branding->preprocess($variables, $this->bound());
    $this->assertArrayNotHasKey('content', $variables);
  }

  /**
   * ODDITY: a branding block gets no region attribute and no other change.
   */
  public function testProcessSystemBrandingBlockDoesNothing(): void {
    $variables = ['base_plugin_id' => 'system_branding_block', 'x' => 1, 'elements' => ['#id' => 'solo_branding']];
    $this->branding()->preprocess($variables, $this->bound());
    $this->assertArrayNotHasKey('content', $variables);
    $this->assertSame(1, $variables['x']);
    $this->assertSame('system_branding_block', $variables['base_plugin_id']);
  }

}
