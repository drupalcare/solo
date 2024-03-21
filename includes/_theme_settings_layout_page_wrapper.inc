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
  '#title' => t("<strong class='status'>Page Wrapper</strong> Settings - Solo Theme"),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
  '#description'   => t("The Page Wrapper serves as the encompassing div for all regions within the site. Any settings applied to it will have a global effect. It's important to note that the CSS should probably be provided by a trusted user, such as an admin or developer."),
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
- dis-text-transform: Disables text transform
- dis-anim-link: Disables submit button wiggle animation
- dis-anim-submit: Disables menu border animation"),
  '#default_value' => theme_get_setting('classes_page_wrapper'),
];

foreach ($attributes as $attribute_key => $attribute_label) {
  $region = 'page_wrapper';
  $label = 'Page Wrapper';
  $form['solo_settings']["settings_{$region}"]["settings_{$region}_{$attribute_key}"] = _generate_form_element($region, $label, $attribute_key, $attribute_label);
}
