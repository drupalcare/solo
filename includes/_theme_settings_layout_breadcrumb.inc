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
if (array_key_exists('breadcrumb', $updated_regions['mix'])) {
  $region = 'breadcrumb';
  $label = $updated_regions['mix']['breadcrumb'];
  $form['solo_settings']["settings_{$region}"] = _generate_form_details($label);
  $form['solo_settings']["settings_{$region}"]["classes_{$region}"] = _generate_css_classes($region, $label);

  $form['solo_settings']["settings_{$region}"]['site_breadcrumb_pagetitle_off'] = [
    '#type' => 'checkbox',
    '#title' => t('Tick this box to exclude the page title from the breadcrumb.'),
    '#default_value' => theme_get_setting('site_breadcrumb_pagetitle_off'),
  ];

  foreach ($attributes as $attribute_key => $attribute_label) {
    $form['solo_settings']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
  }

}
