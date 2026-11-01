<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Kernel;

use Drupal\block\Entity\Block;
use Drupal\Core\Theme\ActiveTheme;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Records two config quirks of Solo's helpers (conversion rules 5 and 9).
 *
 * 1. _count_regions() SAVES solo.settings every time it runs, which is every
 *    view of the theme settings form, and it always writes solo.settings, even
 *    when the active theme is a sub-theme.
 * 2. _set_logo_default() READS solo.settings, so a sub-theme gets logo_default
 *    0 whatever its own setting says.
 *
 * Solo itself is not installed here: the active theme is stubbed by name, which
 * is all both helpers look at. core's Stark supplies a real region list.
 * Needs checking: the same run against a real Solo sub-theme chain.
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
  protected static $modules = ['system', 'block', 'user'];

  /**
   * {@inheritdoc}
   *
   * The schema for solo.settings lives in the theme, which is not installed
   * here, so strict checking would fail on the object under test.
   */
  // phpcs:ignore DrupalPractice.Objects.StrictSchemaDisabled.StrictConfigSchema
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    if (version_compare(\Drupal::VERSION, '11.4.0', '<')) {
      $this->markTestSkipped('Characterizes the Drupal 11.4+ code path.');
    }
    $theme = dirname(__DIR__, 3);
    require_once $theme . '/includes/_solo_theme_preprocess.inc';
    require_once $theme . '/includes/_theme_settings_helper_functions.inc';
    $this->installConfig(['system']);
    $this->container->get('theme_installer')->install(['stark']);
  }

  /**
   * Makes \Drupal::theme() report the given active theme name.
   */
  private function setActiveThemeName(string $name): void {
    $active = $this->createMock(ActiveTheme::class);
    $active->method('getName')->willReturn($name);
    $manager = $this->createMock(ThemeManagerInterface::class);
    $manager->method('getActiveTheme')->willReturn($active);
    $this->container->set('theme.manager', $manager);
  }

  /**
   * Places a block in a Stark region.
   */
  private function placeBlock(string $id, string $region, bool $enabled = TRUE): void {
    $block = Block::create([
      'id' => $id,
      'theme' => 'stark',
      'region' => $region,
      'plugin' => 'system_powered_by_block',
      'status' => $enabled,
    ]);
    $block->save();
  }

  /**
   * Counts only enabled blocks, per container group, and saves solo.settings.
   */
  public function testCountRegionsSavesSoloSettingsOnView(): void {
    $this->setActiveThemeName('stark');
    $this->placeBlock('b_content', 'content');
    $this->placeBlock('b_sidebar', 'sidebar_first');
    // A region holding only disabled blocks does not count.
    $this->placeBlock('b_off', 'sidebar_second', FALSE);
    // A region outside the four groups does not count either.
    $this->placeBlock('b_header', 'header');

    $this->assertTrue($this->config('solo.settings')->isNew());

    $counts = _count_regions();

    $this->assertSame(['top' => 0, 'main' => 2, 'bottom' => 0, 'footer' => 0], $counts);
    $saved = $this->config('solo.settings');
    $this->assertFalse($saved->isNew());
    $this->assertSame(
      ['count_top' => 0, 'count_main' => 2, 'count_bottom' => 0, 'count_footer' => 0],
      $saved->get()
    );
    // Always solo.settings, never the active theme's own object.
    $this->assertTrue($this->config('stark.settings')->isNew());
  }

  /**
   * Every call overwrites the saved counts and queues a status message.
   */
  public function testCountRegionsOverwritesAndAddsMessage(): void {
    $this->setActiveThemeName('stark');
    $this->placeBlock('b_one', 'content');
    $this->config('solo.settings')->set('count_main', 99)->set('keep_me', 'x')->save();

    _count_regions();

    $this->assertSame(1, $this->config('solo.settings')->get('count_main'));
    // Other keys in the same object survive; only the four counts are set.
    $this->assertSame('x', $this->config('solo.settings')->get('keep_me'));
    $messages = $this->container->get('messenger')->messagesByType('status');
    $this->assertCount(1, $messages);
    $text = (string) $messages[0];
    $this->assertStringContainsString("Main Container - <span class='status'>1</span> enabled", $text);
    $this->assertStringContainsString("Top Container - <span class='status'>0</span> enabled region(s)", $text);

    // The messenger drops an identical repeat, so a second view adds none.
    _count_regions();
    $this->assertCount(1, $this->container->get('messenger')->messagesByType('status'));
  }

  /**
   * Run under a different active theme name: still solo.settings, same keys.
   */
  public function testCountRegionsUnderAnotherActiveThemeNameStillWritesSoloSettings(): void {
    // Stark stands in for a sub-theme: a theme other than 'solo' is active.
    $this->setActiveThemeName('stark');
    _count_regions();
    $this->assertSame(
      ['count_top', 'count_main', 'count_bottom', 'count_footer'],
      array_keys($this->config('solo.settings')->get())
    );
  }

  /**
   * Cases for the logo helper.
   *
   * @return array<string, array{string, string, mixed, int}>
   *   Default theme, active theme, solo.settings logo.use_default, answer.
   */
  public static function logoProvider(): array {
    return [
      'solo default, solo active, use_default 1' => ['solo', 'solo', 1, 1],
      'string 1 is loosely equal' => ['solo', 'solo', '1', 1],
      'use_default 0' => ['solo', 'solo', 0, 0],
      'use_default unset' => ['solo', 'solo', NULL, 0],
      'sub-theme active reads the same solo setting but answers 0' => ['solo', 'solo_child', 1, 0],
      'sub-theme is the default theme' => ['solo_child', 'solo_child', 1, 0],
      'solo active but another default theme' => ['claro', 'solo', 1, 0],
      'admin theme active' => ['solo', 'claro', 1, 0],
    ];
  }

  /**
   * The logo flag needs default AND active theme to be exactly 'solo'.
   */
  #[DataProvider('logoProvider')]
  public function testSetLogoDefault(string $default, string $active, mixed $use_default, int $expected): void {
    $this->config('system.theme')->set('default', $default)->save();
    if ($use_default !== NULL) {
      $this->config('solo.settings')->set('logo.use_default', $use_default)->save();
    }
    // The sub-theme's own setting must be ignored by the helper.
    $this->config('solo_child.settings')->set('logo.use_default', 0)->save();
    $this->setActiveThemeName($active);

    $variables = ['existing' => TRUE];
    _set_logo_default($variables);

    $this->assertSame(['existing' => TRUE, 'logo_default' => $expected], $variables);
  }

}
