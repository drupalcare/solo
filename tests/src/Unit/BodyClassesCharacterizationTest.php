<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeSettingsProvider;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Template\Attribute;
use Drupal\solo_utilities\Context\RequestContext;
use Drupal\solo_utilities\Feature\BodyClasses;
use Drupal\solo_utilities\Feature\HtmlPreprocessor;
use Drupal\solo_utilities\Render\AttributeHelper;
use Drupal\solo_utilities\Security\UrlSafety;
use Drupal\solo_utilities\Settings\BoundThemeSettings;
use Drupal\solo_utilities\Settings\SettingsCatalog;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

require_once __DIR__ . '/SoloCharacterizationBase.php';

/**
 * Records the body and html class helpers (conversion rule 4) against 2.0.
 *
 * WHY THIS EXISTS
 * Solo 1 had procedural helpers (_add_node_id_to_body_class and its
 * siblings, _get_html_classes, _get_enabled_skip_links). In Solo 2 the same
 * work lives in Solo Utilities: BodyClasses::apply() builds the body and html
 * classes and HtmlPreprocessor::enabledSkipLinks() lists the skip links. Each
 * case runs the 1.x input through those classes. Where the 2.0 answer differs
 * on purpose, a comment above the expected value quotes the reason.
 *
 * Settings use Solo 2's dotted keys. An unset key now answers with the shipped
 * default (BoundThemeSettings::get(), config/install/solo.settings.yml), so a
 * case that relied on "nothing stored means nothing" says so.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
class BodyClassesCharacterizationTest extends SoloCharacterizationBase {

  /**
   * Builds the settings object the features read, with the shipped defaults.
   */
  private function boundSettings(): BoundThemeSettings {
    $theme_dir = dirname(__DIR__, 3);
    $resolver = $this->createStub(ExtensionPathResolver::class);
    $resolver->method('getPath')->willReturn(basename($theme_dir));
    $catalog = new SettingsCatalog($this->createStub(TypedConfigManagerInterface::class), $resolver, dirname($theme_dir));
    $provider = $this->container->get(ThemeSettingsProvider::class);
    return new BoundThemeSettings($provider, 'solo', $catalog);
  }

  /**
   * Builds the request context over a route, a path and an admin flag.
   *
   * @param string $route
   *   The route name.
   * @param array<string, mixed> $params
   *   Route parameters by name.
   * @param string $path
   *   The internal path.
   * @param bool $admin
   *   Whether core marks the route as admin.
   */
  private function requestContext(string $route, array $params, string $path, bool $admin): RequestContext {
    $route_match = $this->createStub(RouteMatchInterface::class);
    $route_match->method('getRouteName')->willReturn($route);
    $route_match->method('getParameter')->willReturnCallback(fn(string $key) => $params[$key] ?? NULL);
    $route_match->method('getRawParameter')->willReturnCallback(fn(string $key) => $params[$key] ?? NULL);
    $admin_context = $this->createStub(AdminContext::class);
    $admin_context->method('isAdminRoute')->willReturn($admin);
    $stack = $this->createStub(CurrentPathStack::class);
    $stack->method('getPath')->willReturn($path);
    $requests = new RequestStack();
    $requests->push(new Request());
    return new RequestContext(
      $admin_context,
      $route_match,
      $requests,
      $stack,
      $this->createStub(AccountInterface::class),
      $this->createStub(TitleResolverInterface::class),
    );
  }

  /**
   * Runs the body class feature and returns the variables it filled.
   *
   * @param string $route
   *   The route name.
   * @param array<string, mixed> $params
   *   Route parameters by name.
   * @param string $path
   *   The internal path.
   * @param bool $admin
   *   Whether core marks the route as admin.
   * @param array<string, mixed> $variables
   *   The html template variables to start from.
   *
   * @return array<string, mixed>
   *   The variables after the feature ran.
   */
  private function apply(string $route = '', array $params = [], string $path = '', bool $admin = FALSE, array $variables = []): array {
    $feature = new BodyClasses($this->requestContext($route, $params, $path, $admin), new AttributeHelper());
    $feature->apply($variables, $this->boundSettings());
    return $variables;
  }

  /**
   * The body classes the feature adds.
   *
   * @param string $route
   *   The route name.
   * @param array<string, mixed> $params
   *   Route parameters by name.
   * @param string $path
   *   The internal path.
   * @param bool $admin
   *   Whether core marks the route as admin.
   * @param array<string, mixed> $variables
   *   The html template variables to start from.
   *
   * @return list<string>
   *   The class list.
   */
  private function bodyClasses(string $route = '', array $params = [], string $path = '', bool $admin = FALSE, array $variables = []): array {
    return $this->apply($route, $params, $path, $admin, $variables)['attributes']['class'];
  }

  /**
   * The html element classes the feature adds for the stored settings.
   *
   * @return list<string>
   *   The class list.
   */
  private function htmlClasses(): array {
    $variables = $this->apply(variables: ['html_attributes' => new Attribute()]);
    return $variables['html_attributes']->getClass()->value();
  }

  /**
   * An entity with an id and a bundle.
   */
  private function entity(mixed $id, string $bundle = ''): EntityInterface {
    $entity = $this->createStub(EntityInterface::class);
    $entity->method('id')->willReturn($id);
    $entity->method('bundle')->willReturn($bundle);
    return $entity;
  }

  /**
   * The node id is added as path-node-N.
   */
  public function testNodeIdClass(): void {
    $this->assertSame(['path-node-42'], $this->bodyClasses('entity.node.canonical', ['node' => $this->entity(42)]));
  }

  /**
   * Existing classes are kept; a class string becomes the first list item.
   */
  public function testNodeIdClassKeepsExisting(): void {
    $classes = $this->bodyClasses('entity.node.canonical', ['node' => $this->entity(7)], variables: ['attributes' => ['class' => 'front']]);
    $this->assertSame(['front', 'path-node-7'], $classes);
  }

  /**
   * No node, or a node whose id is empty, adds no class.
   */
  public function testNodeIdClassAbsent(): void {
    // CHANGED ON PURPOSE: AttributeHelper::ensureClassArray() "makes the class
    // entry of an attributes array a list", so the variables now hold an empty
    // class list where 1.x left them untouched ([]). No class is added.
    $this->assertSame([], $this->bodyClasses('entity.node.canonical', ['node' => NULL]));
    $this->assertSame([], $this->bodyClasses('entity.node.canonical', ['node' => $this->entity(NULL)]));
    // ODDITY kept: id 0 is falsy, so it adds nothing either.
    $this->assertSame([], $this->bodyClasses('entity.node.canonical', ['node' => $this->entity(0)]));
  }

  /**
   * Routes that carry a node or a term, other than the canonical page.
   *
   * @return array<string, array{string, string, list<string>}>
   *   The route, the parameter name and the 1.x classes.
   */
  public static function otherEntityRouteProvider(): array {
    return [
      'node edit form' => ['entity.node.edit_form', 'node', ['path-node-42']],
      'node revisions' => ['entity.node.version_history', 'node', ['path-node-42']],
      'term edit form' => [
        'entity.taxonomy_term.edit_form',
        'taxonomy_term',
        ['path-taxonomy-term-42', 'path-vocabulary-tags'],
      ],
    ];
  }

  /**
   * Any route carrying the entity parameter gets its classes, as in 1.x.
   *
   * 1.x added these classes on every route with a node or taxonomy_term
   * parameter. Solo 2 had narrowed them to the canonical route with no record
   * of intent; BodyClasses::bodyClasses() was restored to the 1.x rule.
   *
   * @param string $route
   *   The route name.
   * @param string $parameter
   *   The route parameter holding the entity.
   * @param list<string> $expected
   *   The classes 1.x produced.
   */
  #[DataProvider('otherEntityRouteProvider')]
  public function testEntityClassesOnOtherEntityRoutes(string $route, string $parameter, array $expected): void {
    $this->assertSame($expected, $this->bodyClasses($route, [$parameter => $this->entity(42, 'tags')]));
  }

  /**
   * A term adds its id and its cleaned vocabulary id.
   */
  public function testTaxonomyTermClasses(): void {
    $classes = $this->bodyClasses('entity.taxonomy_term.canonical', ['taxonomy_term' => $this->entity(5, 'Blog_Tags')]);
    $this->assertSame(['path-taxonomy-term-5', 'path-vocabulary-Blog-Tags'], $classes);
  }

  /**
   * A term with a bundle but no id still gets the vocabulary class.
   */
  public function testTaxonomyTermBundleOnly(): void {
    $this->assertSame(['path-vocabulary-tags'], $this->bodyClasses('entity.taxonomy_term.canonical', ['taxonomy_term' => $this->entity(NULL, 'tags')]));
    // CHANGED ON PURPOSE: ensureClassArray() leaves an empty class list where
    // 1.x left the variables as [] (see testNodeIdClassAbsent).
    $this->assertSame([], $this->bodyClasses('entity.taxonomy_term.canonical', ['taxonomy_term' => NULL]));
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
      // CHANGED ON PURPOSE: AttributeHelper::classList() "normalizes any stored
      // class value to a list of single class names", so the space splits the
      // segment into two classes. The rendered class attribute is unchanged.
      'raw text segment' => ['/user/a b', ['path-user-a', 'b']],
      // ODDITY kept: an empty second segment still gives a class.
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
    $this->assertSame($expected, $this->bodyClasses(path: $path));
  }

  /**
   * The view class: 1.x gave one list item holding two classes.
   */
  public function testViewIdClass(): void {
    // CHANGED ON PURPOSE: "Every Solo class and ID now starts with solo-"
    // (CHANGELOG, Next release; docs/CLASS-MIGRATION.md: views-page becomes
    // solo-body--views-page). AttributeHelper also keeps one class per item.
    // Case is still kept: cleanCssIdentifier() does not lowercase.
    $this->assertSame(['solo-body--views-page', 'path-view-frontpage-Items'], $this->bodyClasses(params: ['view_id' => 'frontpage_Items']));
    // CHANGED ON PURPOSE: empty class list, see testNodeIdClassAbsent.
    $this->assertSame([], $this->bodyClasses(params: ['view_id' => NULL]));
  }

  /**
   * The admin class follows the admin route test.
   */
  public function testAdminContextClass(): void {
    // CHANGED ON PURPOSE: admin-context is now solo-body--admin-context
    // (docs/CLASS-MIGRATION.md; CHANGELOG: every class starts with solo-).
    $this->assertSame(['solo-body--admin-context'], $this->bodyClasses(admin: TRUE));
    // CHANGED ON PURPOSE: empty class list, see testNodeIdClassAbsent.
    $this->assertSame([], $this->bodyClasses(admin: FALSE));
  }

  /**
   * A stored font size becomes an fs- class on the html element.
   */
  public function testThemeSettingClass(): void {
    // CHANGED ON PURPOSE: 1.x returned a string with a leading space and
    // nothing when unset. Solo 2 adds the class to html_attributes, and an
    // unset key answers with the shipped default: "The shipped default
    // (config/install/solo.settings.yml) owns the value of a key that is
    // unset" (BoundThemeSettings::get()), font_size 16 and regions_gap 16.
    $this->assertSame(['fs-16', 'gap-16'], $this->htmlClasses());
    $this->settings['typography.font_size'] = '16';
    $this->settings['layout.regions_gap'] = '';
    $this->assertSame(['fs-16'], $this->htmlClasses());
    // ODDITY kept: a stored '0' is falsy and gives no class.
    $this->settings['typography.font_size'] = '0';
    $this->assertSame([], $this->htmlClasses());
  }

  /**
   * The html classes are the font size and the region gap, in that order.
   */
  public function testHtmlClasses(): void {
    $this->settings = ['typography.font_size' => 'lg', 'layout.regions_gap' => 'sm'];
    $this->assertSame(['fs-lg', 'gap-sm'], $this->htmlClasses());
    // An empty stored size still gives no class, as in 1.x.
    $this->settings = ['typography.font_size' => '', 'layout.regions_gap' => 'sm'];
    $this->assertSame(['gap-sm'], $this->htmlClasses());
    // CHANGED ON PURPOSE: with only the gap stored, the unset font size takes
    // its shipped default (BoundThemeSettings::get()); 1.x gave ' gap-sm'.
    $this->settings = ['layout.regions_gap' => 'sm'];
    $this->assertSame(['fs-16', 'gap-sm'], $this->htmlClasses());
  }

  /**
   * Skip links: only the enabled four appear, in a fixed order.
   */
  public function testEnabledSkipLinks(): void {
    $context = $this->requestContext('', [], '', FALSE);
    $feature = new HtmlPreprocessor(
      new BodyClasses($context, new AttributeHelper()),
      $context,
      new UrlSafety($this->createStub(FileUrlGeneratorInterface::class)),
      $this->createStub(ModuleHandlerInterface::class),
      $this->createStub(ExtensionPathResolver::class),
      $this->createStub(LoggerChannelFactoryInterface::class),
    );
    $all = ['skip_header_content', 'skip_navigation_content', 'skip_main_content', 'skip_footer_content'];
    // CHANGED ON PURPOSE: 1.x gave [] for unset keys. Solo 2 reads the shipped
    // defaults (config/install/solo.settings.yml: skip_links header,
    // navigation, main and footer are all true; BoundThemeSettings::get()).
    $this->assertSame($all, array_keys($feature->enabledSkipLinks($this->boundSettings())));
    // With every key stored, the answer is the 1.x one: only the enabled ones.
    $this->settings = [
      'skip_links.footer' => 1,
      'skip_links.header' => TRUE,
      'skip_links.navigation' => 0,
      'skip_links.main' => 0,
    ];
    $this->assertSame(['skip_header_content' => TRUE, 'skip_footer_content' => TRUE], $feature->enabledSkipLinks($this->boundSettings()));
    $this->settings = [
      'skip_links.header' => 1,
      'skip_links.navigation' => 1,
      'skip_links.main' => 1,
      'skip_links.footer' => 1,
    ];
    $this->assertSame($all, array_keys($feature->enabledSkipLinks($this->boundSettings())));
  }

}
