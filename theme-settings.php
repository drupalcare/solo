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

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function solo_form_system_theme_settings_alter(&$form, FormStateInterface $form_state) {
  $form['logo']['#weight'] = 97;
  $form['favicon']['#open'] = FALSE;
  $form['favicon']['#weight'] = 98;
  $form['theme_settings']['#open'] = FALSE;
  $form['theme_settings']['#weight'] = 99;

  $form['#attached']['library'][] = 'solo/solo-form-theme-settings';
  $d_s = date('j  F,  Y');
  $d_m = date('D F d, o');
  $d_l = date('g:i A T, D F d, o');
  $updated_regions = _get_updated_regions();
  $counts = _count_regions();
  $attributes = _get_region_attributes();

  // Theme settings files.
  require_once __DIR__ . '/includes/_theme_settings_blueprint.inc';
  require_once __DIR__ . '/includes/_theme_settings_global_misc.inc';
  require_once __DIR__ . '/includes/_theme_settings_libraries_fonts.inc';
  require_once __DIR__ . '/includes/_theme_settings_predefined_themes.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_page_wrapper.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_popup_login_block.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_fixed_search_block.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_headeer.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_primary_sidebar_menu.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_primary_menu.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_welcome_text.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_top.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_highlighted.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_system_messages.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_page_title.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_breadcrumb.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_main.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_bottom.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_footer.inc';
  require_once __DIR__ . '/includes/_theme_settings_layout_footer_menu.inc';
  require_once __DIR__ . '/includes/_theme_settings_sm_icons.inc';
  require_once __DIR__ . '/includes/_theme_settings_credit_copyright.inc';
}
