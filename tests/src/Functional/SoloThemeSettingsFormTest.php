<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Functional;

use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Guards the Solo theme settings form end to end.
 *
 * WHY THIS EXISTS
 * Solo 2 builds its settings UI in Solo Utilities (Form\ThemeSettings, one
 * section class per area), attached to Solo's settings page by a #[Hook]
 * form alter. If that hook stops running, the page still returns HTTP 200
 * with only core's handful of fields.
 *
 * So this test asserts on CONTENT and on a round-trip SAVE rather than on the
 * status code: an empty-but-successful settings page is exactly the failure
 * mode being guarded against.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
#[RunTestsInSeparateProcesses]
class SoloThemeSettingsFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   *
   * Core installs the default theme BEFORE $modules, and Solo 2 depends on
   * Solo Utilities, so Solo is installed and made default in setUp().
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'block', 'user', 'solo_utilities'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->container->get('theme_installer')->install(['solo']);
    $this->config('system.theme')->set('default', 'solo')->save();
  }

  /**
   * The settings route for this theme.
   */
  private const SETTINGS_PATH = '/admin/appearance/settings/solo';

  /**
   * The form builds, and it carries Solo's own sections.
   */
  public function testSettingsFormBuildsWithSoloSections(): void {
    $this->drupalLogin($this->drupalCreateUser(['administer themes']));
    $this->drupalGet(self::SETTINGS_PATH);
    $this->assertSession()->statusCodeEquals(200);

    // Solo Utilities builds the overwhelming majority of this form: hundreds
    // of named fields with it, barely a dozen without. A count is a far better guard
    // than any single field name, because the failure being caught is "the
    // hook stopped running and the page still returned 200".
    //
    // Deliberately NOT asserting a core field such as default_logo: core gates
    // its logo and favicon sections on the File module
    // (ThemeSettingsForm.php:232, "only available when file.module is
    // enabled"), so those names come and go with the site's module list and
    // would make this test environment-dependent.
    $html = $this->getSession()->getPage()->getContent();
    preg_match_all('/<(?:input|select|textarea)\b[^>]*\bname="([^"]+)"/i', $html, $matches);
    $fieldCount = count(array_unique($matches[1]));
    $this->assertGreaterThan(100, $fieldCount, sprintf(
      'Only %d named fields rendered. Solo Utilities builds this form; a '
      . 'collapse to a handful means its form alter is no longer running.',
      $fieldCount
    ));

    // The color scheme picker, from ColorSchemesSection. The category is a
    // value element derived from the chosen scheme, so it has no field.
    $this->assertSession()->fieldExists('color_scheme[name]');
    $this->assertSession()->elementExists('css', '#edit-color-scheme-selection');
  }

  /**
   * A Solo setting round-trips through validate and submit.
   *
   * This is the assertion that the #[Hook] class, the validate handler and the
   * submit handler are all wired, not merely that markup rendered.
   */
  public function testSoloSettingSavesAndPersists(): void {
    $this->drupalLogin($this->drupalCreateUser(['administer themes']));
    $this->drupalGet(self::SETTINGS_PATH);
    $this->assertSession()->statusCodeEquals(200);

    // A real scheme, read from the page: its option value is
    // "category|name", and the validator stores the two keys apart.
    $option = $this->getSession()->getPage()->find('css', 'input[name="color_scheme[name]"][value*="|"]');
    $this->assertNotNull($option, 'The scheme picker offers no scheme.');
    $value = (string) $option->getAttribute('value');
    [$category, $name] = explode('|', $value, 2);

    $this->submitForm(['color_scheme[name]' => $value], 'Save configuration');
    $this->assertSession()->statusCodeEquals(200);

    $saved = \Drupal::config('solo.settings');
    $this->assertSame(
      [$category, $name],
      [$saved->get('color_scheme.category'), $saved->get('color_scheme.name')],
      'The chosen scheme did not persist, so the submit path is not wired.'
    );
  }

  /**
   * The form's own stylesheet is attached, proving the hook body ran.
   */
  public function testSettingsFormLibraryIsAttached(): void {
    $this->drupalLogin($this->drupalCreateUser(['administer themes']));
    $this->drupalGet(self::SETTINGS_PATH);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('solo-form-theme-settings.css');
  }

}
