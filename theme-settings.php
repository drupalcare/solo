<?php

/**
 * @file
 * Solo Theme.
 *
 * Filename:     theme-settings
 * Website:      http://www.flashwebcenter.com
 * Description:  template
 * Author:       Alaa Haddad http://www.alaahaddad.com.
 */

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Config;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function solo_form_system_theme_settings_alter(&$form, FormStateInterface $form_state) {
  $form['#validate'][] = 'solo_theme_settings_validate';
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
  require_once __DIR__ . '/includes/_theme_settings_blueprint.inc';
  require_once __DIR__ . '/includes/_theme_settings_global_misc.inc';
  require_once __DIR__ . '/includes/_theme_settings_libraries_fonts.inc';
  require_once __DIR__ . '/includes/_theme_settings_search_results.inc';
  require_once __DIR__ . '/includes/_theme_settings_predefined_themes.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_page_wrapper.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_highlighted.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_popup_login_block.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_fixed_search_block.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_header.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_primary_sidebar_menu.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_primary_menu.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_welcome_text.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_top.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_system_messages.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_page_title.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_breadcrumb.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_main.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_bottom.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_footer.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_footer_menu.inc';
  require_once __DIR__ . '/includes/_theme_settings_sm_icons.inc';
  require_once __DIR__ . '/includes/_theme_settings_credit_copyright.inc';

  $form['#submit'][] = '_solo_theme_settings_submit';
}

/**
 * Validation handler for the Solo system_theme_settings form.
 */
function solo_theme_settings_validate($form, FormStateInterface $form_state) {
  // Only validate separate footer link fields when not using formatted text.
  if ($form_state->getValue('footer_use_formatted_text')) {
    return;
  }

  $url = $form_state->getValue('footer_link');
  $text = $form_state->getValue('footer_link_text');

  if ($url !== '' && !UrlHelper::isValid($url, TRUE)) {
    $form_state->setErrorByName('footer_link', t('The URL %url is not valid.', [
      '%url' => $url,
    ]));
  }

  if (!empty($url) && empty($text)) {
    $form_state->setErrorByName('footer_link_text', t('You must enter link text if you provide a URL.'));
  }
}

/**
 * Sets or clears a layout config value based on global comparison.
 *
 * @param \Drupal\Core\Config\Config $config
 *   The editable theme config object.
 * @param string $key
 *   The per-content-type config key (e.g. 'solo_layout_main_2col_article').
 * @param mixed $value
 *   The user-submitted value.
 * @param mixed $global
 *   The global fallback value.
 *
 * @return void
 *   This function does not return a value.
 */
function _solo_set_or_clear_layout(Config $config, $key, $value, $global) {
  if ($value !== NULL && $value !== $global) {
    $config->set($key, $value);
  }
  else {
    $config->clear($key);
  }
}

/**
 * Form submit handler for the Solo theme settings form.
 *
 * Saves layout configuration values for grouped regions (`top`, `main`,
 * `bottom`, `footer`),
 * including global layout selections and optional per-content-type overrides
 * for 2-column, 3-column, and 4-column layouts.
 *
 *
 * If per-content-type layout overrides are enabled for a region, the handler
 * compares each value to the global default and saves only the differing ones.
 * If overrides are disabled, any previously stored overrides for that region
 * and content type are cleared.
 *
 * This ensures that all layout-related settings remain clean and fallback to
 * the global configuration when no override is present.
 *
 * @param array $form
 *   The complete form structure.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The current state of the submitted form.
 *
 * @see _solo_set_or_clear_layout()
 */
function _solo_theme_settings_submit($form, FormStateInterface $form_state) {
  // Get the theme whose settings are being altered.
  $theme = $form_state->getBuildInfo()['args'][0];
  $config = \Drupal::configFactory()->getEditable("$theme.settings");

  $content_types = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();
  $regions = ['top', 'main', 'bottom', 'footer'];

  // Handle per-content-type width cleanup when disabled.
  $custom_widths_enabled = (bool) $form_state->getValue('enable_custom_widths', FALSE);
  $config->set('enable_custom_widths', $custom_widths_enabled);

  if (!$custom_widths_enabled) {
    foreach ($content_types as $type) {
      $key = "site_width_{$type->id()}";
      if ($config->get($key) !== NULL) {
        $config->clear($key);
      }
    }
  }

  foreach ($regions as $region) {
    $enable_key = "enable_per_type_layout_$region";
    $enabled = (bool) $form_state->getValue($enable_key, FALSE);
    $config->set($enable_key, $enabled);

    foreach ($content_types as $type_id => $type) {
      $key_2col = "solo_layout_{$region}_2col_$type_id";
      $key_3col = "solo_layout_{$region}_3col_$type_id";
      $key_4col = "solo_layout_{$region}_4col_$type_id";
      if ($enabled) {
        foreach ([2, 3, 4] as $col) {
          $key = "solo_layout_{$region}_{$col}col_$type_id";
          $val = $form_state->getValue($key);
          $global = $form_state->getValue("{$region}_{$col}col");
          _solo_set_or_clear_layout($config, $key, $val, $global);
        }
      }
      else {
        // On disable, always clear overrides (they will fallback to global).
        if ($config->get($key_2col) !== NULL) {
          $config->clear($key_2col);
        }
        if ($config->get($key_3col) !== NULL) {
          $config->clear($key_3col);
        }
        if ($config->get($key_4col) !== NULL) {
          $config->clear($key_4col);
        }
      }
    }
  }
  if (!$form_state->getValue('header_popup_login')) {
    // Reset ALL popup login settings to defaults.
    $settings_to_reset = [
      'header_login_links' => 'Login',
      'popup_login_use_inline_styles' => FALSE,
      'popup_login_animation_duration' => 300,
      'popup_login_close_on_escape' => TRUE,
      'popup_login_close_on_outside_click' => TRUE,
      'popup_login_focus_trap' => TRUE,
      'popup_login_announce_to_screen_readers' => TRUE,
      'popup_login_return_focus_on_close' => TRUE,
      'popup_login_custom_triggers' => '',
      'popup_login_z_index' => 10000,
      'popup_login_overlay_opacity' => 50,
    ];

    // Reset each setting.
    foreach ($settings_to_reset as $key => $default_value) {
      $form_state->setValue($key, $default_value);
    }

    // Clear the stored theme settings.
    foreach ($settings_to_reset as $key => $default_value) {
      $config->clear($key);
    }

  }

  // Menu template assignments (menu_id + template per row).
  $path = [
    'solo_settings',
    'settings_global_misc',
    'menu_template_assignment',
    'menu_template_assignments',
  ];
  $assignments = NestedArray::getValue($form_state->getValues(), $path);
  if (!is_array($assignments)) {
    $assignments = [];
  }
  $cleaned = [];
  foreach ($assignments as $row) {
    if (is_array($row) && !empty($row['menu_id']) && !empty($row['template'])) {
      $cleaned[] = [
        'menu_id' => $row['menu_id'],
        'template' => $row['template'],
      ];
    }
  }
  $config->set('menu_template_assignments', $cleaned);

  // Preloader settings (may be under global_misc_tabs when tab is used).
  $preloader_paths = [
    ['solo_settings', 'settings_global_misc', 'global_misc_tabs', 'preloader'],
    ['solo_settings', 'settings_global_misc', 'preloader'],
  ];
  $preloader_form = [];
  foreach ($preloader_paths as $path) {
    $v = NestedArray::getValue($form_state->getValues(), $path);
    if (is_array($v)) {
      $preloader_form = $v;
      break;
    }
  }
  $preloader_enabled = !empty($preloader_form['preloader_enabled']);

  if (!$preloader_enabled) {
    // User disabled the preloader: reset all preloader settings to defaults.
    $preloader_defaults = [
      'preloader_enabled' => 0,
      'preloader_force_show' => 0,
      'preloader_disable_authenticated' => 1,
      'preloader_disable_admin_routes' => 1,
      'preloader_path_rules' => '',
      'preloader_style' => 'spinner',
      'preloader_logo_url' => '',
      'preloader_text' => '',
      'preloader_text_font' => '',
      'preloader_text_font_size' => 24,
      'preloader_text_animate' => FALSE,
    ];
    foreach ($preloader_defaults as $key => $val) {
      $config->set($key, $val);
    }
    $config->set('settings_preloader___r_bg', '');
    $config->set('settings_preloader___r_tx', '');
  }
  else {
    $preloader_keys = [
      'preloader_enabled',
      'preloader_force_show',
      'preloader_disable_authenticated',
      'preloader_disable_admin_routes',
      'preloader_path_rules',
      'preloader_style',
      'preloader_logo_url',
      'preloader_text',
      'preloader_text_font',
      'preloader_text_font_size',
      'preloader_text_animate',
    ];
    foreach ($preloader_keys as $key) {
      $val = $preloader_form[$key] ?? NULL;
      if ($val === NULL && isset($preloader_form['visibility'][$key])) {
        $val = $preloader_form['visibility'][$key];
      }
      if ($val === NULL && isset($preloader_form['appearance'][$key])) {
        $val = $preloader_form['appearance'][$key];
      }
      if ($val !== NULL) {
        $config->set($key, $val);
      }
    }
    // Preloader colors: read from appearance and save to config reload in form.
    $app = $preloader_form['appearance'] ?? [];
    $bg = $app['settings_preloader___r_bg'] ?? NULL;
    $tx = $app['settings_preloader___r_tx'] ?? NULL;
    if ($bg === NULL || $tx === NULL) {
      $values = $form_state->getValues();
      $with_tabs = NestedArray::getValue($values, [
        'solo_settings',
        'settings_global_misc',
        'global_misc_tabs',
        'preloader',
        'appearance',
      ]);
      $no_tabs = NestedArray::getValue($values, [
        'solo_settings',
        'settings_global_misc',
        'preloader',
        'appearance',
      ]);
      $appearance = is_array($with_tabs) ? $with_tabs : (is_array($no_tabs) ? $no_tabs : []);
      if ($bg === NULL && isset($appearance['settings_preloader___r_bg'])) {
        $bg = $appearance['settings_preloader___r_bg'];
      }
      if ($tx === NULL && isset($appearance['settings_preloader___r_tx'])) {
        $tx = $appearance['settings_preloader___r_tx'];
      }
    }
    $config->set('settings_preloader___r_bg', $bg ?? '');
    $config->set('settings_preloader___r_tx', $tx ?? '');
  }

  // Back to top: nested form values via NestedArray (D11).
  $back_to_top_paths = [
    [
      'solo_settings',
      'settings_global_misc',
      'global_misc_tabs',
      'back_to_top',
    ],
    [
      'solo_settings',
      'settings_global_misc',
      'back_to_top',
    ],
  ];
  $values = $form_state->getValues();
  $back_to_top_form = [];
  foreach ($back_to_top_paths as $path) {
    $v = NestedArray::getValue($values, $path);
    if (is_array($v)) {
      $back_to_top_form = $v;
      break;
    }
  }
  if (is_array($back_to_top_form)) {
    $back_to_top_settings = [
      'back_to_top_enabled' => $back_to_top_form['back_to_top_enabled'] ?? NULL,
      'back_to_top_disable_admin_routes' => NestedArray::getValue($back_to_top_form, [
        'visibility',
        'back_to_top_disable_admin_routes',
      ]),
      'back_to_top_disable_authenticated' => NestedArray::getValue($back_to_top_form, [
        'visibility',
        'back_to_top_disable_authenticated',
      ]),
      'back_to_top_hide_small_screens' => NestedArray::getValue($back_to_top_form, [
        'visibility',
        'back_to_top_hide_small_screens',
      ]),
      'back_to_top_scroll_threshold' => NestedArray::getValue($back_to_top_form, [
        'visibility',
        'back_to_top_scroll_threshold',
      ]),
      'back_to_top_position' => NestedArray::getValue($back_to_top_form, [
        'position',
        'back_to_top_position',
      ]),
      'back_to_top_style' => NestedArray::getValue($back_to_top_form, [
        'style',
        'back_to_top_style',
      ]),
      'back_to_top_icon' => NestedArray::getValue($back_to_top_form, [
        'style',
        'back_to_top_icon',
      ]),
    ];
    foreach ($back_to_top_settings as $key => $val) {
      if ($val !== NULL) {
        $config->set($key, $val);
      }
    }
    $bt_style = $back_to_top_form['style'] ?? [];
    $bt_bg = is_array($bt_style) ? ($bt_style['settings_back_to_top___r_bg'] ?? NULL) : NULL;
    $bt_tx = is_array($bt_style) ? ($bt_style['settings_back_to_top___r_tx'] ?? NULL) : NULL;
    if ($bt_bg === NULL || $bt_tx === NULL) {
      $with_tabs = NestedArray::getValue($values, [
        'solo_settings',
        'settings_global_misc',
        'global_misc_tabs',
        'back_to_top',
        'style',
      ]);
      $no_tabs = NestedArray::getValue($values, [
        'solo_settings',
        'settings_global_misc',
        'back_to_top',
        'style',
      ]);
      $style_arr = is_array($with_tabs) ? $with_tabs : (is_array($no_tabs) ? $no_tabs : []);
      if ($bt_bg === NULL && isset($style_arr['settings_back_to_top___r_bg'])) {
        $bt_bg = $style_arr['settings_back_to_top___r_bg'];
      }
      if ($bt_tx === NULL && isset($style_arr['settings_back_to_top___r_tx'])) {
        $bt_tx = $style_arr['settings_back_to_top___r_tx'];
      }
    }
    $config->set('settings_back_to_top___r_bg', $bt_bg !== NULL && $bt_bg !== '' ? $bt_bg : '');
    $config->set('settings_back_to_top___r_tx', $bt_tx !== NULL && $bt_tx !== '' ? $bt_tx : '');
  }

  // Update file usage for embedded files in the copyright formatted text.
  _solo_footer_formatted_file_usage($form_state, $theme);

  // Save configuration.
  \Drupal::configFactory()->reset($theme . '.settings');
  $config->save();

  // Clear theme registry.
  \Drupal::service('theme.registry')->reset();

  // Clear library discovery - use the correct service and method.
  \Drupal::service('library.discovery')->clearCachedDefinitions();

  // Clear Twig cache.
  \Drupal::service('twig')->invalidate();

  // Invalidate config cache tags.
  Cache::invalidateTags(['config:' . $theme . '.settings']);
}

/**
 * Parses HTML for file entity UUIDs (data-entity-type="file" data-entity-uuid).
 *
 * Mirrors the logic of editor_parse_file_uuids() so theme settings can track
 * embedded file usage without requiring the editor module.
 *
 * @param string $text
 *   Partial (X)HTML snippet.
 *
 * @return array
 *   Array of file entity UUIDs found in the markup.
 *
 * @see editor_parse_file_uuids()
 */
function _solo_parse_file_uuids_from_html($text) {
  if (empty($text) || !is_string($text)) {
    return [];
  }
  $dom = Html::load($text);
  $xpath = new \DOMXPath($dom);
  $uuids = [];
  foreach ($xpath->query('//*[@data-entity-type="file" and @data-entity-uuid]') as $node) {
    $uuids[] = $node->getAttribute('data-entity-uuid');
  }
  return $uuids;
}

/**
 * Updates file usage for files embedded in the copyright formatted text.
 *
 * Marks newly referenced files as permanent and tracks usage; removes usage
 * for files no longer present when the content is updated.
 *
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state (contains the new formatted value).
 * @param string $theme
 *   The theme machine name (used as the file usage module/theme identifier).
 */
function _solo_footer_formatted_file_usage(FormStateInterface $form_state, $theme) {
  $formatted = $form_state->getValue('footer_copyright_formatted');
  if (!is_array($formatted) || empty($formatted['value'])) {
    $formatted = ['value' => '', 'format' => 'basic_html'];
  }
  $new_uuids = _solo_parse_file_uuids_from_html($formatted['value']);

  $config = \Drupal::config($theme . '.settings');
  $old_formatted = $config->get('footer_copyright_formatted');
  $old_value = is_array($old_formatted) && isset($old_formatted['value']) ? $old_formatted['value'] : '';
  $old_uuids = _solo_parse_file_uuids_from_html($old_value);

  $entity_repository = \Drupal::service('entity.repository');
  $file_usage = \Drupal::service('file.usage');

  foreach (array_diff($new_uuids, $old_uuids) as $uuid) {
    $file = $entity_repository->loadEntityByUuid('file', $uuid);
    if ($file && $file->isTemporary()) {
      $file->setPermanent();
      $file->save();
    }
    if ($file) {
      $file_usage->add($file, $theme, 'theme', $theme);
    }
  }

  foreach (array_diff($old_uuids, $new_uuids) as $uuid) {
    $file = $entity_repository->loadEntityByUuid('file', $uuid);
    if ($file) {
      $file_usage->delete($file, $theme, 'theme', $theme);
    }
  }
}
