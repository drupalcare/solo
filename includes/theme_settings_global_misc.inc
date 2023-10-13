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

$form['solo_settings']['settings_global_misc'] = [
  '#type' => 'details',
  '#title' => t("Global MISC Features/Config - Solo Theme"),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
];

$form['solo_settings']['settings_global_misc']['site_global_width'] = [
  '#title' => t("Website's Width - Solo Theme"),
  '#type' => 'radios',
  '#options' => _get_site_width_options(),
  '#default_value' => theme_get_setting('site_global_width'),
  '#description' => t("<p><strong>You can change the whole website's width by selecting any of the desired width in pixels/rem.</strong></p>"),
];

$form['solo_settings']['settings_global_misc']['site_login_page_on'] = [
  '#type' => 'checkbox',
  '#title' => t('Check this box to change the layout for the login, register and reset password pages.'),
  '#description'   => t('<p>If you check this box the login form will be centered and left/right sides will be hidden.</p>'),
  '#default_value' => theme_get_setting('site_login_page_on'),
];

$form['solo_settings']['settings_global_misc']['site_opacity_onscroll_on'] = [
  '#type' => 'checkbox',
  '#title' => t('Check this box to animate the opacity on scroll down.'),
  '#description'   => t('<p>When you visit the page, the hidden blocks are invisible and they will start to show/animate when you scroll down. The default setting is disabled.</p>'),
  '#default_value' => theme_get_setting('site_opacity_onscroll_on'),
];

$form['solo_settings']['settings_global_misc']['site_breadcrumb_pagetitle_off'] = [
  '#type' => 'checkbox',
  '#title' => t('Check this box to remove the page title from breadcrumb.'),
  '#default_value' => theme_get_setting('site_breadcrumb_pagetitle_off'),
];

$form['solo_settings']['settings_global_misc']['site_inline_items_on'] = [
  '#type' => 'checkbox',
  '#title' => t('Check this box to display multi field items on one line, long text fields are not included.'),
  '#default_value' => theme_get_setting('site_inline_items_on'),
];

$form['solo_settings']['settings_global_misc']['header_change_icons'] = [
  '#type' => 'checkbox',
  '#title' => t('Check this box to position Search Icon, User Menu, Sidebar Menu, and Sidebar Hamburger to the left. The Main Sidebar menu will open from the left also.'),
  '#default_value' => theme_get_setting('header_change_icons'),
];

$form['solo_settings']['settings_global_misc']['header_change_sitename'] = [
  '#type' => 'checkbox',
  '#title' => t('Check this box to position the Site (Name, Slogan and Logo) to the center.'),
  '#default_value' => theme_get_setting('header_change_sitename'),
];

$form['solo_settings']['settings_global_misc']['header_popup_login'] = [
  '#type' => 'checkbox',
  '#title' => t('Check this box to login in a popup block.'),
  '#default_value' => theme_get_setting('header_popup_login'),
];

$form['solo_settings']['settings_global_misc']['header_login_links'] = [
  '#type' => 'textfield',
  '#title' => t('You can change the login word.'),
  '#default_value' => theme_get_setting('header_login_links') ?? 'Login',
];

$form['solo_settings']['settings_global_misc']['site_name_animate_on'] = [
  '#type' => 'select',
  '#title' => t("Choose the desired CSS text animation effects for the site name."),
  '#description' => t("Available CSS Text Animation Effects Classes you can apply to any html tag
    <a target='_blank' href='https://www.drupal.org/node/2866181#change-font'> Click here to view the documentaion on drupal.org</a>.<a target='_blank' href='https://drupal8-w3css-theme.flashwebcenter.com/w3css-theme-fonts-css-text-animation-effects'> Click here to view an example for all the CSS Text Animation Effects</a>.</p>"),
  '#options' => [
    'none' => t('None'),
    'animate-name-01' => t('Style 01 (animate-text-01)'),
    'animate-name-02' => t('Style 02 (animate-text-02)'),
    'animate-name-03' => t('Style 03 (animate-text-03)'),
    'animate-name-04' => t('Style 04 (animate-text-04)'),
    'animate-name-05' => t('Style 05 (animate-text-05)'),
    'animate-name-06' => t('Style 06 (animate-text-06)'),
    'animate-name-07' => t('Style 07 (animate-text-07)'),
    'animate-name-08' => t('Style 08 (animate-text-08)'),
    'animate-name-09' => t('Style 09 (animate-text-09)'),
    'animate-name-10' => t('Style 10 (animate-text-10)'),
    'animate-name-11' => t('Style 11 (animate-text-11)'),
    'animate-name-12' => t('Style 12 (animate-text-12)'),
    'animate-name-13' => t('Style 13 (animate-text-13)'),
    'animate-name-14' => t('Style 14 (animate-text-14)'),
    'animate-name-15' => t('Style 15 (animate-text-15)'),
    'animate-name-16' => t('Style 16 (animate-text-16)'),
    'animate-name-17' => t('Style 17 (animate-text-17)'),
    'animate-name-18' => t('Style 18 (animate-text-18)'),
    'animate-name-19' => t('Style 19 (animate-text-19)'),
    'animate-name-20' => t('Style 20 (animate-text-20)'),
    'animate-name-21' => t('Style 21 (animate-text-21)'),
    'animate-name-22' => t('Style 22 (animate-text-22)'),
    'animate-name-23' => t('Style 23 (animate-text-23)'),
    'animate-name-24' => t('Style 24 (animate-text-24)'),

  ],
  '#default_value' => theme_get_setting('site_name_animate_on'),
];

$form['solo_settings']['settings_global_misc']['site_pagetitle_animate_on'] = [
  '#type' => 'select',
  '#title' => t("Choose the desired CSS text animation effects for the page title."),
  '#options' => [
    'none' => t('None'),
    'animate-title-01' => t('Style 01'),
    'animate-title-02' => t('Style 02'),
    'animate-title-03' => t('Style 03'),
    'animate-title-04' => t('Style 04'),
    'animate-title-05' => t('Style 05'),
    'animate-title-06' => t('Style 06'),
    'animate-title-07' => t('Style 07'),
    'animate-title-08' => t('Style 08'),
    'animate-title-09' => t('Style 09'),
    'animate-title-10' => t('Style 10'),
    'animate-title-11' => t('Style 11'),
    'animate-title-12' => t('Style 12'),
    'animate-title-13' => t('Style 13'),
    'animate-title-14' => t('Style 14'),
    'animate-title-15' => t('Style 15'),
    'animate-title-16' => t('Style 16'),
    'animate-title-17' => t('Style 17'),
    'animate-title-18' => t('Style 18'),
    'animate-title-19' => t('Style 19'),
    'animate-title-20' => t('Style 20'),
    'animate-title-21' => t('Style 21'),
    'animate-title-22' => t('Style 22'),
    'animate-title-23' => t('Style 23'),
    'animate-title-24' => t('Style 24'),

  ],
  '#default_value' => theme_get_setting('site_pagetitle_animate_on'),
];
