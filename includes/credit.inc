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

$form['solo_settings']['credit_link'] = [
  '#type' => 'details',
  '#title' => t('Credit - Solo Theme'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
];
$form['solo_settings']['credit_link']['show_credit_link'] = [
  '#type' => 'checkbox',
  '#title' => t('Show/Hide Credit Text'),
  '#default_value' => theme_get_setting('show_credit_link'),
  '#description'   => t('Please display credit in footer'),
];
