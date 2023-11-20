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
 * Helper function.
 */
function _get_site_width_options() {
  return [
    'sw-1600' => t('1600px / 100rem'),
    'sw-2560' => t('2560px / 160rem'),
    'sw-1920' => t('1920px / 120rem'),
    'sw-1440' => t('1440px / 90rem'),
    'sw-1280' => t('1280px / 80rem'),
    'sw-1024' => t('1024px / 64rem'),
    'sw-800' => t('800px / 50rem'),
    'sw-100' => t('100%'),
  ];
}

/**
 * Helper function.
 */
function _get_site_global_regions_gab() {
  return [
    '16' => t('Default 16px / 1rem'),
    '24' => t('24px / 1.5rem'),
    '36' => t('36px / 2.25rem'),
    '48' => t('48px / 3rem'),
    '64' => t('64px / 4rem'),
  ];
}

/**
 * Helper function.
 */
function _get_site_global_font_size() {
  return [
    '16' => t('Default 16px / 1rem'),
    '18' => t('18px / 1.125rem'),
    '20' => t('20px / 1.25rem'),
  ];
}

/**
 * Helper function.
 */
function _get_site_node_date_format($d_s, $d_m, $d_l) {
  return [
    'solo_date_s' => t("Short: ($d_s)"),
    'solo_date_m' => t("Medium: ($d_m)"),
    'solo_date_l' => t("Long: ($d_l)"),
  ];
}

/**
 * Helper function.
 */
function _get_animate_site_name() {
  return [
    '' => t('Animation Effects - None'),
    'animate-site-name-001' => t('Animate Site Name - animate-site-name-001'),
    'animate-site-name-002' => t('Animate Site Name - animate-site-name-002'),
    'animate-site-name-003' => t('Animate Site Name - animate-site-name-003'),
    'animate-site-name-004' => t('Animate Site Name - animate-site-name-004'),
    'animate-site-name-005' => t('Animate Site Name - animate-site-name-005'),
    'animate-site-name-006' => t('Animate Site Name - animate-site-name-006'),
    'animate-site-name-007' => t('Animate Site Name - animate-site-name-007'),
    'animate-site-name-008' => t('Animate Site Name - animate-site-name-008'),
    'animate-site-name-009' => t('Animate Site Name - animate-site-name-009'),
    'animate-site-name-010' => t('Animate Site Name - animate-site-name-010'),
    'animate-site-name-011' => t('Animate Site Name - animate-site-name-011'),
    'animate-site-name-012' => t('Animate Site Name - animate-site-name-012'),
    'animate-site-name-013' => t('Animate Site Name - animate-site-name-013'),
    'animate-site-name-014' => t('Animate Site Name - animate-site-name-014'),
    'animate-site-name-015' => t('Animate Site Name - animate-site-name-015'),
    'animate-site-name-016' => t('Animate Site Name - animate-site-name-016'),
    'animate-site-name-017' => t('Animate Site Name - animate-site-name-017'),
    'animate-site-name-018' => t('Animate Site Name - animate-site-name-018'),
    'animate-site-name-019' => t('Animate Site Name - animate-site-name-019'),
    'animate-site-name-020' => t('Animate Site Name - animate-site-name-020'),
    'animate-site-name-021' => t('Animate Site Name - animate-site-name-020'),
    'animate-site-name-022' => t('Animate Site Name - animate-site-name-022'),
    'animate-site-name-023' => t('Animate Site Name - animate-site-name-023'),
    'animate-site-name-024' => t('Animate Site Name - animate-site-name-024'),
  ];
}

/**
 * Helper function.
 */
function _get_animate_page_title() {
  return [
    '' => t('Animation Effects - None'),
    'animate-page-title-001' => t('Animate Page Title - animate-page-title-001'),
    'animate-page-title-002' => t('Animate Page Title - animate-page-title-002'),
    'animate-page-title-003' => t('Animate Page Title - animate-page-title-003'),
    'animate-page-title-004' => t('Animate Page Title - animate-page-title-004'),
    'animate-page-title-005' => t('Animate Page Title - animate-page-title-005'),
    'animate-page-title-006' => t('Animate Page Title - animate-page-title-006'),
    'animate-page-title-007' => t('Animate Page Title - animate-page-title-007'),
    'animate-page-title-008' => t('Animate Page Title - animate-page-title-008'),
    'animate-page-title-009' => t('Animate Page Title - animate-page-title-009'),
    'animate-page-title-010' => t('Animate Page Title - animate-page-title-010'),
    'animate-page-title-011' => t('Animate Page Title - animate-page-title-011'),
    'animate-page-title-012' => t('Animate Page Title - animate-page-title-012'),
    'animate-page-title-013' => t('Animate Page Title - animate-page-title-013'),
    'animate-page-title-014' => t('Animate Page Title - animate-page-title-014'),
    'animate-page-title-015' => t('Animate Page Title - animate-page-title-015'),
    'animate-page-title-016' => t('Animate Page Title - animate-page-title-016'),
    'animate-page-title-017' => t('Animate Page Title - animate-page-title-017'),
    'animate-page-title-018' => t('Animate Page Title - animate-page-title-018'),
    'animate-page-title-019' => t('Animate Page Title - animate-page-title-019'),
    'animate-page-title-020' => t('Animate Page Title - animate-page-title-020'),
    'animate-page-title-021' => t('Animate Page Title - animate-page-title-020'),
    'animate-page-title-022' => t('Animate Page Title - animate-page-title-022'),
    'animate-page-title-023' => t('Animate Page Title - animate-page-title-023'),
    'animate-page-title-024' => t('Animate Page Title - animate-page-title-024'),
  ];
}

/**
 * Helper function.
 */
function _get_predefined_theme() {
  return [
    '' => t('None'),
    'solo-predefined-00001' => t('00001 - Marengo #4C5866, SPRUCE #2C3E4C, SLATE #757C88'),
    'solo-predefined-00002' => t('00002 - AEGEAN #1F456E, ARSENIC #3B444B'),
    'solo-predefined-00003' => t('00003 - BLUEBERRY #241571, CERULEAN #0492C2'),
    'solo-predefined-00004' => t('00004 - CERULEAN #0492C2, BLUEBERRY #241571'),
    'solo-predefined-00005' => t('00005 - FLINT #7F7D9C, ASH #564C4D'),
    'solo-predefined-00006' => t('00006 - ASH #564C4D, FLINT #7F7D9C'),
    'solo-predefined-00007' => t('00007 - Stone Gray #928E85, Outer Space #414A4C'),
    'solo-predefined-00008' => t('00008 - Outer Space #414A4C, Stone Gray #928E85'),
    'solo-predefined-00009' => t('00009 - Braves Navy #13274F, Zombie Gray #63645E, Mantis #74C365'),
    'solo-predefined-00010' => t('00010 - Mantis #74C365, Zombie Gray #63645E, Braves Navy #13274F'),
    'solo-predefined-00011' => t('00011 - Deep Black #050203, Retro Black #1F201F'),
    'solo-predefined-00012' => t('00012 - Natural Black #07000B, Retro Black #1F201F'),
    'solo-predefined-00013' => t('00013 - AMARANTH PINK #E52B50, Natural Black #07000B'),
    'solo-predefined-00014' => t('00014 - Bleu De France #318CE7, Natural Black #07000B'),
    'solo-predefined-00015' => t('00015 - Natural Black #07000B, Bleu De France #318CE7'),
    'solo-predefined-00016' => t('00016 - Greenish Yellow #EEEA62, Hunter Green #3F704D'),
    'solo-predefined-00017' => t('00017 - Hunter Green #3F704D, Greenish Yellow #EEEA62'),
    'solo-predefined-00018' => t('00018 - LAPIS #2832C2, International Orange #FF4F00'),
    'solo-predefined-00019' => t('00019 - International Orange #FF4F00, LAPIS #2832C2'),
    'solo-predefined-00020' => t('00020 - MAHOGANY #420C09, WALNUT #432616'),
    'solo-predefined-00021' => t('00021 - Brilliant White #EDF1FE, Bright White #F4F5F0, Ceramic #FCFFF9'),
    'solo-predefined-00022' => t('00022 - Bright White #F4F5F0, Ceramic #FCFFF9, Brilliant White #EDF1FE'),
    'solo-predefined-00023' => t('00023 - Ceramic #FCFFF9, Bright White #F4F5F0, Brilliant White #EDF1FE'),
    'solo-predefined-00024' => t('00024 - Acoustic White #EFECE1, Shadow White #EEF1EA, Aircraft White #EDF2F8'),
    'solo-predefined-00025' => t('00025 - Shadow White #EEF1EA, Aircraft White #EDF2F8, Acoustic White #EFECE1'),
    'solo-predefined-00026' => t('00026 - Aircraft White #EDF2F8, Acoustic White #EFECE1, Shadow White #EEF1EA'),
    'solo-predefined-00027' => t('00027 - Anti-Flash White #F2F3F4, Aircraft White #EDF2F8, Shadow White #EEF1EA green, Acoustic White #EFECE1'),
    'solo-predefined-00028' => t('00028 - Shadow White #EEF1EA, Aircraft White #EDF2F8, Acoustic White #EFECE1'),
    'solo-predefined-00029' => t('00029 - Shadow White #EEF1EA, Acoustic White #EFECE1, Aircraft White #EDF2F8'),
    'solo-predefined-00030' => t('00030 - Rose White #FFFAFA, Magnolia #F8F4FF'),
    'solo-predefined-00031' => t('00031 - Black #000000, PUNCH #F25278, White #FFFFFF'),
    'solo-predefined-00032' => t('00032 - Bluegray #1b2327, Denim #151E3D'),
    'solo-predefined-00033' => t('00033 - DodgerBlue 1E90FF, Indigo #281E5D'),
    'solo-predefined-00034' => t('00034 - RED #D0312D, DENIM #151E3D,MIDNIGHT #040406'),
    'solo-predefined-00035' => t('00035 - Rose White #FFFAFA, Magnolia #F8F4FF'),
    'solo-predefined-00036' => t('00036 - Smokey Gray #726E6D, Sonic Gray #757575,Spanish Gray #989898,Taupe Gray #8B8589'),
    'solo-predefined-00037' => t('00037 - Nickel #727472, Outer Space #414A4C, Rocket Metallic #8A7F8D'),
    'solo-predefined-00038' => t('00038 - Gunmetal #2A3439, Marengo #4C5866, Gray Wolf #504A4B'),
    'solo-predefined-00039' => t('00039 - Carbon Gray #625D5D, Davy’s Gray #555555, Dim Gray #696969'),
    'solo-predefined-00040' => t('00040 - PORPOISE #4D4C5C, FLINT #7F7D9C, DOVE #7C6E7F'),
    'solo-predefined-00041' => t('00041 - Black Rock #010127, Neutral Black #0B0B0B, Black Denim #191C27'),
    'solo-predefined-00042' => t('00042 - Black Denim #191C27, Black Rock #010127, Neutral Black #0B0B0B'),
    'solo-predefined-00043' => t('00043 - Piggy Pink #FDDDE6, Cotton Candy #FFBCD9, Cameo Pink #EFBBCC'),
    'solo-predefined-00044' => t('00044 - LBlack Denim #191C27, Salmon #FF9999'),
    'solo-predefined-00045' => t('00045 - AMARANTH PINK #E52B50, RAZZMATAZZ #E3256B, ONYX #030104'),
    'solo-predefined-00046' => t('00046 - Braves Navy #13274F, Astros Navy #002D62'),
    'solo-predefined-00047' => t('00047 - Bayern Blue #0066B2, Berry Blue #4F86F7, Blue Yonder #5072A7, OIL #050100'),
    'solo-predefined-00048' => t('00048 - Egyptian Blue #1034A6, Yale Blue #00356B, Space Cadet #1D2951'),
    'solo-predefined-00049' => t('00049 - AMAZON #3B7A57, DENIM #151E3D'),
    'solo-predefined-00050' => t('00050 - AEGEAN #1F456E, ARSENIC #3B444B'),
  ];
}

/**
 * Helper function.
 */
function _get_global_font_options() {
  return [
    '' => t('None'),
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
  ];
}

/**
 * Helper function.
 */
function _get_heading_font_options() {
  return [
    '' => t('None'),
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
  ];
}

/**
 * Helper function.
 */
function _get_special_font_options() {
  return [
    '' => t('None'),
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
  ];
}

/**
 * Helper function.
 */
function _generate_form_details($label) {
  return [
    '#type' => 'details',
    '#title' => t("<strong class='info'>$label</strong> Settings - Solo Theme"),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#description'   => t('<p>This section is related to general configurations.</p>'),
  ];
}

/**
 * Helper function.
 */
function _generate_form_element($region, $label, $attribute_key, $attribute_label) {
  return [
    '#type' => 'textfield',
    '#maxlength' => 7,
    '#size' => 10,
    '#title' => t("$attribute_label"),
    '#default_value' => theme_get_setting("settings_{$region}_{$attribute_key}"),
    '#attributes' => [
      'pattern' => '^#[a-fA-F0-9]{6}',
    ],
    '#wrapper_attributes' => [
      'data-drupal-selector' => 'solo-color-picker',
    ],
  ];
}

/**
 * Helper function.
 */
function _generate_css_classes($region, $label) {
  return [
    '#type' => 'textfield',
    '#title' => t('CSS Classes Only'),
    '#description' => t('Insert any CSS classes here. The CSS should be input by a trusted user (an admin or developer).'),
    '#default_value' => theme_get_setting("classes_{$region}"),
  ];

}

/**
 * Helper function.
 */
function _animate_region($region) {
  return [
    '#type' => 'checkbox',
    '#title' => t("Tick this box to initiate animation of the region's border."),
    '#default_value' => theme_get_setting("site_regions_{$region}_animate"),
  ];
};

/**
 * Helper function.
 */
function _border_region($region) {
  return [
    '#type' => 'checkbox',
    '#title' => t('Tick this box if you prefer to apply a border to the inside regions'),
    '#default_value' => theme_get_setting("site_regions_{$region}_border"),
  ];
};

/**
 * Helper function.
 */
function _rounded_region($region) {
  return [
    '#type' => 'radios',
    '#title' => t("Tick this box to round the inside regions' corners."),
    '#default_value' => theme_get_setting("site_regions_{$region}_rounded") ?? '',
    '#options' => [
      '' => t('None'),
      'radius-4' => t('4px / 0.25rem'),
      'radius-8' => t('8px / 0.5rem'),
      'radius-16' => t('16px / 1rem'),
      'radius-24' => t('24px / 1.5rem'),
    ],
  ];
};

/**
 * Helper function.
 */
function _disable_region($region) {
  return [
    '#type' => 'radios',
    '#title' => t("Change the visibility for $region region"),
    '#default_value' => theme_get_setting("site_regions_{$region}_disable") ?? 0,
    '#options' => [
      0 => t('Visible on all pages'),
      1 => t('Visiable on Home Page Only.'),
      2 => t('Visiable on All Pages Except Home Page.'),
    ],
  ];
};

/**
 * Helper function.
 */
function _get_active_regions() {

  $active_theme = \Drupal::theme()->getActiveTheme()->getName();
  $theme_regions = system_region_list($active_theme);
  $block_storage = \Drupal::entityTypeManager()->getStorage('block');

  foreach ($theme_regions as $machine_name => $label_name) {
    $blocks = $block_storage->loadByProperties(['theme' => $active_theme, 'region' => $machine_name]);

    if (!empty($blocks)) {
      $active_regions[] = $machine_name;
    }
  }

  return $active_regions;
}

/**
 * Helper function.
 */
function _get_updated_regions() {
  $active_regions = _get_active_regions();
  $updated_regions_all = [];
  $updated_regions_mix = [];
  $updated_regions = [
    'top' => [],
    'main' => [],
    'bottom' => [],
    'footer' => [],
  ];

  $region_config = [
    'top' => [
      'regions' => ['top_first', 'top_second', 'top_third'],
      'container' => 'Top Container Wrapper',
      'labels' => ['Top First Container', 'Top Second Container', 'Top Third Container'],
    ],
    'main' => [
      'regions' => ['sidebar_first', 'content', 'sidebar_second'],
      'container' => 'Main Container Wrapper',
      'labels' => ['Sidebar First', 'Content', 'Sidebar Second'],
    ],
    'bottom' => [
      'regions' => ['bottom_first', 'bottom_second', 'bottom_third', 'bottom_fourth'],
      'container' => 'Bottom Container Wrapper',
      'labels' => ['Bottom First Container', 'Bottom Second Container', 'Bottom Third Container', 'Bottom Fourth Container'],
    ],
    'footer' => [
      'regions' => ['footer_first', 'footer_second', 'footer_third'],
      'container' => 'Footer Container Wrapper',
      'labels' => ['Footer First Container', 'Footer Second Container', 'Footer Third Container'],
    ],
  ];

  foreach ($active_regions as $key => $region) {
    if ($region != 'auto_hidden_block') {
      $label = ucwords(str_replace('_', ' ', $region));

      foreach ($region_config as $section => $config) {
        if (in_array($region, $config['regions'])) {
          $updated_regions[$section]['container'] = $config['container'];
          $updated_regions_all['container'] = $config['container'];

          $index = array_search($region, $config['regions']);
          $updated_regions[$section][$region] = $config['labels'][$index];
        }
      }

      if (!in_array($region, array_merge(...array_column($region_config, 'regions')))) {
        $updated_regions_mix[$region] = $label;
      }

      $updated_regions_all[$region] = $label;
    }
  }

  return array_merge(['all' => $updated_regions_all, 'mix' => $updated_regions_mix], $updated_regions);
}

/**
 * Helper function.
 */
function _count_regions() {

  $active_theme = \Drupal::theme()->getActiveTheme()->getName();
  $theme_regions = system_region_list($active_theme);
  $block_storage = \Drupal::entityTypeManager()->getStorage('block');
  $config = \Drupal::service('config.factory')->getEditable('solo.settings');

  $regions = [
    'top' => ['top_first', 'top_second', 'top_third'],
    'main' => ['sidebar_first', 'content', 'sidebar_second'],
    'bottom' => ['bottom_first', 'bottom_second', 'bottom_third', 'bottom_fourth'],
    'footer' => ['footer_first', 'footer_second', 'footer_third'],
  ];

  $counts = [
    'top' => 0,
    'main' => 0,
    'bottom' => 0,
    'footer' => 0,
  ];

  foreach ($theme_regions as $region => $description) {
    $blocks = $block_storage->loadByProperties(['theme' => $active_theme, 'region' => $region]);

    if (!empty($blocks) && array_filter($blocks, function ($block) {
      return $block->status();
    })) {
      foreach ($regions as $key => $region_group) {
        if (in_array($region, $region_group)) {
          $counts[$key]++;
        }
      }
    }
  }

  $config->set('count_top', $counts['top'])->set('count_main', $counts['main'])->set('count_bottom', $counts['bottom'])->set('count_footer', $counts['footer'])->save();

  \Drupal::messenger()->addMessage(t("<p class='settings-message'>
    <ul class='settings-message'>
    <li><strong><a href='/admin/structure/block'>Regions</a> can be categorized as follows: <span class='error'>empty</span>, Non-Empty with all blocks <span class='info'>disabled</span>, and Non-Empty with at least one block <span class='status'>enabled</span></strong></li>
    <li><strong>Each region has unique settings and if the region is <span class='error'>empty</span> then you will not see it on this page.</strong></li>
    <li><strong>The Top Container has <span class='status'>@top</span> enabled region(s), the Main Container has <span class='status'>@main</span> enabled, the Bottom Container has <span class='status'>@bottom</span> enabled, the Footer Container has <span class='status'>@footer</span> enabled</strong></li>
    </ul>",
    [
      '@top' => $counts['top'],
      '@main' => $counts['main'],
      '@bottom' => $counts['bottom'],
      '@footer' => $counts['footer'],
    ]
  ));

  return $counts;

}

/**
 * Helper function.
 */
function _create_region_layout_radio_four($region) {

  return [
    '#type' => 'radios',
    '#title' => t("The four columns settings for $region region"),
    '#default_value' => theme_get_setting("{$region}_4col") ?? 'solo-col-1-1-1-1',
    '#options' => [
      'solo-col-1-1-1-1' => t('25%, 25%, 25%, 25%'),
      'solo-col-1-2-2-1' => t('16.6%, 33.3%, 33.3%, 16.6%'),
      'solo-col-2-1-1-2' => t('33.3%, 16.6%, 16.6%, 33.3%'),
      'solo-col-1-2-1-2' => t('16.6%, 33.3%, 16.6%, 33.3%'),
    ],
  ];
}

/**
 * Helper function.
 */
function _create_region_layout_radio_three($region) {
  if ($region == 'main') {
    $def = 'solo-col-1-2-1';
  }
  else {
    $def = 'solo-col-1-1-1';
  }
  return [
    '#type' => 'radios',
    '#title' => t("The three columns' settings for the $region region."),
    '#default_value' => theme_get_setting("{$region}_3col") ?? $def,
    '#options' => [
      'solo-col-1-1-1' => t('33.3%, 33.3%, 33.3%'),
      'solo-col-1-2-1' => t('25%, 50%, 25%'),
      'solo-col-1-3-1' => t('20%, 60%, 20%'),
    ],
  ];
}

/**
 * Helper function.
 */
function _get_which_main_col($variables) {
  if (empty($variables['page']['sidebar_first'])) {
    return 'solo-col-2-1';
  }
  elseif (empty($variables['page']['sidebar_second'])) {
    return 'solo-col-1-2';
  }
  else {
    return 'solo-col-1-1';
  }

}

/**
 * Helper function.
 */
function _create_region_layout_radio_two($region, &$variables) {
  if ($region == 'main') {
    $def = _get_which_main_col($variables);
  }
  else {
    $def = 'solo-col-1-1';
  }
  return [
    '#type' => 'radios',
    '#title' => t("The two columns' settings for the $region region."),
    '#default_value' => theme_get_setting("{$region}_2col") ?? $def,
    '#options' => [
      'solo-col-1-1' => t('50%, 50%'),
      'solo-col-2-1' => t('67%, 33%'),
      'solo-col-3-1' => t('75%, 25%'),
      'solo-col-4-1' => t('80%, 20%'),
      'solo-col-1-2' => t('33%, 67%'),
      'solo-col-1-3' => t('25%, 75%'),
      'solo-col-1-4' => t('20%, 80%'),
    ],
  ];
}

/**
 * Helper function.
 */
function _create_megamenu_layout() {
  return [
    '#type' => 'radios',
    '#title' => t('Megamenu Layout (1, 2, 3 or 4 Columns).'),
    '#default_value' => theme_get_setting('primary_menu_megamenu_layout') ?? 'm-1-1',
    '#options' => [
      'm-1' => t('1 Column - 100%'),
      'm-1-1' => t('2 Columns - 50%, 50%'),
      'm-2-1' => t('2 Columns - 67%, 33%'),
      'm-3-1' => t('2 Columns - 75%, 25%'),
      'm-4-1' => t('2 Columns - 80%, 20%'),
      'm-1-2' => t('2 Columns - 33%, 67%'),
      'm-1-3' => t('2 Columns - 25%, 75%'),
      'm-1-4' => t('2 Columns - 20%, 80%'),
      'm-1-1-1' => t('3 Columns - 33.3%, 33.3%, 33.3%'),
      'm-1-2-1' => t('3 Columns - 25%, 50%, 25%'),
      'm-1-3-1' => t('3 Columns - 20%, 60%, 20%'),
      'm-1-1-1-1' => t('4 Columns - 25%, 25%, 25%, 25%'),
      'm-1-2-2-1' => t('4 Columns - 16.6%, 33.3%, 33.3%, 16.6%'),
      'm-2-1-1-2' => t('4 Columns - 33.3%, 16.6%, 16.6%, 33.3%'),
      'm-1-2-1-2' => t('4 Columns - 16.6%, 33.3%, 16.6%, 33.3%'),
    ],
    '#states' => [
      'visible' => [
        ':input[name="primary_menu_megamenu"]' => ['checked' => TRUE],
      ],
    ],
  ];
}
