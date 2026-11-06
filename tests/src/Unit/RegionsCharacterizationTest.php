<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\solo_utilities\Theme\SoloThemeDetectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\solo_utilities\Form\SettingsOptions;
use Drupal\solo_utilities\Form\ThemeSettings\SettingsFormBuilder;
use Drupal\Core\Template\Attribute;
use Drupal\solo_utilities\Css\ThemeCssBuilder;
use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/SoloCharacterizationBase.php';
require_once __DIR__ . '/SoloHookHarness.php';

/**
 * Records the region helpers (conversion rule 4 and 9).
 *
 * WHY THIS EXISTS
 * Solo 1 kept region names, derived name lists, region color variables and the
 * block-occupancy helpers in includes/. In Solo 2 they are answered by Solo
 * Utilities: SettingsCatalog (names and the template variable contract),
 * ThemeCssBuilder (region colors, now a generated stylesheet), PagePreprocessor
 * (the attributes_<region> variables) and RegionInventory (which regions hold
 * blocks). Each case keeps the 1.x answer, or states why 2.0 differs.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
class RegionsCharacterizationTest extends SoloCharacterizationBase {

  use SoloHookHarness;

  /**
   * The 30 site regions, in order. The order is the contract.
   */
  private const REGIONS = [
    'page_wrapper', 'popup_login_block', 'fixed_search_block', 'header',
    'primary_sidebar_menu', 'primary_menu', 'welcome_text', 'top_container',
    'top_first', 'top_second', 'top_third', 'highlighted', 'system_messages',
    'page_title', 'breadcrumb', 'main_container', 'sidebar_first', 'content',
    'sidebar_second', 'bottom_container', 'bottom_first', 'bottom_second',
    'bottom_third', 'bottom_fourth', 'footer_container', 'footer_first',
    'footer_second', 'footer_third', 'footer_menu', 'credit_copyright',
  ];

  /**
   * The 15 attribute keys (1.x names) and their English labels, in order.
   */
  private const ATTRIBUTES = [
    '__r_bg' => 'Region background',
    '__r_tx' => 'Region text',
    '__r_h1' => 'Headings (H1-H3)',
    '__r_lk' => 'Link text',
    '__r_lk_h' => 'Link text (hover)',
    // CHANGED ON PURPOSE: capitalized like every other label in the list.
    '__r_br' => 'Borders',
    '__r_bg_fr' => 'Form field background',
    '__r_tx_lk' => 'Menu link text',
    '__r_tx_lk_h' => 'Menu link text (hover)',
    '__r_bg_lk' => 'Menu link background',
    '__r_bg_lk_h' => 'Menu link background (hover)',
    '__r_tx_bt' => 'Button text',
    '__r_tx_bt_h' => 'Button text (hover)',
    '__r_bg_bt' => 'Button background',
    '__r_bg_bt_h' => 'Button background (hover)',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->wireSolo();
  }

  /**
   * The region list is exactly the 30 names, in order.
   */
  public function testSiteRegions(): void {
    // CHANGED ON PURPOSE: the list now comes from the settings schema mapping
    // (SettingsCatalog::regions() docblock), whose order differs from 1.x. Its
    // only caller, PagePreprocessor, sets one variable per region, so the order
    // does not reach the page; the names must still match exactly.
    $regions = $this->catalog->regions();
    $this->assertCount(30, $regions);
    $this->assertSame([], array_values(array_diff(self::REGIONS, $regions)));
    $this->assertSame([], array_values(array_diff($regions, self::REGIONS)));
    $this->assertSame($regions, array_unique($regions));
  }

  /**
   * The derived name lists follow the region list.
   */
  public function testDerivedRegionLists(): void {
    $groups = $this->catalog->templateVariableGroups();
    $this->assertSame(array_map(fn($r) => "classes_{$r}", self::REGIONS), $groups['css_classes']);
    $this->assertSame(array_map(fn($r) => "align_{$r}", self::REGIONS), $groups['region_alignment']);
    $settings = $groups['settings_region'];
    $this->assertCount(450, $settings);
    $this->assertSame('settings_page_wrapper___r_bg', $settings[0]);
    $this->assertSame('settings_page_wrapper___r_tx', $settings[1]);
    $this->assertSame('settings_popup_login_block___r_bg', $settings[15]);
    $this->assertSame('settings_credit_copyright___r_bg_bt_h', $settings[449]);
  }

  /**
   * The attribute keys and their untranslated labels.
   */
  public function testRegionAttributes(): void {
    $this->assertSame(
      array_keys(self::ATTRIBUTES),
      array_map(fn($property) => '__' . str_replace('-', '_', ltrim($property, '-')), array_values(ThemeCssBuilder::COLOR_PROPERTIES))
    );
    $options = $this->options();
    $labels = array_map(fn($label) => $label->getUntranslatedString(), $options->regionColors());
    $this->assertSame(array_values(self::ATTRIBUTES), array_values($labels));
  }

  /**
   * Set attributes become CSS variables, '_' turned to '-'.
   */
  public function testRegionAttributesForRegion(): void {
    $this->assertSame('', $this->cssBuilder->build('solo')->settingsCss);
    $this->settings = [
      'regions.header.colors.background' => '#112233',
      'regions.header.colors.menu_link_text_hover' => '#ffffff',
      'regions.footer_container.colors.background' => '#000000',
    ];
    // CHANGED ON PURPOSE: the variables are no longer a style attribute on the
    // region element but one rule in the generated stylesheet; ThemeCssBuilder
    // class docblock and PagePreprocessor ("Region colors live in the
    // generated stylesheet"). The rule's variables and order are unchanged.
    $rule = ThemeCssBuilder::REGION_SELECTORS['header'] . '{--r-bg:#112233;--r-tx-lk-h:#ffffff}';
    $this->assertStringContainsString($rule, $this->cssBuilder->build('solo')->settingsCss);
  }

  /**
   * ODDITY: 1.x wrote stored values with no sanitizing.
   *
   * CHANGED ON PURPOSE: ThemeCssBuilder's class docblock says "A refused value
   * emits nothing and its field name is reported". The value is refused, not
   * written, and the field is listed.
   */
  public function testRegionAttributesForRegionIsNotSanitised(): void {
    $this->settings = ['regions.header.colors.background' => 'red;background:url(x)'];
    $assets = $this->cssBuilder->build('solo');
    $this->assertSame('', $assets->settingsCss);
    $this->assertSame(['regions.header.colors.background'], $assets->refusedFields);
  }

  /**
   * Every region gets an Attribute; only regions with settings get a style.
   */
  public function testCreateRegionAttributes(): void {
    $this->settings = [
      'regions.header.colors.background' => '#112233',
      'regions.header.colors.text' => '#ffffff',
    ];
    $variables = [];
    $this->hooks->preprocessPage($variables);
    foreach (self::REGIONS as $region) {
      $this->assertInstanceOf(Attribute::class, $variables["attributes_{$region}"]);
    }
    // CHANGED ON PURPOSE: no region prints a style attribute any more; the
    // colors are in the generated stylesheet (PagePreprocessor comment above
    // the attributes_<region> loop).
    $this->assertSame('', (string) $variables['attributes_header']);
    $this->assertSame('', (string) $variables['attributes_footer_menu']);
  }

  /**
   * The template variable contract: group names and the two fixed lists.
   */
  public function testThemeVariables(): void {
    $groups = $this->catalog->templateVariableGroups();
    $this->assertSame(['misc', 'social', 'css_classes', 'region_alignment', 'settings_region'], array_keys($groups));
    $fixture = dirname(__DIR__, 2) . '/fixtures/solo-characterization/theme-variables.txt';
    $lines = [];
    foreach (['misc', 'social'] as $group) {
      foreach ($groups[$group] as $key) {
        $lines[] = "{$group} {$key}";
      }
    }
    $this->assertSame(trim((string) file_get_contents($fixture)), implode("\n", $lines));
  }

  /**
   * Regions with at least one block are active, in the theme's order.
   */
  public function testActiveRegions(): void {
    $this->themeRegions = ['header' => 'Header', 'content' => 'Content', 'footer_first' => 'Footer first'];
    $this->blocks = [['content', TRUE], ['header', TRUE]];
    $this->assertSame(['header', 'content'], $this->inventory->regionsWithBlocks('solo'));
  }

  /**
   * ODDITY: no region with blocks left $active_regions undefined in 1.x.
   *
   * CHANGED ON PURPOSE: RegionInventory::regionsWithBlocks() is declared
   * "@return list<string>", so no occupied region is an empty list, with no
   * PHP warning (the class docblock: "Reads only").
   */
  public function testActiveRegionsWithNoBlocksReturnsNullWithWarning(): void {
    $this->themeRegions = ['header' => 'Header'];
    $this->blocks = [];
    $warnings = [];
    set_error_handler(function (int $level, string $message) use (&$warnings): bool {
      $warnings[] = $message;
      return TRUE;
    });
    try {
      $result = $this->inventory->regionsWithBlocks('solo');
    }
    finally {
      restore_error_handler();
    }
    $this->assertSame([], $result);
    $this->assertSame([], $warnings);
  }

  /**
   * Updated regions: groups, containers, labels and the 'mix' remainder.
   *
   * The sorting is SettingsFormBuilder::occupiedRegions(), which is private;
   * it is called by reflection with its two collaborators built for real.
   */
  public function testUpdatedRegions(): void {
    $this->themeRegions = [
      'header' => 'Header',
      'top_first' => 'Info Top First',
      'top_third' => 'x',
      'content' => 'Info Content',
      'sidebar_first' => 'x',
      'footer_menu' => 'x',
    ];
    $this->blocks = [
      ['header', TRUE],
      ['top_first', TRUE],
      ['top_third', TRUE],
      ['content', TRUE],
      ['footer_menu', TRUE],
    ];
    $class = SettingsFormBuilder::class;
    $builder = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    foreach (['regionInventory' => $this->inventory, 'options' => $this->options()] as $name => $value) {
      $property = new \ReflectionProperty($class, $name);
      $property->setValue($builder, $value);
    }
    [$mix, $result] = (new \ReflectionMethod($class, 'occupiedRegions'))->invoke($builder, 'solo');
    $this->assertSame(['header' => 'Header', 'footer_menu' => 'Footer Menu'], $mix);
    $this->assertSame(['top', 'main', 'bottom', 'footer'], array_keys($result));
    $this->assertSame(['container', 'top_first', 'top_third'], array_keys($result['top']));
    $this->assertSame('Top Container Wrapper', $result['top']['container']->getUntranslatedString());
    // CHANGED ON PURPOSE: region labels come from the theme's info file, so a
    // region is named in one place (SettingsOptions::groupLabels() docblock).
    $this->assertSame('Info Top First', $result['top']['top_first']->getUntranslatedString());
    $this->assertSame('Info Content', $result['main']['content']->getUntranslatedString());
    $this->assertSame([], $result['bottom']);
    $this->assertSame([], $result['footer']);
  }

  /**
   * Builds the options class on the info the harness gives the theme.
   */
  private function options(): SettingsOptions {
    $list = $this->createStub(ThemeExtensionList::class);
    $list->method('getExtensionInfo')->willReturnCallback(fn() => ['regions' => $this->themeRegions]);
    return new SettingsOptions(
      $this->createStub(EntityTypeManagerInterface::class),
      $list,
      self::siteRoot(),
      $this->createStub(SoloThemeDetectionInterface::class),
    );
  }

}
