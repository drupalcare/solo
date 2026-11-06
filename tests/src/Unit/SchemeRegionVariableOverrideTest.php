<?php

declare(strict_types=1);

namespace Drupal\Tests\solo\Unit;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Tests that a color scheme cannot cut a region's own color off its content.
 *
 * In Solo 1 a region's color settings were inline custom properties on the
 * region wrapper (style="--r-br:#hex"). Schemes then pinned --r-br on every
 * descendant ("<container> *"), which beat the inherited inline value, so a
 * region's "borders" setting colored only the wrapper (#3629228). The fix was
 * a guard on each descendant rule: :where(:not([style*="--r-br:"] *)).
 *
 * Solo 2 prints no inline style. Solo Utilities writes each region's colors as
 * a rule on the region element (ThemeCssBuilder::REGION_SELECTORS), and the
 * content inherits them. The guard can no longer match anything, so a scheme
 * rule that declares a region variable on a region's DESCENDANTS always wins
 * over the inherited setting, at any specificity, because a directly matched
 * declaration beats an inherited value. Four such rules survived the class
 * rename and were removed; this test keeps them out.
 *
 * What the two Solo 1 tests checked on the region element itself (a scheme
 * rule against the generated region rule) is now check-cascade.py P1, run in
 * the css-checks CI job, which reads the generated selectors.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 *
 * @group solo
 */
#[Group('solo')]
class SchemeRegionVariableOverrideTest extends TestCase {

  /**
   * The theme root.
   */
  protected function themePath(): string {
    return dirname(__DIR__, 3);
  }

  /**
   * Splits a stylesheet into [selector list, declaration block] pairs.
   *
   * @return array<int, array{0: string, 1: string}>
   *   One entry per rule.
   */
  protected function rules(string $css): array {
    $css = (string) preg_replace('#/\*.*?\*/#s', '', $css);
    preg_match_all('#([^{}]+)\{([^{}]*)\}#', $css, $m, PREG_SET_ORDER);
    return array_map(fn(array $r): array => [trim($r[1]), $r[2]], $m);
  }

  /**
   * Lists selectors that set a region variable on every descendant.
   *
   * The last compound of such a selector names no element: a bare "*", or
   * the Solo 1 guard :where(:not([style*="--r-xx:"] *)) alone after a
   * descendant combinator.
   *
   * @return list<string>
   *   "selector declares --r-xx" entries.
   */
  protected function descendantPins(string $css): array {
    $pins = [];
    foreach ($this->rules($css) as [$selectors, $body]) {
      if (!preg_match_all('/(--r-[a-z-]+)\s*:/', $body, $vars)) {
        continue;
      }
      // Split on top-level commas only: :not(a, b) is one selector.
      foreach (preg_split('/,(?![^()]*\))/', $selectors) ?: [] as $selector) {
        $selector = trim((string) preg_replace('/\s+/', ' ', $selector));
        if (preg_match('/\s(\*|:where\(:not\(\[style\*="--r-[a-z-]+:"\] \*\)\))$/', $selector) === 1) {
          $pins[] = sprintf('"%s" declares %s', $selector, implode(', ', array_unique($vars[1])));
        }
      }
    }
    return $pins;
  }

  /**
   * No scheme declares a region variable on every descendant of an element.
   */
  public function testSchemesDoNotPinRegionVariablesOnDescendants(): void {
    $files = glob($this->themePath() . '/css/predefined/*.css') ?: [];
    $this->assertGreaterThan(40, count($files), 'The color schemes were not found.');

    $offenders = [];
    $declaring = 0;
    foreach ($files as $file) {
      $css = (string) file_get_contents($file);
      $declaring += preg_match_all('/--r-[a-z-]+\s*:/', $css);
      foreach ($this->descendantPins($css) as $pin) {
        $offenders[] = basename($file) . ': ' . $pin;
      }
    }
    // Control: the schemes really declare region variables.
    $this->assertGreaterThan(500, $declaring, 'Almost no region variables read; the scan is wrong, not the schemes.');
    $this->assertSame([], $offenders,
      'A scheme rule on a region\'s descendants re-declares a region variable '
      . 'on each element, so the region\'s own color (a rule on the region, '
      . 'inherited by its content) never reaches the content. Set it on the '
      . 'region element only.');
  }

  /**
   * The scan flags both shapes of a descendant pin and passes element rules.
   */
  public function testScanCatchesDescendantPins(): void {
    $guard = ':where(:not([style*="--r-br:"] *))';
    $this->assertCount(1, $this->descendantPins(":where(.solo-page .solo-container--main) $guard { --r-br: red; }"));
    $this->assertCount(1, $this->descendantPins('.solo-page .solo-container--main * { --r-br: red; }'));
    $this->assertSame([], $this->descendantPins(":where(.solo-page) .solo-container--main$guard { --r-br: red; }"));
    $this->assertSame([], $this->descendantPins(":where(.solo-page) .solo-input$guard, .a :is(.b, .c) .d { --r-br: red; }"));
    $this->assertSame([], $this->descendantPins(':where(.solo-page) :is(.solo-container--footer a, .solo-copyright a) { --r-tx: red; }'), 'Real elements in :is().');
    $this->assertSame([], $this->descendantPins('.solo-page .x * { color: red; }'), 'Only region variables count.');
  }

}
