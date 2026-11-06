<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Kernel;

use Drupal\block\Entity\Block;
use Drupal\KernelTests\KernelTestBase;
use Drupal\solo_utilities\Feature\BlockBranding;
use Drupal\solo_utilities\Layout\RegionInventory;
use Drupal\solo_utilities\Settings\SettingsReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Two config quirks of Solo 1's helpers, and what Solo 2 does instead.
 *
 * 1. Solo 1's _count_regions() SAVED solo.settings on every view of the theme
 *    settings form. Solo 2 derives the counts (RegionInventory) and stores
 *    nothing: the solo_utilities config map drops count_* with the reason
 *    "Derived from block placement; written on form GET, read by no template".
 * 2. Solo 1's _set_logo_default() answered 1 only when the default AND the
 *    active theme were exactly 'solo'. Solo 2 keeps that rule in
 *    BlockBranding::logoDefault(); the cases below are unchanged.
 *
 * Core's Stark supplies a real region list; Solo is installed for the logo
 * cases, which read its settings.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
#[RunTestsInSeparateProcesses]
class ConfigQuirksCharacterizationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'block',
    'user',
    'field',
    'filter',
    'text',
    'node',
    'path_alias',
    'solo_utilities',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['system']);
    $this->container->get('theme_installer')->install(['stark', 'claro', 'solo']);
  }

  /**
   * Places a block in a Stark region.
   */
  private function placeBlock(string $id, string $region, bool $enabled = TRUE): void {
    Block::create([
      'id' => $id,
      'theme' => 'stark',
      'region' => $region,
      'plugin' => 'system_powered_by_block',
      'status' => $enabled,
    ])->save();
  }

  /**
   * Counts only enabled blocks and saves nothing.
   */
  public function testRegionCountsAreDerivedNotStored(): void {
    $before = $this->config('solo.settings')->get();
    $this->placeBlock('b_content', 'content');
    $this->placeBlock('b_sidebar', 'sidebar_first');
    // A region holding only disabled blocks does not count.
    $this->placeBlock('b_off', 'sidebar_second', FALSE);
    // A region outside the group does not count either.
    $this->placeBlock('b_header', 'header');

    $inventory = $this->container->get(RegionInventory::class);
    $this->assertSame(2, $inventory->countEnabled('stark', ['content', 'sidebar_first', 'sidebar_second']));
    $this->assertSame(0, $inventory->countEnabled('stark', ['highlighted', 'help']));

    // Nothing is written: no count_* keys, solo.settings unchanged.
    $after = $this->config('solo.settings')->get();
    $this->assertSame($before, $after);
    $this->assertSame([], preg_grep('/^count_/', array_keys($after)));
    $this->assertTrue($this->config('stark.settings')->isNew());
  }

  /**
   * Cases for the logo rule.
   *
   * @return array<string, array{string, string, mixed, int}>
   *   Default theme, active theme, solo.settings logo.use_default, answer.
   */
  public static function logoProvider(): array {
    return [
      'solo default, solo active, use_default 1' => ['solo', 'solo', 1, 1],
      'string 1 is loosely equal' => ['solo', 'solo', '1', 1],
      'use_default 0' => ['solo', 'solo', 0, 0],
      'sub-theme active answers 0' => ['solo', 'solo_child', 1, 0],
      'sub-theme is the default theme' => ['solo_child', 'solo_child', 1, 0],
      'solo active but another default theme' => ['claro', 'solo', 1, 0],
      'admin theme active' => ['solo', 'claro', 1, 0],
    ];
  }

  /**
   * The logo flag needs default AND active theme to be exactly 'solo'.
   */
  #[DataProvider('logoProvider')]
  public function testLogoDefault(string $default, string $active, mixed $use_default, int $expected): void {
    $this->config('system.theme')->set('default', $default)->save();
    $this->config('solo.settings')->set('logo.use_default', $use_default)->save();
    $settings = $this->container->get(SettingsReader::class)->forTheme($active);
    $this->assertSame($expected, $this->container->get(BlockBranding::class)->logoDefault($settings));
  }

}
