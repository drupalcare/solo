<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Functional;

use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Guards Solo's preprocess end to end on a real page.
 *
 * WHY THIS EXISTS
 * Solo 2 ships no PHP of its own: every preprocess Solo 1 kept in solo.theme,
 * includes/ and src/Hook now lives in Solo Utilities as #[Hook] methods
 * (solo_utilities/CHANGELOG.md, "Holds all of the Solo theme's PHP"). If that
 * module's hooks stop reaching Solo's pages, the theme still returns HTTP 200
 * and quietly loses its markup.
 *
 * Two DIFFERENT assertions, because either one alone can pass while the
 * theme is broken:
 *
 *   1. REGISTRATION -- the theme registry lists Solo Utilities' preprocess
 *      for the core hooks Solo styles.
 *   2. EXECUTION -- a real page carries markup that exists only if those
 *      hooks ran: Solo's utility classes, the breadcrumb title, the sortable
 *      table heading and the three menu suggestions.
 *
 * That Solo's own src/Hook classes stay empty tombstones is
 * SoloPhpSurfaceTest's job.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
#[RunTestsInSeparateProcesses]
class SoloHookRegistrationTest extends BrowserTestBase {

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
  protected static $modules = [
    'node',
    'block',
    'user',
    'views',
    'menu_link_content',
    'solo_utilities',
  ];

  /**
   * Hooks Solo Utilities preprocesses for Solo that core always builds, so they always exist.
   */
  private const EXPECTED_PREPROCESS = [
    'html' => 'solo_utilities_preprocess_html',
    'page' => 'solo_utilities_preprocess_page',
    'node' => 'solo_utilities_preprocess_node',
    'block' => 'solo_utilities_preprocess_block',
    'menu' => 'solo_utilities_preprocess_menu',
    'field' => 'solo_utilities_preprocess_field',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->container->get('theme_installer')->install(['solo']);
    $this->config('system.theme')->set('default', 'solo')->save();
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
  }

  /**
   * Assertion 1: the theme registry registers Solo Utilities' preprocess hooks.
   */
  public function testPreprocessHooksAreRegistered(): void {
    $registry = \Drupal::service('theme.registry')->getRuntime();

    foreach (self::EXPECTED_PREPROCESS as $hook => $expected) {
      $this->assertTrue($registry->has($hook), sprintf('Theme registry has the "%s" hook.', $hook));

      $names = [];
      foreach (($registry->get($hook)['preprocess functions'] ?? []) as $fn) {
        if (is_string($fn)) {
          $names[] = $fn;
        }
        elseif (is_array($fn) && count($fn) === 2) {
          $names[] = (is_object($fn[0]) ? get_class($fn[0]) : (string) $fn[0]) . '::' . $fn[1];
        }
      }

      $this->assertContains($expected, $names, sprintf(
        'Solo Utilities registers %s for the "%s" hook. Registered: %s',
        $expected,
        $hook,
        implode(', ', $names) ?: '(none)'
      ));
    }
  }

  /**
   * Assertion 2: the preprocess hooks actually run and reach the page.
   */
  public function testHookClassesActuallyRunOnRealPage(): void {
    $node = $this->drupalCreateNode([
      'type' => 'page',
      'title' => 'Solo hook registration probe',
    ]);

    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    // A status code is not a page check -- Drupal 12 can return 200 with a PHP
    // fatal printed into the body, which is how solo_utilities' alpha1 fatal
    // hid on a test site.
    $body = $this->getSession()->getPage()->getContent();
    foreach (['Fatal error', 'Declaration of', 'Undefined array key', 'Error:'] as $needle) {
      $this->assertStringNotContainsString($needle, $body, sprintf('No "%s" in the response body.', $needle));
    }

    // Markup that exists ONLY because Solo's preprocess ran. Measured on a
    // live Solo render before being asserted here, rather than assumed.
    $this->assertStringContainsString('fs-16', $body, 'Solo\'s typography classes reached the page.');
    $this->assertStringContainsString('gap-16', $body, 'Solo\'s spacing classes reached the page.');

    // And the node itself rendered, so preprocess_node ran without dying.
    $this->assertSession()->pageTextContains('Solo hook registration probe');
  }

  /**
   * Solo's breadcrumb preprocess runs: the page title is appended.
   *
   * Core's breadcrumb on a node page is "Home" alone; the trailing title item
   * exists only because the breadcrumb preprocess (Feature\Breadcrumb) ran.
   * In Solo 1 on Drupal 12.0.0-beta1 that hook never ran and the breadcrumb
   * rendered "Home" alone, while every other assertion here stayed green.
   */
  public function testBreadcrumbPreprocessRuns(): void {
    $node = $this->drupalCreateNode([
      'type' => 'page',
      'title' => 'Solo breadcrumb probe',
    ]);

    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', 'nav.solo-breadcrumb__nav');
    $this->assertSession()->elementTextContains('css', 'nav.solo-breadcrumb__nav .solo-breadcrumb__title', 'Solo breadcrumb probe');
  }

  /**
   * Solo's views-table preprocess runs: sortable headings get their class.
   *
   * The 'sortable-heading' class is added only by the views table
   * preprocess, to header cells whose link carries an order and sort.
   * /admin/content is core's table view with such headers, rendered in Solo
   * because no admin theme is set.
   */
  public function testViewsTablePreprocessRuns(): void {
    $this->drupalCreateNode(['type' => 'page', 'title' => 'Solo table probe']);
    $this->drupalLogin($this->drupalCreateUser(['access content overview']));

    $this->drupalGet('admin/content');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', 'table th.sortable-heading');
  }

  /**
   * Solo's three menu SUGGESTION preprocesses run, and are registered once.
   *
   * Solo 1 found preprocess_menu__primary_menu, __primary_sidebar_menu and
   * __account only through the registry's scan of solo.theme; in Solo 2 they
   * are Solo Utilities #[Hook] methods. Each sets markup that exists only when
   * it ran (on a node page: the testing profile's front page is /user/login,
   * where the account template hides the login button):
   *
   * - primary menu: the "solo-menu--responsive-click" class (click_hover);
   * - primary sidebar menu: the "solo-menu--sidebar-click" class;
   * - account menu: the popup login button, and the "Log in" link removed.
   *
   * "Once" is asserted on the registry: each suggestion lists the preprocess
   * exactly once.
   */
  public function testMenuSuggestionPreprocessRuns(): void {
    $this->config('solo.settings')->set('popup_login.enabled', TRUE)->save();
    \Drupal::entityTypeManager()->getStorage('menu_link_content')->create([
      'title' => 'Solo menu probe',
      'link' => ['uri' => 'internal:/'],
      'menu_name' => 'main',
    ])->save();
    $this->drupalPlaceBlock('system_menu_block:main', ['region' => 'primary_menu', 'id' => 'solo_probe_primary']);
    $this->drupalPlaceBlock('system_menu_block:main', ['region' => 'primary_sidebar_menu', 'id' => 'solo_probe_sidebar']);
    $this->drupalPlaceBlock('system_menu_block:account', ['region' => 'header', 'id' => 'solo_probe_account']);

    $node = $this->drupalCreateNode([
      'type' => 'page',
      'title' => 'Solo menu probe page',
    ]);

    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', '.solo-menu--primary-responsive.solo-menu--responsive-click');
    $this->assertSession()->elementExists('css', '.solo-menu--primary-sidebar.solo-menu--sidebar-click');
    $this->assertSession()->elementExists('css', '.solo-account-menu .solo-popup-login__open');
    $this->assertSession()->elementNotExists('css', '.solo-account-menu a[href$="/user/login"]');

    $registry = \Drupal::service('theme.registry')->getRuntime();
    foreach (['primary_menu', 'primary_sidebar_menu', 'account'] as $suggestion) {
      $hook = 'menu__' . $suggestion;
      $this->assertTrue($registry->has($hook), sprintf('Theme registry has the "%s" hook.', $hook));
      $functions = array_filter($registry->get($hook)['preprocess functions'] ?? [], 'is_string');
      $counts = array_count_values($functions);
      $this->assertSame(1, $counts['solo_utilities_preprocess_' . $hook] ?? 0, sprintf(
        'solo_utilities_preprocess_%s is registered exactly once. Registered: %s',
        $hook,
        implode(', ', $functions)
      ));
    }
  }

}
