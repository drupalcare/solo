<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Pins the markup of the templates touched by the semantic HTML fixes.
 *
 * Two kinds of assertion: the hooks every class, id and attribute that must
 * stay (so a fix cannot drop a feature or a sub-theme's selector), and the
 * ARIA defects fixed on 2.0.x-dev (planning/semantic-html-audit.md section 3,
 * SS-D1 to SS-D4), plus the D12 template sync. SS-D1 (landmark elements) and
 * the breadcrumb ol were held in batch 2 and applied in 2.0.x as a new major;
 * the upgrade notes carry the sub-theme impact.
 *
 * Source-level on purpose: the templates need Twig and Drupal filters to
 * render, and what is guarded is which attributes the source emits.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
class SemanticTemplatesCharacterizationTest extends TestCase {

  /**
   * Reads a template of the theme.
   */
  private function tpl(string $path): string {
    $file = dirname(__DIR__, 3) . '/' . $path;
    $this->assertFileExists($file);
    return (string) file_get_contents($file);
  }

  /**
   * SS-D2: no aria-label on the role-less wrapper divs.
   */
  public function testNoAriaLabelOnGenericDivs(): void {
    foreach (['partials/_breadcrumb.html.twig', 'partials/_footer-menu.html.twig', 'partials/_copyright.html.twig'] as $path) {
      $this->assertDoesNotMatchRegularExpression('/<div\b[^>]*\baria-label=/', $this->tpl($path), $path);
    }
  }

  /**
   * SS-D2: the wrappers keep their id, classes and attribute hooks.
   */
  public function testWrappersKeepIdsAndClasses(): void {
    $this->assertStringContainsString('<div id="breadcrumb" class="solo-outer lone breadcrumb{{ classes_breadcrumb ? \' \' ~ classes_breadcrumb }}" {{ attributes_breadcrumb }}>', $this->tpl('partials/_breadcrumb.html.twig'));
    $this->assertStringContainsString('<div id="footer-menu" class="solo-outer lone footer-menu{{ classes_footer_menu ? \' \' ~ classes_footer_menu }}" {{ attributes_footer_menu }}>', $this->tpl('partials/_footer-menu.html.twig'));
    $copyright = $this->tpl('partials/_copyright.html.twig');
    $this->assertSame(2, substr_count($copyright, '<footer id="copyright" class="solo-outer lone copyright{{ classes_credit_copyright ? \' \' ~ classes_credit_copyright }}" {{ attributes_credit_copyright }}>'));
  }

  /**
   * SS-D3: rel is the link type "home", title and alt keep their text.
   */
  public function testBrandingLinksUseRelHome(): void {
    $src = $this->tpl('templates/block/block--system-branding-block.html.twig');
    $this->assertSame(3, substr_count($src, 'rel="home"'));
    $this->assertDoesNotMatchRegularExpression('/rel="\{\{/', $src);
    $this->assertSame(3, substr_count($src, 'title="{{ logo_alt | t }}"'));
    $this->assertSame(1, substr_count($src, 'class="site-name-link"'));
    $this->assertSame(2, substr_count($src, 'class="site-logo"'));
    $this->assertStringContainsString('alt="{{ logo_alt | t }}"', $src);
  }

  /**
   * SS-D4: nav elements carry no redundant role; li has no presentation role.
   */
  public function testNoRedundantNavigationRoles(): void {
    foreach (['templates/navigation/breadcrumb.html.twig', 'templates/navigation/pager.html.twig'] as $path) {
      $this->assertDoesNotMatchRegularExpression('/<nav\b[^>]*\brole="navigation"/', $this->tpl($path), $path);
    }
    $this->assertStringNotContainsString("setAttribute('role', 'navigation')", $this->tpl('templates/block/block--system-menu-block.html.twig'));
    $this->assertStringNotContainsString('role="presentation"', $this->tpl('templates/navigation/pager.html.twig'));
  }

  /**
   * SS-D4: the navs keep their labelling and the breadcrumb its classes.
   */
  public function testNavigationKeepsLabelsAndClasses(): void {
    $bc = $this->tpl('templates/navigation/breadcrumb.html.twig');
    $this->assertStringContainsString('class="solo-block breadcrumb-nav" aria-labelledby="system-breadcrumb"', $bc);
    $this->assertStringContainsString('<h2 id="system-breadcrumb" class="visually-hidden">', $bc);
    $this->assertStringContainsString('class="solo-ul solo-medium breadcrumb-menu"', $bc);
    $this->assertStringContainsString('<nav class="pager" aria-labelledby="{{ heading_id }}">', $this->tpl('templates/navigation/pager.html.twig'));
    $this->assertSame(2, substr_count($this->tpl('templates/navigation/pager.html.twig'), 'class="solo-button pager__item pager__item--ellipsis"'));
    $this->assertStringContainsString("setAttribute('aria-labelledby', heading_id)", $this->tpl('templates/block/block--system-menu-block.html.twig'));
  }

  /**
   * SS-D4: the breadcrumb is an ordered list, and CSS covers both list tags.
   */
  public function testBreadcrumbIsOrderedList(): void {
    $tpl = $this->tpl('templates/navigation/breadcrumb.html.twig');
    $this->assertStringContainsString('<ol class="solo-ul solo-medium breadcrumb-menu">', $tpl);
    $this->assertStringNotContainsString('<ul', $tpl);
    $css = $this->tpl('css/components/solo-breadcrumb.css');
    $this->assertStringContainsString("ul.breadcrumb-menu,\nol.breadcrumb-menu {", $css);
    $this->assertStringContainsString("ul.breadcrumb-menu li > a,\nol.breadcrumb-menu li > a {", $css);
    $this->assertStringContainsString("ul.breadcrumb-menu li > span,\nol.breadcrumb-menu li > span {", $css);
    $this->assertStringContainsString('.solo__bc-scroll ol.breadcrumb-menu {', $css);
  }

  /**
   * SS-D1: main and sidebar wrappers are landmark elements, ids kept.
   */
  public function testLandmarkElements(): void {
    $src = $this->tpl('partials/_main-regions.html.twig');
    $this->assertStringContainsString('<main id="sidebar-box-main" class="{{ content_outer_classes }}">', $src);
    $this->assertStringContainsString('<aside id="sidebar-box-first" class="{{ sidebar_first_outer_classes }}" aria-label="{{ \'Sidebar First\'|t }}">', $src);
    $this->assertStringContainsString('<aside id="sidebar-box-second" class="{{ sidebar_second_outer_classes }}" aria-label="{{ \'Sidebar Second\'|t }}">', $src);
    $this->assertSame(1, substr_count($src, '<main '));
    $this->assertSame(1, substr_count($src, '</main>'));
    $this->assertSame(2, substr_count($src, '</aside>'));
    $this->assertStringNotContainsString('role="main"', $src);
    $this->assertStringNotContainsString('role="complementary"', $src);
    $this->assertStringContainsString('<a id="main-content" tabindex="-1"></a>', $src);
    $copy = $this->tpl('partials/_copyright.html.twig');
    $this->assertSame(2, substr_count($copy, '<footer id="copyright"'));
    $this->assertSame(2, substr_count($copy, '</footer>'));
    $this->assertStringNotContainsString('<div id="copyright"', $copy);
  }

  /**
   * D12 template sync: forms, links and status report follow core D12.
   */
  public function testD12SyncedTemplates(): void {
    $admin = $this->tpl('templates/block/admin-block-content.html.twig');
    $this->assertStringContainsString("(compact is defined and compact) ? 'compact'", $admin);
    $this->assertStringContainsString("'admin-list'", $admin);
    $links = $this->tpl('templates/content/links--node.html.twig');
    $this->assertStringContainsString('<div class="node__links">', $links);
    $this->assertStringContainsString("{{ include('links.html.twig') }}", $links);
    $report = $this->tpl('templates/admin/status-report-grouped.html.twig');
    $this->assertStringContainsString('create_attribute({class: summary_classes})', $report);
    $this->assertStringContainsString("'solo-block',", $this->tpl('templates/form/field-multiple-value-form.html.twig'));
  }

}
