<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\Core\Template\Attribute;
use Drupal\node\NodeInterface;
use Drupal\solo_utilities\Feature\SearchResult;
use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/SoloCharacterizationBase.php';
require_once __DIR__ . '/SoloHookHarness.php';

/**
 * Records Solo's html, page and small preprocess output (conversion rule 1, 5).
 *
 * WHY THIS EXISTS
 * These cases recorded what Solo 1's SoloThemePreprocessHooks produced. Solo 2's
 * class of that name is an empty tombstone; the work is done by Solo Utilities'
 * PreprocessHooks and the feature classes it calls. Each case runs the 1.x
 * input through them (settings keyed by their 2.0 dotted path, SoloHookHarness)
 * and keeps the 1.x answer, or states why the 2.0 answer differs.
 * Settings an install file now ships are read from that file when unset
 * (BoundThemeSettings::get()), so "nothing configured" is "the shipped
 * defaults" in 2.0.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
class HookClassesCharacterizationTest extends SoloCharacterizationBase {

  use SoloHookHarness;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->wireSolo();
  }

  /**
   * Fresh variables as preprocess_html receives them.
   *
   * @return array<string, mixed>
   *   The variables.
   */
  private function htmlVars(): array {
    return ['attributes' => ['class' => []], 'html_attributes' => new Attribute()];
  }

  /**
   * Runs preprocess_html and returns the variables.
   *
   * @param array<string, mixed>|null $vars
   *   Starting variables.
   *
   * @return array<string, mixed>
   *   The variables.
   */
  private function html(?array $vars = NULL): array {
    $vars ??= $this->htmlVars();
    $this->hooks->preprocessHtml($vars);
    return $vars;
  }

  /**
   * Runs preprocess_page and returns the variables.
   *
   * @param array<string, mixed> $vars
   *   Starting variables.
   *
   * @return array<string, mixed>
   *   The variables.
   */
  private function page(array $vars = []): array {
    $this->hooks->preprocessPage($vars);
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
    // CHANGED ON PURPOSE: an unset key answers Solo's shipped default
    // (BoundThemeSettings::get() docblock; config/install/solo.settings.yml).
    $this->assertSame([
      'skip_header_content' => TRUE,
      'skip_navigation_content' => TRUE,
      'skip_main_content' => TRUE,
      'skip_footer_content' => TRUE,
    ], $vars['enabled_skip_links']);
    $this->assertSame([], $this->request->attributes->get('solo_body_classes') ?? []);
  }

  /**
   * Route-specific body classes, in order, and the request copy of them.
   */
  public function testHtmlBodyClasses(): void {
    $this->settings['layout.width.use_pixels'] = TRUE;
    $this->routeName = 'entity.user.canonical';
    $vars = $this->html();
    // CHANGED ON PURPOSE: "Every Solo class and ID now starts with solo-"
    // (solo/CHANGELOG.md, Next release).
    $wanted = ['solo-body--px-widths', 'solo-body--user-profile'];
    $classes = $vars['attributes']['class'];
    $found = array_filter($classes, fn($c) => in_array($c, $wanted, TRUE));
    $this->assertSame($wanted, array_values($found));
    $this->assertSame($classes, $this->request->attributes->get('solo_body_classes'));
  }

  /**
   * Speed presets: normal (default), fast, slow, and unknown falls to normal.
   */
  public function testHtmlSpeedPresets(): void {
    $this->routeName = 'x';
    $this->settings['menus.toggle_speed'] = 'fast';
    $this->assertSame(['slideUpSpeed' => 150, 'slideDownSpeed' => 250, 'megaMenuSpeed' => 500], $this->html()['#attached']['drupalSettings']['solo']);
    $this->settings['menus.toggle_speed'] = 'slow';
    $this->assertSame(['slideUpSpeed' => 500, 'slideDownSpeed' => 700, 'megaMenuSpeed' => 1400], $this->html()['#attached']['drupalSettings']['solo']);
    $this->settings['menus.toggle_speed'] = 'bogus';
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
    $this->settings['typography.imported_google_font'] = 'https://fonts.googleapis.com/css2?family=Inter';
    $this->settings['custom_code.inline_css'] = "a  {  color : red;  }\n/* c */ b{margin:0}";
    $head = $this->html()['#attached']['html_head'];
    $this->assertCount(1, $head);
    $this->assertSame('google_font', $head[0][1]);
    $expected = [
      'href' => 'https://fonts.googleapis.com/css2?family=Inter',
      'rel' => 'stylesheet',
      'type' => 'text/css',
    ];
    $this->assertSame($expected, $head[0][0]['#attributes']);
    // The injector's tag is attached by AssetAttacher from the built assets
    // (inlineCss), minified there; the hook itself only passes the value on.
    $assets = $this->cssBuilder->build('solo');
    $this->assertSame($this->cssBuilder->minify("a  {  color : red;  }\n/* c */ b{margin:0}"), $assets->inlineCss);
    // A value that is not a valid URL adds no link, but the variable is kept.
    $this->settings['typography.imported_google_font'] = 'not a url';
    unset($this->settings['custom_code.inline_css']);
    $vars = $this->html();
    $this->assertArrayNotHasKey('html_head', $vars['#attached'] ?? []);
    $this->assertSame('not a url', $vars['site_import_google_font']);
  }

  /**
   * Cookies module attaches its library.
   */
  public function testHtmlCookiesLibrary(): void {
    $this->routeName = 'x';
    $this->assertArrayNotHasKey('library', $this->html()['#attached'] ?? []);
    $this->modules = ['cookies'];
    $this->assertSame(['solo/theme-cookies'], $this->html()['#attached']['library']);
  }

  /**
   * The admin route adds the admin class after the other body classes.
   */
  public function testHtmlAdminContext(): void {
    $this->routeName = 'x';
    $this->admin = TRUE;
    // CHANGED ON PURPOSE: the class rename (solo/CHANGELOG.md, Next release).
    $this->assertContains('solo-body--admin-context', $this->html()['attributes']['class']);
  }

  /**
   * Preloader on: library, html class, meta flag, cache tag.
   */
  public function testHtmlPreloaderOn(): void {
    $this->routeName = 'x';
    $this->settings['preloader.enabled'] = TRUE;
    $this->settings['preloader.logo.use_theme'] = TRUE;
    // The shipped defaults hide the preloader from signed-in users and admin
    // routes; this request is anonymous on a non-admin route.
    $vars = $this->html();
    $this->assertTrue($vars['preloader_show']);
    $this->assertContains('solo/component-preloader', $vars['#attached']['library']);
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
    // CHANGED ON PURPOSE: an unset key answers Solo's shipped default
    // (BoundThemeSettings::get() docblock; config/install/solo.settings.yml).
    $this->assertSame('sw-1600', $vars['site_width_class']);
    $this->assertArrayNotHasKey('main_layout_order', $vars);
    // CHANGED ON PURPOSE: the breakpoint settings answer their shipped
    // defaults too, so their libraries attach (same citation as above).
    $this->assertSame([
      'solo/breakpoint-page-sb-992',
      'solo/breakpoint-menu-mn-992',
      'solo/component-system-messages-dark',
    ], $vars['#attached']['library']);
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
    $this->settings['region_groups.main.columns_2'] = 'solo-col-3-1';
    $vars = $this->page(['page' => ['content' => ['x'], 'sidebar_second' => ['x']]]);
    $this->assertSame('solo-col-3-1', $vars['main_layout']);
  }

  /**
   * Per-content-type layout override applies only when enabled and non-empty.
   */
  public function testPagePerTypeOverride(): void {
    $node = $this->createStub(NodeInterface::class);
    $node->method('bundle')->willReturn('article');
    $node->method('getCacheContexts')->willReturn([]);
    $node->method('getCacheTags')->willReturn([]);
    $node->method('getCacheMaxAge')->willReturn(-1);
    $this->params['node'] = $node;
    $page = ['page' => ['top_first' => ['x'], 'top_second' => ['x']]];
    $this->settings['region_groups.top.per_content_type.columns_2.article'] = 'solo-col-9-1';
    $this->assertSame('solo-col-1-1', $this->page($page)['top_layout']);
    $this->settings['region_groups.top.per_content_type_enabled'] = TRUE;
    $this->assertSame('solo-col-9-1', $this->page($page)['top_layout']);
    $this->settings['region_groups.top.per_content_type.columns_2.article'] = '';
    $this->assertSame('solo-col-1-1', $this->page($page)['top_layout']);
  }

  /**
   * Size, order, center and breakpoint settings become classes and libraries.
   */
  public function testPageSettingsToClassesAndLibraries(): void {
    $this->settings += [
      'layout.mobile_order' => 'content_first',
      'header.site_name.font_size_small' => '14',
      'header.site_name.font_size_large' => '30',
      'page_title.font_size_small' => '20',
      'page_title.font_size_large' => '40',
      'page_title.centered' => TRUE,
      'layout.breakpoints.page' => 'md',
      'layout.breakpoints.menu' => 'lg',
      'menus.keyboard_navigation' => ['main' => 'main', 'footer' => 0],
      'regions.system_messages.disable_dark' => TRUE,
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
    $this->assertSame(['solo/breakpoint-page-md', 'solo/breakpoint-menu-lg', 'solo/component-menu-keyboard'], $vars['#attached']['library']);
    $this->assertSame(['main'], $vars['#attached']['drupalSettings']['solo']['keyboardMenus']);
  }

  /**
   * Login page hides both sidebars and adds its library; other paths do not.
   */
  public function testPageLoginPage(): void {
    $this->settings['layout.centered_login_page'] = TRUE;
    $page = ['page' => ['sidebar_first' => ['x'], 'sidebar_second' => ['x'], 'content' => ['x']]];
    $this->path = '/user/login';
    $vars = $this->page($page);
    $this->assertArrayNotHasKey('sidebar_first', $vars['page']);
    $this->assertArrayNotHasKey('sidebar_second', $vars['page']);
    $this->assertContains('solo/layout-user-login', $vars['#attached']['library']);
    $this->path = '/user/login/extra';
    $this->assertArrayHasKey('sidebar_first', $this->page($page)['page']);
  }

  /**
   * Width: the global width is used unless a per-type width is on and set.
   */
  public function testPageWidthResolution(): void {
    $node = $this->createStub(NodeInterface::class);
    $node->method('bundle')->willReturn('article');
    $node->method('getCacheContexts')->willReturn([]);
    $node->method('getCacheTags')->willReturn([]);
    $node->method('getCacheMaxAge')->willReturn(-1);
    $this->params['node'] = $node;
    $this->settings += [
      'layout.width.global' => 'w-global',
      'layout.width.per_content_type' => ['article' => 'w-article'],
    ];
    $this->assertSame('w-global', $this->page()['site_width_class']);
    // CHANGED ON PURPOSE: 1.x also needed the solo_utilities module enabled
    // (this assertion was 'w-global' until the module was listed); Solo 2
    // requires that module, so only the setting decides (solo_utilities/
    // CHANGELOG.md: "Solo 2 requires this module").
    $this->settings['layout.width.per_content_type_enabled'] = TRUE;
    $this->assertSame('w-article', $this->page()['site_width_class']);
    $this->settings['layout.width.per_content_type'] = ['page' => 'w-page'];
    $this->assertSame('w-global', $this->page()['site_width_class']);
  }

  /**
   * Popup login: drupalSettings block with defaults and an RGB from valid hex.
   */
  public function testPagePopupLogin(): void {
    $this->settings += ['popup_login.enabled' => TRUE, 'regions.popup_login_block.colors.background' => '#ff8000'];
    $popup = $this->page()['#attached']['drupalSettings']['solo']['popupLogin'];
    $this->assertSame([
      'enabled' => TRUE,
      'useInlineStyles' => FALSE,
      'animationDuration' => 300,
      // CHANGED ON PURPOSE: the five unset flags answer the shipped default,
      // TRUE (BoundThemeSettings::get(); solo.settings.yml popup_login).
      'closeOnEscape' => TRUE,
      'closeOnOutsideClick' => TRUE,
      'focusTrap' => TRUE,
      'announceToScreenReaders' => TRUE,
      'returnFocusOnClose' => TRUE,
      'customTriggers' => '',
      'zIndex' => 10000,
      'overlayOpacity' => 50,
      // CHANGED ON PURPOSE: an "r, g, b" triplet (CssValueValidator::
      // rgbTriplet() docblock); the script puts it inside rgba().
      'backgroundColorRgb' => '255, 128, 0',
    ], $popup);
    $this->settings['regions.popup_login_block.colors.background'] = '#fff';
    // CHANGED ON PURPOSE: a short hex is expanded first (rgbTriplet() docblock:
    // 1.x misread it, so it printed nothing).
    $this->assertSame('255, 255, 255', $this->page()['#attached']['drupalSettings']['solo']['popupLogin']['backgroundColorRgb']);
    unset($this->settings['popup_login.enabled']);
    $this->assertArrayNotHasKey('popupLogin', $this->page()['#attached']['drupalSettings']['solo'] ?? []);
  }

  /**
   * Sidebar branding copies the logo and name; alt falls back to Home.
   */
  public function testPageSidebarBranding(): void {
    $this->settings += ['menus.primary_sidebar.branding' => TRUE, 'logo.url' => '/logo.svg'];
    $this->siteName = 'My Site';
    $vars = $this->page();
    $this->assertSame('My Site', $vars['site_name']);
    $this->assertSame('Home', $vars['logo_alt']);
    $this->settings['header.logo_alt'] = 'Brand';
    $this->assertSame('Brand', $this->page()['logo_alt']);
  }

  /**
   * Small preprocessors: filter caption, node created prefix, search result.
   */
  public function testSmallPreprocessors(): void {
    $vars = [];
    $this->contentPreprocessor->preprocessFilterCaption($vars);
    $this->assertSame(' caption', $vars['classes']);
    $vars = ['classes' => 'a'];
    $this->contentPreprocessor->preprocessFilterCaption($vars);
    $this->assertSame('a caption', $vars['classes']);
    $vars = ['items' => [['content' => ['#prefix' => 'x', '#markup' => 'y']], ['content' => ['#markup' => 'z']]]];
    $this->contentPreprocessor->preprocessFieldNodeCreated($vars);
    $this->assertSame([['content' => ['#markup' => 'y']], ['content' => ['#markup' => 'z']]], $vars['items']);
    // With search.teaser_mode off the class never touches its entity manager.
    $search = (new \ReflectionClass(SearchResult::class))->newInstanceWithoutConstructor();
    $vars = [];
    $search->preprocess($vars, $this->reader->forTheme('solo'));
    // CHANGED ON PURPOSE: the result now carries the settings' cache tags, so
    // a changed setting refreshes cached results; nothing else is added.
    $this->assertSame(['#cache'], array_keys($vars));
    $this->assertContains('config:solo.settings', $vars['#cache']['tags']);
  }

}
