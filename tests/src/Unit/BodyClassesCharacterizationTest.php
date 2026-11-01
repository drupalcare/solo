<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/SoloCharacterizationBase.php';

/**
 * Records the body and html class helpers (conversion rule 4).
 *
 * Covers: _add_node_id_to_body_class, _add_taxonomy_term_to_body_class,
 * _add_user_path_to_body_class, _add_view_id_to_body_class,
 * _add_admin_context_to_body_class, _get_theme_setting_class,
 * _get_html_classes, _get_enabled_skip_links.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * Each test runs in its own process: other Solo tests may already have loaded
 * includes/ from another path (a site's copy), and a function cannot be
 * declared twice.
 *
 * @group solo
 */
#[Group('solo')]
#[RunTestsInSeparateProcesses]
class BodyClassesCharacterizationTest extends SoloCharacterizationBase {

  /**
   * A route match answering one parameter.
   */
  private function routeMatch(string $name, mixed $value): RouteMatchInterface {
    $route_match = $this->createMock(RouteMatchInterface::class);
    $route_match->method('getParameter')->willReturnCallback(fn(string $key) => $key === $name ? $value : NULL);
    return $route_match;
  }

  /**
   * An entity stub with an id and a bundle.
   */
  private function entity(mixed $id, mixed $bundle = NULL): object {
    return new class($id, $bundle) {

      /**
       * Constructs the stub.
       */
      public function __construct(private mixed $id, private mixed $bundle) {}

      /**
       * Id stub.
       */
      public function id(): mixed {
        return $this->id;
      }

      /**
       * Bundle stub.
       */
      public function bundle(): mixed {
        return $this->bundle;
      }

    };
  }

  /**
   * The node id is added as path-node-N.
   */
  public function testNodeIdClass(): void {
    $variables = [];
    _add_node_id_to_body_class($this->routeMatch('node', $this->entity(42)), $variables);
    $this->assertSame(['path-node-42'], $variables['attributes']['class']);
  }

  /**
   * Existing classes are kept; a class string becomes the first list item.
   */
  public function testNodeIdClassKeepsExisting(): void {
    $variables = ['attributes' => ['class' => 'front']];
    _add_node_id_to_body_class($this->routeMatch('node', $this->entity(7)), $variables);
    $this->assertSame(['front', 'path-node-7'], $variables['attributes']['class']);
  }

  /**
   * No node, or a node whose id is empty, adds nothing and creates nothing.
   */
  public function testNodeIdClassAbsent(): void {
    $variables = [];
    _add_node_id_to_body_class($this->routeMatch('node', NULL), $variables);
    _add_node_id_to_body_class($this->routeMatch('node', $this->entity(NULL)), $variables);
    // ODDITY: id 0 and '0' are falsy, so they add nothing either.
    _add_node_id_to_body_class($this->routeMatch('node', $this->entity(0)), $variables);
    $this->assertSame([], $variables);
  }

  /**
   * A term adds its id and its cleaned vocabulary id.
   */
  public function testTaxonomyTermClasses(): void {
    $variables = [];
    _add_taxonomy_term_to_body_class($this->routeMatch('taxonomy_term', $this->entity(5, 'Blog_Tags')), $variables);
    $this->assertSame(['path-taxonomy-term-5', 'path-vocabulary-Blog-Tags'], $variables['attributes']['class']);
  }

  /**
   * A term with a bundle but no id still gets the vocabulary class.
   */
  public function testTaxonomyTermBundleOnly(): void {
    $variables = [];
    _add_taxonomy_term_to_body_class($this->routeMatch('taxonomy_term', $this->entity(NULL, 'tags')), $variables);
    $this->assertSame(['path-vocabulary-tags'], $variables['attributes']['class']);

    $variables = [];
    _add_taxonomy_term_to_body_class($this->routeMatch('taxonomy_term', NULL), $variables);
    $this->assertSame([], $variables);
  }

  /**
   * Current paths and the user class they give.
   *
   * @return array<string, array{string, list<string>}>
   *   The path and the classes.
   */
  public static function userPathProvider(): array {
    return [
      'profile' => ['/user/7', ['path-user-7']],
      'sub page uses the second segment' => ['/user/7/edit', ['path-user-7']],
      'no leading slash' => ['user/9', ['path-user-9']],
      'user alone adds nothing' => ['/user', []],
      'other path adds nothing' => ['/node/7', []],
      'login adds a class' => ['/user/login', ['path-user-login']],
      // ODDITY: the segment is not validated, so any text becomes a class.
      'raw text segment' => ['/user/a b', ['path-user-a b']],
      // ODDITY: an empty second segment still gives a class.
      'trailing slash empty segment' => ['/user/', ['path-user-']],
    ];
  }

  /**
   * The user path class comes from the second path segment, as given.
   *
   * @param string $path
   *   The current path.
   * @param list<string> $expected
   *   The classes.
   */
  #[DataProvider('userPathProvider')]
  public function testUserPathClass(string $path, array $expected): void {
    $stack = $this->createMock(CurrentPathStack::class);
    $stack->method('getPath')->willReturn($path);
    $this->container->set('path.current', $stack);
    $variables = [];
    _add_user_path_to_body_class($variables);
    $this->assertSame($expected, $variables['attributes']['class'] ?? []);
  }

  /**
   * The view class is one list item holding two classes.
   */
  public function testViewIdClass(): void {
    $variables = [];
    _add_view_id_to_body_class($this->routeMatch('view_id', 'frontpage_Items'), $variables);
    // ODDITY: case is kept (cleanCssIdentifier does not lowercase), and
    // 'views-page path-view-...' is ONE array element with a space.
    $this->assertSame(['views-page path-view-frontpage-Items'], $variables['attributes']['class']);

    $variables = [];
    _add_view_id_to_body_class($this->routeMatch('view_id', NULL), $variables);
    $this->assertSame([], $variables);
  }

  /**
   * The admin class follows the admin route test.
   */
  public function testAdminContextClass(): void {
    foreach ([TRUE => ['admin-context'], FALSE => NULL] as $is_admin => $expected) {
      $context = $this->createMock(AdminContext::class);
      $context->method('isAdminRoute')->willReturn((bool) $is_admin);
      $this->container->set('router.admin_context', $context);
      $variables = [];
      _add_admin_context_to_body_class($variables);
      $this->assertSame($expected, $variables['attributes']['class'] ?? NULL);
    }
  }

  /**
   * A setting becomes ' prefix-value', with a leading space.
   */
  public function testThemeSettingClass(): void {
    $this->assertSame('', _get_theme_setting_class('site_global_font_size', 'fs'));
    $this->settings['site_global_font_size'] = '16';
    $this->assertSame(' fs-16', _get_theme_setting_class('site_global_font_size', 'fs'));
    // ODDITY: a stored '0' is falsy and gives no class.
    $this->settings['site_global_font_size'] = '0';
    $this->assertSame('', _get_theme_setting_class('site_global_font_size', 'fs'));
  }

  /**
   * The html classes are the two settings joined with no separator.
   */
  public function testHtmlClasses(): void {
    $this->assertSame('', _get_html_classes());
    $this->settings = ['site_global_font_size' => 'lg', 'site_global_regions_gap' => 'sm'];
    $this->assertSame(' fs-lg gap-sm', _get_html_classes());
    $this->settings = ['site_global_regions_gap' => 'sm'];
    $this->assertSame(' gap-sm', _get_html_classes());
  }

  /**
   * Skip links: only the enabled four appear, in a fixed order.
   */
  public function testEnabledSkipLinks(): void {
    $this->assertSame([], _get_enabled_skip_links());
    $this->settings = [
      'skip_footer_content' => 1,
      'skip_header_content' => TRUE,
      'skip_main_content' => 0,
    ];
    $this->assertSame(['skip_header_content' => TRUE, 'skip_footer_content' => TRUE], _get_enabled_skip_links());
    $this->settings = [
      'skip_header_content' => 1,
      'skip_navigation_content' => 1,
      'skip_main_content' => 1,
      'skip_footer_content' => 1,
    ];
    $this->assertSame(
      ['skip_header_content', 'skip_navigation_content', 'skip_main_content', 'skip_footer_content'],
      array_keys(_get_enabled_skip_links())
    );
  }

}
