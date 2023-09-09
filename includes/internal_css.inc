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

$form['solo_settings']['w3_internal_css'] = [
  '#type' => 'details',
  '#title' => t('Add internal CSS - Solo Theme'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
  '#description'   => t("Add custom css to the theme."),
];

$form['solo_settings']['w3_internal_css']['internal_css'] = [
  '#type' => 'textarea',
  '#title' => t('internal CSS'),
  '#default_value' => theme_get_setting('internal_css'),
];
