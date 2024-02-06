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

if ($updated_regions['main']) {

  $region_label = implode(", ", $updated_regions['main']);

  $form['solo_settings']["settings_main_container"] = [
    '#type' => 'details',
    '#title' => t("<strong class='status'>Main Containers</strong> Settings - Solo Theme"),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#description'   => t("<p>The settings for @region_label.</p>", ['@region_label' => $region_label]),
  ];

  foreach ($updated_regions['main'] as $region => $label) {

    if ($region == 'container') {
      $region = 'main_container';
      $form['solo_settings']["settings_main_container"]["main_2col"] = _create_region_layout_radio_two('main', $variables);
      $form['solo_settings']["settings_main_container"]["main_3col"] = _create_region_layout_radio_three('main');
      $form['solo_settings']["settings_main_container"]["site_regions_main_rounded"] = _rounded_region('main');
      $form['solo_settings']["settings_main_container"]["site_regions_main_border"] = _border_region('main');

      $form['solo_settings']['settings_main_container']['site_flip_main_left'] = [
        '#type' => 'checkbox',
        '#title' => t('Tick this box to rearrange mobile layout to show Main Content, then Left and Right Sidebars.'),
        '#description'   => t('<p>This layout prioritizes the visibility of the main content when viewed on smaller screens.</p>'),
        '#default_value' => theme_get_setting('site_flip_main_left'),
      ];

      $form['solo_settings']["settings_main_container"]["classes_{$region}"] = _generate_css_classes($region, $label);

      foreach ($attributes as $attribute_key => $attribute_label) {
        $form['solo_settings']["settings_main_container"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
      }
    }
    else {
      $form['solo_settings']['settings_main_container']["settings_{$region}"] = _generate_form_details($label);
      foreach ($attributes as $attribute_key => $attribute_label) {
        $form['solo_settings']['settings_main_container']["settings_{$region}"]["classes_{$region}"] = _generate_css_classes($region, $label);
        $form['solo_settings']['settings_main_container']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
      }
    }
  }

}
