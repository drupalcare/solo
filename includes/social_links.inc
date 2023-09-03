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

$form['solo_settings']['social_icon'] = [
  '#type' => 'details',
  '#title' => t('Social Media Links - Solo Theme'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
];

$form['solo_settings']['social_icon']['social_media_size'] = [
  '#type' => 'radios',
  '#title' => t('Select The Social Media Icon Size'),
  '#default_value' => theme_get_setting('social_media_size'),
  '#options' => [
    0 => t('Small - Default'),
    1 => t('Medium'),
    2 => t('Large'),
  ],
];

$form['solo_settings']['social_icon']['show_social_icon'] = [
  '#type' => 'checkbox',
  '#title' => t('Show Social Icons'),
  '#default_value' => theme_get_setting('show_social_icon'),
  '#description' => t("Check this option to show Social Icon. Uncheck to hide."),
];
$form['solo_settings']['social_icon']['facebook_url'] = [
  '#type' => 'textfield',
  '#title' => t('Facebook Page'),
  '#default_value' => theme_get_setting('facebook_url'),
];
$form['solo_settings']['social_icon']['twitter_url'] = [
  '#type' => 'textfield',
  '#title' => t('Twitter Profile'),
  '#default_value' => theme_get_setting('twitter_url'),
];
$form['solo_settings']['social_icon']['pinterest_url'] = [
  '#type' => 'textfield',
  '#title' => t('Pinterest Profile'),
  '#default_value' => theme_get_setting('pinterest_url'),
];
$form['solo_settings']['social_icon']['linkedin_url'] = [
  '#type' => 'textfield',
  '#title' => t('Linkedin Profile'),
  '#default_value' => theme_get_setting('linkedin_url'),
];
$form['solo_settings']['social_icon']['instagram_url'] = [
  '#type' => 'textfield',
  '#title' => t('Instagram Profile'),
  '#default_value' => theme_get_setting('instagram_url'),
];
$form['solo_settings']['social_icon']['youtube_url'] = [
  '#type' => 'textfield',
  '#title' => t('Youtube Profile'),
  '#default_value' => theme_get_setting('youtube_url'),
];
$form['solo_settings']['social_icon']['drupal_url'] = [
  '#type' => 'textfield',
  '#title' => t('Drupal Profile'),
  '#default_value' => theme_get_setting('drupal_url'),
];
$form['solo_settings']['social_icon']['rss_url'] = [
  '#type' => 'textfield',
  '#title' => t('RSS Link'),
  '#default_value' => theme_get_setting('rss_url'),
];
$form['solo_settings']['social_icon']['mastodon_url'] = [
  '#type' => 'textfield',
  '#title' => t('Mastodon Profile'),
  '#default_value' => theme_get_setting('mastodon_url'),
];
