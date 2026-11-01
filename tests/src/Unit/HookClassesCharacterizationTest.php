<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\node\NodeInterface;
use Drupal\solo\Hook\SoloThemePreprocessHooks;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Extension\ExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Render\Markup;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Theme\ActiveTheme;
use Drupal\Core\Theme\ThemeManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

require_once __DIR__ . '/SoloCharacterizationBase.php';

/**
 * Records SoloThemePreprocessHooks as a whole (conversion rule 1 and 5).
 *
 * Covers preprocessHtml, preprocessPage and the small preprocess methods.
 * Asserts today's output, oddities included; see the batch 2 result file.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
#[RunTestsInSeparateProcesses]
class HookClassesCharacterizationTest extends SoloCharacterizationBase {

  /**
   * Theme config the stubbed config factory answers with.
   *
   * @var array<string, mixed>
   */
  protected array $config = [];

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
   * The request the hooks see.
   */
  protected Request $request;

  /**
   * Whether the current route is an admin route.
   */
  protected bool $admin = FALSE;

  /**
   * The current path.
   */
  protected string $path = '/node/1';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $theme = dirname(__DIR__, 3);
    require_once $theme . '/src/SoloIncludes.php';
    require_once $theme . '/src/Hook/SoloThemePreprocessHooks.php';
    $this->config = [];
    $this->params = [];
    $this->modules = [];
    $this->request = Request::create('/node/1');
    $stack = new RequestStack();
    $stack->push($this->request);
    $this->container->set('request_stack', $stack);
    $route = $this->createMock(RouteMatchInterface::class);
    $route->method('getRouteName')->willReturnCallback(fn() => $this->routeName);
    $route->method('getParameter')->willReturnCallback(fn(string $k) => $this->params[$k] ?? NULL);
    $this->container->set('current_route_match', $route);
    $list = $this->createMock(ExtensionList::class);
    $list->method('getPath')->willReturn('themes/contrib/solo');
    $this->container->set('extension.list.theme', $list);
    $active = $this->createMock(ActiveTheme::class);
    $active->method('getName')->willReturn('solo');
    $manager = $this->createMock(ThemeManagerInterface::class);
    $manager->method('getActiveTheme')->willReturn($active);
    $this->container->set('theme.manager', $manager);
    $handler = $this->createMock(ModuleHandlerInterface::class);
    $handler->method('moduleExists')->willReturnCallback(fn(string $m) => in_array($m, $this->modules, TRUE));
    $this->container->set('module_handler', $handler);
    $factory = $this->createMock(ConfigFactoryInterface::class);
    $factory->method('get')->willReturnCallback(function () {
      $config = $this->createMock(ImmutableConfig::class);
      $config->method('get')->willReturnCallback(fn($key = '') => $this->config[$key] ?? NULL);
      return $config;
    });
    $this->container->set('config.factory', $factory);
    $current = $this->createMock(CurrentPathStack::class);
    $current->method('getPath')->willReturnCallback(fn() => $this->path);
    $this->container->set('path.current', $current);
    $admin = $this->createMock(AdminContext::class);
    $admin->method('isAdminRoute')->willReturnCallback(fn() => $this->admin);
    $this->container->set('router.admin_context', $admin);
    $user = $this->createMock(AccountProxyInterface::class);
    $user->method('isAnonymous')->willReturn(TRUE);
    $user->method('hasPermission')->willReturn(FALSE);
    $this->container->set('current_user', $user);
  }

  /**
   * Fresh variables as preprocess_html receives them.
   */
  private function htmlVars(): array {
    return ['attributes' => ['class' => []], 'html_attributes' => new Attribute()];
  }

  /**
   * Runs preprocessHtml and returns the variables.
   */
  private function html(?array $vars = NULL): array {
    $vars ??= $this->htmlVars();
    SoloThemePreprocessHooks::preprocessHtml($vars);
    return $vars;
  }

  /**
   * Runs preprocessPage and returns the variables.
   */
  private function page(array $vars = []): array {
    SoloThemePreprocessHooks::preprocessPage($vars);
    return $vars;
  }

  /**
   * With nothing configured the document gets only the defaults.
   */
  public function testHtmlDefaults(): void {
    $this->routeName = 'view.frontpage.page_1';
    $vars = $this->html();
    $this->assertSame('/themes/contrib/solo', $vars['solo_path']);
    $this->assertFalse($vars['preloader_show']);
    $this->assertFalse($vars['back_to_top_show']);
    $this->assertSame([], $vars['back_to_top_config']);
    $this->assertSame(['slideUpSpeed' => 350, 'slideDownSpeed' => 500, 'megaMenuSpeed' => 800], $vars['#attached']['drupalSettings']['solo']);
    $this->assertArrayNotHasKey('html_head', $vars['#attached'] ?? []);
    $this->assertNull($vars['site_css_injector']);
    $this->assertSame([], $vars['enabled_skip_links'] ?? []);
    $this->assertSame([], $this->request->attributes->get('solo_body_classes') ?? []);
  }

  /**
   * Route-specific body classes, in order, and the request copy of them.
   */
  public function testHtmlBodyClasses(): void {
    $this->settings['enable_px_based_widths'] = TRUE;
    $this->routeName = 'entity.user.canonical';
    $vars = $this->html();
    $wanted = ['px', 'user-profile-page'];
    $found = array_filter($vars['attributes']['class'], fn($c) => in_array($c, $wanted, TRUE));
    $this->assertSame($wanted, array_values($found));
    $this->assertSame($vars['attributes']['class'], $this->request->attributes->get('solo_body_classes'));
  }

  /**
   * Speed presets: normal (default), fast, slow, and unknown falls to normal.
   */
  public function testHtmlSpeedPresets(): void {
    $this->routeName = 'x';
    $this->settings['site_toggle_speed'] = 'fast';
    $this->assertSame(['slideUpSpeed' => 150, 'slideDownSpeed' => 250, 'megaMenuSpeed' => 500], $this->html()['#attached']['drupalSettings']['solo']);
    $this->settings['site_toggle_speed'] = 'slow';
    $this->assertSame(['slideUpSpeed' => 500, 'slideDownSpeed' => 700, 'megaMenuSpeed' => 1400], $this->html()['#attached']['drupalSettings']['solo']);
    $this->settings['site_toggle_speed'] = 'bogus';
    $this->assertSame(350, $this->html()['#attached']['drupalSettings']['solo']['slideUpSpeed']);
  }

  /**
   * Existing drupalSettings.solo keys are kept; the speeds win on a clash.
   */
  public function testHtmlMergesExistingDrupalSettings(): void {
    $this->routeName = 'x';
    $vars = $this->htmlVars();
    $vars['#attached']['drupalSettings']['solo'] = ['keyboardMenus' => ['main'], 'slideUpSpeed' => 1];
    $out = $this->html($vars);
    $expected = [
      'keyboardMenus' => ['main'],
      'slideUpSpeed' => 350,
      'slideDownSpeed' => 500,
      'megaMenuSpeed' => 800,
    ];
    $this->assertSame($expected, $out['#attached']['drupalSettings']['solo']);
  }

  /**
   * Google font: only a valid absolute URL becomes a link tag; CSS injector raw.
   */
  public function testHtmlHeadAttachments(): void {
    $this->routeName = 'x';
    $this->settings['site_import_google_font'] = 'https://fonts.googleapis.com/css2?family=Inter';
    $this->settings['site_css_injector'] = "a  {  color : red;  }\n/* c */ b{margin:0}";
    $head = $this->html()['#attached']['html_head'];
    $this->assertCount(2, $head);
    $this->assertSame('google_font', $head[0][1]);
    $expected = [
      'href' => 'https://fonts.googleapis.com/css2?family=Inter',
      'rel' => 'stylesheet',
      'type' => 'text/css',
    ];
    $this->assertSame($expected, $head[0][0]['#attributes']);
    $this->assertSame('inline_css', $head[1][1]);
    $this->assertSame('solo-css-injector', $head[1][0]['#attributes']['id']);
    $this->assertInstanceOf(Markup::class, $head[1][0]['#value']);
    $this->assertSame(_solo_minify_inline_css("a  {  color : red;  }\n/* c */ b{margin:0}"), (string) $head[1][0]['#value']);
    // A value that is not a valid URL adds no link, but the variable is kept.
    $this->settings['site_import_google_font'] = 'not a url';
    unset($this->settings['site_css_injector']);
    $vars = $this->html();
    $this->assertArrayNotHasKey('html_head', $vars['#attached'] ?? []);
    $this->assertSame('not a url', $vars['site_import_google_font']);
  }

  /**
   * Cookies module attaches its library; the node route adds the node class.
   */
  public function testHtmlCookiesLibrary(): void {
    $this->routeName = 'x';
    $this->assertArrayNotHasKey('library', $this->html()['#attached'] ?? []);
    $this->modules = ['cookies'];
    $this->assertSame(['solo/solo-cookies'], $this->html()['#attached']['library']);
  }

  /**
   * The admin route adds admin-context after the other body classes.
   */
  public function testHtmlAdminContext(): void {
    $this->routeName = 'x';
    $this->admin = TRUE;
    $this->assertContains('admin-context', $this->html()['attributes']['class']);
  }

  /**
   * Preloader on: library, html class, meta flag, cache tag.
   */
  public function testHtmlPreloaderOn(): void {
    $this->routeName = 'x';
    $this->config['preloader_enabled'] = TRUE;
    $this->config['preloader_logo_use_theme'] = TRUE;
    $vars = $this->html();
    $this->assertTrue($vars['preloader_show']);
    $this->assertContains('solo/solo-preloader', $vars['#attached']['library']);
    $this->assertStringContainsString('solo-preloader-enabled', (string) $vars['html_attributes']);
    $this->assertSame('solo_preloader_flag', $vars['#attached']['html_head'][0][1]);
    $this->assertContains('config:solo.settings', $vars['#cache']['tags']);
  }

  /**
   * Page: with nothing configured, the layout slots are NULL and counts zero.
   */
  public function testPageDefaults(): void {
    $vars = $this->page();
    foreach (['top', 'main', 'bottom', 'footer'] as $region) {
      $this->assertNull($vars["{$region}_layout"]);
    }
    $this->assertSame('', $vars['footer_copyright_formatted_rendered']);
    $this->assertNull($vars['site_width_class']);
    $this->assertArrayNotHasKey('main_layout_order', $vars);
    $this->assertSame(['solo/solo-system-messages-dark'], $vars['#attached']['library']);
    $this->assertArrayNotHasKey('keyboardMenus', $vars['#attached']['drupalSettings']['solo'] ?? []);
  }

  /**
   * Column counts choose the layout class; defaults apply when unset.
   */
  public function testPageLayoutByCount(): void {
    $page = [
      'top_first' => ['x'],
      'sidebar_first' => ['x'],
      'content' => ['x'],
      'bottom_first' => ['x'],
      'bottom_second' => ['x'],
      'bottom_third' => ['x'],
      'bottom_fourth' => ['x'],
    ];
    $vars = $this->page(['page' => $page]);
    $this->assertSame('solo-col-1', $vars['top_layout']);
    // Two main columns without sidebar_second: the smart default is 1-2.
    $this->assertSame('solo-col-1-2', $vars['main_layout']);
    $this->assertNull($vars['footer_layout']);
    $this->assertSame('solo-col-1-1-1-1', $vars['bottom_layout']);
    // Smart sidebar: no sidebar_first with two columns uses the 2-1 default.
    $vars = $this->page(['page' => ['content' => ['x'], 'sidebar_second' => ['x']]]);
    $this->assertSame('solo-col-2-1', $vars['main_layout']);
    $vars = $this->page(['page' => ['sidebar_first' => ['x'], 'content' => ['x']]]);
    $this->assertSame('solo-col-1-2', $vars['main_layout']);
    // Three main columns default to 1-2-1; three top columns to 1-1-1.
    $regions = [
      'sidebar_first' => ['x'],
      'content' => ['x'],
      'sidebar_second' => ['x'],
      'top_first' => ['x'],
      'top_second' => ['x'],
      'top_third' => ['x'],
    ];
    $vars = $this->page(['page' => $regions]);
    $this->assertSame('solo-col-1-2-1', $vars['main_layout']);
    $this->assertSame('solo-col-1-1-1', $vars['top_layout']);
    // A stored setting wins over the smart default.
    $this->settings['main_2col'] = 'solo-col-3-1';
    $vars = $this->page(['page' => ['content' => ['x'], 'sidebar_second' => ['x']]]);
    $this->assertSame('solo-col-3-1', $vars['main_layout']);
  }

  /**
   * Per-content-type layout override applies only when enabled and non-empty.
   */
  public function testPagePerTypeOverride(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->method('bundle')->willReturn('article');
    $this->params['node'] = $node;
    $page = ['page' => ['top_first' => ['x'], 'top_second' => ['x']]];
    $this->settings['solo_layouts'] = ['top' => ['2col' => ['article' => 'solo-col-9-1']]];
    $this->assertSame('solo-col-1-1', $this->page($page)['top_layout']);
    $this->settings['enable_per_type_layout_top'] = TRUE;
    $this->assertSame('solo-col-9-1', $this->page($page)['top_layout']);
    $this->settings['solo_layouts'] = ['top' => ['2col' => ['article' => '']]];
    $this->assertSame('solo-col-1-1', $this->page($page)['top_layout']);
  }

  /**
   * Size, order, centre and breakpoint settings become classes and libraries.
   */
  public function testPageSettingsToClassesAndLibraries(): void {
    $this->settings += [
      'site_mobile_layout_order' => 'content_first',
      'site_name_font_size_s' => '14',
      'site_name_font_size_l' => '30',
      'site_pagetitle_font_size_s' => '20',
      'site_pagetitle_font_size_l' => '40',
      'site_pagetitle_center' => TRUE,
      'site_global_breakpoints' => 'md',
      'site_menu_breakpoints' => 'lg',
      'keyboard_navigation_menus' => ['main' => 'main', 'footer' => 0],
      'system_messages_disable_dark' => TRUE,
    ];
    $vars = $this->page();
    $this->assertSame('content-first', $vars['main_layout_order']);
    $expected = [
      'name_size_s' => 'name-s-14',
      'name_size_l' => 'name-l-30',
      'title_size_s' => 'title-s-20',
      'title_size_l' => 'title-l-40',
    ];
    $this->assertSame($expected, array_intersect_key($vars, $expected));
    $this->assertSame('pt-center', $vars['site_pagetitle_center']);
    $this->assertSame(['solo/solo-global-breakpoints-md', 'solo/solo-menu-breakpoints-lg', 'solo/solo-menu-keyboard'], $vars['#attached']['library']);
    $this->assertSame(['main'], $vars['#attached']['drupalSettings']['solo']['keyboardMenus']);
  }

  /**
   * Login page hides both sidebars and adds its library; other paths do not.
   */
  public function testPageLoginPage(): void {
    $this->settings['site_login_page_on'] = TRUE;
    $page = ['page' => ['sidebar_first' => ['x'], 'sidebar_second' => ['x'], 'content' => ['x']]];
    $this->path = '/user/login';
    $vars = $this->page($page);
    $this->assertArrayNotHasKey('sidebar_first', $vars['page']);
    $this->assertArrayNotHasKey('sidebar_second', $vars['page']);
    $this->assertContains('solo/solo-user-login-custom', $vars['#attached']['library']);
    $this->path = '/user/login/extra';
    $this->assertArrayHasKey('sidebar_first', $this->page($page)['page']);
  }

  /**
   * Width: the global width is used unless solo_utilities is on and overrides.
   */
  public function testPageWidthResolution(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->method('bundle')->willReturn('article');
    $this->params['node'] = $node;
    $this->settings += [
      'site_global_width' => 'w-global',
      'enable_custom_widths' => TRUE,
      'site_widths' => ['article' => 'w-article'],
    ];
    $this->assertSame('w-global', $this->page()['site_width_class']);
    $this->modules = ['solo_utilities'];
    $this->assertSame('w-article', $this->page()['site_width_class']);
    $this->settings['site_widths'] = ['page' => 'w-page'];
    $this->assertSame('w-global', $this->page()['site_width_class']);
  }

  /**
   * Popup login: drupalSettings block with defaults and an RGB from valid hex.
   */
  public function testPagePopupLogin(): void {
    $this->settings += ['header_popup_login' => TRUE, 'settings_popup_login_block___r_bg' => '#ff8000'];
    $popup = $this->page()['#attached']['drupalSettings']['solo']['popupLogin'];
    $this->assertSame([
      'enabled' => TRUE,
      'useInlineStyles' => FALSE,
      'animationDuration' => 300,
      'closeOnEscape' => FALSE,
      'closeOnOutsideClick' => FALSE,
      'focusTrap' => FALSE,
      'announceToScreenReaders' => FALSE,
      'returnFocusOnClose' => FALSE,
      'customTriggers' => '',
      'zIndex' => 10000,
      'overlayOpacity' => 50,
      'backgroundColorRgb' => '255,128,0',
    ], $popup);
    $this->settings['settings_popup_login_block___r_bg'] = '#fff';
    $this->assertSame('', $this->page()['#attached']['drupalSettings']['solo']['popupLogin']['backgroundColorRgb']);
    unset($this->settings['header_popup_login']);
    $this->assertArrayNotHasKey('popupLogin', $this->page()['#attached']['drupalSettings']['solo'] ?? []);
  }

  /**
   * Sidebar branding copies the logo and name; alt falls back to Home.
   */
  public function testPageSidebarBranding(): void {
    $this->settings += ['primary_sidebar_menu_branding' => TRUE, 'logo.url' => '/logo.svg'];
    $this->config['name'] = 'My Site';
    $vars = $this->page();
    $this->assertSame('My Site', $vars['site_name']);
    $this->assertSame('Home', $vars['logo_alt']);
    $this->settings['header_logo_image_alt'] = 'Brand';
    $this->assertSame('Brand', $this->page()['logo_alt']);
  }

  /**
   * Small preprocessors: filter caption, node created prefix, maintenance.
   */
  public function testSmallPreprocessors(): void {
    $vars = [];
    SoloThemePreprocessHooks::preprocessFilterCaption($vars);
    $this->assertSame(' caption', $vars['classes']);
    $vars = ['classes' => 'a'];
    SoloThemePreprocessHooks::preprocessFilterCaption($vars);
    $this->assertSame('a caption', $vars['classes']);
    $vars = ['items' => [['content' => ['#prefix' => 'x', '#markup' => 'y']], ['content' => ['#markup' => 'z']]]];
    SoloThemePreprocessHooks::preprocessFieldNodeCreated($vars);
    $this->assertSame([['content' => ['#markup' => 'y']], ['content' => ['#markup' => 'z']]], $vars['items']);
    $vars = [];
    SoloThemePreprocessHooks::preprocessSearchResult($vars);
    $this->assertSame([], $vars);
  }

}
