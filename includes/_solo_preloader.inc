<?php

/**
 * @file
 * Preloader: centralized visibility decision and config.
 *
 * Rule-based, cache-aware. No logic in Twig; library attached conditionally.
 */

/**
 * Determines whether the preloader should be attached for this request.
 *
 * Call from preprocess_html only. Adds cache contexts when visibility
 * depends on user or route.
 *
 * @param string $theme
 *   Active theme machine name (for config).
 *
 * @return array
 *   - show: (bool) Whether to show the preloader and attach library.
 *   - config: (array) Settings for the preloader (style, bg_color, etc.).
 *   - cache_contexts: (array) Cache contexts that were used in the decision.
 */
function _solo_preloader_decision($theme) {
  $config = \Drupal::config($theme . '.settings');
  $force_show = (bool) $config->get('preloader_force_show');
  $show = $force_show || (bool) $config->get('preloader_enabled');
  $cache_contexts = [];

  if (!$show) {
    return ['show' => FALSE, 'config' => [], 'cache_contexts' => []];
  }

  // Force show for testing: ignore visibility rules, show 30s on every page.
  if ($force_show) {
    $cache_contexts[] = 'user.roles';
    $cache_contexts[] = 'route';
    $r_bg = trim((string) ($config->get('settings_preloader___r_bg') ?? ''));
    $r_tx = trim((string) ($config->get('settings_preloader___r_tx') ?? ''));
    return [
      'show' => TRUE,
      'config' => [
        'style' => $config->get('preloader_style') ?? 'spinner',
        'r_bg' => $r_bg !== '' ? $r_bg : '#ffffff',
        'r_tx' => $r_tx !== '' ? $r_tx : '#000000',
        'logo_url' => trim((string) ($config->get('preloader_logo_url') ?? '')),
        'text' => trim((string) ($config->get('preloader_text') ?? '')),
        'text_font' => trim((string) ($config->get('preloader_text_font') ?? '')),
        'text_font_size' => (int) ($config->get('preloader_text_font_size') ?: 24),
        'text_animate' => (bool) $config->get('preloader_text_animate'),
        'duration' => 30000,
        'force_show' => TRUE,
      ],
      'cache_contexts' => array_unique($cache_contexts),
    ];
  }

  $user = \Drupal::currentUser();
  $route_match = \Drupal::routeMatch();
  $route_name = $route_match ? $route_match->getRouteName() : '';
  $path = $route_match && $route_match->getRouteObject() ? $route_match->getRawParameters()->get('path', '') : '';

  // Disable for authenticated users.
  if ($config->get('preloader_disable_authenticated') !== FALSE) {
    $cache_contexts[] = 'user.roles';
    if (!$user->isAnonymous()) {
      return ['show' => FALSE, 'config' => [], 'cache_contexts' => $cache_contexts];
    }
  }

  // Disable on admin routes.
  if ($config->get('preloader_disable_admin_routes') !== FALSE) {
    $cache_contexts[] = 'route';
    if (_solo_preloader_is_admin_route($route_name, $path)) {
      return ['show' => FALSE, 'config' => [], 'cache_contexts' => $cache_contexts];
    }
  }

  // Path include/exclude rules.
  $path_rules = trim((string) $config->get('preloader_path_rules') ?? '');
  if ($path_rules !== '') {
    $cache_contexts[] = 'route';
    $current_path = \Drupal::request()->getPathInfo();
    $path_matches = _solo_preloader_path_rules_match($path_rules, $current_path);
    if ($path_matches === FALSE) {
      return ['show' => FALSE, 'config' => [], 'cache_contexts' => $cache_contexts];
    }
  }

  $duration = (int) $config->get('preloader_duration');
  $duration = max(300, min(1500, $duration));

  $r_bg = trim((string) ($config->get('settings_preloader___r_bg') ?? ''));
  $r_tx = trim((string) ($config->get('settings_preloader___r_tx') ?? ''));
  return [
    'show' => TRUE,
    'config' => [
      'style' => $config->get('preloader_style') ?? 'spinner',
      'r_bg' => $r_bg !== '' ? $r_bg : '#ffffff',
      'r_tx' => $r_tx !== '' ? $r_tx : '#000000',
      'logo_url' => trim((string) ($config->get('preloader_logo_url') ?? '')),
      'text' => trim((string) ($config->get('preloader_text') ?? '')),
      'text_font' => trim((string) ($config->get('preloader_text_font') ?? '')),
      'text_font_size' => (int) ($config->get('preloader_text_font_size') ?: 24),
      'text_animate' => (bool) $config->get('preloader_text_animate'),
      'duration' => $duration,
      'force_show' => FALSE,
    ],
    'cache_contexts' => array_unique($cache_contexts),
  ];
}

/**
 * Checks if the current route is an admin/editor route.
 *
 * @param string $route_name
 *   Route name.
 * @param string $path
 *   Path (may be empty).
 *
 * @return bool
 *   TRUE if the route or path is considered admin/editor, FALSE otherwise.
 */
function _solo_preloader_is_admin_route($route_name, $path) {
  if ($route_name === NULL || $route_name === '') {
    $path = $path ?: \Drupal::request()->getPathInfo();
    return (bool) preg_match('#^/admin#', $path);
  }
  $admin_routes = [
    'system.admin',
    'system.admin_content',
    'system.admin_structure',
    'system.theme_settings',
    'entity.node.edit_form',
    'entity.node.delete_form',
    'entity.block_content.edit_form',
    'layout_builder.overrides.node.view',
    'layout_builder.defaults.node.view',
  ];
  if (in_array($route_name, $admin_routes, TRUE)) {
    return TRUE;
  }
  if (strpos($route_name, 'entity.') === 0 && (strpos($route_name, '.edit_form') !== FALSE || strpos($route_name, '.delete_form') !== FALSE)) {
    return TRUE;
  }
  if (strpos($route_name, 'layout_builder.') === 0) {
    return TRUE;
  }
  $path = $path ?: \Drupal::request()->getPathInfo();
  return (bool) preg_match('#^/admin#', $path);
}

/**
 * Applies path include/exclude rules.
 *
 * Lines starting with - mean exclude; otherwise include only those paths.
 * Empty lines ignored. Paths are matched against the current path (path alias
 * or internal path).
 *
 * @param string $rules
 *   One path per line.
 * @param string $current_path
 *   Current path (e.g. request path).
 *
 * @return bool|null
 *   TRUE if preloader should show, FALSE if not, NULL if rules don't apply.
 */
function _solo_preloader_path_rules_match($rules, $current_path) {
  $lines = array_filter(array_map('trim', explode("\n", $rules)));
  if (empty($lines)) {
    return NULL;
  }
  $normalize = function ($p) {
    $p = trim($p, '/');
    return $p === '' ? '' : '/' . $p;
  };
  $current_path = $normalize($current_path);
  $include_only = [];
  $exclude = [];
  foreach ($lines as $line) {
    // Lines starting with "-/" are exclusion rules (e.g. "-/contact").
    if (strpos($line, '-/') === 0 || $line === '-') {
      $exclude[] = $normalize(trim(substr($line, 1)));
    }
    else {
      $include_only[] = $normalize($line);
    }
  }
  foreach ($exclude as $p) {
    if ($current_path === $p || ($p !== '' && $p !== '/' && strpos($current_path, $p . '/') === 0)) {
      return FALSE;
    }
  }
  if (!empty($include_only)) {
    foreach ($include_only as $p) {
      if ($current_path === $p || ($p !== '' && $p !== '/' && strpos($current_path, $p . '/') === 0)) {
        return TRUE;
      }
    }
    return FALSE;
  }
  return NULL;
}
