<?php

/**
 * @file
 * Theme Sugestions.
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
    array_splice($suggestions, 1, 0, 'block__type__' . $variables['elements']['content']['#block_content']->bundle());
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter() for menu.
 */
function solo_theme_suggestions_menu_alter(array &$suggestions, array $variables): void {
  // Construct the suggestion based on the region, if it exists.
  if (isset($variables['attributes']['region'])) {
    $region_suggestion = 'menu__' . $variables['attributes']['region'];

    // Only add the suggestion if it's not already in the array.
    if (!in_array($region_suggestion, $suggestions)) {
      array_unshift($suggestions, $region_suggestion);
    }
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
function solo_theme_suggestions_user_alter(array &$suggestions, array $variables) {
  $viewModeKey = '#view_mode';
  $elements = $variables['elements'] ?? [];

  if (isset($elements[$viewModeKey])) {
    $viewMode = str_replace('.', '_', $elements[$viewModeKey]);
    $userId = \Drupal::currentUser()->id();

    $suggestions[] = "user__$viewMode";
    $suggestions[] = "user__$userId";
    $suggestions[] = "user__{$userId}__$viewMode";
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter() for select elements.
 */
function solo_theme_suggestions_select_alter(array &$suggestions, array $variables) {
  // Check if custom select is enabled in theme settings.
  $use_custom_selects = theme_get_setting('enable_custom_select');

  if ($use_custom_selects) {
    // Determine if it's a single or multiple select.
    if (empty($variables['element']['#multiple'])) {
      // Uses select--single.html.twig.
      $suggestions[] = 'select__single';
    }
    else {
      // Get the selected multi-select UI from theme settings.
      $multi_select_ui = theme_get_setting('multi_select_ui');

      // Apply the correct template based on the selected multi-select UI type.
      switch ($multi_select_ui) {
        case 'tags':
          // Uses select--multi--tags.html.twig.
          $suggestions[] = 'select__multi__tags';
          break;

        case 'searchable':
          // Uses select--multi--searchable.html.twig.
          $suggestions[] = 'select__multi__searchable';
          break;

        case 'dual_list':
          // Uses select--multi--dual-list.html.twig.
          $suggestions[] = 'select__multi__dual_list';
          break;

        default:
          // Uses select--multi--dropdown.html.twig.
          $suggestions[] = 'select__multi__dropdown';
          break;
      }
    }
  }
}
