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

  $form['#attached']['library'][] = 'solo/solo-form-theme-settings';

  require_once __DIR__ . '/includes/theme_settings_helper_functions.inc';

  // Theme settings files.
  require_once __DIR__ . '/includes/theme_settings_blueprint.inc';
  require_once __DIR__ . '/includes/theme_settings_global_misc.inc';
  require_once __DIR__ . '/includes/theme_settings_libraries_fonts.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_page_wrapper.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_popup_login_block.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_fixed_search_block.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_headeer.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_primary_sidebar_menu.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_primary_menu.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_welcome_text.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_top.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_highlighted.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_system_messages.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_page_title.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_breadcrumb.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_main.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_bottom.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_footer.inc';
  require_once __DIR__ . '/includes/theme_settings_layout_footer_menu.inc';
  require_once __DIR__ . '/includes/theme_settings_predefined_themes.inc';
  require_once __DIR__ . '/includes/theme_settings_sm_icons.inc';
  require_once __DIR__ . '/includes/theme_settings_credit_copyright.inc';

}
