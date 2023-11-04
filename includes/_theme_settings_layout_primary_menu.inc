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
if (array_key_exists('primary_menu', $updated_regions['mix'])) {
  $region = 'primary_menu';
  $label = $updated_regions['mix']['primary_menu'];
  $form['solo_settings']["settings_{$region}"] = _generate_form_details($label);
  $form['solo_settings']["settings_{$region}"]["classes_{$region}"] = _generate_css_classes($region, $label);

  $form['solo_settings']["settings_{$region}"]['main_menu_on_hover'] = [
    '#type' => 'checkbox',
    '#title' => t('Tick this box if you prefer to show the main menu dropdowns when hovering over them with the mouse, instead of clicking.'),
    '#default_value' => theme_get_setting('main_menu_on_hover'),
  ];

  $form['solo_settings']["settings_{$region}"]['main_menu_megamenu'] = [
    '#type' => 'checkbox',
    '#title' => t('Tick this box to transform the main menu dropdowns into a Mega Menu.'),
    '#description' => t('Megamenus necessitate the implementation of hierarchical three-level menus. In this structure, the second level of menu items is instrumental in defining the "columns" for the megamenu. If the option "Tick this box to display the second level as headers" is selected, the second level will be exhibited as a header for the third-level menu items.'),
    '#default_value' => theme_get_setting('main_menu_megamenu'),
  ];

  $form['solo_settings']["settings_{$region}"]['main_menu_megamenu_layout'] = _create_megamenu_layout();

  $form['solo_settings']["settings_{$region}"]['main_menu_submenu_header'] = [
    '#type' => 'checkbox',
    '#title' => t('Tick this box to display the second level as headers.'),
    '#default_value' => theme_get_setting('main_menu_submenu_header'),
    '#states' => [
      'visible' => [
        ':input[name="main_menu_megamenu"]' => ['checked' => TRUE],
      ],
    ],
  ];

  foreach ($attributes as $attribute_key => $attribute_label) {
    $form['solo_settings']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
  }

}
