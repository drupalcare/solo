<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Extension\Theme;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Theme\ActiveTheme;
use Drupal\Core\Theme\ThemeManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/SoloCharacterizationBase.php';

/**
 * Records the region helpers (conversion rule 4 and 9).
 *
 * Covers: _get_site_regions, _get_css_classes, _get_region_alignment,
 * _get_region_attributes, _get_settings_region,
 * _get_region_attributes_for_region, _create_region_attributes,
 * _get_theme_variables, _get_active_regions, _get_updated_regions. The two that write config live in the Kernel test.
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
class RegionsCharacterizationTest extends SoloCharacterizationBase {

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
   * The 15 attribute keys and their English labels, in order.
   */
  private const ATTRIBUTES = [
    '__r_bg' => 'Region background',
    '__r_tx' => 'Region text',
    '__r_h1' => 'Headings (H1-H3)',
    '__r_lk' => 'Link text',
    '__r_lk_h' => 'Link text (hover)',
    '__r_br' => 'borders',
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
   * The region list is exactly the 30 names, in order.
   */
  public function testSiteRegions(): void {
    $this->assertSame(self::REGIONS, _get_site_regions());
  }

  /**
   * The derived name lists follow the region list.
   */
  public function testDerivedRegionLists(): void {
    $this->assertSame(array_map(fn($r) => "classes_{$r}", self::REGIONS), _get_css_classes());
    $this->assertSame(array_map(fn($r) => "align_{$r}", self::REGIONS), _get_region_alignment());
    $settings = _get_settings_region();
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
    $attributes = _get_region_attributes();
    $this->assertSame(array_keys(self::ATTRIBUTES), array_keys($attributes));
    $labels = array_map(fn($label) => $label->getUntranslatedString(), $attributes);
    $this->assertSame(array_values(self::ATTRIBUTES), array_values($labels));
  }

  /**
   * Set attributes become CSS variables, '_' turned to '-'.
   */
  public function testRegionAttributesForRegion(): void {
    $this->assertSame([], _get_region_attributes_for_region('header', self::ATTRIBUTES));
    $this->settings = [
      'settings_header___r_bg' => '#112233',
      'settings_header___r_tx_lk_h' => '#ffffff',
      'settings_footer___r_bg' => '#000000',
    ];
    $this->assertSame(
      ['--r-bg:#112233', '--r-tx-lk-h:#ffffff'],
      _get_region_attributes_for_region('header', self::ATTRIBUTES)
    );
  }

  /**
   * ODDITY: values are written as stored, with no sanitising.
   *
   * Rule 4 of the conversion rules says this path goes through
   * _solo_sanitize_hex_for_css(); today it does not, so a stored value with a
   * semicolon becomes a second declaration in the style attribute.
   */
  public function testRegionAttributesForRegionIsNotSanitised(): void {
    $this->settings = ['settings_header___r_bg' => 'red;background:url(x)'];
    $this->assertSame(
      ['--r-bg:red;background:url(x)'],
      _get_region_attributes_for_region('header', self::ATTRIBUTES)
    );
  }

  /**
   * Every region gets an Attribute; only regions with settings get a style.
   */
  public function testCreateRegionAttributes(): void {
    $this->settings = [
      'settings_header___r_bg' => '#112233',
      'settings_header___r_tx' => '#ffffff',
    ];
    $variables = [];
    _create_region_attributes($variables);
    $this->assertCount(30, $variables);
    foreach (self::REGIONS as $region) {
      $this->assertInstanceOf(Attribute::class, $variables["attributes_{$region}"]);
    }
    $this->assertSame(' style="--r-bg:#112233;--r-tx:#ffffff"', (string) $variables['attributes_header']);
    $this->assertSame('', (string) $variables['attributes_footer_menu']);
  }

  /**
   * The template variable contract: group names and the two fixed lists.
   */
  public function testThemeVariables(): void {
    $variables = _get_theme_variables();
    $this->assertSame(['misc', 'social', 'css_classes', 'region_alignment', 'settings_region'], array_keys($variables));
    $this->assertSame(_get_css_classes(), $variables['css_classes']);
    $this->assertSame(_get_region_alignment(), $variables['region_alignment']);
    $this->assertSame(_get_settings_region(), $variables['settings_region']);
    $fixture = dirname(__DIR__, 2) . '/fixtures/solo-characterization/theme-variables.txt';
    $lines = [];
    foreach (['misc', 'social'] as $group) {
      foreach ($variables[$group] as $key) {
        $lines[] = "{$group} {$key}";
      }
    }
    $this->assertSame(trim((string) file_get_contents($fixture)), implode("\n", $lines));
  }

  /**
   * Stubs the active theme, its regions and the block storage.
   *
   * @param array<string, string> $regions
   *   Region machine names the theme lists.
   * @param array<string, list<object>> $blocks
   *   Blocks per region.
   */
  private function stubRegions(array $regions, array $blocks): void {
    $active = $this->createMock(ActiveTheme::class);
    $active->method('getName')->willReturn('solo');
    $manager = $this->createMock(ThemeManagerInterface::class);
    $manager->method('getActiveTheme')->willReturn($active);
    $this->container->set('theme.manager', $manager);

    $theme = $this->createMock(Theme::class);
    $theme->method('listAllRegions')->willReturn($regions);
    $handler = $this->createMock(ThemeHandlerInterface::class);
    $handler->method('getTheme')->with('solo')->willReturn($theme);
    $this->container->set('theme_handler', $handler);

    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')->willReturnCallback(
      fn(array $props) => $props['theme'] === 'solo' ? ($blocks[$props['region']] ?? []) : []
    );
    $types = $this->createMock(EntityTypeManagerInterface::class);
    $types->method('getStorage')->with('block')->willReturn($storage);
    $this->container->set('entity_type.manager', $types);
  }

  /**
   * Regions with at least one block are active, in the theme's order.
   */
  public function testActiveRegions(): void {
    $this->stubRegions(
      ['header' => 'Header', 'content' => 'Content', 'footer_first' => 'Footer first'],
      ['content' => [new \stdClass()], 'header' => [new \stdClass()]]
    );
    $this->assertSame(['header', 'content'], _get_active_regions());
  }

  /**
   * ODDITY: no region with blocks leaves $active_regions undefined.
   *
   * PHP raises a warning and the function returns NULL, not an empty list.
   */
  public function testActiveRegionsWithNoBlocksReturnsNullWithWarning(): void {
    $this->stubRegions(['header' => 'Header'], []);
    $warnings = [];
    set_error_handler(function (int $level, string $message) use (&$warnings): bool {
      $warnings[] = $message;
      return TRUE;
    });
    try {
      $result = _get_active_regions();
    }
    finally {
      restore_error_handler();
    }
    $this->assertNull($result);
    $this->assertSame(['Undefined variable $active_regions'], $warnings);
  }

  /**
   * Updated regions: groups, containers, labels and the 'mix' remainder.
   */
  public function testUpdatedRegions(): void {
    $this->stubRegions(
      [
        'header' => 'Header',
        'top_first' => 'x',
        'top_third' => 'x',
        'content' => 'x',
        'sidebar_first' => 'x',
        'footer_menu' => 'x',
      ],
      [
        'header' => [new \stdClass()],
        'top_first' => [new \stdClass()],
        'top_third' => [new \stdClass()],
        'content' => [new \stdClass()],
        'footer_menu' => [new \stdClass()],
      ]
    );
    $result = _get_updated_regions();
    $this->assertSame(['all', 'mix', 'top', 'main', 'bottom', 'footer'], array_keys($result));
    $this->assertSame(['header' => 'Header', 'footer_menu' => 'Footer Menu'], $result['mix']);
    $this->assertSame(['container', 'top_first', 'top_third'], array_keys($result['top']));
    $this->assertSame('Top Container Wrapper', $result['top']['container']->getUntranslatedString());
    $this->assertSame('Top First Container', $result['top']['top_first']->getUntranslatedString());
    $this->assertSame('Top Third Container', $result['top']['top_third']->getUntranslatedString());
    $this->assertSame('Content', $result['main']['content']->getUntranslatedString());
    $this->assertSame([], $result['bottom']);
    $this->assertSame([], $result['footer']);
    // ODDITY: 'all' holds one 'container' entry, set by the last group seen,
    // and it sits between the regions in insertion order.
    $this->assertSame(['header', 'container', 'top_first', 'top_third', 'content', 'footer_menu'], array_keys($result['all']));
    $this->assertSame('Main Container Wrapper', $result['all']['container']->getUntranslatedString());
    $this->assertSame('Top First', $result['all']['top_first']);
  }

}
