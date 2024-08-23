<?php

/**
 * @file
 * Solo Theme.
 *
 * Filename:     solo.theme
 * Website:      http://www.flashwebcenter.com
 * Description:  template
 * Author:       Alaa Haddad http://www.alaahaddad.com.
 */

/**
 * The theme settings.
 */

$description = '<p><strong><a target="_blank" href="https://unitedstarsofamerica.com/color-schemes">The demo site provides examples for all of the color schemes.</a>.</strong></p><p>The Solo theme comes with 50 different color schemes. You can now select a specific color scheme to be applied to various parts of your website, such as content types, taxonomies, user roles, and specific URLs. To use this feature, ensure that the <a target="_blank" href="https://www.drupal.org/project/solo_utilities">Solo Utilities</a> module is enabled.</p>';

if (\Drupal::moduleHandler()->moduleExists('solo_utilities')) {
  $description .= '<p><strong><a href="/admin/config/system/solo_utilities">Click here to manage Library Load Rules.</a></strong></p>';
}

$form['solo_settings']['settings_predefined_theme'] = [
  '#type' => 'details',
  '#title' => t("<strong class='warning'>Predefined Color Schemes</strong> Settings - Solo Theme"),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
  '#description' => t($description),
];

$form['solo_settings']['settings_predefined_theme']['theme_category'] = [
  '#type' => 'select',
  '#title' => t('Select a category'),
  '#options' => _get_theme_category(),
  '#default_value' => theme_get_setting('theme_category'),
];

$form['solo_settings']['settings_predefined_theme']['predefined_current_theme'] = [
  '#type' => 'select',
  '#title' => t('Choose the desired color scheme'),
  '#options' => _get_predefined_theme(),
  '#states' => [
    'visible' => [
      ':input[name="theme_category"]' => ['!value' => 'none'],
    ],
  ],
  '#default_value' => theme_get_setting('predefined_current_theme'),
];

