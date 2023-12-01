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

// Popup login block region.
if (array_key_exists('popup_login_block', $updated_regions['mix'])) {

  $region = 'popup_login_block';
  $label = $updated_regions['mix'][$region];

  $form['solo_settings']["settings_{$region}"] = _generate_form_details($label);
  $form['solo_settings']["settings_{$region}"]["classes_{$region}"] = _generate_css_classes($region, $label);

  $form['solo_settings']["settings_{$region}"]['header_popup_login'] = [
    '#type' => 'checkbox',
    '#title' => t("Check this box to login in a popup block. <strong><a class='error' href='/admin/structure/block'>The User Login Block Must Be Enabled</a></strong>"),
    '#default_value' => theme_get_setting('header_popup_login'),
  ];

  $form['solo_settings']["settings_{$region}"]['header_login_links'] = [
    '#type' => 'textfield',
    '#title' => t('You can change the login word.'),
    '#default_value' => theme_get_setting('header_login_links') ?? 'Login',
  ];

  foreach ($attributes as $attribute_key => $attribute_label) {
    $form['solo_settings']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
  }


}
