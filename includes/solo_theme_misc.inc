<?php

/**
 * @file
 */

/**
 * Helper function.
 */
function _load_google_fonts(array &$attachments): void {
  $fonts = ['site_font_google_global_on', 'site_font_google_heading_on'];
  foreach ($fonts as $font) {
    if (theme_get_setting($font)) {
      $font_setting = theme_get_setting($font);
      $font_path = 'solo/solo-gf' . (theme_get_setting('site_font_google_local_on') ? 'in' : 'ex') . substr($font_setting, 4);
      $attachments['#attached']['library'][] = $font_path;
    }
  }
}

/**
 * Helper function.
 */
function _load_special_fonts(array &$attachments): void {
  if (theme_get_setting('site_font_google_special_on')) {
    $ex_font_s = theme_get_setting('site_font_google_special_on');
    $current_exgfs = 'solo/solo-gfex' . substr($ex_font_s, 4);
    $attachments['#attached']['library'][] = $current_exgfs;
  }
}

/**
 * Helper function.
 */
function _load_predefined_color_theme(array &$attachments): void {
  $is_theme = theme_get_setting('predefined_current_theme') ?: 'none';
  // $theme_path = 'solo/' . ($is_theme == 'none' ?
  // 'solo-predefined-00052' : $is_theme);
  $theme_path = 'solo/' . $is_theme;
  $attachments['#attached']['library'][] = $theme_path;
}

/**
 * Helper function.
 */
function _load_font_awesome(array &$attachments): void {
  $font_awesome_versions = [
    1 => 'font-awesome-4-in',
    2 => 'font-awesome-4-ex',
    3 => 'font-awesome-5-ex',
    4 => 'font-awesome-6-ex',
  ];
  $fw_version = theme_get_setting('site_font_awesome_on');
  if (isset($font_awesome_versions[$fw_version])) {
    $attachments['#attached']['library'][] = 'solo/' . $font_awesome_versions[$fw_version];
  }
}

/**
 * Helper function.
 */
function _load_full_opacity(array &$attachments): void {
  /** @var \Drupal\Core\Routing\AdminContext $admin_context */
  $admin_context = \Drupal::service('router.admin_context');
  if (theme_get_setting('site_opacity_onscroll_on') && !$admin_context->isAdminRoute()) {
    $attachments['#attached']['library'][] = 'solo/solo-full-opacity';
  }
}

/**
 * Helper function.
 */
function _site_load_w3css_library(array &$attachments): void {
  /** @var \Drupal\Core\Routing\AdminContext $admin_context */
  if (theme_get_setting('site_load_w3css_on')) {
    $attachments['#attached']['library'][] = 'solo/w3-css';
  }
}

/**
 * Implements hook_page_attachments_alter().
 */
function solo_page_attachments_alter(array &$attachments): void {
  _load_google_fonts($attachments);
  _load_special_fonts($attachments);
  _load_predefined_color_theme($attachments);
  _load_font_awesome($attachments);
  _load_full_opacity($attachments);
  _site_load_w3css_library($attachments);
}

/**
 * Converts hex color strings to array of HSL values.
 *
 * @param string $hex_string
 *   The 6-character hexadecimal color code, optionally with a leading hash.
 *
 * @return array
 *   Array containing hue, saturation, and lightness values.
 *   $hue: integer of value 0-360 indicating the hue on a color wheel.
 *   $saturation: string of saturation as a percentage (0% all gray, 100% full color).
 *   $lightness: string of lightness as a percentage (0% darkened to black, 50% full color, 100% lightened to white).
 *
 * @see https://css-tricks.com/converting-color-spaces-in-javascript
 *   Code based on JS version.
 * @see https://www.rapidtables.com/convert/color/rgb-to-hsl.html
 *   Mathematical formula for rgb-to-hsl conversion.
 *
 * @internal
 */
function _solo_convert_hex_to_hsl(string $hex_string): array {
  // Convert hexcode pairs to rgb values (0-255).
  $hex_val = trim($hex_string, '#');
  $r0 = hexdec($hex_val[0] . $hex_val[1]);
  $g0 = hexdec($hex_val[2] . $hex_val[3]);
  $b0 = hexdec($hex_val[4] . $hex_val[5]);

  // Convert rgb's 0-255 to decimal values.
  $r = fdiv($r0, 255);
  $g = fdiv($g0, 255);
  $b = fdiv($b0, 255);

  // Calculate Hue.
  $c_min = min($r, $g, $b);
  $c_max = max($r, $g, $b);
  $delta = $c_max - $c_min;

  if ($delta == 0) {
    $h = 0;
  }
  else {
    switch ($c_max) {
      case $r:
        $h = fmod((($g - $b) / $delta), 6);
        break;

      case $g:
        $h = (($b - $r) / $delta) + 2;
        break;

      case $b:
        $h = (($r - $g) / $delta) + 4;
        break;

      default:
        $h = 0;
        break;
    }
  }

  $h = round($h * 60);

  // Shift hue range from [-60 - 300] to [0 - 360].
  if ($h < 0) {
    $h += 360;
  }

  // Calculate Lightness.
  $l = ($c_max + $c_min) / 2;

  // Calculate Saturation.
  $s = $delta == 0 ? 0 : $delta / (1 - abs((2 * $l) - 1));

  // Convert Saturation and Lightness to percentages.
  $s = round($s * 100);
  $l = round($l * 100);

  return [$h, $s, $l];
}
