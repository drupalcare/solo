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

// Page title region.
if (array_key_exists('page_title', $updated_regions['mix'])) {

  $region = 'page_title';
  $label = $updated_regions['mix'][$region];

  $form['solo_settings']["settings_{$region}"] = _generate_form_details($label);
  $form['solo_settings']["settings_{$region}"]["classes_{$region}"] = _generate_css_classes($region, $label);

  $form['solo_settings']["settings_{$region}"]['site_pagetitle_animate_on'] = [
    '#type' => 'select',
    '#title' => t("Choose the desired CSS text animation effects for the page title."),
    '#options' => _get_animate_page_title(),
    '#description' => t("You can apply the available CSS Text Animation Effects Classes to any HTML tag. To see examples of all the CSS Text Animation Effects, <a target='_blank' href='https://unitedstarsofamerica.com/node/190'> Click here to view a live demo for all available aimations.</a>"),
    '#default_value' => theme_get_setting('site_pagetitle_animate_on'),
  ];

  foreach ($attributes as $attribute_key => $attribute_label) {
    $form['solo_settings']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
  }

}
