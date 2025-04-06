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
    '#title' => t("<strong class='status'>Top Containers</strong> Settings - Solo Theme"),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#description'   => t("<p>The settings for @region_label.</p>", ['@region_label' => $region_label]),
  ];

  foreach ($updated_regions['top'] as $region => $label) {

    if ($region == 'container') {
      $region = 'top_container';
      $form['solo_settings']["settings_top_container"]["top_2col"] = _create_region_layout_radio_two('top', $variables);
      $form['solo_settings']["settings_top_container"]["top_3col"] = _create_region_layout_radio_three('top');
      $form['solo_settings']["settings_top_container"]["site_regions_top_disable"] = _disable_region('top');
      $form['solo_settings']["settings_top_container"]["site_regions_top_rounded"] = _rounded_region('top');
      $form['solo_settings']["settings_top_container"]["site_regions_top_border"] = _border_region('top');
      $form['solo_settings']["settings_top_container"]["site_regions_top_animate_border"] = _animate_region_border('top');
      $form['solo_settings']["settings_top_container"]["site_regions_top_animate_hover"] = _animate_region_hover('top');
      $form['solo_settings']["settings_top_container"]["classes_{$region}"] = _generate_css_classes($region, $label);
      foreach ($attributes as $attribute_key => $attribute_label) {
        $form['solo_settings']["settings_top_container"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
      }
    }
    else {
      $form['solo_settings']['settings_top_container']["settings_{$region}"] = _generate_form_details($label);
      foreach ($attributes as $attribute_key => $attribute_label) {
        $form['solo_settings']['settings_top_container']["settings_{$region}"]["classes_{$region}"] = _generate_css_classes($region, $label);
        $form['solo_settings']['settings_top_container']["settings_{$region}"]["align_{$region}"] = [
          '#title' => t("Region Block Alignment Option"),
          '#type' => 'select',
          '#options' => _alignment_region($region),
          '#default_value' => theme_get_setting("align_{$region}") ?? 'region-left',
          '#description' => t("<p>By default, block content is left-aligned. This feature allows theme users to align blocks to the center or right within specific regions through region-level configuration. (Large screen only).</p>"),
        ];
        $form['solo_settings']['settings_top_container']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
      }
    }
  }
}
