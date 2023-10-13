<?php

/**
 * @file
 */

use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function solo_theme_suggestions_form_alter(array &$suggestions, array $variables): void {
  // Check if form_id exists in variables.
  if (isset($variables['element']['#form_id'])) {
    // Get the form ID.
    $form_id = $variables['element']['#form_id'];

    // Suggest the name for the search block.
    if ($form_id === 'search_block_form') {
      $suggestions[] = 'form__search_block_form';
    }
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter() for form templates.
 */
function solo_theme_suggestions_block_alter(array &$suggestions, array $variables): void {
  if (isset($variables['elements']['content']['#block_content'])) {
    array_splice($suggestions, 1, 0, 'block__bundle__' . $variables['elements']['content']['#block_content']->bundle());
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter() for menu.
 */
function solo_theme_suggestions_menu_alter(&$suggestions, array $variables): void {
  if (isset($variables['attributes']['region'])) {
    $suggestions[] = 'menu__' . $variables['attributes']['region'];
  }
}

/**
 * Helper function.
 */
function _add_node_suggestions(RouteMatchInterface $route_match, array &$suggestions): void {
  if ($route_match->getRouteName() === 'entity.node.canonical') {
    $node_bundle = $route_match->getParameter('node')->bundle();
    array_splice($suggestions, 1, 0, 'page__' . $node_bundle);
  }
}

/**
 * Helper function.
 */
function _add_taxonomy_suggestions(RouteMatchInterface $route_match, array &$suggestions): void {
  if ($route_match->getRouteName() === 'entity.taxonomy_term.canonical') {
    $taxonomy_bundle = $route_match->getParameter('taxonomy_term')->bundle();
    array_splice($suggestions, 1, 0, 'page__taxonomy__' . $taxonomy_bundle);
  }
}

/**
 * Helper function.
 */
function _add_view_suggestions(RouteMatchInterface $route_match, array &$suggestions): void {
  if ($view_name = $route_match->getRouteObject()->getDefault('view_id')) {
    $suggestions[] = 'page__' . $view_name;
  }
}

/**
 * Implements hook_theme_suggestions_page_alter().
 */
function solo_theme_suggestions_page_alter(array &$suggestions, array $variables): void {
  $route_match = \Drupal::service('current_route_match');
  _add_node_suggestions($route_match, $suggestions);
  _add_taxonomy_suggestions($route_match, $suggestions);
  _add_view_suggestions($route_match, $suggestions);
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function solo_theme_suggestions_user_alter(&$suggestions, $variables): ?array {

  $viewModeKey = '#view_mode';
  $elements = $variables['elements'] ?? [];

  if (!isset($elements[$viewModeKey])) {
    return NULL;
  }

  $viewMode = str_replace('.', '_', $elements[$viewModeKey]);
  $userId = \Drupal::service('current_user')->id();

  $suggestions = [
    "user__$viewMode",
    "user__$userId",
    "user__{$userId}__$viewMode",
  ];

  return $suggestions;

}
