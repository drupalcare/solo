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

if ($updated_regions['top']) {
  $region_label = implode(", ", $updated_regions['top']);
  $form['solo_settings']["settings_top_container"] = [
    '#type' => 'details',
    '#title' => t("$region_label regions color - Solo Theme"),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#description'   => t('<p> The colors for all stand alone regions.</p>'),
  ];
  foreach ($updated_regions['top'] as $region => $label) {
    if ($region == 'container') {
      $region = 'top_container';
      $form['solo_settings']["settings_top_container"]["site_regions_layout_top"] = _create_region_layout_radio('top');
      $form['solo_settings']["settings_top_container"]["site_regions_animate_top"] = _animate_region('top');
      $form['solo_settings']["settings_top_container"]["classes_{$region}"] = _generate_css_classes($region, $label);
      foreach ($attributes as $attribute_key => $attribute_label) {
        $form['solo_settings']["settings_top_container"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
      }
    }
    else {
      $form['solo_settings']['settings_top_container']["settings_{$region}"] = _generate_form_details($label);
      foreach ($attributes as $attribute_key => $attribute_label) {
        $form['solo_settings']['settings_top_container']["settings_{$region}"]["classes_{$region}"] = _generate_css_classes($region, $label);
        $form['solo_settings']['settings_top_container']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
      }
    }
  }
}
