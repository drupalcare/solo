<?php

/**
 * @file
 * Theme preprocess functions.
 */

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Url;
use Drupal\views\Render\ViewsRenderPipelineMarkup;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\File\FileExists;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Controller\TitleResolver;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\user\UserInterface;
use Drupal\webform\Entity\Webform;
use Drupal\Core\Render\Element;

/**
 * Ensures the class attribute is an array before appending values.
 *
 * This defensive helper prevents "[] operator not supported for strings" errors
 * that occur when Drupal's Attribute objects convert class arrays to strings
 * during the rendering process.
 *
 * @param array $variables
 *   The variables array containing attributes.
 * @param string $attribute_path
 *   The attribute path (default: 'attributes' for $variables['attributes']['class']).
 */
function _solo_ensure_class_array(array &$variables, string $attribute_path = 'attributes'): void {
  // Handle standard attributes path.
  if ($attribute_path === 'attributes') {
    if (!isset($variables['attributes']['class'])) {
      $variables['attributes']['class'] = [];
    }
    elseif (!is_array($variables['attributes']['class'])) {
      // Convert string to array if already processed by Attribute object.
      $variables['attributes']['class'] = [$variables['attributes']['class']];
    }
  }
  // Handle nested paths like 'label.#attributes'.
  elseif (str_contains($attribute_path, '.')) {
    $parts = explode('.', $attribute_path);
    $ref = &$variables;
    foreach ($parts as $part) {
      $part = trim($part, '#');
      if (!isset($ref[$part])) {
        $ref[$part] = [];
      }
      $ref = &$ref[$part];
    }
    if (!isset($ref['class'])) {
      $ref['class'] = [];
    }
    elseif (!is_array($ref['class'])) {
      $ref['class'] = [$ref['class']];
    }
  }
}

/**
 * Helper function to add node ID to body class.
 *
 * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
 *   The route match service.
 * @param array $variables
 *   The variables array (passed by reference).
 */
function _add_node_id_to_body_class(RouteMatchInterface $route_match, array &$variables): void {
  $node = $route_match->getParameter('node');
  if ($node && $node_id = $node->id()) {
    _solo_ensure_class_array($variables);
    $variables['attributes']['class'][] = 'path-node-' . $node_id;
  }
}

/**
 * Helper function to add taxonomy term to body class.
 *
 * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
 *   The route match service.
 * @param array $variables
 *   The variables array (passed by reference).
 */
function _add_taxonomy_term_to_body_class(RouteMatchInterface $route_match, array &$variables): void {
  $term = $route_match->getParameter('taxonomy_term');
  if ($term && $term->id()) {
    _solo_ensure_class_array($variables);
    $variables['attributes']['class'][] = 'path-taxonomy-term-' . $term->id();
  }
  if ($term && $term->bundle()) {
    _solo_ensure_class_array($variables);
    $variables['attributes']['class'][] = 'path-vocabulary-' . Html::cleanCssIdentifier($term->bundle());
  }
}

/**
 * Helper function.
 */
function _add_user_path_to_body_class(&$variables): void {
  $curr_path = \Drupal::service('path.current')->getPath();
  $curr_path = ltrim($curr_path, '/');
  $user_url = explode('/', $curr_path);
  if (isset($user_url[0], $user_url[1]) && ($user_url[0] == 'user')) {
    _solo_ensure_class_array($variables);
    $variables['attributes']['class'][] = 'path-user-' . $user_url[1];
  }
}

/**
 * Helper function to add view ID to body class.
 *
 * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
 *   The route match service.
 * @param array $variables
 *   The variables array (passed by reference).
 */
function _add_view_id_to_body_class(RouteMatchInterface $route_match, array &$variables): void {
  $view_id = $route_match->getParameter('view_id');
  if ($view_id) {
    _solo_ensure_class_array($variables);
    $variables['attributes']['class'][] = 'views-page path-view-' . Html::cleanCssIdentifier($view_id);
  }
}

/**
 * Helper function.
 */
function _add_admin_context_to_body_class(&$variables): void {
  /** @var \Drupal\Core\Routing\AdminContext $admin_context */
  $admin_context = \Drupal::service('router.admin_context');
  if ($admin_context->isAdminRoute()) {
    _solo_ensure_class_array($variables);
    $variables['attributes']['class'][] = 'admin-context';
  }
}

/**
 * Helper function.
 */
function _get_theme_setting_class($setting, $class_prefix) {
  $value = theme_get_setting($setting);
  return $value ? " {$class_prefix}-{$value}" : '';
}

/**
 * Helper function.
 */
function _get_html_classes() {
  $fs_size = _get_theme_setting_class('site_global_font_size', 'fs');
  $gap_size = _get_theme_setting_class('site_global_regions_gap', 'gap');

  return $fs_size . $gap_size;
}

/**
 * Helper function.
 */
function _get_enabled_skip_links() {
  $enabled_skip_links = [];

  if (theme_get_setting('skip_header_content')) {
    $enabled_skip_links['skip_header_content'] = TRUE;
  }

  if (theme_get_setting('skip_navigation_content')) {
    $enabled_skip_links['skip_navigation_content'] = TRUE;
  }

  if (theme_get_setting('skip_main_content')) {
    $enabled_skip_links['skip_main_content'] = TRUE;
  }

  if (theme_get_setting('skip_footer_content')) {
    $enabled_skip_links['skip_footer_content'] = TRUE;
  }

  return $enabled_skip_links;
}

/**
 * Implements hook_preprocess_HOOK() for HTML document templates.
 */
function solo_preprocess_html(&$variables): void {
  $route_match = \Drupal::routeMatch();
  $route_name = $route_match->getRouteName();
  $solo_utilities = \Drupal::request()->getBasePath();
  $solo_list = \Drupal::service('extension.list.theme')->getPath('solo');
  $solo_path = $solo_utilities . '/' . $solo_list;
  $variables['solo_path'] = $solo_path;

  // Ensure class attribute is always an array before appending.
  _solo_ensure_class_array($variables);

  // Add 'px' class if the setting is enabled.
  if (theme_get_setting('enable_px_based_widths')) {
    $variables['attributes']['class'][] = 'px';
  }

  // Check if we're viewing a user profile page.
  if ($route_name === 'entity.user.canonical') {
    // Add a custom CSS class to the body tag.
    $variables['attributes']['class'][] = 'user-profile-page';
  }

  $theme_head_vars = ['site_css_injector',
    'site_css_dynamic',
    'site_import_google_font',
  ];
  foreach ($theme_head_vars as $theme_head_var) {
    $variables[$theme_head_var] = theme_get_setting($theme_head_var);
  }

  $google_font = theme_get_setting('site_import_google_font');
  if (!empty($google_font)) {
    $variables['#attached']['html_head'][] = [
      [
        '#tag' => 'link',
        '#attributes' => [
          'href' => $google_font,
          'rel' => 'stylesheet',
          'type' => 'text/css',
        ],
      ],
      'google_font',
    ];
  }

  $css_injector = theme_get_setting('site_css_injector');
  if (!empty($css_injector)) {
    $variables['#attached']['html_head'][] = [
      [
        '#type' => 'html_tag',
        '#tag' => 'style',
        '#value' => $css_injector,
      ],
      'inline_css',
    ];
  }

  $file_system = \Drupal::service('file_system');
  $file_url_generator = \Drupal::service('file_url_generator');

  // Assuming you've already retrieved your dynamic content.
  $css_dynamic = theme_get_setting('site_css_dynamic');
  $js_dynamic = theme_get_setting('site_js_dynamic');

  // Define the file URIs.
  $css_file_uri = 'public://solo/css/solo-css-dynamic.css';
  $js_file_uri = 'public://solo/js/solo-js-dynamic.js';

  // Check and prepare directories.
  $css_directory = $file_system->dirname($css_file_uri);
  $js_directory = $file_system->dirname($js_file_uri);
  if (!$file_system->prepareDirectory($css_directory, FileSystemInterface::CREATE_DIRECTORY) ||
      !$file_system->prepareDirectory($js_directory, FileSystemInterface::CREATE_DIRECTORY)) {
    // Log an error if directories could not be prepared.
    \Drupal::logger('solo_preprocess_html')->error('Failed to prepare directory for CSS or JS.');
    return;
  }

  // Handle dynamic CSS.
  if (!empty($css_dynamic)) {
    // Write the CSS content to the file.
    if ($file_system->saveData($css_dynamic, $css_file_uri, FileExists::Replace) !== FALSE) {
      // Generate the absolute URL for the CSS file and attach it to the HTML.
      $css_hash = md5($css_dynamic);
      $css_url = $file_url_generator->generateAbsoluteString($css_file_uri) . '?v=' . $css_hash;
      $variables['#attached']['html_head'][] = [
        [
          '#type' => 'html_tag',
          '#tag' => 'link',
          '#attributes' => ['href' => $css_url, 'rel' => 'stylesheet'],
        ],
        'solo_dynamic_css',
      ];
    }
    else {
      \Drupal::logger('solo_preprocess_html')->error('Failed to save dynamic CSS content.');
    }
  }
  else {
    // Delete the CSS file if $css_dynamic is empty.
    $css_real_path = \Drupal::service('file_system')->realpath($css_file_uri);
    if (file_exists($css_real_path)) {
      $file_system->delete($css_file_uri);
    }
  }

  // Handle dynamic JS.
  if (!empty($js_dynamic)) {
    // Attach the JS library.
    $variables['#attached']['library'][] = 'solo/solo-js-dynamic';
    // Write the JS content to the file.
    if ($file_system->saveData($js_dynamic, $js_file_uri, FileExists::Replace) !== FALSE) {
      // Generate the absolute URL for the JS file and attach it to the HTML.
      $js_hash = md5($js_dynamic);
      $js_url = $file_url_generator->generateAbsoluteString($js_file_uri) . '?v=' . $js_hash;
      $variables['#attached']['html_head'][] = [
        [
          '#type' => 'html_tag',
          '#tag' => 'script',
          '#attributes' => [
            'src' => $js_url,
            'type' => 'application/javascript',
          ],
        ],
        'solo_dynamic_js',
      ];
    }
    else {
      \Drupal::logger('solo_preprocess_html')->error('Failed to save dynamic JS content.');
    }
  }
  else {
    // Delete the JS file if $js_dynamic is empty.
    $js_real_path = \Drupal::service('file_system')->realpath($js_file_uri);
    if (file_exists($js_real_path)) {
      $file_system->delete($js_file_uri);
    }
  }

  if (theme_get_setting('site_global_font_size') || theme_get_setting('site_global_regions_gap')) {
    $variables['html_attributes']->setAttribute('class', _get_html_classes());
  }

  // Add node id to the body class.
  if ($route_name === 'entity.node.canonical') {
    _add_node_id_to_body_class($route_match, $variables);
  }

  // Add vocabulary name and term id to the body class.
  if ($route_name === 'entity.taxonomy_term.canonical') {
    _add_taxonomy_term_to_body_class($route_match, $variables);
  }

  // Check if the cookies module is enabled.
  if (\Drupal::moduleHandler()->moduleExists('cookies')) {
    // Attach the library when the module is enabled.
    $variables['#attached']['library'][] = 'solo/solo-cookies';
  }

  // If the user is logged in use ID if not use second word after user/login.
  _add_user_path_to_body_class($variables);

  // Add view id name to the body class.
  _add_view_id_to_body_class($route_match, $variables);

  // Add admin context to the body class.
  _add_admin_context_to_body_class($variables);

  $variables['enabled_skip_links'] = _get_enabled_skip_links();

  $request = \Drupal::request();
  $request->attributes->set('solo_body_classes', $variables['attributes']['class'] ?? []);

  $speed_setting = theme_get_setting('site_toggle_speed');

  // Default values for 'normal'.
  $speed_values = [
    'slideUpSpeed' => 350,
    'slideDownSpeed' => 500,
    'megaMenuSpeed' => 800,
  ];

  switch ($speed_setting) {
    case 'fast':
      $speed_values = [
        'slideUpSpeed' => 150,
        'slideDownSpeed' => 250,
        'megaMenuSpeed' => 500,
      ];
      break;

    case 'slow':
      $speed_values = [
        'slideUpSpeed' => 500,
        'slideDownSpeed' => 700,
        'megaMenuSpeed' => 1400,
      ];
      break;
  }

  $variables['#attached']['drupalSettings']['solo'] = array_merge(
  $variables['#attached']['drupalSettings']['solo'] ?? [],
  $speed_values
  );

}

/**
 * Helper function.
 */
function _get_site_regions() {
  return [
    'page_wrapper',
    'popup_login_block',
    'fixed_search_block',
    'header',
    'primary_sidebar_menu',
    'primary_menu',
    'welcome_text',
    'top_container',
    'top_first',
    'top_second',
    'top_third',
    'highlighted',
    'system_messages',
    'page_title',
    'breadcrumb',
    'main_container',
    'sidebar_first',
    'content',
    'sidebar_second',
    'bottom_container',
    'bottom_first',
    'bottom_second',
    'bottom_third',
    'bottom_fourth',
    'footer_container',
    'footer_first',
    'footer_second',
    'footer_third',
    'footer_menu',
    'credit_copyright',
  ];
}

/**
 * Helper function.
 */
function _get_css_classes() {
  $regions = _get_site_regions();
  $css_classes = [];
  foreach ($regions as $region) {
    $css_classes[] = "classes_{$region}";
  }
  return $css_classes;
}

/**
 * Helper function.
 */
function _get_region_alignment() {
  $regions = _get_site_regions();
  $region_alignment = [];
  foreach ($regions as $region) {
    $region_alignment[] = "align_{$region}";
  }
  return $region_alignment;
}

/**
 * Helper function.
 */
function _get_region_attributes(): array {
  return [
    '__r_bg'      => t('Region background'),
    '__r_tx'      => t('Region text'),
    '__r_h1'      => t('Headings (H1-H3)'),
    '__r_lk'      => t('Link text'),
    '__r_lk_h'    => t('Link text (hover)'),
    '__r_br'      => t('borders'),
    '__r_bg_fr'   => t('Form field background'),
    '__r_tx_lk'   => t('Menu link text'),
    '__r_tx_lk_h' => t('Menu link text (hover)'),
    '__r_bg_lk'   => t('Menu link background'),
    '__r_bg_lk_h' => t('Menu link background (hover)'),
    '__r_tx_bt'   => t('Button text'),
    '__r_tx_bt_h' => t('Button text (hover)'),
    '__r_bg_bt'   => t('Button background'),
    '__r_bg_bt_h' => t('Button background (hover)'),
  ];
}

/**
 * Helper function.
 */
function _get_settings_region() {
  $regions = _get_site_regions();
  $attributes = _get_region_attributes();
  $settings_region = [];
  foreach ($regions as $region) {
    foreach ($attributes as $attribute_key => $attribute_label) {
      $settings_region[] = "settings_{$region}_{$attribute_key}";
    }
  }
  return $settings_region;
}

/**
 * Convert hex color to RGB array.
 */
function _solo_hex_to_rgb(string $hex): array {
  $hex = trim($hex, '#');
  return [
    'r' => hexdec(substr($hex, 0, 2)),
    'g' => hexdec(substr($hex, 2, 2)),
    'b' => hexdec(substr($hex, 4, 2)),
  ];
}

/**
 * Helper function.
 */
function _get_region_attributes_for_region($region, $attributes) {
  $region_attr = [];

  foreach ($attributes as $key => $value) {
    if (theme_get_setting("settings_{$region}_{$key}")) {
      $css_val = theme_get_setting("settings_{$region}_{$key}");
      $css_var = str_replace('_', '-', $key);
      // Un comment the below to use hsla colors.
      // $css_val = _solo_convert_hex_to_hsl($css_val);
      // $css_val = implode(', ', $css_val);
      // $css_val = "hsla({$css_val},1)";.
      $css_var = "{$css_var}:{$css_val}";
      $region_attr[] = $css_var;
    }
  }

  return $region_attr;
}

/**
 * Helper function.
 */
function _create_region_attributes(&$variables) {
  $site_regions = _get_site_regions();
  $attributes = _get_region_attributes();

  foreach ($site_regions as $region) {
    $variables["attributes_{$region}"] = new Attribute();
    $region_attr = _get_region_attributes_for_region($region, $attributes);

    if ($region_attr) {
      $variables["attributes_{$region}"]->setAttribute('style', implode(';', $region_attr));
    }
  }
}

/**
 * Helper function.
 */
function _get_theme_variables() {

  return [
    'misc' => [
      'opacity_page_wrapper',
      'site_global_breakpoints',
      'site_menu_breakpoints',
      'site_global_width',
      'site_css_injector',
      'site_name_google_font',
      'site_pagetitle_google_font',
      'site_font_google_special_on',
      'site_font_google_heading_on',
      'site_font_google_global_on',
      'site_font_google_local_on',
      'site_font_awesome_on',
      'site_load_w3css_on',
      'site_opacity_onscroll_on',
      'site_login_page_on',
      'site_pagetitle_animate_on',
      'site_pagetitle_font_size_s',
      'site_pagetitle_font_size_l',
      'site_name_font_size_s',
      'site_name_font_size_l',
      'site_name_animate_on',
      'site_breadcrumb_scroll',
      'site_breadcrumb_pagetitle_off',
      'site_inline_items_on',
      'site_regions_collapse_order',
      'site_regions_top_animate_border',
      'site_regions_bottom_animate_border',
      'site_regions_footer_animate_border',
      'site_regions_top_animate_hover',
      'site_regions_bottom_animate_hover',
      'site_regions_footer_animate_hover',
      'site_regions_top_rounded',
      'site_regions_main_rounded',
      'site_regions_bottom_rounded',
      'site_regions_footer_rounded',
      'site_regions_highlighted_disable',
      'site_regions_welcome_disable',
      'site_regions_welcome_width',
      'site_regions_top_disable',
      'site_regions_bottom_disable',
      'site_regions_footer_disable',
      'site_flip_header_menu',
      'site_mobile_layout_order',
      'primary_menu_border',
      'primary_menu_branding',
      'primary_menu_justify_content',
      'primary_sidebar_menu_border',
      'primary_sidebar_menu_branding',
      'site_regions_top_border',
      'site_regions_main_border',
      'site_regions_bottom_border',
      'site_regions_footer_border',
      'top_2col',
      'top_3col',
      'main_2col',
      'main_3col',
      'bottom_2col',
      'bottom_3col',
      'bottom_4col',
      'footer_2col',
      'footer_3col',
      'header_change_icons',
      'header_sitename_center',
      'header_sitename_center_small',
      'header_sitename_center_default',
      'header_logo_image_alt',
      'header_popup_login',
      'count_top',
      'count_main',
      'count_bottom',
      'count_footer',
      'theme_category',
      'enable_aria_bottom',
      'enable_aria_top',
      'enable_aria_footer',
      'enable_aria_welcome',
      'enable_aria_highlighted',
      'enable_block_title_visibility',
      'disable_block_title_visibility',
    ],
    'social' => [
      'sm_icon_size',
      'sm_icon_colors',
      'sm_show_icons',
      'sm_icon_snapchat',
      'sm_icon_tiktok',
      'sm_icon_threads',
      'sm_icon_whatsapp',
      'sm_icon_telegram',
      'sm_icon_reddit',
      'sm_icon_facebook',
      'sm_icon_twitter',
      'sm_icon_bluesky',
      'sm_icon_pinterest',
      'sm_icon_linkedin',
      'sm_icon_instagram',
      'sm_icon_youtube',
      'sm_icon_drupal',
      'sm_icon_email',
      'sm_icon_rss',
      'sm_icon_mastodon',
      'footer_copyright',
      'footer_link',
      'footer_link_label',
      'footer_link_text',
    ],
    'css_classes' => _get_css_classes(),
    'region_alignment' => _get_region_alignment(),
    'settings_region' => _get_settings_region(),
  ];
}

/**
 * Returns the width class for a given node, if custom node widths are enabled.
 *
 * @param \Drupal\node\NodeInterface $node
 *   The node to check.
 *
 * @return string|null
 *   The width class string, or NULL if not defined or not supported.
 */
function _get_node_width_class(NodeInterface $node): ?string {
  if (\Drupal::moduleHandler()->moduleExists('solo_utilities') &&
      \Drupal::entityTypeManager()->hasDefinition('node_width')) {
    $items = \Drupal::entityTypeManager()->getStorage('node_width')
      ->loadByProperties(['node_id' => $node->id()]);
    if (!empty($items)) {
      return reset($items)->get('width_class')->value;
    }
  }
  return NULL;
}

/**
 * Implements hook_preprocess_page() for page templates.
 */
function solo_preprocess_page(&$variables) {

  $keyboard_menus = theme_get_setting('keyboard_navigation_menus');
  if ($keyboard_menus) {
    // Filter out unchecked items (checkboxes return 0 for unchecked)
    $enabled_menus = array_filter($keyboard_menus);
    $variables['#attached']['drupalSettings']['solo']['keyboardMenus'] = array_values($enabled_menus);
  }

  // Get current content type.
  $node = \Drupal::routeMatch()->getParameter('node');
  $content_type = $node instanceof NodeInterface ? $node->bundle() : NULL;
  // $variables['solo_path'] = &$variables['solo_path'];
  $variables_lists = _get_theme_variables();
  foreach ($variables_lists as $variables_list) {
    foreach ($variables_list as $variable_list_value) {
      $variables[$variable_list_value] = theme_get_setting($variable_list_value);
    }
  }

  if (theme_get_setting('site_global_breakpoints')) {
    $g_point = theme_get_setting('site_global_breakpoints');
    $variables['#attached']['library'][] = 'solo/solo-global-breakpoints-' . $g_point;
  }

  if (theme_get_setting('site_menu_breakpoints')) {
    $m_point = theme_get_setting('site_menu_breakpoints');
    $variables['#attached']['library'][] = 'solo/solo-menu-breakpoints-' . $m_point;
  }

  $keyboard_menus = theme_get_setting('keyboard_navigation_menus');
  if ($keyboard_menus && array_filter($keyboard_menus)) {
    $variables['#attached']['library'][] = 'solo/solo-menu-keyboard';
  }

  if ($layout_order = theme_get_setting('site_mobile_layout_order')) {
    $layout_class = str_replace('_', '-', $layout_order);
    $variables['main_layout_order'] = $layout_class;
  }

  if (!theme_get_setting('system_messages_disable_dark')) {
    $variables['#attached']['library'][] = 'solo/solo-system-messages-dark';
  }

  if (theme_get_setting('site_name_font_size_s')) {
    $variables['name_size_s'] = 'name-s-' . theme_get_setting('site_name_font_size_s');
  }

  if (theme_get_setting('site_name_font_size_l')) {
    $variables['name_size_l'] = 'name-l-' . theme_get_setting('site_name_font_size_l');
  }

  if (theme_get_setting('site_pagetitle_font_size_s')) {
    $variables['title_size_s'] = 'title-s-' . theme_get_setting('site_pagetitle_font_size_s');
  }

  if (theme_get_setting('site_pagetitle_font_size_l')) {
    $variables['title_size_l'] = 'title-l-' . theme_get_setting('site_pagetitle_font_size_l');
  }

  if (theme_get_setting('site_pagetitle_center')) {
    $variables['site_pagetitle_center'] = 'pt-center';
  }

  if (theme_get_setting('primary_sidebar_menu_branding')) {
    $variables['site_logo'] = theme_get_setting('logo.url');
    $variables['logo_default'] = _set_logo_default($variables);
    $variables['site_name'] = \Drupal::config('system.site')->get('name');
    $variables['logo_alt'] = theme_get_setting('header_logo_image_alt') ?: 'Home';
  }

  $variables['enabled_skip_links'] = _get_enabled_skip_links();

  // Top regions variables.
  $regions = [
    'top' => ['first', 'second', 'third'],
    'sidebar' => ['first', 'content', 'second'],
    'bottom' => ['first', 'second', 'third', 'fourth'],
    'footer' => ['first', 'second', 'third'],
  ];

  // Detect subregion presence and calculate region counts.
  foreach ($regions as $region => $subregions) {
    foreach ($subregions as $subregion) {
      if ($subregion === 'content') {
        ${"has_{$subregion}"} = !empty($variables['page']["{$subregion}"]);
      }
      ${"has_{$region}_{$subregion}"} = !empty($variables['page']["{$region}_{$subregion}"]);
    }
  }

  $count_top = (int) $has_top_first + (int) $has_top_second + (int) $has_top_third;
  $count_main = (int) $has_sidebar_first + (int) $has_content + (int) $has_sidebar_second;
  $count_bottom = (int) $has_bottom_first + (int) $has_bottom_second + (int) $has_bottom_third + (int) $has_bottom_fourth;
  $count_footer = (int) $has_footer_first + (int) $has_footer_second + (int) $has_footer_third;
  $found_main = [$has_sidebar_first, $has_content, $has_sidebar_second];

  $counts = [
    'top' => $count_top,
    'main' => $count_main,
    'bottom' => $count_bottom,
    'footer' => $count_footer,
  ];

  // Assign layout classes based on active column count and overrides.
  foreach ($counts as $region => $count) {
    $layout = NULL;

    if ($count === 1) {
      $layout = 'solo-col-1';
    }

    elseif ($count === 2) {
      $layout = theme_get_setting("{$region}_2col") ?? 'solo-col-1-1';

      // Smart sidebar layout for main.
      if ($region === 'main') {
        if (!$found_main[0]) {
          $layout = theme_get_setting("{$region}_2col") ?? 'solo-col-2-1';
        }
        elseif (!$found_main[2]) {
          $layout = theme_get_setting("{$region}_2col") ?? 'solo-col-1-2';
        }
      }

      // Override per content type.
      if ($content_type && theme_get_setting("enable_per_type_layout_{$region}")) {
        $override = theme_get_setting("solo_layout_{$region}_2col_{$content_type}");
        if (!empty($override)) {
          $layout = $override;
        }
      }
    }

    elseif ($count === 3) {
      $layout = theme_get_setting("{$region}_3col") ?? 'solo-col-1-1-1';

      if ($region === 'main') {
        $layout = theme_get_setting("{$region}_3col") ?? 'solo-col-1-2-1';
      }

      if ($content_type && theme_get_setting("enable_per_type_layout_{$region}")) {
        $override = theme_get_setting("solo_layout_{$region}_3col_{$content_type}");
        if (!empty($override)) {
          $layout = $override;
        }
      }
    }

    elseif ($count === 4) {
      $layout = theme_get_setting("{$region}_4col") ?? 'solo-col-1-1-1-1';

      if ($content_type && theme_get_setting("enable_per_type_layout_{$region}")) {
        $override = theme_get_setting("solo_layout_{$region}_4col_{$content_type}");
        if (!empty($override)) {
          $layout = $override;
        }
      }
    }

    $variables["{$region}_layout"] = $layout;
  }

  // Check if the curren user on login/register or reset.
  if (theme_get_setting('site_login_page_on')) {
    $curr_path = \Drupal::service('path.current')->getPath();
    $curr_path = ltrim($curr_path, '/');
    if ($curr_path == 'user/login' || $curr_path == 'user/register' || $curr_path == 'user/password') {
      unset($variables['page']['sidebar_first'], $variables['page']['sidebar_second']);
      $variables['#attached']['library'][] = 'solo/solo-user-login-custom';
    }
  }

  _create_region_attributes($variables);

  // Initialize with the global width setting.
  $final_widths = theme_get_setting('site_global_width');

  // Only apply per-content-type and node widths if solo_utilities is active.
  if (\Drupal::moduleHandler()->moduleExists('solo_utilities')) {
    $type_widths = theme_get_setting('enable_custom_widths');
    $node_widths_enabled = theme_get_setting('enable_custom_node_width');

    if ($type_widths || $node_widths_enabled) {
      if ($node instanceof NodeInterface) {
        $content_type = $node->bundle();

        // Check for content type-specific width override.
        if ($type_widths) {
          $content_type_width = theme_get_setting('site_width_' . $content_type);
          if (!empty($content_type_width)) {
            $final_widths = $content_type_width;
          }
        }

        // Check for node-specific width override.
        if ($node_widths_enabled) {
          $node_class = _get_node_width_class($node);
          if ($node_class) {
            $final_widths = $node_class;
          }
        }
      }
    }
  }

  // Pass the width class to the Twig template.
  $variables['site_width_class'] = $final_widths;

  if (theme_get_setting('header_popup_login')) {
    $bg_color = theme_get_setting('settings_popup_login_block___r_bg') ?: '';
    $bg_rgb = '';

    if ($bg_color && preg_match('/^#[a-fA-F0-9]{6}$/', $bg_color)) {
      $rgb = _solo_hex_to_rgb($bg_color);
      $bg_rgb = implode(',', [$rgb['r'], $rgb['g'], $rgb['b']]);
    }

    $variables['#attached']['drupalSettings']['solo']['popupLogin'] = [
      'enabled' => (bool) theme_get_setting('header_popup_login'),
      'useInlineStyles' => (bool) theme_get_setting('popup_login_use_inline_styles'),
      'animationDuration' => (int) (theme_get_setting('popup_login_animation_duration') ?: 300),
      'closeOnEscape' => (bool) theme_get_setting('popup_login_close_on_escape'),
      'closeOnOutsideClick' => (bool) theme_get_setting('popup_login_close_on_outside_click'),
      'focusTrap' => (bool) theme_get_setting('popup_login_focus_trap'),
      'announceToScreenReaders' => (bool) theme_get_setting('popup_login_announce_to_screen_readers'),
      'returnFocusOnClose' => (bool) theme_get_setting('popup_login_return_focus_on_close'),
      'customTriggers' => (string) (theme_get_setting('popup_login_custom_triggers') ?: ''),
      'zIndex' => (int) (theme_get_setting('popup_login_z_index') ?: 10000),
      'overlayOpacity' => (int) (theme_get_setting('popup_login_overlay_opacity') ?: 50),
      'backgroundColorRgb' => $bg_rgb,
    ];
  }

}

/**
 * Implements hook_preprocess_HOOK() for node.html.twig.
 */
function solo_preprocess_node(array &$variables) {
  $d_format = theme_get_setting('site_node_date_format');

  // Do not override if the user selected "none" (i.e., use Drupal's default).
  if (
    is_string($d_format) &&
    $d_format !== 'none' &&
    !empty($variables['date']) &&
    !empty($variables['display_submitted']) &&
    $variables['display_submitted'] === TRUE
  ) {
    $variables['date'] = \Drupal::service('date.formatter')->format(
      $variables['node']->getCreatedTime(),
      $d_format
    );
  }

  // Remove the "Add new comment" link on teasers or when comment form visible.
  if ($variables['teaser'] || !empty($variables['content']['comments']['comment_form'])) {
    unset($variables['content']['links']['comment']['#links']['comment-add']);
  }

  // Add reading mode class if enabled.
  $content_type = $variables['node']->getType();
  $reading_mode_enabled = theme_get_setting('site_reading_mode_content_type');
  if (is_array($reading_mode_enabled) && in_array($content_type, $reading_mode_enabled, TRUE)) {
    _solo_ensure_class_array($variables);
    $variables['attributes']['class'][] = 'reading-mode';
  }
}

/**
 * Helper function: Adds node titles to the breadcrumb.
 */
function _add_node_breadcrumb(RouteMatchInterface $route_match, array &$breadcrumb): void {
  if ($route_match->getRouteName() === 'entity.node.canonical') {
    $node = $route_match->getParameter('node');
    if ($node instanceof NodeInterface) {
      $label = $node->label();
      if (is_array($label)) {
        $label = reset($label);
      }
      $breadcrumb[] = ['text' => Html::decodeEntities((string) $label)];
    }
  }
}

/**
 * Helper function: Adds taxonomy term titles to the breadcrumb.
 */
function _add_taxonomy_breadcrumb(RouteMatchInterface $route_match, array &$breadcrumb): void {
  if ($route_match->getRouteName() === 'entity.taxonomy_term.canonical') {
    $term = $route_match->getParameter('taxonomy_term');
    if ($term instanceof TermInterface) {
      $label = $term->label();
      if (is_array($label)) {
        $label = reset($label);
      }
      $breadcrumb[] = ['text' => Html::decodeEntities((string) $label)];
    }
  }
}

/**
 * Helper function: Adds user profile names to the breadcrumb.
 */
function _add_user_breadcrumb(RouteMatchInterface $route_match, array &$breadcrumb): void {
  if ($route_match->getRouteName() === 'entity.user.canonical') {
    $user = $route_match->getParameter('user');
    if ($user instanceof UserInterface) {
      $display_name = $user->getDisplayName();
      if (is_array($display_name)) {
        $display_name = reset($display_name);
      }
      $breadcrumb[] = ['text' => Html::decodeEntities((string) $display_name)];
    }
  }
}

/**
 * Helper function: Adds webform titles to the breadcrumb.
 */
function _add_webform_breadcrumb(RouteMatchInterface $route_match, array &$breadcrumb): void {
  if ($route_match->getRouteName() === 'entity.webform.canonical') {
    $webform = $route_match->getParameter('webform');
    if ($webform instanceof Webform) {
      $label = $webform->label();
      if (is_array($label)) {
        $label = reset($label);
      }
      $breadcrumb[] = ['text' => Html::decodeEntities((string) $label)];
    }
  }
}

/**
 * Helper function: Adds views titles to the breadcrumb.
 */
function _add_view_breadcrumb(RouteMatchInterface $route_match, array &$breadcrumb, TitleResolver $title_resolver, array $body_classes) {
  // Get the route and check if the page is generated by a View.
  $route = $route_match->getRouteObject();
  $view_id = $route ? $route->getDefault('view_id') : NULL;

  // Ensure it's a Views Page by checking both `view_id` and 'views-page' class.
  $is_views_page = $view_id || (!empty($body_classes) && in_array('views-page', $body_classes));

  if (!$is_views_page) {
    return;
  }

  // Attempt to get the title from the title resolver.
  $request = \Drupal::request();
  $title = $title_resolver->getTitle($request, $route);

  // Handle ViewsRenderPipelineMarkup objects.
  if ($title instanceof ViewsRenderPipelineMarkup) {
    $title = $title->__toString();
  }

  // If the title is a render array, extract #markup.
  if (is_array($title) && isset($title['#markup'])) {
    $title = $title['#markup'];
  }

  // Ensure the title is a valid string.
  if (!is_string($title) || empty($title)) {
    return;
  }

  // Add the title to the breadcrumb.
  $breadcrumb[] = ['text' => Html::decodeEntities($title)];
}

/**
 * Implements hook_preprocess_breadcrum().
 */
function solo_preprocess_breadcrumb(&$variables) {
  if (!empty($variables['breadcrumb']) && !theme_get_setting('site_breadcrumb_pagetitle_off')) {
    $variables['breadcrumb'] = $variables['breadcrumb'] ?? [];

    $breadcrumb     = [];
    $request        = \Drupal::request();
    $route_match    = \Drupal::service('current_route_match');
    $title_resolver = \Drupal::service('title_resolver');
    $route          = $route_match->getRouteObject();
    $body_classes   = $request->attributes->get('solo_body_classes', []);

    // Only get the page title if we have a valid route object.
    if ($route) {
      $page_title = $title_resolver->getTitle($request, $route);

      if (!empty($page_title) && is_string($page_title)) {
        $title_exists = FALSE;
        // Check if the page title already exists in the breadcrumb.
        foreach ($variables['breadcrumb'] as $breadcrumb_item) {
          if (!empty($breadcrumb_item['text']) && $breadcrumb_item['text'] === $page_title) {
            $title_exists = TRUE;
            break;
          }
        }
        // If not found, add the page title.
        if (!$title_exists) {
          $variables['breadcrumb'][] = ['text' => Html::decodeEntities($page_title)];
          $variables['#cache']['contexts'][] = 'url';
        }
      }
    }

    _add_node_breadcrumb($route_match, $breadcrumb);
    _add_taxonomy_breadcrumb($route_match, $breadcrumb);
    _add_user_breadcrumb($route_match, $breadcrumb);
    _add_webform_breadcrumb($route_match, $breadcrumb);
    _add_view_breadcrumb($route_match, $breadcrumb, $title_resolver, $body_classes);

    $variables['breadcrumb'] = array_merge($variables['breadcrumb'], $breadcrumb);
    if (!empty($variables['breadcrumb'])) {
      $unique_breadcrumb = [];
      $seen_texts = [];

      foreach ($variables['breadcrumb'] as $item) {
        if (!empty($item['text'])) {
          $text = $item['text'];
          if (is_array($text)) {
            if (isset($text['#markup']) && is_string($text['#markup'])) {
              $text = $text['#markup'];
            }
            else {
              continue;
            }
          }
          $decoded_text = Html::decodeEntities($text);

          if (!in_array($decoded_text, $seen_texts, TRUE)) {
            $seen_texts[] = $decoded_text;
            $unique_breadcrumb[] = $item;
          }
        }
      }

      $variables['breadcrumb'] = $unique_breadcrumb;
    }
  }
}

/**
 * Implements hook_preprocess_field().
 */
function solo_preprocess_field(&$variables, $hook) {
  // Check if the field is of type 'entity_reference'.
  if (isset($variables['element']['#field_type']) && $variables['element']['#field_type'] === 'entity_reference') {
    // Check if items are set and not empty.
    if (!empty($variables['element']['#items'])) {
      // Load the first item.
      $first_item = $variables['element']['#items']->first();

      // Check if the first item is not empty and has an 'entity' property.
      if ($first_item && $first_item->entity) {
        $entity = $first_item->entity;

        // Get the entity type of the referenced entity.
        $entity_type = $entity->getEntityTypeId();

        // Create a CSS class based on the entity type
        // (e.g., 'taxonomy-entity', 'user-entity').
        $class = $entity_type . '-entity';

        // Sanitize and add the CSS class to the field wrapper.
        _solo_ensure_class_array($variables);
        $variables['attributes']['class'][] = Html::getClass($class);
      }
    }
  }
}

/**
 * Implements hook_preprocess_HOOK().
 */
function solo_preprocess_field__node__created(&$variables): void {
  array_walk($variables['items'], function (&$item) {
    if (isset($item['content']['#prefix'])) {
      unset($item['content']['#prefix']);
    }
  });
}

/**
 * Implements hook_preprocess_HOOK() for setting classes.
 */
function solo_preprocess_filter_caption(&$variables): void {
  $variables['classes'] = $variables['classes'] ?? '';
  $variables['classes'] .= ' caption';
}

/**
 * Implements hook_preprocess_search_result().
 */
function solo_preprocess_search_result(&$variables): void {
  // Check if teaser mode is enabled.
  if (theme_get_setting('enable_teaser_mode')) {
    // Render the teaser view mode for the node, if available.
    if (
      isset($variables['result']['node']) &&
      $variables['result']['node'] instanceof
    NodeInterface) {
      $node = $variables['result']['node'];
      $variables['search_teaser'] = \Drupal::entityTypeManager()
        ->getViewBuilder('node')
        ->view($node, 'teaser');
    }
  }
}

/**
 * Implements hook_preprocess_HOOK() for comment templates.
 *
 * Provides configurable comment display based on theme settings.
 * Supports accessible heading-first layout and customizable visibility
 * of comment metadata elements.
 */
function solo_preprocess_comment(array &$variables): void {
  /** @var \Drupal\comment\CommentInterface $comment */
  $comment = $variables['elements']['#comment'];
  $comment_id = $comment->id();

  // Get the date formatter service.
  /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
  $date_formatter = \Drupal::service('date.formatter');

  // Get theme settings with defaults.
  $heading_first = theme_get_setting('comment_heading_first') ?? TRUE;
  $date_format = theme_get_setting('comment_date_format') ?? 'medium';
  $title_tag = theme_get_setting('comment_title_tag') ?? 'h3';
  $show_permalink = theme_get_setting('comment_show_permalink') ?? TRUE;
  $show_author = theme_get_setting('comment_show_author') ?? TRUE;
  $show_date = theme_get_setting('comment_show_date') ?? TRUE;
  $show_picture = theme_get_setting('comment_show_picture') ?? TRUE;
  $show_new_indicator = theme_get_setting('comment_show_new_indicator') ?? TRUE;

  // Pass settings to template.
  $variables['heading_first'] = $heading_first;
  $variables['show_permalink'] = $show_permalink;
  $variables['show_author'] = $show_author;
  $variables['show_date'] = $show_date;
  $variables['show_picture'] = $show_picture;
  $variables['show_new_indicator'] = $show_new_indicator;
  $variables['title_tag'] = $title_tag;

  // Format the date according to the selected format.
  $created_time = $comment->getCreatedTime();

  if ($show_date) {
    // Check if we should use relative time format ("X time ago").
    if ($date_format === 'solo_relative' || $date_format === 'relative') {
      $request_time = DrupalDateTime::createFromTimestamp(\Drupal::time()->getRequestTime());
      $variables['formatted_date'] = t('@time ago', [
        '@time' => $date_formatter->formatInterval($request_time->getTimestamp() - $created_time),
      ]);
    }
    else {
      // Use the selected date format.
      $variables['formatted_date'] = $date_formatter->format($created_time, $date_format);
    }

    // Provide ISO 8601 date for the <time> element datetime attribute.
    $variables['datetime'] = $date_formatter->format($created_time, 'custom', 'c');
  }

  // Also update 'created' variable for backwards compatibility.
  $request_time = DrupalDateTime::createFromTimestamp(\Drupal::time()->getRequestTime());
  $variables['created'] = t('@time ago', [
    '@time' => $date_formatter->formatInterval($request_time->getTimestamp() - $created_time),
  ]);

  // Generate unique title ID for aria-labelledby reference.
  $variables['title_id'] = 'comment-' . $comment_id . '-title';

  // Check if this comment has replies (child comments).
  /** @var \Drupal\comment\CommentStorageInterface $comment_storage */
  $comment_storage = \Drupal::entityTypeManager()->getStorage('comment');

  // Query for direct child comments only.
  $query = $comment_storage->getQuery()
    ->accessCheck(TRUE)
    ->condition('entity_id', $comment->getCommentedEntityId())
    ->condition('entity_type', $comment->getCommentedEntityTypeId())
    ->condition('field_name', $comment->getFieldName())
    ->condition('pid', $comment->id())
    ->condition('status', 1)
    ->count();

  $replies_count = $query->execute();

  // Set has_replies flag and count.
  $variables['has_replies'] = $replies_count > 0;
  $variables['replies_count'] = $replies_count;

  // Add cache tags for proper invalidation.
  $variables['#cache']['tags'][] = 'comment_list';
}

/**
 * Implements hook_preprocess_HOOK() for block.html.twig.
 */
function solo_preprocess_block(&$variables): void {
  // Check if page title block has an actual title.
  if ($variables['plugin_id'] === 'page_title_block') {
    $request = \Drupal::request();
    $route_match = \Drupal::routeMatch();
    $route = $route_match->getRouteObject();

    if ($route) {
      $title = \Drupal::service('title_resolver')->getTitle($request, $route);
      $variables['has_page_title'] = !empty($title);
    }
    else {
      $variables['has_page_title'] = FALSE;
    }

    // Add cache context since visibility depends on the route.
    $variables['#cache']['contexts'][] = 'route';
  }

  if (empty($variables['elements']['#id'])) {
    return;
  }

  $block = _load_block($variables['elements']['#id']);
  if (!$block) {
    return;
  }

  _process_system_menu_block($variables, $block);
  _process_system_branding_block($variables);
  _set_logo_default($variables);
  _set_logo_alt($variables);
}

/**
 * Helper function.
 */
function _load_block($id): ?EntityInterface {
  return \Drupal::entityTypeManager()
    ->getStorage('block')
    ->load($id);
}

/**
 * Helper function.
 */
function _process_system_menu_block(&$variables, $block): void {
  if ($variables['base_plugin_id'] !== 'system_menu_block') {
    return;
  }

  $region = $block->getRegion();
  $variables['content']['#attributes']['region'] = $region;
}

/**
 * Helper function.
 */
function _set_logo_default(&$variables): void {
  // Get the machine name of the current default theme.
  $default_theme = \Drupal::config('system.theme')->get('default');
  // Get the machine name of the active theme.
  $active_theme_name = \Drupal::theme()->getActiveTheme()->getName();
  // Get the 'logo.use_default' configuration from the 'solo' theme.
  $logo_default = \Drupal::config('solo.settings')->get('logo.use_default');

  // If both active and default themes are 'solo' and 'logo.use_default' is 1.
  if ($default_theme === 'solo' && $active_theme_name === 'solo' && $logo_default == 1) {
    $variables['logo_default'] = 1;
  }
  else {
    // Initialize $logo_default to 0.
    $variables['logo_default'] = 0;
  }
}

/**
 * Helper function.
 */
function _set_logo_alt(&$variables): void {
  $variables['logo_alt'] = theme_get_setting('header_logo_image_alt') ?: 'Home';
}

/**
 * Helper function.
 */
function _process_system_branding_block(&$variables): void {
  // Ensure this function only processes system branding blocks.
  if ($variables['base_plugin_id'] !== 'system_branding_block') {
    return;
  }
}

/**
 * Implements hook_preprocess_menu().
 */
function solo_preprocess_menu__primary_menu(&$variables): void {

  if (theme_get_setting('primary_menu_on_hover')) {
    $variables['click_hover'] = 'navigation-responsive-hover';
    if (theme_get_setting('primary_menu_arrow_hover')) {
      $variables['remove_arrow'] = 'remove-arrow';
    }
  }
  else {
    $variables['click_hover'] = 'navigation-responsive-click';
  }

  if (theme_get_setting('primary_menu_alignment')) {
    $alignment = theme_get_setting('primary_menu_alignment');
    $variables['menu_alignment'] = 'menu-align-' . $alignment;
  }

  if (theme_get_setting('primary_menu_expand_left')) {
    $variables['expand_left'] = 'expand-left';
  }
  else {
    $variables['expand_left'] = 'expand-right';
  }

  if (theme_get_setting('primary_menu_megamenu')) {
    $variables['megamenu'] = 'navigation__megamenu';
    $variables['sub_mega'] = 'sub-mega';
    $variables['megamenu_layout'] = (theme_get_setting('primary_menu_megamenu_layout'));
    if (theme_get_setting('primary_menu_submenu_header')) {
      $variables['sub_menu_header'] = 'sub__menu-header';
    }
  }

  if (theme_get_setting('primary_menu_branding')) {
    $variables['primary_menu_branding'] = 'activate-branding';
    $variables['site_logo'] = theme_get_setting('logo.url');
    $variables['logo_default'] = _set_logo_default($variables);
    $variables['site_name'] = \Drupal::config('system.site')->get('name');
    $variables['logo_alt'] = theme_get_setting('header_logo_image_alt') ?: 'Home';
  }

}

/**
 * Implements hook_preprocess_menu().
 */
function solo_preprocess_menu__primary_sidebar_menu(&$variables): void {

  if (theme_get_setting('primary_sidebar_menu_hover')) {
    $variables['click_hover_sidebar'] = 'navigation-sidebar-hover';
  }
  else {
    $variables['click_hover_sidebar'] = 'navigation-sidebar-click';
  }

}

/**
 * Implements hook_preprocess_menu().
 */
function solo_preprocess_menu__account(&$variables): void {
  if (!$variables['user']->isAuthenticated()) {
    $enableLoginBlock = theme_get_setting('header_popup_login');

    if ($enableLoginBlock) {
      $loginText = theme_get_setting('header_login_links');
      $currentPath = \Drupal::service('path.current')->getPath();
      $variables['login_popup_block'] = $enableLoginBlock;
      $variables['login_text'] = $loginText;
      $variables['current_path'] = $currentPath;

      foreach ($variables['items'] as $key => $item) {
        $url = $item['url'];
        if ($url instanceof Url && $url->isRouted()) {
          if ($url->getRouteName() === 'user.login') {
            unset($variables['items'][$key]);
          }
        }
      }
    }
  }
}

/**
 * Implements hook_preprocess_menu().
 */
function solo_preprocess_menu(&$variables) {
  $keyboard_menus = theme_get_setting('keyboard_navigation_menus');
  if (!$keyboard_menus) {
    return;
  }

  $enabled_menus = array_filter($keyboard_menus);
  if (empty($enabled_menus)) {
    return;
  }

  $menu_name = $variables['menu_name'] ?? NULL;
  if (!$menu_name) {
    return;
  }

  if (isset($enabled_menus[$menu_name])) {
    $variables['keyboard_enabled'] = TRUE;
  }
}

/**
 * Implements hook_preprocess_HOOK() for form-element.html.twig.
 */
function solo_preprocess_form_element(array &$variables): void {
  $element = $variables['element'];

  // Ensure attributes class array is defensively initialized.
  _solo_ensure_class_array($variables);

  // Field state detection.
  if (!empty($element['#value'])) {
    $variables['attributes']['class'][] = 'has-value';
  }

  if (!empty($element['#required'])) {
    $variables['attributes']['class'][] = 'is-required';
  }

  if (!empty($element['#errors'])) {
    $variables['attributes']['class'][] = 'has-error';
  }

  // Add field machine name as class.
  if (isset($element['#field_name'])) {
    $variables['attributes']['class'][] = 'field--' . Html::getClass($element['#field_name']);
  }

  // Boolean field types.
  $element_type = $element['#type'] ?? NULL;
  if ($element_type !== NULL && in_array($element_type, ['checkbox', 'radio'], TRUE)) {
    $variables['attributes']['class'][] = 'form-type-boolean';
  }

  // Add field type classes for better targeting.
  if (isset($element['#type'])) {
    $type_to_class = [
      'date' => 'solo-field-date',
      'datetime' => 'solo-field-datetime',
      'datelist' => 'solo-field-datelist',
      'daterange' => 'solo-field-daterange',
      'datetime_timestamp' => 'solo-field-timestamp',
      'textfield' => 'solo-field-text',
      'select' => 'solo-field-select',
      'checkbox' => 'solo-field-checkbox',
      'checkboxes' => 'solo-field-checkboxes',
      'radio' => 'solo-field-radio',
      'radios' => 'solo-field-radios',
      'textarea' => 'solo-field-textarea',
      'file' => 'solo-field-file',
      'managed_file' => 'solo-field-managed-file',
      'entity_autocomplete' => 'solo-field-autocomplete',
      'number' => 'solo-field-number',
      'email' => 'solo-field-email',
      'tel' => 'solo-field-tel',
      'url' => 'solo-field-url',
    ];

    if (isset($type_to_class[$element['#type']])) {
      $variables['attributes']['class'][] = $type_to_class[$element['#type']];
    }
  }

  // Check if element name suggests it's date-related.
  $element_name = $element['#name'] ?? '';
  if ($element_name !== '') {
    $date_indicators = ['date', 'created', 'changed', 'published', 'authored', 'start', 'end', 'from', 'to'];
    foreach ($date_indicators as $indicator) {
      if (stripos($element_name, $indicator) !== FALSE) {
        $variables['attributes']['class'][] = 'solo-date-related';
        break;
      }
    }
  }

  // Description handling.
  if (!empty($variables['description']['attributes'])) {
    $variables['description']['attributes']->addClass('form-item__description');
  }

  // Disabled state handling.
  if (!empty($variables['disabled'])) {
    // Ensure label attributes class is an array.
    if (!isset($variables['label']['#attributes']['class'])) {
      $variables['label']['#attributes']['class'] = [];
    }
    elseif (!is_array($variables['label']['#attributes']['class'])) {
      $variables['label']['#attributes']['class'] = [$variables['label']['#attributes']['class']];
    }
    $variables['label']['#attributes']['class'][] = 'is-disabled';
  }
}

/**
 * Implements hook_preprocess_HOOK() for form-element-label.html.twig.
 */
function solo_preprocess_form_element_label(array &$variables): void {
  _solo_ensure_class_array($variables);
  $variables['attributes']['class'][] = 'form-item__label';
}

/**
 * Implements hook_preprocess_HOOK() for input.html.twig.
 */
function solo_preprocess_input(array &$variables): void {
  $element = $variables['element'] ?? [];

  // Ensure attributes class array is defensively initialized.
  _solo_ensure_class_array($variables);

  // Handle title display attribute.
  if (
    !empty($element['#title_display']) &&
    $element['#title_display'] === 'attribute' &&
    !empty((string) $element['#title'])
  ) {
    $variables['attributes']['title'] = (string) $element['#title'];
  }

  // Safely read input attributes from the element itself first.
  $input_attributes = $element['#attributes'] ?? [];
  $type_api = $element['#type'] ?? '';
  $type_html = (string) ($input_attributes['type'] ?? ($variables['attributes']['type'] ?? ''));
  $size_attr = $input_attributes['size'] ?? ($variables['attributes']['size'] ?? NULL);
  $accept_attr = $input_attributes['accept'] ?? ($variables['attributes']['accept'] ?? NULL);

  // Define text input types for classification.
  $text_types_html = [
    'text',
    'email',
    'tel',
    'number',
    'search',
    'password',
    'date',
    'time',
    'file',
    'color',
    'datetime-local',
    'url',
    'month',
    'week',
  ];

  // Add classes for text-like inputs.
  if ($type_html !== '' && in_array($type_html, $text_types_html, TRUE)) {
    $variables['attributes']['class'][] = 'form-element';
    $variables['attributes']['class'][] = Html::getClass('form-element--type-' . $type_html);
    $variables['attributes']['class'][] = Html::getClass('form-element--api-' . $type_api);

    // Handle autocomplete message.
    if (!empty($element['#autocomplete_route_name'])) {
      $variables['autocomplete_message'] = t('Loading…');
    }
  }

  // Add classes for boolean inputs.
  if (in_array($type_html, ['checkbox', 'radio'], TRUE)) {
    $variables['attributes']['class'][] = 'form-boolean';
    $variables['attributes']['class'][] = Html::getClass('form-boolean--type-' . $type_html);
  }

  // Add size-based classes using ternary operator.
  if ($size_attr !== NULL) {
    $size = (int) $size_attr;
    $variables['attributes']['class'][] = match (TRUE) {
      $size <= 10 => 'input-small',
      $size <= 30 => 'input-medium',
      default => 'input-large',
    };
  }

  // Detect file inputs with specific extensions.
  if ($type_html === 'file' && $accept_attr !== NULL) {
    $accept = (string) $accept_attr;
    if (str_contains($accept, 'image')) {
      $variables['attributes']['class'][] = 'input-image-upload';
    }
    // Additional file type detection.
    if (str_contains($accept, 'pdf')) {
      $variables['attributes']['class'][] = 'input-pdf-upload';
    }
    if (str_contains($accept, 'video')) {
      $variables['attributes']['class'][] = 'input-video-upload';
    }
  }
}

/**
 * Implements hook_preprocess_HOOK() for menu-local-task.html.twig.
 */
function solo_preprocess_menu_local_task(array &$variables): void {
  // Ensure link options attributes class is an array.
  if (!isset($variables['link']['#options']['attributes']['class'])) {
    $variables['link']['#options']['attributes']['class'] = [];
  }
  elseif (!is_array($variables['link']['#options']['attributes']['class'])) {
    $variables['link']['#options']['attributes']['class'] = [$variables['link']['#options']['attributes']['class']];
  }

  $variables['link']['#options']['attributes']['class'][] = 'tabs__link';
  $variables['link']['#options']['attributes']['class'][] = 'js-tabs-link';

  // Ensure is-active class is set when the tab is active.
  if (isset($variables['is_active']) && $variables['is_active'] === TRUE) {
    $variables['link']['#options']['attributes']['class'][] = 'is-active';
  }

  if (isset($variables['element']['#level'])) {
    $variables['level'] = $variables['element']['#level'];
  }

  $url = $variables['element']['#link']['url'] ?? NULL;
  if ($url instanceof Url && $url->isRouted()) {
    $route_name = $url->getRouteName();
    $route_classes = [
      'entity.node.canonical' => 'view-link',
      'entity.node.edit_form' => 'edit-link',
      'entity.node.delete_form' => 'delete-link',
      'entity.node.version_history' => 'revisions-link',
    ];

    if (isset($route_classes[$route_name])) {
      $variables['link']['#options']['attributes']['class'][] = $route_classes[$route_name];
    }
  }
}

/**
 * Implements hook_preprocess_HOOK() for field--comment.html.twig.
 */
function solo_preprocess_field__comment(array &$variables): void {
  // Add a comment_count.
  $variables['comment_count'] = count(array_filter($variables['comments'], 'is_numeric', ARRAY_FILTER_USE_KEY));

  // Add user.compact to field-comment if profile's avatar
  // of current user exists.
  $current_user = \Drupal::currentUser();

  if ($current_user->isAuthenticated()) {
    // Load the full user entity.
    /** @var \Drupal\user\UserInterface|null $user */
    $user = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->load($current_user->id());

    if ($user instanceof UserInterface &&
        $user->hasField('user_picture') &&
        !$user->get('user_picture')->isEmpty()) {
      $variables['user_picture'] = \Drupal::entityTypeManager()
        ->getViewBuilder('user')
        ->view($user, 'compact');
    }

    $variables['#cache']['contexts'][] = 'user';
  }
}

/**
 * Sets the site name for maintenance pages.
 *
 * @param array $variables
 *   The variables array.
 */
function _set_site_name(array &$variables): void {
  // By default, site_name is set to Drupal if no db connection is available
  // or during site installation. Setting site_name to an empty string makes
  // the site and update pages look cleaner.
  // @see template_preprocess_maintenance_page
  if (!($variables['db_is_active'] ?? FALSE)) {
    $variables['site_name'] = '';
  }
}

/**
 * Adds maintenance page library.
 *
 * @param array $variables
 *   The variables array.
 */
function _maintenance_library(array &$variables): void {
  // Solo has custom styling for the maintenance page.
  $variables['#attached']['library'][] = 'solo/templates/layout/maintenance_page';
}

/**
 * Implements hook_preprocess_HOOK() for maintenance-page.html.twig.
 */
function solo_preprocess_maintenance_page(array &$variables): void {
  _set_site_name($variables);
  _maintenance_library($variables);
}

/**
 * Implements template_preprocess_HOOK() for views-view-table.html.twig.
 */
function solo_preprocess_views_view_table(array &$variables): void {
  // Apply sortable-heading logic if needed.
  if (!empty($variables['header'])) {
    foreach ($variables['header'] as &$header_cell) {
      if (!empty($header_cell['url'])) {
        $parsed_url = UrlHelper::parse($header_cell['url']);
        $query = $parsed_url['query'] ?? [];

        if (isset($query['order'], $query['sort'])) {
          $header_cell['attributes']->addClass('sortable-heading');
        }
      }
    }
  }
}

/**
 * Implements hook_preprocess_HOOK() for fieldset.html.twig.
 */
function solo_preprocess_fieldset(array &$variables): void {
  // Ensure attributes class array is defensively initialized.
  _solo_ensure_class_array($variables);

  // DEFENSIVE CHECK: Ensure element exists and is an array before processing.
  if (!isset($variables['element']) || !is_array($variables['element'])) {
    return;
  }
  // Count different field types inside.
  $field_types = [];
  foreach (Element::children($variables['element']) as $child) {
    if (isset($variables['element'][$child]['#type'])) {
      $field_types[$variables['element'][$child]['#type']] = TRUE;
    }
  }

  // Add class if all fields are same type.
  if (count($field_types) === 1) {
    $type = array_key_first($field_types);
    if ($type !== NULL) {
      $variables['attributes']['class'][] = 'fieldset-uniform-' . Html::getClass($type);
    }
  }

  // Detect inline-friendly fieldsets (2-3 small fields).
  $child_count = count(Element::children($variables['element']));
  if ($child_count >= 2 && $child_count <= 3) {
    $variables['attributes']['class'][] = 'fieldset-inline-ready';
  }
}

/**
 * Implements hook_preprocess_HOOK() for form.html.twig.
 */
function solo_preprocess_form(array &$variables): void {
  // DEFENSIVE CHECK: Ensure element exists and is an array before processing.
  if (!isset($variables['element']) || !is_array($variables['element'])) {
    return;
  }
  // Add data attributes for AJAX behaviors.
  _solo_mark_entity_reference_fields($variables['element']);
}

/**
 * Marks entity reference fields for AJAX behaviors.
 *
 * @param array $elements
 *   The form elements array.
 */
function _solo_mark_entity_reference_fields(array &$elements): void {
  foreach (Element::children($elements) as $key) {
    if (isset($elements[$key]['#type'])) {
      // Detect entity reference fields.
      if ($elements[$key]['#type'] === 'entity_autocomplete') {
        $elements[$key]['#attributes']['data-entity-type'] = $elements[$key]['#target_type'] ?? '';
        $target_bundles = $elements[$key]['#selection_settings']['target_bundles'] ?? [];
        $elements[$key]['#attributes']['data-bundle'] = is_array($target_bundles) ? implode(',', $target_bundles) : '';

        // Ensure wrapper_attributes class is an array.
        if (!isset($elements[$key]['#wrapper_attributes']['class'])) {
          $elements[$key]['#wrapper_attributes']['class'] = [];
        }
        elseif (!is_array($elements[$key]['#wrapper_attributes']['class'])) {
          $elements[$key]['#wrapper_attributes']['class'] = [$elements[$key]['#wrapper_attributes']['class']];
        }
        $elements[$key]['#wrapper_attributes']['class'][] = 'entity-ref-wrapper';
      }

      // Detect select lists that are entity references.
      if ($elements[$key]['#type'] === 'select' && isset($elements[$key]['#entity_type'])) {
        // Ensure wrapper_attributes class is an array.
        if (!isset($elements[$key]['#wrapper_attributes']['class'])) {
          $elements[$key]['#wrapper_attributes']['class'] = [];
        }
        elseif (!is_array($elements[$key]['#wrapper_attributes']['class'])) {
          $elements[$key]['#wrapper_attributes']['class'] = [$elements[$key]['#wrapper_attributes']['class']];
        }
        $elements[$key]['#wrapper_attributes']['class'][] = 'entity-select-wrapper';
      }
    }
    // Recurse into children.
    if (is_array($elements[$key])) {
      _solo_mark_entity_reference_fields($elements[$key]);
    }
  }
}

/**
 * Implements hook_preprocess_HOOK() for container.html.twig.
 */
function solo_preprocess_container(array &$variables): void {
  // Ensure class attribute is an array (may be string if already processed).
  _solo_ensure_class_array($variables);

  // Detect AJAX wrappers.
  $container_id = $variables['attributes']['id'] ?? '';
  if ($container_id !== '' && str_contains($container_id, 'ajax-wrapper')) {
    $variables['attributes']['class'][] = 'ajax-container';
  }

  // DEFENSIVE CHECK: Ensure element exists and is an array before processing.
  if (!isset($variables['element']) || !is_array($variables['element'])) {
    return;
  }

  // Count and classify children.
  $child_count = count(Element::children($variables['element']));
  $variables['attributes']['class'][] = 'container-children-' . $child_count;

  // Detect form actions container.
  $classes = $variables['attributes']['class'] ?? [];
  if (is_array($classes) && in_array('form-actions', $classes, TRUE)) {
    $button_count = 0;
    foreach (Element::children($variables['element']) as $child) {
      $child_type = $variables['element'][$child]['#type'] ?? '';
      if (in_array($child_type, ['submit', 'button'], TRUE)) {
        $button_count++;
      }
    }
    $variables['attributes']['class'][] = 'actions-buttons-' . $button_count;
  }

  // Detect field types within container.
  $field_types_found = [];
  _solo_detect_container_field_types($variables['element'], $field_types_found);

  // Add classes based on field types found.
  foreach ($field_types_found as $type => $count) {
    $variables['attributes']['class'][] = 'contains-' . Html::getClass($type);
    if ($count > 1) {
      $variables['attributes']['class'][] = 'contains-multiple-' . Html::getClass($type);
    }
  }

  // Special detection for date ranges.
  if (isset($field_types_found['date']) && $field_types_found['date'] === 2) {
    $variables['attributes']['class'][] = 'solo-date-range';
  }

  // Detect if container has min/max pattern.
  if (_solo_container_has_min_max($variables['element'])) {
    $variables['attributes']['class'][] = 'solo-has-date-range';
  }
}

/**
 * Check if container has min/max fields.
 *
 * @param array $element
 *   The element to check.
 *
 * @return bool
 *   TRUE if has min/max fields, FALSE otherwise.
 */
function _solo_container_has_min_max(array $element): bool {
  $has_min = FALSE;
  $has_max = FALSE;

  foreach (Element::children($element) as $key) {
    // Direct check.
    if ($key === 'min') {
      $has_min = TRUE;
    }
    if ($key === 'max') {
      $has_max = TRUE;
    }

    // Check nested elements.
    if (isset($element[$key]) && is_array($element[$key])) {
      foreach (Element::children($element[$key]) as $child_key) {
        if ($child_key === 'min') {
          $has_min = TRUE;
        }
        elseif (isset($element[$key][$child_key]['#name'])) {
          $child_name = (string) $element[$key][$child_key]['#name'];
          if (str_contains($child_name, '[min]')) {
            $has_min = TRUE;
          }
          if (str_contains($child_name, '[max]')) {
            $has_max = TRUE;
          }
        }

        if ($child_key === 'max') {
          $has_max = TRUE;
        }
      }
    }
  }

  return $has_min && $has_max;
}

/**
 * Implements hook_preprocess_HOOK() for field-multiple-value-form.html.twig.
 */
function solo_preprocess_field_multiple_value_form(array &$variables): void {
  // Ensure attributes class array is defensively initialized.
  _solo_ensure_class_array($variables);

  // DEFENSIVE CHECK: Ensure element exists and is an array before processing.
  if (!isset($variables['element']) || !is_array($variables['element'])) {
    // Provide safe fallback even when element is missing.
    $variables['attributes']['class'][] = 'field-multiple-count-0';
    return;
  }
  // Add classes for different table layouts.
  // Ensure #value exists and is countable before using count().
  $item_count = 0;
  if (isset($variables['element']['#value'])) {
    $value = $variables['element']['#value'];
    if (is_array($value) || $value instanceof \Countable) {
      $item_count = count($value);
    }
  }

  $variables['attributes']['class'][] = 'field-multiple-count-' . $item_count;

  // Detect if draggable.
  if (!empty($variables['element']['#tabledrag'])) {
    $variables['attributes']['class'][] = 'has-tabledrag';
  }
}
