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

$form['solo_settings']['copyright'] = [
  '#type' => 'details',
  '#title' => t('Copyright - Solo Theme'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
];
$form['solo_settings']['copyright']['copyright_text'] = [
  '#type' => 'textfield',
  '#title' => t('Enter Copyright Text'),
  '#description' => t("Type %year% instead of an actual year number for dynamic copyright. (Copyright © %year% Company Name - All rights reserved)."),
  '#default_value' => theme_get_setting('copyright_text'),
];
