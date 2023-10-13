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

// Page wrapper settings.
$form['solo_settings']['settings_page_wrapper'] = [
  '#type' => 'details',
  '#title' => t("Page Wrapper Settings - Solo Theme"),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
  '#description'   => t('<p> Page Wrapper is the wrapper div to all regions in the site. This settings will be applied globally.</p>'),
];

$form['solo_settings']['settings_page_wrapper']['site_css_injector'] = [
  '#type' => 'textarea',
  '#title' => t('internal CSS'),
  '#description'   => t("Add custom css to the theme. It will injected in the head globally."),
  '#default_value' => theme_get_setting('site_css_injector'),
];

$form['solo_settings']['settings_page_wrapper']['classes_page_wrapper'] = [
  '#type' => 'textfield',
  '#title' => t('Page Wrapper'),
  '#description' => t('Insert any css classee here'),
  '#default_value' => theme_get_setting('classes_page_wrapper'),
];


foreach ($attributes as $attribute_key => $attribute_label) {

  $region = 'page_wrapper';
  $label = 'Page Wrapper';
  $form['solo_settings']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
}
