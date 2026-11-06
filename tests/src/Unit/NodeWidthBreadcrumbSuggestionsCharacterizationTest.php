<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Extension\ThemeSettingsProvider;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\solo_utilities\Context\RequestContext;
use Drupal\solo_utilities\Feature\Breadcrumb;
use Drupal\solo_utilities\Feature\Suggestions;
use Drupal\solo_utilities\Layout\LayoutResolver;
use Drupal\solo_utilities\Settings\BoundThemeSettings;
use Drupal\solo_utilities\Settings\SettingsCatalog;
use Drupal\taxonomy\TermInterface;
use Drupal\user\UserInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;

require_once __DIR__ . '/SoloCharacterizationBase.php';

/**
 * Records node width, breadcrumb and page suggestion behavior (rules 4 and 5).
 *
 * WHY THIS EXISTS
 * Solo 1 had procedural helpers for these (_get_node_width_class,
 * _add_node_breadcrumb and friends, _add_node_suggestions and friends). In
 * Solo 2 the same behavior lives in Solo Utilities: LayoutResolver::widthClass(),
 * Breadcrumb::preprocess() and Suggestions::page(). Each case runs the 1.x
 * scenario against that class. A case whose 2.0 answer differs on purpose
 * says why in a comment above its expected value.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
class NodeWidthBreadcrumbSuggestionsCharacterizationTest extends SoloCharacterizationBase {

  /**
   * Builds a route match with a name, one parameter and an optional route.
   */
  private function routeMatch(string $route_name, string $param = '', mixed $value = NULL, ?Route $route = NULL): RouteMatchInterface {
    $route_match = $this->createMock(RouteMatchInterface::class);
    $route_match->method('getRouteName')->willReturn($route_name);
    $route_match->method('getParameter')->willReturnCallback(fn(string $key) => $key === $param ? $value : NULL);
    $route_match->method('getRawParameter')->willReturn(NULL);
    $route_match->method('getRouteObject')->willReturn($route);
    return $route_match;
  }

  /**
   * Builds the request context Solo Utilities features ask for the route.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route the page is on.
   * @param mixed $title
   *   What the title resolver answers.
   * @param array<int, string> $body_classes
   *   Body classes remembered on the request.
   */
  private function requestContext(RouteMatchInterface $route_match, mixed $title = NULL, array $body_classes = []): RequestContext {
    $resolver = $this->createStub(TitleResolverInterface::class);
    $resolver->method('getTitle')->willReturn($title);
    $request = Request::create('/frontpage');
    $request->attributes->set('solo_body_classes', $body_classes);
    $stack = new RequestStack();
    $stack->push($request);
    return new RequestContext($this->createStub(AdminContext::class), $route_match, $stack, $this->createStub(CurrentPathStack::class), $this->createStub(AccountInterface::class), $resolver);
  }

  /**
   * Builds the theme settings the features read, from $this->settings.
   */
  private function boundSettings(): BoundThemeSettings {
    $provider = $this->createStub(ThemeSettingsProvider::class);
    $provider->method('getSetting')->willReturnCallback(fn(string $name) => $this->settings[$name] ?? NULL);
    $paths = $this->createStub(ExtensionPathResolver::class);
    $paths->method('getPath')->willReturn('.');
    $catalog = new SettingsCatalog($this->createStub(TypedConfigManagerInterface::class), $paths, dirname(__DIR__, 3));
    return new BoundThemeSettings($provider, 'solo', $catalog);
  }

  /**
   * Wires the node width storage and returns the resolver under test.
   *
   * @param callable $load
   *   The loadByProperties implementation.
   */
  private function resolver(callable $load): LayoutResolver {
    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')->willReturnCallback($load);
    $types = $this->createMock(EntityTypeManagerInterface::class);
    $types->method('getStorage')->with('node_width')->willReturn($storage);
    $this->settings = [
      'layout.width.global' => 'sw-global',
      'layout.width.per_node_enabled' => TRUE,
      'layout.width.per_content_type_enabled' => FALSE,
    ];
    return new LayoutResolver($types);
  }

  /**
   * An entity mock that reports empty cache metadata, as Breadcrumb reads it.
   *
   * @param class-string $interface
   *   The entity interface to mock.
   */
  private function entity(string $interface): object {
    $entity = $this->createMock($interface);
    $entity->method('getCacheContexts')->willReturn([]);
    $entity->method('getCacheTags')->willReturn([]);
    $entity->method('getCacheMaxAge')->willReturn(-1);
    return $entity;
  }

  /**
   * A node stub with an id.
   */
  private function node(int $nid): NodeInterface {
    $node = $this->createMock(NodeInterface::class);
    $node->method('id')->willReturn($nid);
    $node->method('bundle')->willReturn('article');
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
   * Runs the breadcrumb feature on a breadcrumb that starts with Home.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route the page is on.
   * @param mixed $title
   *   What the title resolver answers.
   * @param array<int, string> $body_classes
   *   Body classes remembered on the request.
   *
   * @return array<int, array<string, mixed>>
   *   The breadcrumb after preprocessing.
   */
  private function breadcrumb(RouteMatchInterface $route_match, mixed $title = NULL, array $body_classes = []): array {
    // Cache::mergeContexts() asks this service to validate the context names.
    $contexts = $this->createStub(CacheContextsManager::class);
    $contexts->method('assertValidTokens')->willReturn(TRUE);
    $this->container->set('cache_contexts_manager', $contexts);
    $this->settings = ['breadcrumb.hide_page_title' => FALSE];
    $variables = ['breadcrumb' => [['text' => 'Home']]];
    (new Breadcrumb($this->requestContext($route_match, $title, $body_classes)))->preprocess($variables, $this->boundSettings());
    return $variables['breadcrumb'];
  }

  /**
   * Runs the page suggestions on a list.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route the page is on.
   * @param array<int, string> $suggestions
   *   The starting suggestions.
   *
   * @return array<int, string>
   *   The suggestions after the feature ran.
   */
  private function suggest(RouteMatchInterface $route_match, array $suggestions): array {
    (new Suggestions($this->requestContext($route_match)))->page($suggestions);
    return $suggestions;
  }

  /**
   * The stored width class is returned for the node id.
   */
  public function testNodeWidthClassFound(): void {
    $seen = [];
    $resolver = $this->resolver(function (array $props) use (&$seen) {
      $seen[] = $props;
      return [$this->widthEntity('sw-800'), $this->widthEntity('ignored')];
    });
    $this->assertSame('sw-800', $resolver->widthClass($this->boundSettings(), $this->node(101)));
    $this->assertSame([['node_id' => 101]], $seen);
  }

  /**
   * No stored row leaves the global width in place.
   *
   * The 1.x cases for a missing solo_utilities module or node_width entity
   * type are REMOVED: Solo 2 keeps all theme PHP in Solo Utilities and the
   * entity type is declared by its attribute (solo_utilities CHANGELOG, "Holds
   * all of the Solo theme's PHP" and "declared by their attributes only").
   */
  public function testNodeWidthClassMissing(): void {
    $resolver = $this->resolver(fn() => []);
    $this->assertSame('sw-global', $resolver->widthClass($this->boundSettings(), $this->node(104)));
  }

  /**
   * Every call reads the stored width, so a row added later is seen.
   *
   * CHANGED ON PURPOSE: 1.x cached the answer per node id, NULL included,
   * which hid a row saved later in the same request, and after a storage
   * exception returned the cached fallback instead of retrying (both marked
   * ODDITY in the 1.x characterization). widthClass() has one caller,
   * PagePreprocessor, once per page, so there is nothing to cache.
   */
  public function testNodeWidthClassReadsStorageEachCall(): void {
    $calls = 0;
    $resolver = $this->resolver(function () use (&$calls) {
      $calls++;
      return $calls === 1 ? [] : [$this->widthEntity('sw-100')];
    });
    $this->assertSame('sw-global', $resolver->widthClass($this->boundSettings(), $this->node(105)));
    $this->assertSame('sw-100', $resolver->widthClass($this->boundSettings(), $this->node(105)));
    $this->assertSame(2, $calls);
  }

  /**
   * A storage exception is not swallowed, on the first call or the next.
   */
  public function testNodeWidthClassExceptionPropagates(): void {
    $resolver = $this->resolver(function () {
      throw new \RuntimeException('storage down');
    });
    foreach ([1, 2] as $attempt) {
      try {
        $resolver->widthClass($this->boundSettings(), $this->node(107));
        $this->fail("Expected the storage exception on call $attempt.");
      }
      catch (\RuntimeException $e) {
        $this->assertSame('storage down', $e->getMessage());
      }
    }
  }

  /**
   * A node route adds the decoded label; other routes add nothing.
   */
  public function testNodeBreadcrumb(): void {
    $node = $this->entity(NodeInterface::class);
    $node->method('label')->willReturn('Fish &amp; Chips');
    $this->assertSame([['text' => 'Home'], ['text' => 'Fish & Chips']], $this->breadcrumb($this->routeMatch('entity.node.canonical', 'node', $node)));

    $this->assertSame([['text' => 'Home']], $this->breadcrumb($this->routeMatch('entity.node.edit_form', 'node', $node)));
    $this->assertSame([['text' => 'Home']], $this->breadcrumb($this->routeMatch('entity.node.canonical', 'node', NULL)));
  }

  /**
   * ODDITY: entities are decoded, so an escaped tag comes out as a real tag.
   *
   * Escaping is left to the template; the helper hands raw text on.
   */
  public function testNodeBreadcrumbDecodesEscapedMarkup(): void {
    $node = $this->entity(NodeInterface::class);
    $node->method('label')->willReturn('&lt;b&gt;x&lt;/b&gt;');
    $this->assertSame([['text' => 'Home'], ['text' => '<b>x</b>']], $this->breadcrumb($this->routeMatch('entity.node.canonical', 'node', $node)));
  }

  /**
   * Term and user breadcrumb items.
   */
  public function testTaxonomyAndUserBreadcrumb(): void {
    $term = $this->entity(TermInterface::class);
    $term->method('label')->willReturn('Tag A');
    $this->assertSame([['text' => 'Home'], ['text' => 'Tag A']], $this->breadcrumb($this->routeMatch('entity.taxonomy_term.canonical', 'taxonomy_term', $term)));

    $user = $this->entity(UserInterface::class);
    $user->method('getDisplayName')->willReturn('Ann &amp; Bo');
    $this->assertSame([['text' => 'Home'], ['text' => 'Ann & Bo']], $this->breadcrumb($this->routeMatch('entity.user.canonical', 'user', $user)));

    $this->assertSame([['text' => 'Home']], $this->breadcrumb($this->routeMatch('entity.node.canonical', 'taxonomy_term', $term)));
    $this->assertSame([['text' => 'Home']], $this->breadcrumb($this->routeMatch('entity.user.canonical', 'user', new \stdClass())));
  }

  /**
   * The webform item is added for no object that is not an entity.
   */
  public function testWebformBreadcrumbIgnoresOtherObjects(): void {
    $this->assertSame([['text' => 'Home']], $this->breadcrumb($this->routeMatch('entity.webform.canonical', 'webform', new \stdClass())));
    $this->assertSame([['text' => 'Home']], $this->breadcrumb($this->routeMatch('entity.node.canonical', 'webform', new \stdClass())));
  }

  /**
   * A views page route adds the resolved title.
   */
  public function testViewBreadcrumb(): void {
    $route = new Route('/frontpage', ['view_id' => 'frontpage']);
    $match = $this->routeMatch('view.frontpage.page_1', 'view_id', 'frontpage', $route);
    $this->assertSame([['text' => 'Home'], ['text' => 'News & Events']], $this->breadcrumb($match, 'News &amp; Events'));
  }

  /**
   * Titles that are render arrays use #markup; other shapes are skipped.
   */
  public function testViewBreadcrumbTitleShapes(): void {
    $route = new Route('/frontpage', ['view_id' => 'frontpage']);
    $home = ['text' => 'Home'];
    $cases = [
      [['#markup' => 'From array'], [$home, ['text' => 'From array']]],
      [['#markup' => 'x', 'other' => 1], [$home, ['text' => 'x']]],
      [['no_markup' => 'x'], [$home]],
      ['', [$home]],
      [NULL, [$home]],
      [42, [$home]],
    ];
    foreach ($cases as [$title, $expected]) {
      $match = $this->routeMatch('r', 'view_id', 'frontpage', $route);
      $this->assertSame($expected, $this->breadcrumb($match, $title), 'Title: ' . var_export($title, TRUE));
    }
  }

  /**
   * Without a view id, only the views-page body class marks a views page.
   *
   * The title is a render array here: Breadcrumb adds a plain string title on
   * every route, so only an array isolates the views item.
   */
  public function testViewBreadcrumbBodyClassFallback(): void {
    $route = new Route('/x');
    $home = ['text' => 'Home'];
    $title = ['#markup' => 'T'];
    $this->assertSame([$home], $this->breadcrumb($this->routeMatch('r', '', NULL, $route), $title, ['front']));
    // CHANGED ON PURPOSE: the body class is renamed 'views-page' to
    // 'solo-body--views-page' (solo docs/CLASS-MIGRATION.md line 286).
    $match = $this->routeMatch('r', '', NULL, $route);
    $classes = ['front', 'solo-body--views-page'];
    $this->assertSame([$home, ['text' => 'T']], $this->breadcrumb($match, $title, $classes));
    // ODDITY: a body class that holds both words in one string does not match.
    $match = $this->routeMatch('r', '', NULL, $route);
    $this->assertSame([$home], $this->breadcrumb($match, $title, ['solo-body--views-page path-view-x']));
  }

  /**
   * ODDITY: a route object that is NULL counts as no view and stops quietly.
   */
  public function testViewBreadcrumbWithoutRoute(): void {
    $this->assertSame([['text' => 'Home']], $this->breadcrumb($this->routeMatch('r'), ['#markup' => 'T']));
  }

  /**
   * Node and term suggestions go in position 1, after the base 'page'.
   */
  public function testNodeAndTermSuggestionsPosition(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->method('bundle')->willReturn('article');
    $match = $this->routeMatch('entity.node.canonical', 'node', $node);
    $this->assertSame(['page', 'page__article', 'page__front'], $this->suggest($match, ['page', 'page__front']));

    $term = $this->createMock(TermInterface::class);
    $term->method('bundle')->willReturn('tags');
    $this->assertSame(['page', 'page__taxonomy__tags'], $this->suggest($this->routeMatch('entity.taxonomy_term.canonical', 'taxonomy_term', $term), ['page']));

    // ODDITY: into an empty list the value is simply appended.
    $this->assertSame(['page__article'], $this->suggest($this->routeMatch('entity.node.canonical', 'node', $node), []));
  }

  /**
   * Other routes leave the suggestions alone.
   */
  public function testSuggestionsIgnoreOtherRoutes(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->method('bundle')->willReturn('article');
    $this->assertSame(['page'], $this->suggest($this->routeMatch('entity.node.edit_form', 'node', $node), ['page']));
    $this->assertSame(['page'], $this->suggest($this->routeMatch('entity.node.canonical', 'taxonomy_term', $node), ['page']));
  }

  /**
   * A canonical route with no node parameter adds nothing.
   *
   * CHANGED ON PURPOSE: 1.x was a fatal Error here. RequestContext::routeEntity()
   * says an unconverted parameter "was a fatal error in 1.x" and now gives NULL.
   */
  public function testNodeSuggestionWithoutNodeIsFatal(): void {
    $this->assertSame([], $this->suggest($this->routeMatch('entity.node.canonical', 'node', NULL), []));
  }

  /**
   * The term route with no term parameter adds nothing.
   *
   * CHANGED ON PURPOSE: 1.x was a fatal Error; see routeEntity() above.
   */
  public function testTermSuggestionWithoutTermIsFatal(): void {
    $this->assertSame([], $this->suggest($this->routeMatch('entity.taxonomy_term.canonical', 'taxonomy_term', NULL), []));
  }

  /**
   * A view route appends 'page__<view id>' at the end.
   */
  public function testViewSuggestion(): void {
    $match = $this->routeMatch('r', 'view_id', 'news');
    $this->assertSame(['page', 'page__x', 'page__news'], $this->suggest($match, ['page', 'page__x']));
    $this->assertSame(['page'], $this->suggest($this->routeMatch('r', '', NULL, new Route('/v')), ['page']));
  }

  /**
   * With no route object the view suggestion adds nothing.
   *
   * CHANGED ON PURPOSE: 1.x was a fatal Error. Suggestions::page() reads the
   * route parameter 'view_id' (RequestContext::routeString() docblock: a route
   * default such as view_id is a parameter), never the route object. No line
   * names this case; the citation is the design, needs manager confirmation.
   */
  public function testViewSuggestionWithoutRouteIsFatal(): void {
    $this->assertSame([], $this->suggest($this->routeMatch('r'), []));
  }

}
