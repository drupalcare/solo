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

$form['solo_settings']['settings_predefined_theme'] = [
  '#type' => 'details',
  '#title' => t("<strong class='warning'>Predefined Color Themes</strong> Settings - Solo Theme"),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
  '#description'   => t('<p><strong><a target="_blank" href="https://drupal8-w3css-theme.flashwebcenter.com/w3css-predefined-color-themes">The demo site provides examples for all of the color themes.</a>.</strong></p>'),
];

$form['solo_settings']['settings_predefined_theme']['predefined_current_theme'] = [
  '#type' => 'select',
  '#title' => t("Choose the desired predefined theme"),
  '#options' => _get_predefined_theme(),
  '#default_value' => theme_get_setting('predefined_current_theme'),
];
