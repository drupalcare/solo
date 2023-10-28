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
$region = 'credit_copyright';
$label = 'credit & Copyright';
$form['solo_settings']["settings_{$region}"] = [
  '#type' => 'details',
  '#title' => t("<strong class='purple'>Copyright & Credit</strong> Settings - Solo Theme"),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
];

$form['solo_settings']["settings_{$region}"]["classes_{$region}"] = _generate_css_classes($region, $label);

foreach ($attributes as $attribute_key => $attribute_label) {
  $form['solo_settings']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
}

$form['solo_settings']["settings_{$region}"]['footer_copyright'] = [
  '#type' => 'textfield',
  '#title' => t('Enter Copyright Text'),
  '#description' => t("Type %year% instead of an actual year number for dynamic copyright. (Copyright © %year% Company Name - All rights reserved)."),
  '#default_value' => theme_get_setting('footer_copyright'),
];

$form['solo_settings']["settings_{$region}"]['footer_credit_link'] = [
  '#type' => 'checkbox',
  '#title' => t('Show/Hide Credit Text'),
  '#default_value' => theme_get_setting('footer_credit_link'),
  '#description'   => t('Please display credit in footer'),
];
