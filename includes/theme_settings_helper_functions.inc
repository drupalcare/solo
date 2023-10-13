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
function _get_predefined_theme() {
  return [
    'none' => t('None'),
    'solo-predefined-00000' => t('00000 - Plain No Colors'),
    'solo-predefined-00001' => t('00001 - Blue Gray'),
    'solo-predefined-00002' => t('00002 - Blue'),
    'solo-predefined-00003' => t('00003 - Green'),
    'solo-predefined-00004' => t('00004 - Purple'),
    'solo-predefined-00005' => t('00005 - Brown'),
    'solo-predefined-00006' => t('00006 - Red'),
    'solo-predefined-00007' => t('00007 - Indigo'),
    'solo-predefined-00008' => t('00008 - Red Turquoise'),
    'solo-predefined-00009' => t('00009 - DarkCyan'),
    'solo-predefined-00010' => t('00010 - Green Grass'),
    'solo-predefined-00011' => t('00011 - Dark Brown'),
    'solo-predefined-00012' => t('00012 - Blue Red'),
    'solo-predefined-00013' => t('00013 - CadetBlue'),
    'solo-predefined-00014' => t('00014 - SlateGray'),
    'solo-predefined-00015' => t('00015 - CadetBlue SteelBlue'),
    'solo-predefined-00016' => t('00016 - BlueOrange'),
    'solo-predefined-00017' => t('00017 - DarkOrange MidnightBlue'),
    'solo-predefined-00018' => t('00018 - Dark Theme - BlackGray'),
    'solo-predefined-00019' => t('00019 - Dark Theme - BlackGreen'),
    'solo-predefined-00020' => t('00020 - Dark Theme - BlackBlue'),
    'solo-predefined-00021' => t('00021 - Dark Theme - BlackPurple'),
    'solo-predefined-00022' => t('00022 - Dark Theme - BlackRed'),
    'solo-predefined-00023' => t('00023 - Dark Theme - BlackPink'),
    'solo-predefined-00024' => t('00024 - Dark Theme - BlackLightGreen'),
    'solo-predefined-00025' => t('00025 - Dark Theme - BlackDarkPurple'),
    'solo-predefined-00026' => t('00026 - Dark Theme - BlackYellow'),
    'solo-predefined-00027' => t('00027 - Dark Theme - BlackLightBlue'),
    'solo-predefined-00028' => t('00028 - DarkRed LightBlue'),
    'solo-predefined-00029' => t('00029 - Colorfull Image Background'),
    'solo-predefined-00030' => t('00030 - Amber'),
    'solo-predefined-00031' => t('00031 - Black'),
    'solo-predefined-00032' => t('00032 - Blue Gray'),
    'solo-predefined-00033' => t('00033 - Blue'),
    'solo-predefined-00034' => t('00034 - Brown'),
    'solo-predefined-00035' => t('00035 - Cyan'),
    'solo-predefined-00036' => t('00036 - Dark Gray'),
    'solo-predefined-00037' => t('00037 - Deep Orange'),
    'solo-predefined-00038' => t('00038 - Deep Purple'),
    'solo-predefined-00039' => t('00039 - Green'),
    'solo-predefined-00040' => t('00040 - Gray'),
    'solo-predefined-00041' => t('00041 - Indigo'),
    'solo-predefined-00042' => t('00042 - Khaki'),
    'solo-predefined-00043' => t('00043 - Light Blue'),
    'solo-predefined-00044' => t('00044 - Light Green'),
    'solo-predefined-00045' => t('00045 - Lime'),
    'solo-predefined-00046' => t('00046 - Orange'),
    'solo-predefined-00047' => t('00047 - Pink'),
    'solo-predefined-00048' => t('00048 - Purple'),
    'solo-predefined-00049' => t('00049 - Red'),
    'solo-predefined-00050' => t('00050 - Teal'),
    'solo-predefined-00051' => t('00051 - Yellow'),
    'solo-predefined-00052' => t('00052 - Black Gray White'),
  ];
}

/**
 * Helper function.
 */
function _get_site_width_options() {
  return [
    'solo-site-width-1600' => t('1600px / 100rem'),
    'solo-site-width-2560' => t('2560px / 160rem'),
    'solo-site-width-1920' => t('1920px / 120rem'),
    'solo-site-width-1440' => t('1440px / 90rem'),
    'solo-site-width-1280' => t('1280px / 80rem'),
    'solo-site-width-1024' => t('1024px / 64rem'),
    'solo-site-width-800' => t('800px / 50rem'),
    'solo-site-width-100' => t('100%'),
  ];
}

/**
 * Helper function.
 */
function _get_global_font_options() {
  return [
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
  ];
}

/**
 * Helper function.
 */
function _get_heading_font_options() {
  return [
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
  ];
}

/**
 * Helper function.
 */
function _get_special_font_options() {
  return [
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
  ];
}

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

  \Drupal::messenger()->addMessage(t('Top container has @top active region, Main container has @main active region, Bottom container has @bottom active region, footer container has @footer active region ',
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
function _get_region_options($count) {

  switch ($count) {
    case 4:
      return [
        'solo-col-1-1-1-1' => t('25%, 25%, 25%, 25%'),
        'solo-col-1-2-2-1' => t('16.6%, 33.3%, 33.3%, 16.6%'),
        'solo-col-2-1-1-2' => t('33.3%, 16.6%, 16.6%, 33.3%'),
        'solo-col-1-2-1-2' => t('16.6%, 33.3%, 16.6%, 33.3%'),
      ];

    break;
    case 3:
      return [
        'solo-col-1-1-1' => t('33.3%, 33.3%, 33.3%'),
        'solo-col-1-2-1' => t('25%, 50%, 25%'),
        'solo-col-1-3-1' => t('20%, 60%, 20%'),
      ];

    break;
    case 2:
      return [
        'solo-col-1-1' => t('50%, 50%'),
        'solo-col-2-1' => t('67%, 33%'),
        'solo-col-3-1' => t('75%, 25%'),
        'solo-col-4-1' => t('80%, 20%'),
        'solo-col-1-2' => t('33%, 67%'),
        'solo-col-1-3' => t('25%, 75%'),
        'solo-col-1-4' => t('20%, 80%'),
      ];

    break;
    case 1:
      return [
        'solo-col-1' => t('You have one active region and its stretched to 100%.'),
      ];

    break;
    default:
      return [
        'disabled' => t('You have no active regions.'),
      ];
  }
}

/**
 * Helper function.
 */
function _get_region_default($count, $region) {
  $theme_setting = theme_get_setting($region);
  $two_columns = [
    'solo-col-1-1',
    'solo-col-2-1',
    'solo-col-3-1',
    'solo-col-4-1',
    'solo-col-1-2',
    'solo-col-1-3',
    'solo-col-1-4',
  ];
  $three_columns = [
    'solo-col-1-1-1',
    'solo-col-1-2-1',
    'solo-col-1-3-1',
  ];
  $four_columns = [
    'solo-col-1-1-1-1',
    'solo-col-1-2-2-1',
    'solo-col-2-1-1-2',
    'solo-col-1-2-1-2',
  ];

  if ($count > 0) {

    switch ($count) {
      case 4:
        return in_array($theme_setting, $four_columns) ? $theme_setting : 'solo-col-1-1-1-1';

      break;

      case 3:
        return in_array($theme_setting, $three_columns) ? $theme_setting : 'solo-col-1-1-1';

      break;

      case 2:
        return in_array($theme_setting, $two_columns) ? $theme_setting : 'solo-col-1-1';

      break;

      case 1:
        return 'solo-col-1';

      break;

      default:
        return [
          'disabled' => t('You have no active regions.'),
        ];

    }
  }
}

/**
 * Helper function.
 */
function _generate_form_details($label) {
  return [
    '#type' => 'details',
    '#title' => t("$label Color - Solo Theme"),
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
    '#title' => t($label),
    '#description' => t('Insert any css classee here'),
    '#default_value' => theme_get_setting("classes_{$region}"),
  ];

}

/**
 * Helper function.
 */
function _create_region_layout_radio($region) {
  $counts = _count_regions();
  return [
    '#type' => 'radios',
    '#title' => t("The $region region layout"),
    '#default_value' => _get_region_default($counts[$region], "site_regions_layout_$region"),
    '#options' => _get_region_options($counts[$region]),
  ];
}

/**
 * Helper function.
 */
function _animate_region($region) {
  return [
    '#type' => 'checkbox',
    '#title' => t("Check this box to animate the region's border."),
    '#default_value' => theme_get_setting("site_regions_animate_$region"),
  ];
};

$updated_regions = _get_updated_regions();
$counts = _count_regions();
$attributes = _get_region_attributes();
// Foreach ($attributes as $key => $value) {
//   echo $key . $value . '<br>';
// }.
// Foreach ($updated_regions['footer'] as $region ) {
//   echo $region . '<br>';
// }.
