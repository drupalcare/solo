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

$form['solo_settings']['settings_libraries_fonts'] = [
  '#type' => 'details',
  '#title' => t("External Libraries and Fonts - Solo Theme"),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
];

$form['solo_settings']['settings_libraries_fonts']['site_load_w3css_on'] = [
  '#type' => 'checkbox',
  '#title' => t('Check this box to enable the w3.css library.'),
  '#default_value' => theme_get_setting('site_load_w3css_on'),
];

$form['solo_settings']['settings_libraries_fonts']['site_font_awesome_on'] = [
  '#type' => 'select',
  '#title' => t('Select Font Awesome Library'),
  '#default_value' => theme_get_setting('site_font_awesome_on'),
  '#options' => [
    0 => t('None'),
    1 => t('Load Font Awesome 4.7 locally.'),
    2 => t('Load Font Awesome 4.7 from the CDN.'),
    3 => t('Load Font Awesome 5.15.4 from the CDN.'),
    4 => t('Load Font Awesome 6.1.1 from the CDN.'),
  ],
];

$form['solo_settings']['settings_libraries_fonts']['site_font_google_local_on'] = [
  '#type' => 'checkbox',
  '#title' => t('Check this box if you would like to load Google fonts (Global Site Fonts and Heading Fonts only) locally. This is for use with the Solo Theme.'),
  '#description'   => t('<p>Any font you choose for the site, heading or special will have a CSS class that you can apply to any html tag. <a target="_blank" href="https://www.drupal.org/node/2866181#change-font">click here to view the documentaion on drupal.org</a></p>'),
  '#default_value' => theme_get_setting('site_font_google_local_on'),
];

$form['solo_settings']['settings_libraries_fonts']['site_font_google_global_on'] = [
  '#type' => 'select',
  '#title' => t('50 Global Site Fonts: Choose the site font - The font will be applied to the whole website.<a target="_blank" href="https://drupal8-w3css-theme.flashwebcenter.com/w3css-theme-google-fonts-global-site-fonts"> Click here to view a live demo for all available fonts.</a>'),
  '#description'   => t('<p>Global Site Font CSS Classes: open-sans, abel, alegreya-sans, alegreya-sans-sc, amita, archivo-narrow, arimo, arvo, barlow, barlow-semi-condensed
, bitter, cairo, comfortaa, courgette, domine, dosis, exo-2, fira-sans, glegoo, hind-siliguri, ibm-plex-sans, inconsolata, josefin-sans, kanit, kavivanar, lato, libre-baskerville, lora, merienda, merriweather, montserrat, mukta, nunito, oranienbaum, oswald, oxygen, playfair-display, poppins, prompt, pt-serif, quicksand, raleway, roboto, roboto-condensed, roboto-mono, spline-sans, source-sans-3, titillium-web, ubuntu, work-sans. </p>'),
  '#options' => _get_global_font_options(),
  '#default_value' => theme_get_setting('site_font_google_global_on'),
];

$form['solo_settings']['settings_libraries_fonts']['site_font_google_heading_on'] = [
  '#type' => 'select',
  '#title' => t('26 Heading Fonts: Choose the heading font - The font will be applied to h1, h2 and h3 only.<a target="_blank" href="https://drupal8-w3css-theme.flashwebcenter.com/w3css-theme-google-fonts-heading-fonts"> Click here to view a live demo for all available fonts.</a>'),
  '#description'   => t('<p>THeading Font CSS Classes: abril-fatface, aladin, anton, archivo-black, bebas-neue, big-shoulders-stencil-display, big-shoulders-stencil-text, black-ops-one, bree-serif, cinzel, dancing-script, dm-serif-display, doppio-one, fjalla-one, francois-one, fredericka-the-great, fredoka, hammersmith-one, indie-flower, kalam, lobster-two, patrick-hand, prosto-one, righteous, staatliches, yanone-kaffeesatz.</p>'),
  '#options' => _get_heading_font_options(),
  '#default_value' => theme_get_setting('site_font_google_heading_on'),
];

$form['solo_settings']['settings_libraries_fonts']['site_font_google_special_on'] = [
  '#type' => 'select',
  '#title' => t('25 Special Fonts: They are not applied to any html tags by default and they are only imported. Each font library has a CSS class that you can apply to any html tag. <a target="_blank" href="https://drupal8-w3css-theme.flashwebcenter.com/w3css-theme-google-fonts-special-fonts">Click here to view a live demo for all available fonts.</a>'),
  '#description'   => t('<p>Spcial Font CSS Classes: akronim, barriecito, bungee-shade, ceviche-one, creepster, faster-one, finger-paint, frijole, irish-grover, kumar-one, londrina-outline, megrim, metal-mania, monoton, moo-lah-lah, new-rocker, nosifer, pirata-one, ruslan-display, rye, sancreek, slackey, trade-winds, train-one, unifrakturmaguntia.</p>'),
  '#options' => _get_special_font_options(),
  '#default_value' => theme_get_setting('site_font_google_special_on'),
];
