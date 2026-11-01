<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\Core\Config\Config;
use Drupal\Core\Form\FormState;
use Drupal\Core\StringTranslation\TranslationInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/SoloCharacterizationBase.php';

/**
 * Records the settings form validate callback and the layout store helper.
 *
 * The submit callback itself (_solo_theme_settings_submit) is NOT covered: it
 * writes files and file usage, so it needs a Kernel test (see the batch 2
 * result file).
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
#[RunTestsInSeparateProcesses]
class SettingsCallbacksCharacterizationTest extends SoloCharacterizationBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    require_once dirname(__DIR__, 3) . '/includes/_solo_theme_settings.inc';
    $translation = $this->createMock(TranslationInterface::class);
    $translation->method('translateString')->willReturnCallback(fn($s) => strtr((string) $s->getUntranslatedString(), $s->getOptions()));
    $this->container->set('string_translation', $translation);
  }

  /**
   * Runs validate with the given values and returns the errors by name.
   */
  private function validate(array $values): array {
    $form = ['#parents' => []];
    $state = new FormState();
    $state->setValues($values + ['footer_link' => '', 'footer_link_text' => '']);
    solo_theme_settings_validate($form, $state);
    return array_map('strval', $state->getErrors());
  }

  /**
   * Empty form: no errors.
   */
  public function testValidateClean(): void {
    $this->assertSame([], $this->validate([]));
  }

  /**
   * Footer link: invalid URL and missing text each report on their field.
   */
  public function testValidateFooterLink(): void {
    $errors = $this->validate(['footer_link' => 'javascript:alert(1) x y', 'footer_link_text' => '']);
    $this->assertSame(['footer_link', 'footer_link_text'], array_keys($errors));
    $this->assertSame(['footer_link_text'], array_keys($this->validate(['footer_link' => 'https://example.com'])));
    $this->assertSame([], $this->validate(['footer_link' => 'https://example.com', 'footer_link_text' => 'Go']));
    // Formatted text switches the footer-link checks off completely.
    $values = [
      'footer_use_formatted_text' => TRUE,
      'footer_link' => 'bad url x',
      'footer_link_text' => '',
    ];
    $this->assertSame([], $this->validate($values));
  }

  /**
   * Social icon colours: a bare custom property name and rubbish are refused.
   */
  public function testValidateSocialIconColours(): void {
    $this->assertSame([], $this->validate(['sm_icon_colors' => '#1877f2']));
    $this->assertSame([], $this->validate(['sm_icon_colors' => 'var(--solo-fb)']));
    $bare = $this->validate(['sm_icon_colors' => '--solo-fb']);
    $this->assertSame(['sm_icon_colors'], array_keys($bare));
    $this->assertStringContainsString('custom property name', $bare['sm_icon_colors']);
    $junk = $this->validate(['sm_icon_colors' => 'red;background:url(x)']);
    $this->assertStringContainsString('not a colour this setting accepts', $junk['sm_icon_colors']);
    // Blank (whitespace) is allowed.
    $this->assertSame([], $this->validate(['sm_icon_colors' => '   ']));
  }

  /**
   * Preloader logo path is checked only when enabled and not the theme logo.
   */
  public function testValidatePreloaderLogoPath(): void {
    $bad = [
      'preloader_enabled' => TRUE,
      'preloader_logo_use_theme' => FALSE,
      'preloader_logo_url' => '../../etc/passwd',
    ];
    $this->assertCount(1, $this->validate($bad));
    $this->assertSame([], $this->validate(['preloader_logo_use_theme' => TRUE] + $bad));
    $this->assertSame([], $this->validate(['preloader_enabled' => FALSE] + $bad));
    $this->assertSame([], $this->validate(['preloader_logo_url' => ''] + $bad));
    $this->assertSame([], $this->validate(['preloader_logo_url' => 'public://logo.svg'] + $bad));
  }

  /**
   * Layout store: only a value that differs from the global is kept.
   */
  public function testSetOrClearLayout(): void {
    $config = $this->createMock(Config::class);
    $calls = [];
    $config->method('set')->willReturnCallback(function ($k, $v) use (&$calls, $config) {
      $calls[] = ['set', $k, $v];
      return $config;
    });
    $config->method('clear')->willReturnCallback(function ($k) use (&$calls, $config) {
      $calls[] = ['clear', $k];
      return $config;
    });
    _solo_set_or_clear_layout($config, 'a', 'x', 'y');
    _solo_set_or_clear_layout($config, 'b', 'y', 'y');
    _solo_set_or_clear_layout($config, 'c', NULL, 'y');
    _solo_set_or_clear_layout($config, 'd', '', 'y');
    $this->assertSame([['set', 'a', 'x'], ['clear', 'b'], ['clear', 'c'], ['set', 'd', '']], $calls);
  }

}
