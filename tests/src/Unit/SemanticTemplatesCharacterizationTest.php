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
    // CHANGED ON PURPOSE: ids and classes carry the solo- prefix
    // (docs/CLASS-MIGRATION.md: breadcrumb to solo-breadcrumb, lone to
    // solo-container--single, footer-menu to solo-footer-nav). The attribute
    // hooks are unchanged.
    $this->assertStringContainsString('<div id="solo-breadcrumb" class="solo-outer solo-container--single breadcrumb{{ classes_breadcrumb ? \' \' ~ classes_breadcrumb }}" {{ attributes_breadcrumb }}>', $this->tpl('partials/_breadcrumb.html.twig'));
    $this->assertStringContainsString('<div id="solo-footer-menu" class="solo-outer solo-container--single solo-footer-nav{{ classes_footer_menu ? \' \' ~ classes_footer_menu }}" {{ attributes_footer_menu }}>', $this->tpl('partials/_footer-menu.html.twig'));
    $copyright = $this->tpl('partials/_copyright.html.twig');
    // CHANGED ON PURPOSE: "the copyright band is now a <div> inside [the
    // footer landmark]" (CHANGELOG), with the solo- names above.
    $this->assertSame(2, substr_count($copyright, '<div id="solo-copyright" class="solo-outer solo-container--single solo-copyright{{ classes_credit_copyright ? \' \' ~ classes_credit_copyright }}" {{ attributes_credit_copyright }}>'));
  }

  /**
   * SS-D3: rel is the link type "home", title and alt keep their text.
   */
  public function testBrandingLinksUseRelHome(): void {
    $src = $this->tpl('templates/block/block--system-branding-block.html.twig');
    $this->assertSame(3, substr_count($src, 'rel="home"'));
    $this->assertDoesNotMatchRegularExpression('/rel="\{\{/', $src);
    // CHANGED ON PURPOSE: "the repeated title attributes are gone"
    // (CHANGELOG, branding link entry).
    $this->assertStringNotContainsString('title="', $src);
    // CHANGED ON PURPOSE: site-name-link is solo-branding__site-name-link
    // (docs/CLASS-MIGRATION.md).
    $this->assertSame(1, substr_count($src, 'class="solo-branding__site-name-link"'));
    $this->assertSame(2, substr_count($src, 'class="site-logo"'));
    // CHANGED ON PURPOSE: "the logo and the site name are one link"; the logo
    // image is decorative beside the name, and the logo name is the site's own
    // alt text, only the fallback "Home" translated (CHANGELOG).
    $this->assertSame(1, substr_count($src, 'alt="{{ site_name ? \'\' : logo_label }}"'));
    $this->assertStringContainsString("{% set logo_label = logo_alt == 'Home' ? 'Home'|t : logo_alt %}", $src);
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
    // CHANGED ON PURPOSE: breadcrumb-nav and breadcrumb-menu are
    // solo-breadcrumb__nav and solo-breadcrumb__menu (docs/CLASS-MIGRATION.md).
    // CHANGED ON PURPOSE (weak citation, needs a CHANGELOG line): the heading
    // id is now made unique with clean_unique_id, so two breadcrumbs on a page
    // no longer share one id; only commit 11c9940's diff says so.
    $this->assertStringContainsString("{% set breadcrumb_heading_id = 'system-breadcrumb'|clean_unique_id %}", $bc);
    $this->assertStringContainsString('class="solo-block solo-breadcrumb__nav" aria-labelledby="{{ breadcrumb_heading_id }}"', $bc);
    $this->assertStringContainsString('<h2 id="{{ breadcrumb_heading_id }}" class="visually-hidden">', $bc);
    $this->assertStringContainsString('class="solo-ul solo-medium solo-breadcrumb__menu"', $bc);
    $this->assertStringContainsString('<nav class="pager" aria-labelledby="{{ heading_id }}">', $this->tpl('templates/navigation/pager.html.twig'));
    $this->assertSame(2, substr_count($this->tpl('templates/navigation/pager.html.twig'), 'class="solo-button pager__item pager__item--ellipsis"'));
    $this->assertStringContainsString("setAttribute('aria-labelledby', heading_id)", $this->tpl('templates/block/block--system-menu-block.html.twig'));
  }

  /**
   * SS-D4: the breadcrumb is an ordered list, and CSS covers both list tags.
   */
  public function testBreadcrumbIsOrderedList(): void {
    $tpl = $this->tpl('templates/navigation/breadcrumb.html.twig');
    // CHANGED ON PURPOSE: breadcrumb-menu is solo-breadcrumb__menu and
    // solo__bc-scroll is solo-page--breadcrumb-scroll (docs/CLASS-MIGRATION.md).
    // The :where(ol) and :where(.solo-page--breadcrumb-scroll) forms keep the
    // ol rule at the specificity of the ul rule: the CSS follows core's
    // Stylelint rules (CHANGELOG, CSS lint entry; weak citation).
    $this->assertStringContainsString('<ol class="solo-ul solo-medium solo-breadcrumb__menu">', $tpl);
    $this->assertStringNotContainsString('<ul', $tpl);
    $css = $this->tpl('css/components/solo-breadcrumb.css');
    $this->assertStringContainsString("ul.solo-breadcrumb__menu,\n:where(ol).solo-breadcrumb__menu {", $css);
    $this->assertStringContainsString("ul.solo-breadcrumb__menu li > a,\nol.solo-breadcrumb__menu li > a {", $css);
    $this->assertStringContainsString("ul.solo-breadcrumb__menu li > span,\nol.solo-breadcrumb__menu li > span {", $css);
    $this->assertStringContainsString(".solo-page--breadcrumb-scroll ul.solo-breadcrumb__menu,\n:where(.solo-page--breadcrumb-scroll) ol.solo-breadcrumb__menu {", $css);
  }

  /**
   * SS-D1: main and sidebar wrappers are landmark elements, ids kept.
   */
  public function testLandmarkElements(): void {
    $src = $this->tpl('partials/_main-regions.html.twig');
    // CHANGED ON PURPOSE: the ids carry the solo- prefix (docs/CLASS-MIGRATION.md:
    // sidebar-box-main, sidebar-box-first and sidebar-box-second).
    $this->assertStringContainsString('<main id="solo-sidebar-box-main" class="{{ content_outer_classes }}">', $src);
    $this->assertStringContainsString('<aside id="solo-sidebar-box-first" class="{{ sidebar_first_outer_classes }}" aria-label="{{ \'Sidebar First\'|t }}">', $src);
    $this->assertStringContainsString('<aside id="solo-sidebar-box-second" class="{{ sidebar_second_outer_classes }}" aria-label="{{ \'Sidebar Second\'|t }}">', $src);
    $this->assertSame(1, substr_count($src, '<main '));
    $this->assertSame(1, substr_count($src, '</main>'));
    $this->assertSame(2, substr_count($src, '</aside>'));
    $this->assertStringNotContainsString('role="main"', $src);
    $this->assertStringNotContainsString('role="complementary"', $src);
    // CHANGED ON PURPOSE: the skip target prints only with its skip link
    // ("a skip link ... shows only when its region has content", CHANGELOG).
    $this->assertStringContainsString("{% if enabled_skip_links.skip_main_content %}\n          <a id=\"main-content\" tabindex=\"-1\" class=\"solo-skip-target\"></a>\n          {% endif %}", $src);
    // CHANGED ON PURPOSE: "the footer regions, footer menu and copyright band
    // sit in one <footer> landmark (#solo-footer-landmark); the copyright band
    // is now a <div> inside it" (CHANGELOG).
    $copy = $this->tpl('partials/_copyright.html.twig');
    $this->assertStringNotContainsString('<footer', $copy);
    $this->assertStringNotContainsString('</footer>', $copy);
    $this->assertSame(2, substr_count($copy, '<div id="solo-copyright"'));
    $page = $this->tpl('templates/layout/page.html.twig');
    $this->assertSame(1, substr_count($page, '<footer id="solo-footer-landmark" class="solo-footer-landmark">'));
    $this->assertSame(1, substr_count($page, '</footer>'));
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
