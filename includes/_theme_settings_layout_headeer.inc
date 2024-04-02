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

// Header region.
if (array_key_exists('header', $updated_regions['mix'])) {

  $region = 'header';
  $label = $updated_regions['mix'][$region];

  $form['solo_settings']["settings_{$region}"] = _generate_form_details($label);
  $form['solo_settings']["settings_{$region}"]["classes_{$region}"] = _generate_css_classes($region, $label);

  $form['solo_settings']["settings_{$region}"]['header_sitename_center_default'] = [
    '#type' => 'checkbox',
    '#title' => t("Select this option to center the Site's Logo, Site's Name, and Slogan within the header. The logo will be positioned on the left, while the site name and slogan will be centered alongside it."),
    '#default_value' => theme_get_setting('header_sitename_center_default'),
  ];

  $form['solo_settings']["settings_{$region}"]['header_sitename_center'] = [
    '#type' => 'checkbox',
    '#title' => t("Select this option to align the Site's Logo, Site's Name, and Slogan centrally. The logo will be positioned at the top, followed by the site name and slogan."),
    '#default_value' => theme_get_setting('header_sitename_center'),
  ];

  $form['solo_settings']["settings_{$region}"]['header_sitename_center_small'] = [
    '#type' => 'checkbox',
    '#title' => t("For small screen, select this option to align the Site's Logo, Site's Name, and Slogan centrally. The logo will be positioned at the top, followed by the site name and slogan. This configuration will adhere to the global breakpoints set on the site."),
    '#default_value' => theme_get_setting('header_sitename_center_small'),
  ];

  $form['solo_settings']["settings_{$region}"]['header_change_icons'] = [
    '#type' => 'checkbox',
    '#title' => t("Select this option to position the Search Icon, User Menu, Sidebar Menu, and Sidebar Hamburger to the left, and make the Main Sidebar menu open from the left as well."),
    '#default_value' => theme_get_setting('header_change_icons'),
  ];

  $form['solo_settings']["settings_{$region}"]['site_name_animate_on'] = [
    '#type' => 'select',
    '#title' => t("Select the preferred CSS text animation effects for the Site Name."),
    '#description' => t("Note that the animations are sorted based on their names (e.g., 'Aurora Wave', 'Dreamy Bloom', etc.)"),
    '#options' => _get_animate_site_name(),
    '#default_value' => theme_get_setting('site_name_animate_on'),
  ];

  foreach ($attributes as $attribute_key => $attribute_label) {
    $form['solo_settings']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
  }

}
