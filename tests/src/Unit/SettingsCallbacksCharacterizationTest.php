<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Extension\ThemeSettingsProvider;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Theme\Registry;
use Drupal\solo_utilities\Form\SettingsOptions;
use Drupal\solo_utilities\Form\ThemeSettings\SettingsFormSubmitter;
use Drupal\solo_utilities\Form\ThemeSettings\SettingsFormValidator;
use Drupal\solo_utilities\Security\CssValueValidator;
use Drupal\solo_utilities\Security\UrlSafety;
use Drupal\solo_utilities\Settings\SettingsCatalog;
use Drupal\solo_utilities\Settings\SettingsReader;
use Drupal\solo_utilities\Theme\SoloThemeDetectionInterface;
use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/SoloCharacterizationBase.php';

/**
 * Records the settings form validate callback and the layout store rule.
 *
 * In Solo 1 these were solo_theme_settings_validate() and
 * _solo_set_or_clear_layout(). In Solo 2 they are SettingsFormValidator::
 * validate() and SettingsFormSubmitter::cleanLayouts(), and the form values
 * use the Solo 2 setting names (CHANGELOG: "Settings are migrated from the
 * Solo 1 names to the Solo 2 names"), so a field's error name changed with it.
 *
 * The submit callback itself is NOT covered: it writes files and file usage,
 * so it needs a Kernel test.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
class SettingsCallbacksCharacterizationTest extends SoloCharacterizationBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $translation = $this->createMock(TranslationInterface::class);
    $translation->method('translateString')->willReturnCallback(fn($s) => strtr((string) $s->getUntranslatedString(), $s->getOptions()));
    $this->container->set('string_translation', $translation);
  }

  /**
   * Builds a catalog that reads the given schema and no shipped defaults.
   *
   * @param array<string, mixed> $definition
   *   The schema definition the catalog sees.
   */
  private function catalog(array $definition = []): SettingsCatalog {
    $typed = $this->createStub(TypedConfigManagerInterface::class);
    $typed->method('getDefinition')->willReturn($definition);
    $catalog = new SettingsCatalog($typed, $this->createStub(ExtensionPathResolver::class), '');
    (new \ReflectionProperty(SettingsCatalog::class, 'defaults'))->setValue($catalog, []);
    return $catalog;
  }

  /**
   * Runs validate with the given nested values and returns errors by name.
   *
   * @param array<string, mixed> $values
   *   The submitted values, in the Solo 2 shape.
   *
   * @return array<string, string>
   *   The errors.
   */
  private function validate(array $values): array {
    $detection = $this->createStub(SoloThemeDetectionInterface::class);
    $detection->method('editedThemeName')->willReturn('solo');
    $reader = new SettingsReader($this->container->get(ThemeSettingsProvider::class), $this->createStub(ConfigFactoryInterface::class), $this->catalog());
    $paths = $this->createStub(ExtensionPathResolver::class);
    $paths->method('getPath')->willReturn(dirname(__DIR__, 3));
    $validator = new SettingsFormValidator(
      $detection,
      $reader,
      new UrlSafety($this->createStub(FileUrlGeneratorInterface::class)),
      new CssValueValidator(),
      $this->createStub(MessengerInterface::class),
      $paths,
    );
    $form = ['#parents' => []];
    $state = new FormState();
    $state->setValues($values);
    $validator->validate($form, $state);
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
    // CHANGED ON PURPOSE: the fields are footer.credit_link.url and .text
    // (1.x: footer_link, footer_link_text); the error name follows the Solo 2
    // setting name.
    $url_field = 'footer][credit_link][url';
    $text_field = 'footer][credit_link][text';
    $bad = ['footer' => ['credit_link' => ['url' => 'not a url x y', 'text' => '']]];
    $this->assertSame([$url_field, $text_field], array_keys($this->validate($bad)));
    $no_text = ['footer' => ['credit_link' => ['url' => 'https://example.com']]];
    $this->assertSame([$text_field], array_keys($this->validate($no_text)));
    $good = ['footer' => ['credit_link' => ['url' => 'https://example.com', 'text' => 'Go']]];
    $this->assertSame([], $this->validate($good));
    // Formatted text switches the footer-link checks off completely.
    $values = [
      'footer' => [
        'use_formatted_copyright' => TRUE,
        'credit_link' => ['url' => 'bad url x', 'text' => ''],
      ],
    ];
    $this->assertSame([], $this->validate($values));
  }

  /**
   * Social icon colors: a bare custom property name and rubbish are refused.
   */
  public function testValidateSocialIconColours(): void {
    $field = 'social_icons][color';
    $this->assertSame([], $this->validate(['social_icons' => ['color' => '#1877f2']]));
    $this->assertSame([], $this->validate(['social_icons' => ['color' => 'var(--solo-fb)']]));
    $bare = $this->validate(['social_icons' => ['color' => '--solo-fb']]);
    $this->assertSame([$field], array_keys($bare));
    $this->assertStringContainsString('custom property name', $bare[$field]);
    $junk = $this->validate(['social_icons' => ['color' => 'red;background:url(x)']]);
    // CHANGED ON PURPOSE: American spelling; solo_utilities CHANGELOG:
    // "comments use American English" (the message said "colour" in 1.x).
    $this->assertStringContainsString('not a color this setting accepts', $junk[$field]);
    // Blank (whitespace) is allowed.
    $this->assertSame([], $this->validate(['social_icons' => ['color' => '   ']]));
  }

  /**
   * Preloader logo path is checked only when enabled and not the theme logo.
   */
  public function testValidatePreloaderLogoPath(): void {
    $bad = [
      'preloader' => [
        'enabled' => TRUE,
        'logo' => ['use_theme' => FALSE, 'url' => 'themes/../../outside.svg'],
      ],
    ];
    $this->assertCount(1, $this->validate($bad));
    $theme_logo = $bad;
    $theme_logo['preloader']['logo']['use_theme'] = TRUE;
    $this->assertSame([], $this->validate($theme_logo));
    $off = $bad;
    $off['preloader']['enabled'] = FALSE;
    $this->assertSame([], $this->validate($off));
    $empty = $bad;
    $empty['preloader']['logo']['url'] = '';
    $this->assertSame([], $this->validate($empty));
    $stream = $bad;
    $stream['preloader']['logo']['url'] = 'public://logo.svg';
    $this->assertSame([], $this->validate($stream));
  }

  /**
   * Layout store: only a value that differs from the global is kept.
   */
  public function testSetOrClearLayout(): void {
    // The rule lives in SettingsFormSubmitter::cleanLayouts() now, one content
    // type per case.
    $types = [];
    foreach (['a', 'b', 'c', 'd'] as $id) {
      $entity = $this->createStub(EntityInterface::class);
      $entity->method('label')->willReturn($id);
      $types[$id] = $entity;
    }
    $storage = $this->createStub(EntityStorageInterface::class);
    $storage->method('loadMultiple')->willReturn($types);
    $manager = $this->createStub(EntityTypeManagerInterface::class);
    $manager->method('getStorage')->willReturn($storage);
    $options = new SettingsOptions($manager, $this->createStub(ThemeExtensionList::class), '', $this->createStub(SoloThemeDetectionInterface::class));
    $catalog = $this->catalog(['mapping' => ['region_groups' => ['mapping' => ['top' => ['type' => 'mapping']]]]]);
    $submitter = new SettingsFormSubmitter(
      $this->createStub(SoloThemeDetectionInterface::class),
      $this->createStub(ConfigFactoryInterface::class),
      $catalog,
      $options,
      $this->createStub(EntityRepositoryInterface::class),
      $this->createStub(Registry::class),
      $this->createStub(TwigEnvironment::class),
    );

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
    $state = new FormState();
    $state->setValues([
      'region_groups' => [
        'top' => [
          'per_content_type_enabled' => TRUE,
          'columns_2' => 'y',
          'per_content_type' => ['columns_2' => ['a' => 'x', 'b' => 'y', 'd' => '']],
        ],
      ],
    ]);
    (new \ReflectionMethod($submitter, 'cleanLayouts'))->invoke($submitter, $config, $state);

    $prefix = 'region_groups.top.per_content_type.columns_2.';
    $two_column = array_values(array_filter($calls, fn(array $call) => str_starts_with($call[1], $prefix)));
    $this->assertSame([
      ['set', $prefix . 'a', 'x'],
      ['clear', $prefix . 'b'],
      ['clear', $prefix . 'c'],
      ['set', $prefix . 'd', ''],
    ], $two_column);
  }

}
