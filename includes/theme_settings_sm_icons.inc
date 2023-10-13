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

/**
 * The theme settings.
 */

$form['solo_settings']['settings_social_icons'] = [
  '#type' => 'details',
  '#title' => t('Social Media Links - Solo Theme'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
];

$form['solo_settings']['settings_social_icons']['sm_icon_size'] = [
  '#type' => 'radios',
  '#title' => t('You can change the Social Media Icon Size'),
  '#default_value' => 'sm-icons-small' ?? theme_get_setting('sm_icon_size'),
  '#options' => [
    'sm-icons-small' => t('Small - Default'),
    'sm-icons-medium'  => t('Medium icons'),
    'sm-icons-large'   => t('Large icons'),
  ],
];

$form['solo_settings']['settings_social_icons']['sm_show_icons'] = [
  '#type' => 'checkbox',
  '#title' => t('Show Social Icons'),
  '#default_value' => theme_get_setting('sm_show_icons'),
  '#description' => t("Check this option to show Social Icon. Uncheck to hide."),
];

$social_icons = [
  'facebook' => 'Facebook Page',
  'twitter' => 'Twitter Profile',
  'pinterest' => 'Pinterest Profile',
  'linkedin' => 'Linkedin Profile',
  'instagram' => 'Instagram Profile',
  'youtube' => 'Youtube Profile',
  'drupal' => 'Drupal Profile',
  'rss' => 'RSS Link',
  'mastodon' => 'Mastodon Profile',
];

foreach ($social_icons as $key => $value) {
  $form['solo_settings']['settings_social_icons']['sm_icon_' . $key] = [
    '#type' => 'textfield',
    '#title' => t($value),
    '#default_value' => theme_get_setting('sm_icon_' . $key),
  ];
}
