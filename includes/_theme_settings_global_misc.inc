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
  '#title' => t("<strong class='purple'>Global Site</strong> Settings - Solo Theme"),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
];

$form['solo_settings']['settings_global_misc']['site_global_width'] = [
  '#title' => t("Website's Width - Solo Theme"),
  '#type' => 'radios',
  '#options' => _get_site_width_options(),
  '#default_value' => theme_get_setting('site_global_width'),
  '#description' => t("<p><strong>You can adjust the entire website's width by choosing your preferred size in pixels or rem units.</strong></p>"),
];

$form['solo_settings']['settings_global_misc']['site_global_regions_gab'] = [
  '#title' => t("Website Region's Spaces"),
  '#type' => 'radios',
  '#options' => _get_site_global_regions_gab(),
  '#default_value' => theme_get_setting('site_global_regions_gab') ?? '16',
  '#description' => t("<p><strong>You can adjust the entire website's padding by choosing your preferred size in pixels or rem units.</strong></p>"),
];

$form['solo_settings']['settings_global_misc']['site_global_font_size'] = [
  '#title' => t("Website's Font Size"),
  '#type' => 'radios',
  '#options' => _get_site_global_font_size(),
  '#default_value' => theme_get_setting('site_global_font_size') ?? '16',
  '#description' => t("<p><strong>You can adjust the entire website's font size by choosing your preferred size in pixels or rem units.</strong></p>"),
];

$form['solo_settings']['settings_global_misc']['site_import_googel_font'] = [
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

$form['solo_settings']['settings_global_misc']['site_css_injector'] = [
  '#type' => 'textarea',
  '#title' => t('CSS Injector'),
  '#description'   => t("Inject custom CSS into the theme's head globally."),
  '#default_value' => theme_get_setting('site_css_injector'),
];

$form['solo_settings']['settings_global_misc']['site_login_page_on'] = [
  '#type' => 'checkbox',
  '#title' => t('Tick this box to modify the layout for the login, register, and password reset pages.'),
  '#description'   => t('<p>The login form will be centralized, with the left and right sides concealed.</p>'),
  '#default_value' => theme_get_setting('site_login_page_on'),
];

$form['solo_settings']['settings_global_misc']['site_flip_header_menu'] = [
  '#type' => 'checkbox',
  '#title' => t('Tick this box to flip the main menu order. It will be displayed before the header.'),
  '#default_value' => theme_get_setting('site_flip_header_menu'),
];

$form['solo_settings']['settings_global_misc']['site_opacity_onscroll_on'] = [
  '#type' => 'checkbox',
  '#title' => t('Tick this box to animate opacity while scrolling down.'),
  '#description'   => t('<p>Upon visiting the page, the hidden blocks remain unseen. They only begin to animate and become visible as you scroll down. However, this feature is disabled by default.</p>'),
  '#default_value' => theme_get_setting('site_opacity_onscroll_on'),
];

$form['solo_settings']['settings_global_misc']['site_inline_items_on'] = [
  '#type' => 'checkbox',
  '#title' => t('Tick this box to display multiple field items on the same line. Note that this does not apply to long text fields.'),
  '#description'   => t('<p>In the current implementation, a field with multiple values is displayed in a block layout. This means each value is displayed on a new line, one below the other. This can take up a lot of vertical space, especially if there are many values.<br> The task is to change this layout so that the values are displayed inline. This means the values will be displayed on the same line, separated by a space, This will make the display more compact and easier to read.</p>'),
  '#default_value' => theme_get_setting('site_inline_items_on'),
];

$form['solo_settings']['settings_global_misc']['site_node_date_format'] = [
  '#title' => t("Choose the node date format."),
  '#type' => 'radios',
  '#options' => _get_site_node_date_format($d_s, $d_m, $d_l),
  '#default_value' => theme_get_setting('site_node_date_format') ?? 'solo_date_l',
];
