<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Extension\ThemeSettingsProvider;
use PHPUnit\Framework\TestCase;

/**
 * Shared set-up for the Solo characterization tests.
 *
 * WHY THIS EXISTS
 * Solo 2 moves the procedural helpers in includes/ into solo_utilities. These
 * tests record what the helpers do TODAY, oddities included, so the move can
 * prove identical output. A test here asserts current behaviour; when the
 * assertion looks wrong, the answer is a note in the result file, not a fix.
 *
 * The helpers read services through \Drupal::, so each test builds a small
 * container holding only mocks for what the helper under test asks for. The
 * helpers choose their code path with \Drupal::VERSION; the 11.3+ path is the
 * one recorded here (the pre-11.3 path calls theme_get_setting(), which needs
 * a full bootstrap).
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */
abstract class SoloCharacterizationBase extends TestCase {

  /**
   * Theme settings the stubbed ThemeSettingsProvider answers with.
   *
   * @var array<string, mixed>
   */
  protected array $settings = [];

  /**
   * The container the helpers see through \Drupal::.
   */
  protected ContainerBuilder $container;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    if (version_compare(\Drupal::VERSION, '11.4.0', '<')) {
      $this->markTestSkipped('Characterizes the Drupal 11.4+ code path; the older path needs a full bootstrap.');
    }
    // Loaded here, in the test's own process: a class-level hook would run in
    // the parent process, where another test may have loaded another copy.
    $core = dirname((string) (new \ReflectionClass(\Drupal::class))->getFileName(), 2);
    require_once $core . '/includes/bootstrap.inc';
    $theme = dirname(__DIR__, 3);
    require_once $theme . '/includes/_solo_theme_preprocess.inc';
    require_once $theme . '/includes/_theme_settings_helper_functions.inc';
    require_once $theme . '/includes/_solo_theme_suggestions.inc';
    $this->settings = [];
    $this->container = new ContainerBuilder();
    $provider = $this->createMock(ThemeSettingsProvider::class);
    $provider->method('getSetting')->willReturnCallback(fn(string $name) => $this->settings[$name] ?? NULL);
    $this->container->set(ThemeSettingsProvider::class, $provider);
    \Drupal::setContainer($this->container);
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    \Drupal::unsetContainer();
    parent::tearDown();
  }

}
