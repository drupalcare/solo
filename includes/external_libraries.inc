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

$form['solo_settings']['w3_fontawesome_libraries'] = [
  '#type' => 'details',
  '#title' => t('Font awesome - Solo Theme'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
  '#description'   => t("<p>Font awesome is now not enabled by default. All the default theme's icons were replaced by svg images. You can enable this library if you are using any font awesome icons in your site.</p>"),
];

$form['solo_settings']['w3_fontawesome_libraries']['solo_font_awesome'] = [
  '#type' => 'radios',
  '#title' => t('Select Font Awesome Library'),
  '#default_value' => theme_get_setting('solo_font_awesome'),
  '#options' => [
    0 => t('None'),
    1 => t('Load Font Awesome 4.7 locally.'),
    2 => t('Load Font Awesome 4.7 from the CDN.'),
    3 => t('Load Font Awesome 5.15.4 from the CDN.'),
    4 => t('Load Font Awesome 6.1.1 from the CDN.'),
  ],
];

$form['solo_settings']['w3_external_libraries'] = [
  '#type' => 'details',
  '#title' => t('Google Fonts - Solo Theme'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
  '#description'   => t('<p>Any font you choose for the site, heading or special will have a CSS class that you can apply to any html tag. <a target="_blank" href="https://www.drupal.org/node/2866181#change-font">click here to view the documentaion on drupal.org</a></p>'),
];

$form['solo_settings']['w3_external_libraries']['solo_google_font_local'] = [
  '#type' => 'checkbox',
  '#title' => t('Check this box if you would like to load Google fonts (Global Site Fonts and Heading Fonts only) locally. This is for use with the Solo Theme.'),
  '#default_value' => theme_get_setting('solo_google_font_local'),
];

$form['solo_settings']['w3_external_libraries']['solo_site_gf'] = [
  '#type' => 'select',
  '#title' => t('50 Global Site Fonts: Choose the site font - The font will be applied to the whole website.<a target="_blank" href="https://drupal8-w3css-theme.flashwebcenter.com/w3css-theme-google-fonts-global-site-fonts"> Click here to view a live demo for all available fonts.</a>'),
  '#description'   => t('<p>Global Site Font CSS Classes: open-sans, abel, alegreya-sans, alegreya-sans-sc, amita, archivo-narrow, arimo, arvo, barlow, barlow-semi-condensed
, bitter, cairo, comfortaa, courgette, domine, dosis, exo-2, fira-sans, glegoo, hind-siliguri, ibm-plex-sans, inconsolata, josefin-sans, kanit, kavivanar, lato, libre-baskerville, lora, merienda, merriweather, montserrat, mukta, nunito, oranienbaum, oswald, oxygen, playfair-display, poppins, prompt, pt-serif, quicksand, raleway, roboto, roboto-condensed, roboto-mono, spline-sans, source-sans-3, titillium-web, ubuntu, work-sans. </p>'),
  '#options' => [
    'none' => t('None'),
    'solo-abel' => t('Abel - sans-serif'),
    'solo-alegreya-sans' => t('Alegreya Sans - sans-serif'),
    'solo-alegreya-sans-sc' => t('Alegreya Sans SC - sans-serif'),
    'solo-amita' => t('Amita - cursive'),
    'solo-archivo-narrow' => t('Archivo Narrow - sans-serif'),
    'solo-arimo' => t('Arimo - sans-serif'),
    'solo-arvo' => t('Arvo - serif'),
    'solo-barlow' => t('Barlow - sans-serif'),
    'solo-barlow-semi-condensed' => t('Barlow Semi Condensed - sans-serif'),
    'solo-bitter' => t('Bitter - serif'),
    'solo-cairo' => t('Cairo - sans-serif'),
    'solo-comfortaa' => t('Comfortaa - cursive'),
    'solo-courgette' => t('Courgette - cursive'),
    'solo-domine' => t('Domine - serif'),
    'solo-dosis' => t('Dosis - sans-serif'),
    'solo-exo-2' => t('EXO 2 - sans-serif'),
    'solo-fira-sans' => t('Fira Sans - sans-serif'),
    'solo-glegoo' => t('Glegoo - serif'),
    'solo-hind-siliguri' => t('Hind Siliguri - sans-serif'),
    'solo-ibm-plex-sans' => t('IBM Plex Sans - sans-serif'),
    'solo-inconsolata' => t('Inconsolata - monospace'),
    'solo-josefin-sans' => t('Josefin Sans  - sans-serif'),
    'solo-kanit' => t('Kanit - sans-serif'),
    'solo-kavivanar' => t('Kavivanar - cursive'),
    'solo-lato' => t('Lato - sans-serif'),
    'solo-libre-baskerville' => t('Libre Baskerville  - serif'),
    'solo-lora' => t('Lora - serif'),
    'solo-merienda' => t('Merienda - cursive'),
    'solo-merriweather' => t('Merriweather  - serif'),
    'solo-montserrat' => t('Montserrat - sans-serif'),
    'solo-mukta' => t('Mukta - sans-serif'),
    'solo-nunito' => t('Nunito - sans-serif'),
    'solo-open-sans' => t('Open Sans - sans-serif'),
    'solo-oranienbaum' => t('Oranienbaum - serif'),
    'solo-oswald' => t('Oswald - sans-serif'),
    'solo-oxygen' => t('Oxygen - sans-serif'),
    'solo-playfair-display' => t('Playfair Display - serif'),
    'solo-poppins' => t('Poppins - sans-serif'),
    'solo-prompt' => t('Prompt - sans-serif'),
    'solo-pt-serif' => t('PT Serif - serif'),
    'solo-quicksand' => t('Quicksand - sans-serif'),
    'solo-raleway' => t('Raleway - sans-serif'),
    'solo-roboto' => t('Roboto - sans-serif'),
    'solo-roboto-condensed' => t('Roboto Condensed - sans-serif'),
    'solo-roboto-mono' => t('Roboto Mono - monospace'),
    'solo-spline-sans' => t('Spline Sans - sans-serif'),
    'solo-source-sans-3' => t('Source Sans 3 - sans-serif'),
    'solo-titillium-web' => t('Titillium Web - sans-serif'),
    'solo-ubuntu' => t('Ubuntu - sans-serif'),
    'solo-work-sans' => t('Work Sans - sans-serif'),

  ],
  '#default_value' => theme_get_setting('solo_site_gf'),
];

$form['solo_settings']['w3_external_libraries']['solo_site_gfh'] = [
  '#type' => 'select',
  '#title' => t('26 Heading Fonts: Choose the heading font - The font will be applied to h1, h2 and h3 only.<a target="_blank" href="https://drupal8-w3css-theme.flashwebcenter.com/w3css-theme-google-fonts-heading-fonts"> Click here to view a live demo for all available fonts.</a>'),
  '#description'   => t('<p>THeading Font CSS Classes: abril-fatface, aladin, anton, archivo-black, bebas-neue, big-shoulders-stencil-display, big-shoulders-stencil-text, black-ops-one, bree-serif, cinzel, dancing-script, dm-serif-display, doppio-one, fjalla-one, francois-one, fredericka-the-great, fredoka, hammersmith-one, indie-flower, kalam, lobster-two, patrick-hand, prosto-one, righteous, staatliches, yanone-kaffeesatz.</p>'),
  '#options' => [
    'none' => t('None'),
    'solo-abril-fatface' => t('Abril Fatface  - cursive'),
    'solo-aladin' => t('Aladin  - cursive'),
    'solo-anton' => t('Anton  - sans-serif'),
    'solo-archivo-black' => t('Archivo Black - sans-serif'),
    'solo-bebas-neue' => t('Bebas Neue - cursive'),
    'solo-big-shoulders-stencil-display' => t('Big Shoulders Stencil Display - cursive'),
    'solo-big-shoulders-stencil-text' => t('Big Shoulders Stencil Text - cursive'),
    'solo-black-ops-one' => t('Black Ops One - cursive'),
    'solo-bree-serif' => t('Bree Serif - serif'),
    'solo-cinzel' => t('Cinzel - serif'),
    'solo-dancing-script' => t('Dancing Script - cursive'),
    'solo-dm-serif-display' => t('DM Serif Display - serif'),
    'solo-doppio-one' => t('Doppio - sans-serif'),
    'solo-fjalla-one' => t('Fjalla One - sans-serif'),
    'solo-francois-one' => t('Francois One - sans-serif'),
    'solo-fredericka-the-great' => t('Fredericka The Great  - cursive'),
    'solo-fredoka' => t('Fredoka - cursive'),
    'solo-hammersmith-one' => t('Hammersmith One - sans-serif'),
    'solo-indie-flower' => t('Indie Flower - cursive'),
    'solo-kalam' => t('Kalam - cursive'),
    'solo-lobster-two' => t('Lobster Two - cursive'),
    'solo-patrick-hand' => t('Patrick Hand - cursive'),
    'solo-prosto-one' => t('Prosto One - cursive'),
    'solo-righteous' => t('Righteous - cursive'),
    'solo-staatliches' => t('Staatliches - cursive'),
    'solo-yanone-kaffeesatz' => t('Yanone Kaffeesatz - sans-serif'),

  ],
  '#default_value' => theme_get_setting('solo_site_gfh'),
];

$form['solo_settings']['w3_external_libraries']['solo_site_gfs'] = [
  '#type' => 'select',
  '#title' => t('25 Special Fonts: They are not applied to any html tags by default and they are only imported. Each font library has a CSS class that you can apply to any html tag. <a target="_blank" href="https://drupal8-w3css-theme.flashwebcenter.com/w3css-theme-google-fonts-special-fonts">Click here to view a live demo for all available fonts.</a>'),
  '#description'   => t('<p>Spcial Font CSS Classes: akronim, barriecito, bungee-shade, ceviche-one, creepster, faster-one, finger-paint, frijole, irish-grover, kumar-one, londrina-outline, megrim, metal-mania, monoton, moo-lah-lah, new-rocker, nosifer, pirata-one, ruslan-display, rye, sancreek, slackey, trade-winds, train-one, unifrakturmaguntia.</p>'),
  '#options' => [
    'none' => t('None'),
    'solo-akronim' => t('akronim - cursive'),
    'solo-barriecito' => t('barriecito - cursive'),
    'solo-bungee-shade' => t('bungee-shade - cursive'),
    'solo-ceviche-one' => t('ceviche-one - cursive'),
    'solo-creepster' => t('creepster - cursive'),
    'solo-faster-one' => t('faster-one - cursive'),
    'solo-finger-paint' => t('finger-paint - cursive'),
    'solo-frijole' => t('frijole - cursive'),
    'solo-irish-grover' => t('irish-grover - cursive'),
    'solo-kumar-one' => t('kumar-one - cursive'),
    'solo-londrina-outline' => t('londrina-outline - cursive'),
    'solo-megrim' => t('megrim - cursive'),
    'solo-metal-mania' => t('metal-mania - cursive'),
    'solo-monoton' => t('monoton - cursive'),
    'solo-moo-lah-lah' => t('moo-lah-lah - cursive'),
    'solo-new-rocker' => t('new-rocker - cursive'),
    'solo-nosifer' => t('nosifer - cursive'),
    'solo-pirata-one' => t('pirata-one - cursive'),
    'solo-ruslan-display' => t('ruslan-display - cursive'),
    'solo-rye' => t('rye - cursive'),
    'solo-sancreek' => t('sancreek - cursive'),
    'solo-slackey' => t('slackey - cursive'),
    'solo-trade-winds' => t('trade-winds - cursive'),
    'solo-train-one' => t('train-one - cursive'),
    'solo-unifrakturmaguntia' => t('unifrakturmaguntia - cursive'),

  ],
  '#default_value' => theme_get_setting('solo_site_gfs'),
];
