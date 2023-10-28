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
  '#title' => t("<strong class='error'>Page Wrapper</strong> Settings - Solo Theme"),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
  '#description'   => t("The Page Wrapper serves as the encompassing div for all regions within the site. Any settings applied to it will have a global effect. It's important to note that the CSS should probably being provided by a trusted user, such as an admin or developer."),
];

$form['solo_settings']['settings_page_wrapper']['site_import_googel_font'] = [
  '#type' => 'textfield',
  '#title' => t('Import Google Font'),
  '#description' => t("To import a Google font, follow these steps:<br>

Step 1: Obtain the Google Fonts URL. You can do this by visiting Google Fonts and copying the URL. For example, <strong class='info'>https://fonts.googleapis.com/css2?family=Teko:wght@300;400;500;600;700&display=swap</strong>.<br>

Step 2: Inject the CSS rules to specify the font families in the field below. For instance,
<strong class='info'>h1, h2, h3 {font-family: 'Teko', sans-serif <span class='error'>!important;</span>}</strong>.<br>

<span class='error'>Note</span>: If you are using Global Site Fonts or Heading Fonts, you must include <span class='error'>!important</span>; in your CSS.
 "),
  '#default_value' => theme_get_setting('site_import_googel_font'),
];

$form['solo_settings']['settings_page_wrapper']['site_css_injector'] = [
  '#type' => 'textarea',
  '#title' => t('CSS Injector'),
  '#description'   => t("Inject custom CSS into the theme's head globally."),
  '#default_value' => theme_get_setting('site_css_injector'),
];


$form['solo_settings']['settings_page_wrapper']['classes_page_wrapper'] = [
  '#type' => 'textfield',
  '#title' => t('CSS Classes Only'),
  '#description' => t("Here are the predefined CSS classes you can use:
- dis-box-shadow: Disables shadow
- dis-border: Disables border
- dis-border-radius: Disables border round
- dis-margin: Disables margin
- dis-padding: Disables padding
- dis-opacity: Disables the whole page opacity
- image-auto: Disables 100 percent image inside any link
- dis-text-transform: Disables text transform"),
  '#default_value' => theme_get_setting('classes_page_wrapper'),
];


foreach ($attributes as $attribute_key => $attribute_label) {
  $region = 'page_wrapper';
  $label = 'Page Wrapper';
  $form['solo_settings']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
}
