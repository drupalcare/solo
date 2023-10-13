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

if ($updated_regions['bottom']) {
  $region_label = implode(", ", $updated_regions['bottom']);
  $form['solo_settings']["settings_bottom_container"] = [
    '#type' => 'details',
    '#title' => t("$region_label regions color - Solo Theme"),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#description'   => t('<p> The colors for all stand alone regions.</p>'),
  ];
  foreach ($updated_regions['bottom'] as $region => $label) {
    if ($region == 'container') {
      $region = 'bottom_container';
      $form['solo_settings']["settings_bottom_container"]["site_regions_layout_bottom"] = _create_region_layout_radio('bottom');
      $form['solo_settings']["settings_bottom_container"]["site_regions_animate_bottom"] = _animate_region('bottom');
      $form['solo_settings']["settings_bottom_container"]["classes_{$region}"] = _generate_css_classes($region, $label);
      foreach ($attributes as $attribute_key => $attribute_label) {
        $form['solo_settings']["settings_bottom_container"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
      }
    }
    else {
      $form['solo_settings']['settings_bottom_container']["settings_{$region}"] = _generate_form_details($label);
      foreach ($attributes as $attribute_key => $attribute_label) {
        $form['solo_settings']['settings_bottom_container']["settings_{$region}"]["classes_{$region}"] = _generate_css_classes($region, $label);
        $form['solo_settings']['settings_bottom_container']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
      }
    }
  }
}
