<?php

/**
 * @file
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Template\Attribute;
use Drupal\user\UserInterface;

/**
 * Helper function.
 */
function _add_node_id_to_body_class($route_match, &$variables): void {
  $node_id = $route_match->getParameter('node')->id();
  if ($node_id) {
    $variables['attributes']['class'][] = 'path-node-' . $node_id;
  }
}

/**
 * Helper function.
 */
function _add_taxonomy_term_to_body_class($route_match, &$variables): void {
  $term = $route_match->getParameter('taxonomy_term');
  if ($term->id()) {
    $variables['attributes']['class'][] = 'path-taxonomy-term-' . $term->id();
  }
  if ($term->bundle()) {
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
    $variables['attributes']['class'][] = 'path-user-' . $user_url[1];
  }
}

/**
 * Helper function.
 */
function _add_view_id_to_body_class($route_match, &$variables): void {
  $view_id = $route_match->getParameter('view_id');
  if ($view_id) {
    $variables['attributes']['class'][] = 'path-view-' . Html::cleanCssIdentifier($view_id);
  }
}

/**
 * Helper function.
 */
function _add_admin_context_to_body_class(&$variables): void {
  /** @var \Drupal\Core\Routing\AdminContext $admin_context */
  $admin_context = \Drupal::service('router.admin_context');
  if ($admin_context->isAdminRoute()) {
    $variables['attributes']['class'][] = 'admin-context';
  }
}

/**
 * Implements hook_preprocess_HOOK() for HTML document templates.
 */
function solo_preprocess_html(&$variables): void {
  $route_match = \Drupal::routeMatch();
  $route_name = $route_match->getRouteName();

  $variables['solo_path'] = \Drupal::request()->getBasePath() . '/' . \Drupal::service('extension.list.theme')->getPath('solo');

  // Add node id to the body class.
  if ($route_name === 'entity.node.canonical') {
    _add_node_id_to_body_class($route_match, $variables);
  }

  // Add vocabulary name and term id to the body class.
  if ($route_name === 'entity.taxonomy_term.canonical') {
    _add_taxonomy_term_to_body_class($route_match, $variables);
  }

  // If the user is logged in use ID if not use second word after user/login.
  _add_user_path_to_body_class($variables);

  // Add view id name to the body class.
  _add_view_id_to_body_class($route_match, $variables);

  // Add admin context to the body class.
  _add_admin_context_to_body_class($variables);

  // Color's variables.
  $cssInternal = 'site_css_injector';
  $variables[$cssInternal] = theme_get_setting($cssInternal);

}

/**
 * Helper function.
 */
function _reset_regions($count, $region_name, &$variables): void {
  $current_settings = theme_get_setting($region_name);
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

  switch ($count) {
    case 4:
      $variables[$region_name] = in_array($current_settings, $four_columns) ? $variables[$region_name] : 'solo-col-4';
      break;

    case 3:
      $variables[$region_name] = in_array($current_settings, $three_columns) ? $variables[$region_name] : 'solo-col-3';
      break;

    case 2:
      $variables[$region_name] = in_array($current_settings, $two_columns) ? $variables[$region_name] : 'solo-col-2';
      break;

    case 1:
      $variables[$region_name] = 'solo-col-1';
      break;

    default:
      $variables[$region_name] = '';
  }

}

/**
 * Helper function.
 */
function _call_reset_egions($count, $region, &$variables) {
  if ($count > 0) {
    _reset_regions($count, $region, $variables);
  }
}

/**
 * Helper function.
 */
function _get_site_regions() {
  return ['page_wrapper', 'popup_login_block', 'fixed_search_block', 'header', 'primary_sidebar_menu', 'primary_menu', 'welcome_text', 'top_container', 'top_first', 'top_second', 'top_third', 'highlighted', 'system_messages', 'page_title', 'breadcrumb', 'main_container', 'sidebar_first', 'content', 'sidebar_second', 'bottom_container', 'bottom_first', 'bottom_second', 'bottom_third', 'bottom_fourth', 'footer_container', 'footer_first', 'footer_second', 'footer_third', 'footer_menu', 'credit_copyright'];
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
function _get_region_attributes() {
  return [
    '__r_bg' => 'Background',
    '__r_tx' => 'Text',
    '__r_lk' => 'Link',
    '__r_lk_h' => 'Link Hover',
    '__r_lk_bg' => 'Link BG',
    '__r_lk_bg_h' => 'Link BG Hover',
    '__r_bt_bg' => 'Button BG',
    '__r_bt_bg_h' => 'Button BG Hover',
    '__r_bt_tx' => 'Button Text',
    '__r_br' => 'Border',
  ];
};

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
 * Helper function.
 */
function _create_region_attributes(&$variables) {
  $site_regions = _get_site_regions();
  $attributes = _get_region_attributes();
  foreach ($site_regions as $region) {
    $region_attr = [];
    $variables["attributes_{$region}"] = new Attribute();

    foreach ($attributes as $key => $value) {

      if (theme_get_setting("settings_{$region}_{$key}")) {

        $css_val = theme_get_setting("settings_{$region}_{$key}");
        $css_var = str_replace('_', '-', $key);
        $css_var = "{$css_var}:{$css_val}";
        $region_attr[] = $css_var;

      }
    } // end attr
    if ($region_attr) {
      $variables["attributes_{$region}"]->setAttribute('style', implode(';', $region_attr));
    }

  }

}

/**
 *
 */
function _get_theme_variables() {

  return [
    'misc' => [
      'site_global_width',
      'site_css_injector',
      'site_font_google_special_on',
      'site_font_google_heading_on',
      'site_font_google_global_on',
      'site_font_google_local_on',
      'site_font_awesome_on',
      'site_load_w3css_on',
      'site_opacity_onscroll_on',
      'site_login_page_on',
      'site_pagetitle_animate_on',
      'site_name_animate_on',
      'site_breadcrumb_pagetitle_off',
      'site_inline_items_on',
      'site_regions_animate_top',
      'site_regions_animate_bottom',
      'site_regions_animate_footer',
      'site_regions_layout_top',
      'site_regions_layout_main',
      'site_regions_layout_bottom',
      'site_regions_layout_footer',
      'header_change_icons',
      'header_change_sitename',
      'header_popup_login',
      'count_top',
      'count_main',
      'count_bottom',
      'count_footer',
    ],
    'social' => [
      'sm_icon_size',
      'sm_show_icons',
      'sm_icon_facebook',
      'sm_icon_twitter',
      'sm_icon_pinterest',
      'sm_icon_linkedin',
      'sm_icon_instagram',
      'sm_icon_youtube',
      'sm_icon_drupal',
      'sm_icon_rss',
      'sm_icon_mastodon',
      'footer_copyright',
      'footer_credit_link',
    ],
    'css_classes' => _get_css_classes(),
    'settings_region' => _get_settings_region(),
  ];
}

/**
 * Implements hook_preprocess_page() for page templates.
 */
function solo_preprocess_page(&$variables) {

  $variables['#attached']['html_head'][] = [
    [
      '#tag' => 'style',
      '#value' => theme_get_setting('site_css_injector'),
    ],
    'solo',
  ];

  $variables_lists = _get_theme_variables();
  foreach ($variables_lists as $variables_list) {
    foreach ($variables_list as $variable_list_value) {
      $variables[$variable_list_value] = theme_get_setting($variable_list_value);
    }
  }

  // Top regions variables.
  $regions = [
    'top' => ['first', 'second', 'third'],
    'sidebar' => ['first', 'content', 'second'],
    'bottom' => ['first', 'second', 'third', 'fourth'],
    'footer' => ['first', 'second', 'third'],
  ];

  foreach ($regions as $region => $subregions) {
    // Loop through each subregion.
    foreach ($subregions as $subregion) {
      if ($subregion == 'content') {
        ${"has_{$subregion}"} = !empty($variables['page']["{$subregion}"]);
      }
      // Check if the region and subregion exist in the 'page' array.
      ${"has_{$region}_{$subregion}"} = !empty($variables['page']["{$region}_{$subregion}"]);
    }
  }
  // Add Responsive classes to Top Container.
  // $config = \Drupal::config('solo.settings');.
  $count_top = (int) $has_top_first + (int) $has_top_second + (int) $has_top_third;
  $count_main = (int) $has_sidebar_first + (int) $has_content + (int) $has_sidebar_second;
  $count_bottom = (int) $has_bottom_first + (int) $has_bottom_second + (int) $has_bottom_third + (int) $has_bottom_fourth;
  $count_footer = (int) $has_footer_first + (int) $has_footer_second + (int) $has_footer_third;

  _call_reset_egions($count_top, 'site_regions_layout_top', $variables);
  _call_reset_egions($count_main, 'site_regions_layout_main', $variables);
  _call_reset_egions($count_bottom, 'site_regions_layout_bottom', $variables);
  _call_reset_egions($count_footer, 'site_regions_layout_footer', $variables);

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

}

/**
 * Implements hook_preprocess_HOOK() for node.html.twig.
 */
function solo_preprocess_node(&$variables) {
  $is_teaser = $variables['teaser'];
  $is_comment_form_present = !empty($variables['content']['comments']['comment_form']);

  if ($is_teaser || $is_comment_form_present) {
    unset($variables['content']['links']['comment']['#links']['comment-add']);
  }

  // Apply custom date formatter to "date" field.
  if (!empty($variables['date']) && !empty($variables['display_submitted']) && $variables['display_submitted'] === TRUE) {
    $variables['date'] = \Drupal::service('date.formatter')->format($variables['node']->getCreatedTime(), 'solo_medium');
  }

}

/**
 * Implements hook_preprocess_breadcrumb().
 */
function solo_preprocess_breadcrumb(&$variables): void {
  if ($variables['breadcrumb'] && !theme_get_setting('site_breadcrumb_pagetitle_off')) {
    $is_request = \Drupal::request();
    $route_match = \Drupal::routeMatch();
    $page_title = \Drupal::service('title_resolver')
      ->getTitle($is_request, $route_match->getRouteObject());
    if (!empty($page_title)) {
      $variables['breadcrumb'][] = [
        'text' => $page_title,
      ];
      // Add cache context based on url.
      $variables['#cache']['contexts'][] = 'url';
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
  // Apply custom date formatter to "date" field.
  if (!empty($variables['result']['date'])) {
    $variables['info_date'] = \Drupal::service('date.formatter')->format($variables['result']['node']->getCreatedTime(), 'solo_medium');
  }
}

/**
 * Implements hook_preprocess_HOOK() for comment.html.twig.
 */
function solo_preprocess_comment(&$variables): void {
  // Getting the node creation time stamp from the comment object.
  $date = $variables['comment']->getCreatedTime();

  // Getting the current request time.
  $request_time = DrupalDateTime::createFromTimestamp(\Drupal::time()->getRequestTime());

  // Getting the date formatter service.
  $date_formatter = \Drupal::service('date.formatter');

  // Formatting "created" as "X days ago".
  $variables['created'] = t('@time ago', [
    '@time' => $date_formatter->formatInterval($request_time->getTimestamp() - $date),
  ]);
}

/**
 * Implements hook_preprocess_HOOK() for block.html.twig.
 */
function solo_preprocess_block(&$variables): void {
  if (!empty($variables['elements']['#id'])) {
    /** @var \Drupal\block\BlockInterface $block */
    $block = \Drupal::entityTypeManager()
      ->getStorage('block')
      ->load($variables['elements']['#id']);
    if ($block) {
      $region = $block->getRegion();

      if ($variables['base_plugin_id'] === 'system_menu_block') {
        $variables['content']['#attributes']['region'] = $region;
      }
    }
  }
}

/**
 * Implements hook_preprocess_menu().
 */
function solo_preprocess_menu__account(&$variables): void {
  if (!$variables['user']->isAuthenticated()) {
    $enableLoginBlock = theme_get_setting('header_popup_login');
    $loginText = theme_get_setting('header_login_links');
    $currentPath = \Drupal::service('path.current')->getPath();

    if ($enableLoginBlock) {
      $variables['login_popup_block'] = $enableLoginBlock;
      $variables['login_text'] = $loginText;
      $variables['current_path'] = $currentPath;
      foreach ($variables['items'] as $key => $item) {
        $url = $item['url']->toString();
        if ($url == '/user/login') {
          unset($variables['items'][$key]);
        }
      }
    }
  }
}

/**
 * Implements hook_preprocess_form_element().
 */
function solo_preprocess_form_element(&$variables): void {
  if (in_array($variables['element']['#type'] ?? FALSE, ['checkbox', 'radio'], TRUE)) {
    $variables['attributes']['class'][] = 'form-type-boolean';
  }

  if (!empty($variables['description']['attributes'])) {
    $variables['description']['attributes']->addClass('form-item__description');
  }

  if ($variables['disabled']) {
    $variables['label']['#attributes']['class'][] = 'is-disabled';
  }
}

/**
 * Implements hook_preprocess_HOOK().
 */
function solo_preprocess_form_element_label(&$variables): void {
  $variables['attributes']['class'][] = 'form-item__label';
}

/**
 * Implements hook_preprocess_HOOK().
 */
function solo_preprocess_input(&$variables): void {
  if (
    !empty($variables['element']['#title_display']) &&
    $variables['element']['#title_display'] === 'attribute' &&
    !empty((string) $variables['element']['#title'])
  ) {
    $variables['attributes']['title'] = (string) $variables['element']['#title'];
  }

  $type_api = $variables['element']['#type'];
  $type_html = $variables['attributes']['type'];
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

  if (in_array($type_html, $text_types_html, TRUE)) {
    $variables['attributes']['class'][] = 'form-element';
    $variables['attributes']['class'][] = Html::getClass('form-element--type-' . $type_html);
    $variables['attributes']['class'][] = Html::getClass('form-element--api-' . $type_api);

    // This logic is functioning as expected, but there is nothing in the theme that renders the result.
    // As a result it can't currently be covered by a functional test.
    if (!empty($variables['element']['#autocomplete_route_name'])) {
      $variables['autocomplete_message'] = t('Loading…');
    }
  }

  if (in_array($type_html, ['checkbox', 'radio'], TRUE)) {
    $variables['attributes']['class'][] = 'form-boolean';
    $variables['attributes']['class'][] = Html::getClass('form-boolean--type-' . $type_html);
  }
}

/**
 * Implements hook_preprocess_HOOK() for menu-local-task templates.
 */
function solo_preprocess_menu_local_task(&$variables): void {
  $variables['link']['#options']['attributes']['class'][] = 'tabs__link';
  $variables['link']['#options']['attributes']['class'][] = 'js-tabs-link';

  // Ensure is-active class is set when the tab is active. The generic active
  // link handler applies stricter comparison rules than what is necessary for
  // tabs.
  if (isset($variables['is_active']) && $variables['is_active'] === TRUE) {
    $variables['link']['#options']['attributes']['class'][] = 'is-active';
  }

  if (isset($variables['element']['#level'])) {
    $variables['level'] = $variables['element']['#level'];
  }
}

/**
 * Implements hook_preprocess_HOOK() for field--comment.html.twig.
 */
function solo_preprocess_field__comment(&$variables): void {
  // Add a comment_count.
  $variables['comment_count'] = count(array_filter($variables['comments'], 'is_numeric', ARRAY_FILTER_USE_KEY));

  // Add user.compact to field-comment if profile's avatar of current user
  // exist.
  $user = \Drupal::currentUser();
  if ($user->isAuthenticated() && $user instanceof UserInterface) {
    if ($user->hasField('user_picture') && !$user->get('user_picture')->isEmpty()) {
      $variables['user_picture'] = \Drupal::entityTypeManager()
        ->getViewBuilder('user')
        ->view($user, 'compact');
    }

    $variables['#cache']['contexts'][] = 'user';
  }
}

/**
 * Helper functions.
 */
function _set_site_name(&$variables): void {
  // By default, site_name is set to Drupal if no db connection is available
  // or during site installation. Setting site_name to an empty string makes
  // the site and update pages look cleaner.
  // @see template_preprocess_maintenance_page
  if (!$variables['db_is_active']) {
    $variables['site_name'] = '';
  }
}

/**
 * Helper functions.
 */
function _maintenance_library(&$variables): void {
  // Solo has custom styling for the maintenance page.
  $variables['#attached']['library'][] = 'solo/templates/layout/maintenance_page';
}

/**
 * Implements hook_preprocess_HOOK() for maintenance-page.html.twig.
 */
function solo_preprocess_maintenance_page(&$variables): void {
  _set_site_name($variables);
  _maintenance_library($variables);
}
