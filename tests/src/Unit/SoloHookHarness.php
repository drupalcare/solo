<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Extension\Extension;
use Psr\Log\LoggerInterface;
use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\solo_utilities\ColorSchemesNegotiator;
use Drupal\solo_utilities\Css\ThemeLibraryRegistrar;
use Drupal\solo_utilities\Css\ThemeAssetFiles;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Extension\ThemeSettingsProvider;
use Drupal\solo_utilities\Asset\AssetAttacher;
use Drupal\solo_utilities\Context\RequestContext;
use Drupal\solo_utilities\Css\ThemeCssBuilder;
use Drupal\solo_utilities\Feature\BackToTop;
use Drupal\solo_utilities\Feature\BlockBranding;
use Drupal\solo_utilities\Feature\BodyClasses;
use Drupal\solo_utilities\Feature\ContentPreprocessor;
use Drupal\solo_utilities\Feature\HtmlPreprocessor;
use Drupal\solo_utilities\Feature\PagePreprocessor;
use Drupal\solo_utilities\Feature\Preloader;
use Drupal\solo_utilities\Hook\PreprocessHooks;
use Drupal\solo_utilities\Layout\LayoutResolver;
use Drupal\solo_utilities\Layout\RegionInventory;
use Drupal\solo_utilities\Render\AttributeHelper;
use Drupal\solo_utilities\Security\CssValueValidator;
use Drupal\solo_utilities\Security\UrlSafety;
use Drupal\solo_utilities\Settings\SchemaBounds;
use Drupal\solo_utilities\Settings\SettingsCatalog;
use Drupal\solo_utilities\Settings\SettingsReader;
use Drupal\solo_utilities\Theme\SoloThemeDetectionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Builds the Solo Utilities classes that took over Solo 1's theme hooks.
 *
 * Solo 1 ran its preprocess code from Drupal\solo\Hook\SoloThemePreprocessHooks
 * and read theme_get_setting(). In Solo 2 the same work is done by Solo
 * Utilities' feature classes, which read BoundThemeSettings. This trait wires
 * those classes to stubs, so a unit test runs them with settings keyed by their
 * 2.0 dotted path (the base class answers from $this->settings) and the real
 * schema and install defaults of the theme under test.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */
trait SoloHookHarness {

  /**
   * The current route name.
   */
  protected ?string $routeName = 'entity.node.canonical';

  /**
   * Route parameters.
   *
   * @var array<string, mixed>
   */
  protected array $params = [];

  /**
   * Modules reported as enabled.
   *
   * @var string[]
   */
  protected array $modules = [];

  /**
   * The request the code sees.
   */
  protected Request $request;

  /**
   * Whether the current route is an admin route.
   */
  protected bool $admin = FALSE;

  /**
   * The internal path.
   */
  protected string $path = '/node/1';

  /**
   * The site name the config stub answers with.
   */
  protected string $siteName = 'Test site';

  /**
   * Enabled blocks of the Solo theme: [region, status] pairs.
   *
   * @var array<int, array{string, bool}>
   */
  protected array $blocks = [];

  /**
   * The settings catalog, built on the real schema and install defaults.
   */
  protected SettingsCatalog $catalog;

  /**
   * The settings reader.
   */
  protected SettingsReader $reader;

  /**
   * The generated CSS builder.
   */
  protected ThemeCssBuilder $cssBuilder;

  /**
   * The region inventory.
   */
  protected RegionInventory $inventory;

  /**
   * The composed preprocess hooks.
   */
  protected PreprocessHooks $hooks;

  /**
   * The page preprocessor.
   */
  protected PagePreprocessor $pagePreprocessor;

  /**
   * The content preprocessor.
   */
  protected ContentPreprocessor $contentPreprocessor;

  /**
   * Regions the Solo theme info lists: label keyed by machine name.
   *
   * @var array<string, string>
   */
  protected array $themeRegions = [];

  /**
   * Builds every collaborator. Call from setUp() after parent::setUp().
   */
  protected function wireSolo(): void {
    $contexts = $this->createStub(CacheContextsManager::class);
    $contexts->method('assertValidTokens')->willReturn(TRUE);
    $this->container->set('cache_contexts_manager', $contexts);
    $translation = $this->createStub(TranslationInterface::class);
    $translation->method('translateString')->willReturnCallback(fn($markup) => $markup->getUntranslatedString());
    $this->container->set('string_translation', $translation);
    $theme_dir = dirname(__DIR__, 3);
    $schema = Yaml::decode((string) file_get_contents($theme_dir . '/config/schema/solo.schema.yml'))['solo_theme_settings'];
    $typed = $this->createStub(TypedConfigManagerInterface::class);
    $typed->method('getDefinition')->willReturn($schema);
    $resolver = $this->createStub(ExtensionPathResolver::class);
    $resolver->method('getPath')->willReturnCallback(
      fn(string $type, string $name) => $name === 'solo_utilities' ? 'modules/contrib/solo_utilities' : 'themes/contrib/solo'
    );
    $this->catalog = new SettingsCatalog($typed, $resolver, dirname(__DIR__, 6));
    $config = $this->createStub(ImmutableConfig::class);
    $config->method('get')->willReturnCallback(fn($key = '') => $key === 'name' ? $this->siteName : NULL);
    $factory = $this->createStub(ConfigFactoryInterface::class);
    $factory->method('get')->willReturn($config);
    $provider = $this->container->get(ThemeSettingsProvider::class);
    $this->reader = new SettingsReader($provider, $factory, $this->catalog);

    $this->request = Request::create('/node/1');
    $stack = new RequestStack();
    $stack->push($this->request);
    $route = $this->createStub(RouteMatchInterface::class);
    $route->method('getRouteName')->willReturnCallback(fn() => $this->routeName);
    $route->method('getParameter')->willReturnCallback(fn(string $k) => $this->params[$k] ?? NULL);
    $route->method('getRawParameter')->willReturn(NULL);
    $admin = $this->createStub(AdminContext::class);
    $admin->method('isAdminRoute')->willReturnCallback(fn() => $this->admin);
    $current = $this->createStub(CurrentPathStack::class);
    $current->method('getPath')->willReturnCallback(fn() => $this->path);
    $user = $this->createStub(AccountInterface::class);
    $user->method('isAnonymous')->willReturn(TRUE);
    $context = new RequestContext($admin, $route, $stack, $current, $user, $this->createStub(TitleResolverInterface::class));

    $none = $this->createStub(EntityStorageInterface::class);
    $none->method('loadByProperties')->willReturn([]);
    $block_storage = $this->createStub(EntityStorageInterface::class);
    $block_storage->method('loadByProperties')->willReturnCallback(function (): array {
      $blocks = [];
      foreach ($this->blocks as [$region, $status]) {
        $block = new class($region, $status) {

          /**
           * Stands in for a block entity.
           */
          public function __construct(private string $region, private bool $status) {}

          /**
           * The region.
           */
          public function getRegion(): string {
            return $this->region;
          }

          /**
           * Whether enabled.
           */
          public function status(): bool {
            return $this->status;
          }

        };
        $blocks[] = $block;
      }
      return $blocks;
    });
    $types = $this->createStub(EntityTypeManagerInterface::class);
    $types->method('getStorage')->willReturnCallback(fn(string $type) => $type === 'block' ? $block_storage : $none);

    $detection = $this->createStub(SoloThemeDetectionInterface::class);
    $detection->method('isActive')->willReturn(TRUE);
    $detection->method('activeThemeName')->willReturn('solo');
    $detection->method('defaultThemeName')->willReturn('solo');
    $detection->method('isInHierarchy')->willReturn(TRUE);

    $validator = new CssValueValidator();
    $attributes = new AttributeHelper();
    $this->cssBuilder = new ThemeCssBuilder($this->reader, $validator, new SchemaBounds($this->catalog));
    $handler = $this->createStub(ModuleHandlerInterface::class);
    $handler->method('moduleExists')->willReturnCallback(fn(string $m) => in_array($m, $this->modules, TRUE));
    $logger_factory = $this->createStub(LoggerChannelFactoryInterface::class);
    $logger_factory->method('get')->willReturn($this->createStub(LoggerChannelInterface::class));
    $html = new HtmlPreprocessor(
      new BodyClasses($context, $attributes),
      $context,
      new UrlSafety($this->createStub(FileUrlGeneratorInterface::class)),
      $handler,
      $resolver,
      $logger_factory,
    );
    $date = $this->createStub(DateFormatterInterface::class);
    $date->method('format')->willReturn('2026');
    $time = $this->createStub(TimeInterface::class);
    $time->method('getRequestTime')->willReturn(1780000000);
    $this->pagePreprocessor = new PagePreprocessor(
      $this->catalog,
      $this->reader,
      $context,
      new LayoutResolver($types),
      new BlockBranding($context, $detection, $types),
      $validator,
      $html,
      $date,
      $time,
    );
    $blank = fn(string $class) => (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    $assets = new AssetAttacher(
      $this->reader,
      $detection,
      $this->cssBuilder,
      $blank(ThemeAssetFiles::class),
      $blank(ThemeLibraryRegistrar::class),
      $validator,
      $context,
      $blank(ColorSchemesNegotiator::class),
      $this->createStub(LibraryDiscoveryInterface::class),
      $this->createStub(FileUrlGeneratorInterface::class),
      $this->createStub(LoggerInterface::class),
    );
    $this->hooks = new PreprocessHooks(
      $detection,
      $this->reader,
      $html,
      $this->pagePreprocessor,
      new Preloader($context, new UrlSafety($this->createStub(FileUrlGeneratorInterface::class)), $assets),
      new BackToTop($context),
    );
    $this->contentPreprocessor = new ContentPreprocessor($date, $attributes);
    $theme = new Extension(dirname(__DIR__, 6), 'theme', 'themes/contrib/solo/solo.info.yml');
    $themes = $this->createStub(ThemeHandlerInterface::class);
    $themes->method('themeExists')->willReturn(TRUE);
    $themes->method('getTheme')->willReturnCallback(function () use ($theme) {
      $theme->info = ['regions' => $this->themeRegions];
      return $theme;
    });
    $this->inventory = new RegionInventory($themes, $types);
  }

}
