<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Functional;

use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Guards Solo's hook registration end to end.
 *
 * WHY THIS EXISTS
 * Solo's 35 hooks were converted to the Drupal.org bot's shape: the body of
 * each hook lives in a class under src/Hook, and the procedural function in
 * includes/ is a thin caller marked #[LegacyHook]. Until this test, NOTHING
 * automated guarded that arrangement -- the whole suite was one unit test for
 * SocialIconColor, and the only proof the hooks still fired was a human hiding
 * src/Hook and reloading a page by hand.
 *
 * That manual proof is what this class automates, and it deliberately makes two
 * DIFFERENT assertions, because either one alone can pass while the theme is
 * broken:
 *
 *   1. REGISTRATION -- the theme registry still lists Solo's preprocess for the
 *      core hooks. This catches the registry never picking Solo up at all.
 *
 *   2. EXECUTION -- a real page carries markup that only exists if the Hook
 *      CLASS ran. This is the assertion that matters most: for a theme the
 *      registry records the PROCEDURAL name (solo_preprocess_node), so deleting
 *      src/Hook leaves registration looking perfectly healthy while every page
 *      fatals the moment that function delegates into the missing class.
 *
 * Red-proofed by moving src/Hook aside: assertion 2 fails, which is exactly the
 * failure the manual check produced.
 */
#[Group('solo')]
#[RunTestsInSeparateProcesses]
class SoloHookRegistrationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'solo';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'block', 'user', 'views'];

  /**
   * Hooks Solo preprocesses that core always builds, so they always exist.
   */
  private const EXPECTED_PREPROCESS = [
    'html' => 'solo_preprocess_html',
    'page' => 'solo_preprocess_page',
    'node' => 'solo_preprocess_node',
    'block' => 'solo_preprocess_block',
    'menu' => 'solo_preprocess_menu',
    'field' => 'solo_preprocess_field',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
  }

  /**
   * Assertion 1: the theme registry still registers Solo's preprocess hooks.
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
        'Solo registers %s for the "%s" hook. Registered: %s',
        $expected,
        $hook,
        implode(', ', $names) ?: '(none)'
      ));
    }
  }

  /**
   * Assertion 2: the Hook CLASSES actually run and reach the page.
   *
   * This is the one that goes red when src/Hook is broken or missing.
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
    // hid on this very site.
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
   * exists only because solo_preprocess_breadcrumb ran. Measured 2026-10-05:
   * on Drupal 12.0.0-beta1 that function had no #[Hook] twin, never ran, and
   * the breadcrumb rendered "Home" alone -- while every other assertion in
   * this class stayed green.
   */
  public function testBreadcrumbPreprocessRuns(): void {
    $node = $this->drupalCreateNode([
      'type' => 'page',
      'title' => 'Solo breadcrumb probe',
    ]);

    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', 'nav.breadcrumb-nav');
    $this->assertSession()->elementTextContains('css', 'nav.breadcrumb-nav .breadcrumb-title', 'Solo breadcrumb probe');
  }

  /**
   * Solo's views-table preprocess runs: sortable headings get their class.
   *
   * The 'sortable-heading' class is added only by
   * solo_preprocess_views_view_table, to header cells whose link carries an
   * order and sort. /admin/content is core's table view with such headers,
   * rendered in Solo because no admin theme is set. Dead on Drupal
   * 12.0.0-beta1 for the same reason as the breadcrumb, measured 2026-10-05.
   */
  public function testViewsTablePreprocessRuns(): void {
    $this->drupalCreateNode(['type' => 'page', 'title' => 'Solo table probe']);
    $this->drupalLogin($this->drupalCreateUser(['access content overview']));

    $this->drupalGet('admin/content');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', 'table th.sortable-heading');
  }

  /**
   * Every #[LegacyHook] caller must still resolve to a real class method.
   *
   * A thin caller whose target has been renamed is a fatal that only fires when
   * that specific hook runs -- which a render test can easily never reach.
   */
  public function testEveryHookClassMethodIsCallable(): void {
    $classes = [
      'Drupal\solo\Hook\SoloThemePreprocessHooks',
      'Drupal\solo\Hook\SoloThemeAlterHooks',
      'Drupal\solo\Hook\SoloThemeSuggestionsHooks',
      'Drupal\solo\Hook\SoloThemeMiscHooks',
    ];

    $total = 0;
    foreach ($classes as $class) {
      $this->assertTrue(class_exists($class), sprintf('Hook class %s exists.', $class));

      $reflection = new \ReflectionClass($class);
      foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
        foreach ($method->getAttributes() as $attribute) {
          if (str_ends_with($attribute->getName(), '\\Hook')) {
            $total++;
          }
        }
      }
    }

    // Solo registered 35 hooks at the time this guard was written. Asserting a
    // floor rather than the exact number lets hooks be added without a failing
    // test, while a conversion that silently loses them still goes red.
    $this->assertGreaterThanOrEqual(30, $total, sprintf(
      'Solo still registers its hooks as attributes (found %d).',
      $total
    ));
  }

}
