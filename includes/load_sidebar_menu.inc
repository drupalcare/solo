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

$form['solo_settings']['w3_primary_sidebar'] = [
  '#type' => 'details',
  '#title' => t("Slide The Primary Sidebar Menu from the Right Side."),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
  '#description'   => t('<p>When you insert a menu into The Primary Sidebar Menu, the menu will open/slide from the left side. If you would like to open/slide it from the right side, you can check this box.</p>'),
];
$form['solo_settings']['w3_primary_sidebar']['primary_sidebar_right'] = [
  '#type' => 'checkbox',
  '#title' => t('Check this box to slide the sidebar menu from the right side.'),
  '#default_value' => theme_get_setting('primary_sidebar_right'),
];
