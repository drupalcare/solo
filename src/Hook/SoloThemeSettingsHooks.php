<?php

namespace Drupal\solo\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\solo\SoloIncludes;

/**
 * Theme settings form hook for solo.
 *
 * Drupal 11.5.0 deprecated theme-settings.php (removed in drupal:12.0.0) and
 * core triggers on the file existing, not on its contents. On Drupal 12 core
 * also scans only "<theme>.theme" and "theme-settings.php" for PROCEDURAL
 * theme hooks, so a hook moved into includes/ would be invisible there and the
 * settings page would return HTTP 200 with every Solo section missing. The
 * hook therefore lives here, and includes/_solo_theme_settings.inc keeps a
 * #[LegacyHook] caller for Drupal 10 and Drupal 11 before 11.5.
 *
 * The 23 require_once calls below are deliberate and load INTO this method's
 * scope: each include reads $form, $form_state, $attributes, $updated_regions
 * and $counts from here and none of them stands alone. That is why the
 * variables are assigned before the includes and why they look unused.
 */
class SoloThemeSettingsHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_form_system_theme_settings_alter().
   */
  #[Hook('form_system_theme_settings_alter')]
  public function formSystemThemeSettingsAlter(&$form, FormStateInterface $form_state): void {
    SoloIncludes::load();
    $form['#validate'][] = 'solo_theme_settings_validate';
    // The callbacks this form names live in the include files; a cached
    // rebuild must load them without solo.theme.
    SoloIncludes::addToFormBuildInfo($form_state);
    $form['solo_readiness'] = $this->buildReadinessPanel();
    $form['logo']['#weight'] = 97;
    $form['favicon']['#open'] = FALSE;
    $form['favicon']['#weight'] = 98;
    $form['theme_settings']['#open'] = FALSE;
    $form['theme_settings']['#weight'] = 99;

    $form['#attached']['library'][] = 'solo/solo-form-theme-settings';
    // Variables below are used by required theme settings include files.
    // phpcs:disable DrupalPractice.CodeAnalysis.VariableAnalysis.UnusedVariable
    $d_s = date('j  F,  Y');
    $d_m = date('D F d, o');
    $d_l = date('g:i A T, D F d, o');
    $updated_regions = _get_updated_regions();
    $counts = _count_regions();
    $attributes = _get_region_attributes();
    // phpcs:enable DrupalPractice.CodeAnalysis.VariableAnalysis.UnusedVariable

    $layout_region_override_toggles = [
      'enable_per_type_layout_top' => 0,
      'enable_per_type_layout_main' => 0,
      'enable_per_type_layout_bottom' => 0,
      'enable_per_type_layout_footer' => 0,
    ];

    foreach ($layout_region_override_toggles as $setting_key => $default_value) {
      if (!isset($form_state->getValues()[$setting_key])) {
        $form_state->setValue($setting_key, $default_value);
      }
    }

    // Theme settings files.
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_blueprint.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_global_misc.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_libraries_fonts.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_search_results.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_predefined_themes.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_page_wrapper.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_highlighted.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_popup_login_block.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_fixed_search_block.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_header.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_primary_sidebar_menu.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_primary_menu.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_welcome_text.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_top.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_system_messages.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_page_title.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_breadcrumb.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_main.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_bottom.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_footer.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_layout_footer_menu.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_sm_icons.inc';
    require_once dirname(__DIR__, 2) . '/includes/_theme_settings_credit_copyright.inc';

    $form['#submit'][] = '_solo_theme_settings_submit';
  }

  /**
   * Builds the read-only "Solo 2 readiness" panel.
   *
   * The scanner lives in the Solo Utilities module so Solo carries one
   * scanner and no second copy. Without the module the panel only says so.
   *
   * @return array<string, mixed>
   *   A details element. It saves nothing: it has no form input.
   */
  protected function buildReadinessPanel(): array {
    $panel = [
      '#type' => 'details',
      '#title' => $this->t('Solo 2 readiness'),
      '#open' => FALSE,
      '#weight' => 96,
    ];
    if (!\Drupal::hasService('solo_utilities.readiness_scanner')) {
      $panel['note'] = [
        '#markup' => '<p>' . $this->t('Solo 2 needs the Solo Utilities module. Enable Solo Utilities (1.x) to check your Solo sub-themes for what Solo 2 will change. Nothing was scanned.') . '</p>',
      ];
      return $panel;
    }

    $result = \Drupal::service('solo_utilities.readiness_scanner')->scan();
    $panel['intro'] = [
      '#markup' => '<p>' . $this->t('This check only reads your Solo sub-themes. It changes no file and no setting. The same list is available with <code>drush solo:preflight</code>.') . '</p>',
    ];
    if ($result['subthemes'] === []) {
      $panel['none'] = ['#markup' => '<p>' . $this->t('No installed Solo sub-theme was found. Nothing to change.') . '</p>'];
      return $panel;
    }
    $total = 0;
    foreach ($result['subthemes'] as $name => $subtheme) {
      $count = count($subtheme['findings']);
      $total += $count;
      $panel[$name] = [
        '#type' => 'details',
        '#title' => $count === 0
          ? $this->t('@name: ready', ['@name' => $name])
          : $this->formatPlural($count, '@name: 1 item to change', '@name: @count items to change', ['@name' => $name]),
        '#open' => $count > 0,
      ];
      if ($count === 0) {
        $panel[$name]['ok'] = ['#markup' => '<p>' . $this->t('Nothing to change.') . '</p>'];
        continue;
      }
      $rows = [];
      foreach ($subtheme['findings'] as $finding) {
        $rows[] = [
          $finding['type'],
          $finding['file'] . ($finding['line'] > 0 ? ':' . $finding['line'] : ''),
          $finding['detail'],
        ];
      }
      $panel[$name]['table'] = [
        '#type' => 'table',
        '#header' => [$this->t('Kind'), $this->t('Where'), $this->t('What to change')],
        '#rows' => $rows,
      ];
    }
    if ($total > 0) {
      $panel['#open'] = TRUE;
    }
    return $panel;
  }

}
