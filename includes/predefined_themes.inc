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

$form['solo_settings']['w3_theme_current'] = [
  '#type' => 'details',
  '#title' => t('Predefined Color Themes - Solo Theme'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
  '#description'   => t('<p>solo-predefined-00000, solo-predefined-00001, solo-predefined-00002, solo-predefined-00003, solo-predefined-00004, solo-predefined-00005, solo-predefined-00006, solo-predefined-00007, solo-predefined-00008, solo-predefined-00009, solo-predefined-00010, solo-predefined-00011, solo-predefined-00012, solo-predefined-00013, solo-predefined-00014, solo-predefined-00015, solo-predefined-00016, solo-predefined-00017, solo-predefined-00018, solo-predefined-00019, solo-predefined-00020, solo-predefined-00021, solo-predefined-00022, solo-predefined-00023, solo-predefined-00024, solo-predefined-00025, solo-predefined-00026, solo-predefined-00027, solo-predefined-00028 and solo-predefined-00029, solo-predefined-00030,solo-predefined-00031, solo-predefined-00032, solo-predefined-00033, solo-predefined-00034, solo-predefined-00035, solo-predefined-00036, solo-predefined-00037, solo-predefined-00038, solo-predefined-00039, solo-predefined-00040, solo-predefined-00041, solo-predefined-00042, solo-predefined-00043, solo-predefined-00044, solo-predefined-00045, solo-predefined-00046, solo-predefined-00047, solo-predefined-00048, solo-predefined-00049, solo-predefined-00050, solo-predefined-00051 and solo-predefined-00052. <strong><a target="_blank" href="https://drupal8-w3css-theme.flashwebcenter.com/w3css-predefined-color-themes">Demo Site has an example for all color themes</a>.</strong></p>'),
];
$form['solo_settings']['w3_theme_current']['current_theme'] = [
  '#type' => 'select',
  '#title' => t("Choose the desired predefined theme"),
  '#options' => [
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
  ],
  '#default_value' => theme_get_setting('current_theme'),
];
