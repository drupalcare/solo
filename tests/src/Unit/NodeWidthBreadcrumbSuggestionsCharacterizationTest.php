<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\Core\Controller\TitleResolver;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\user\UserInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;

require_once __DIR__ . '/SoloCharacterizationBase.php';

/**
 * Records node width, breadcrumb and page suggestion helpers (rules 4 and 5).
 *
 * Covers: _get_node_width_class, _add_node_breadcrumb,
 * _add_taxonomy_breadcrumb, _add_user_breadcrumb, _add_webform_breadcrumb,
 * _add_view_breadcrumb, _add_node_suggestions, _add_taxonomy_suggestions,
 * _add_view_suggestions.
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
class NodeWidthBreadcrumbSuggestionsCharacterizationTest extends SoloCharacterizationBase {

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    drupal_static_reset('_get_node_width_class');
    parent::tearDown();
  }

  /**
   * Builds a route match with a name, a parameter and an optional route.
   */
  private function routeMatch(string $route_name, string $param = '', mixed $value = NULL, ?Route $route = NULL): RouteMatchInterface {
    $route_match = $this->createMock(RouteMatchInterface::class);
    $route_match->method('getRouteName')->willReturn($route_name);
    $route_match->method('getParameter')->willReturnCallback(fn(string $key) => $key === $param ? $value : NULL);
    $route_match->method('getRouteObject')->willReturn($route);
    return $route_match;
  }

  /**
   * Wires the services _get_node_width_class() reads.
   *
   * @param bool $module
   *   Whether solo_utilities is enabled.
   * @param bool $definition
   *   Whether the node_width entity type exists.
   * @param callable $load
   *   The loadByProperties implementation.
   */
  private function wireNodeWidth(bool $module, bool $definition, callable $load): void {
    $handler = $this->createMock(ModuleHandlerInterface::class);
    $handler->method('moduleExists')->with('solo_utilities')->willReturn($module);
    $this->container->set('module_handler', $handler);
    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')->willReturnCallback($load);
    $types = $this->createMock(EntityTypeManagerInterface::class);
    $types->method('hasDefinition')->with('node_width')->willReturn($definition);
    $types->method('getStorage')->with('node_width')->willReturn($storage);
    $this->container->set('entity_type.manager', $types);
  }

  /**
   * A node stub with an id.
   */
  private function node(int $nid): NodeInterface {
    $node = $this->createMock(NodeInterface::class);
    $node->method('id')->willReturn($nid);
    return $node;
  }

  /**
   * A stored width entity stub.
   */
  private function widthEntity(string $class): object {
    $field = (object) ['value' => $class];
    return new class($field) {

      /**
       * Constructs the stub.
       */
      public function __construct(private object $field) {}

      /**
       * Field stub.
       */
      public function get(string $name): object {
        return $this->field;
      }

    };
  }

  /**
   * The stored width class is returned for the node id.
   */
  public function testNodeWidthClassFound(): void {
    $seen = [];
    $this->wireNodeWidth(TRUE, TRUE, function (array $props) use (&$seen) {
      $seen[] = $props;
      return [$this->widthEntity('sw-800'), $this->widthEntity('ignored')];
    });
    $this->assertSame('sw-800', _get_node_width_class($this->node(101)));
    $this->assertSame([['node_id' => 101]], $seen);
  }

  /**
   * No module, no entity type or no stored row all give NULL.
   */
  public function testNodeWidthClassMissing(): void {
    $this->wireNodeWidth(FALSE, TRUE, fn() => $this->fail('storage must not be read'));
    $this->assertNull(_get_node_width_class($this->node(102)));
    $this->wireNodeWidth(TRUE, FALSE, fn() => $this->fail('storage must not be read'));
    $this->assertNull(_get_node_width_class($this->node(103)));
    $this->wireNodeWidth(TRUE, TRUE, fn() => []);
    $this->assertNull(_get_node_width_class($this->node(104)));
  }

  /**
   * The answer, NULL included, is cached per node id for the request.
   */
  public function testNodeWidthClassIsCachedPerNodeId(): void {
    $calls = 0;
    $this->wireNodeWidth(TRUE, TRUE, function () use (&$calls) {
      $calls++;
      return $calls === 1 ? [] : [$this->widthEntity('sw-100')];
    });
    $this->assertNull(_get_node_width_class($this->node(105)));
    // ODDITY: the cached NULL hides a row added after the first call.
    $this->assertNull(_get_node_width_class($this->node(105)));
    $this->assertSame(1, $calls);
    // A different node id reads storage again.
    $this->assertSame('sw-100', _get_node_width_class($this->node(106)));
    $this->assertSame(2, $calls);
  }

  /**
   * ODDITY: the cache entry is written before the lookup runs.
   *
   * A storage exception propagates once; the next call for that node returns
   * NULL from the cache and never retries.
   */
  public function testNodeWidthClassExceptionLeavesCachedNull(): void {
    $this->wireNodeWidth(TRUE, TRUE, function () {
      throw new \RuntimeException('storage down');
    });
    try {
      _get_node_width_class($this->node(107));
      $this->fail('Expected the storage exception.');
    }
    catch (\RuntimeException $e) {
      $this->assertSame('storage down', $e->getMessage());
    }
    $this->assertNull(_get_node_width_class($this->node(107)));
  }

  /**
   * A node route adds the decoded label; other routes add nothing.
   */
  public function testNodeBreadcrumb(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->method('label')->willReturn('Fish &amp; Chips');
    $breadcrumb = [['text' => 'Home']];
    _add_node_breadcrumb($this->routeMatch('entity.node.canonical', 'node', $node), $breadcrumb);
    $this->assertSame([['text' => 'Home'], ['text' => 'Fish & Chips']], $breadcrumb);

    $other = [];
    _add_node_breadcrumb($this->routeMatch('entity.node.edit_form', 'node', $node), $other);
    _add_node_breadcrumb($this->routeMatch('entity.node.canonical', 'node', NULL), $other);
    $this->assertSame([], $other);
  }

  /**
   * ODDITY: entities are decoded, so an escaped tag comes out as a real tag.
   *
   * Escaping is left to the template; the helper hands raw text on.
   */
  public function testNodeBreadcrumbDecodesEscapedMarkup(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->method('label')->willReturn('&lt;b&gt;x&lt;/b&gt;');
    $breadcrumb = [];
    _add_node_breadcrumb($this->routeMatch('entity.node.canonical', 'node', $node), $breadcrumb);
    $this->assertSame([['text' => '<b>x</b>']], $breadcrumb);
  }

  /**
   * An array label (a render array) is reduced to its first value.
   */
  public function testNodeBreadcrumbArrayLabel(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->method('label')->willReturn(['first', 'second']);
    $breadcrumb = [];
    _add_node_breadcrumb($this->routeMatch('entity.node.canonical', 'node', $node), $breadcrumb);
    $this->assertSame([['text' => 'first']], $breadcrumb);
  }

  /**
   * Term and user breadcrumb helpers.
   */
  public function testTaxonomyAndUserBreadcrumb(): void {
    $term = $this->createMock(TermInterface::class);
    $term->method('label')->willReturn('Tag A');
    $breadcrumb = [];
    _add_taxonomy_breadcrumb($this->routeMatch('entity.taxonomy_term.canonical', 'taxonomy_term', $term), $breadcrumb);
    $this->assertSame([['text' => 'Tag A']], $breadcrumb);

    $user = $this->createMock(UserInterface::class);
    $user->method('getDisplayName')->willReturn('Ann &amp; Bo');
    $breadcrumb = [];
    _add_user_breadcrumb($this->routeMatch('entity.user.canonical', 'user', $user), $breadcrumb);
    $this->assertSame([['text' => 'Ann & Bo']], $breadcrumb);

    $breadcrumb = [];
    _add_taxonomy_breadcrumb($this->routeMatch('entity.node.canonical', 'taxonomy_term', $term), $breadcrumb);
    _add_user_breadcrumb($this->routeMatch('entity.user.canonical', 'user', new \stdClass()), $breadcrumb);
    $this->assertSame([], $breadcrumb);
  }

  /**
   * The webform helper adds nothing for a non-webform object.
   *
   * The Webform class is optional; without it (or with another object) the
   * instanceof check is simply false.
   */
  public function testWebformBreadcrumbIgnoresOtherObjects(): void {
    $breadcrumb = [];
    _add_webform_breadcrumb($this->routeMatch('entity.webform.canonical', 'webform', new \stdClass()), $breadcrumb);
    _add_webform_breadcrumb($this->routeMatch('entity.node.canonical', 'webform', new \stdClass()), $breadcrumb);
    $this->assertSame([], $breadcrumb);
  }

  /**
   * Stubs the request stack and the title resolver for the views helper.
   */
  private function viewTitle(mixed $title): TitleResolver {
    $resolver = $this->createMock(TitleResolver::class);
    $resolver->method('getTitle')->willReturn($title);
    $stack = new RequestStack();
    $stack->push(Request::create('/frontpage'));
    $this->container->set('request_stack', $stack);
    return $resolver;
  }

  /**
   * A views page route adds the resolved title.
   */
  public function testViewBreadcrumb(): void {
    $route = new Route('/frontpage', ['view_id' => 'frontpage']);
    $breadcrumb = [];
    _add_view_breadcrumb($this->routeMatch('view.frontpage.page_1', '', NULL, $route), $breadcrumb, $this->viewTitle('News &amp; Events'), []);
    $this->assertSame([['text' => 'News & Events']], $breadcrumb);
  }

  /**
   * Titles that are render arrays use #markup; other shapes are skipped.
   */
  public function testViewBreadcrumbTitleShapes(): void {
    $route = new Route('/frontpage', ['view_id' => 'frontpage']);
    $cases = [
      [['#markup' => 'From array'], [['text' => 'From array']]],
      [['#markup' => 'x', 'other' => 1], [['text' => 'x']]],
      [['no_markup' => 'x'], []],
      ['', []],
      [NULL, []],
      [42, []],
    ];
    foreach ($cases as [$title, $expected]) {
      $breadcrumb = [];
      _add_view_breadcrumb($this->routeMatch('r', '', NULL, $route), $breadcrumb, $this->viewTitle($title), []);
      $this->assertSame($expected, $breadcrumb, 'Title: ' . var_export($title, TRUE));
    }
  }

  /**
   * Without a view_id, only the 'views-page' body class marks a views page.
   */
  public function testViewBreadcrumbBodyClassFallback(): void {
    $route = new Route('/x');
    $breadcrumb = [];
    _add_view_breadcrumb($this->routeMatch('r', '', NULL, $route), $breadcrumb, $this->viewTitle('T'), ['front']);
    $this->assertSame([], $breadcrumb);
    $match = $this->routeMatch('r', '', NULL, $route);
    _add_view_breadcrumb($match, $breadcrumb, $this->viewTitle('T'), ['front', 'views-page']);
    $this->assertSame([['text' => 'T']], $breadcrumb);
    // ODDITY: a body class that holds both words in one string does not match.
    $breadcrumb = [];
    _add_view_breadcrumb($this->routeMatch('r', '', NULL, $route), $breadcrumb, $this->viewTitle('T'), ['views-page path-view-x']);
    $this->assertSame([], $breadcrumb);
  }

  /**
   * ODDITY: a route object that is NULL counts as no view and stops quietly.
   */
  public function testViewBreadcrumbWithoutRoute(): void {
    $breadcrumb = [];
    _add_view_breadcrumb($this->routeMatch('r'), $breadcrumb, $this->viewTitle('T'), []);
    $this->assertSame([], $breadcrumb);
  }

  /**
   * Node and term suggestions go in position 1, after the base 'page'.
   */
  public function testNodeAndTermSuggestionsPosition(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->method('bundle')->willReturn('article');
    $suggestions = ['page', 'page__front'];
    _add_node_suggestions($this->routeMatch('entity.node.canonical', 'node', $node), $suggestions);
    $this->assertSame(['page', 'page__article', 'page__front'], $suggestions);

    $term = $this->createMock(TermInterface::class);
    $term->method('bundle')->willReturn('tags');
    $suggestions = ['page'];
    _add_taxonomy_suggestions($this->routeMatch('entity.taxonomy_term.canonical', 'taxonomy_term', $term), $suggestions);
    $this->assertSame(['page', 'page__taxonomy__tags'], $suggestions);

    // ODDITY: into an empty list the value is simply appended.
    $suggestions = [];
    _add_node_suggestions($this->routeMatch('entity.node.canonical', 'node', $node), $suggestions);
    $this->assertSame(['page__article'], $suggestions);
  }

  /**
   * Other routes leave the suggestions alone.
   */
  public function testSuggestionsIgnoreOtherRoutes(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->method('bundle')->willReturn('article');
    $suggestions = ['page'];
    _add_node_suggestions($this->routeMatch('entity.node.edit_form', 'node', $node), $suggestions);
    _add_taxonomy_suggestions($this->routeMatch('entity.node.canonical', 'taxonomy_term', $node), $suggestions);
    $this->assertSame(['page'], $suggestions);
  }

  /**
   * ODDITY: a canonical route with no parameter is a fatal Error.
   */
  public function testNodeSuggestionWithoutNodeIsFatal(): void {
    $suggestions = [];
    $this->expectException(\Error::class);
    _add_node_suggestions($this->routeMatch('entity.node.canonical', 'node', NULL), $suggestions);
  }

  /**
   * ODDITY: the term helper has the same fatal.
   */
  public function testTermSuggestionWithoutTermIsFatal(): void {
    $suggestions = [];
    $this->expectException(\Error::class);
    _add_taxonomy_suggestions($this->routeMatch('entity.taxonomy_term.canonical', 'taxonomy_term', NULL), $suggestions);
  }

  /**
   * A view route appends 'page__<view id>' at the end.
   */
  public function testViewSuggestion(): void {
    $suggestions = ['page', 'page__x'];
    _add_view_suggestions($this->routeMatch('r', '', NULL, new Route('/v', ['view_id' => 'news'])), $suggestions);
    $this->assertSame(['page', 'page__x', 'page__news'], $suggestions);

    $suggestions = ['page'];
    _add_view_suggestions($this->routeMatch('r', '', NULL, new Route('/v')), $suggestions);
    $this->assertSame(['page'], $suggestions);
  }

  /**
   * ODDITY: with no route object the view suggestion helper is a fatal Error.
   */
  public function testViewSuggestionWithoutRouteIsFatal(): void {
    $suggestions = [];
    $this->expectException(\Error::class);
    _add_view_suggestions($this->routeMatch('r'), $suggestions);
  }

}
