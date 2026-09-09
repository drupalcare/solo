<?php

namespace Drupal\solo\Hook;

use Drupal\Component\Utility\DeprecationHelper;
use Drupal\Core\Extension\ThemeSettingsProvider;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\views\Render\ViewsRenderPipelineMarkup;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Controller\TitleResolver;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\user\UserInterface;
use Drupal\webform\Entity\Webform;
use Drupal\Core\Render\Element;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
/**
 * Hook implementations for solo.
 */
class SoloThemePreprocessHooks
{
    use StringTranslationTrait;
    /**
     * Implements hook_preprocess_HOOK() for HTML document templates.
     */
    #[Hook('preprocess_html')]
    public static function preprocessHtml(&$variables): void
    {
        $route_match = \Drupal::routeMatch();
        $route_name = $route_match->getRouteName();
        $solo_utilities = \Drupal::request()->getBasePath();
        $solo_list = \Drupal::service('extension.list.theme')->getPath('solo');
        $solo_path = $solo_utilities . '/' . $solo_list;
        $variables['solo_path'] = $solo_path;
        $active_theme = \Drupal::theme()->getActiveTheme()->getName();
        _solo_preprocess_html_preloader($variables, $active_theme);
        _solo_preprocess_html_back_to_top($variables, $active_theme);
        // Ensure class attribute is always an array before appending.
        _solo_ensure_class_array($variables);
        // Add 'px' class if the setting is enabled.
        if (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('enable_px_based_widths'), fn() => theme_get_setting('enable_px_based_widths'))) {
            $variables['attributes']['class'][] = 'px';
        }
        // Check if we're viewing a user profile page.
        if ($route_name === 'entity.user.canonical') {
            // Add a custom CSS class to the body tag.
            $variables['attributes']['class'][] = 'user-profile-page';
        }
        $theme_head_vars = [
            'site_css_injector',
            'site_css_dynamic',
            'site_import_google_font',
        ];
        foreach ($theme_head_vars as $theme_head_var) {
            $variables[$theme_head_var] = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting($theme_head_var), fn() => theme_get_setting($theme_head_var));
        }
        $google_font = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_import_google_font'), fn() => theme_get_setting('site_import_google_font'));
        if (!empty($google_font) && \Drupal\Component\Utility\UrlHelper::isValid($google_font, TRUE)) {
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
        $css_injector = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_css_injector'), fn() => theme_get_setting('site_css_injector'));
        if (!empty($css_injector)) {
            // Trusted site-builder CSS should be emitted raw, but compacted for a
            // smaller inline <style> block. Do not HTML-escape it: that would corrupt
            // valid CSS syntax and escaped selectors.
            $variables['#attached']['html_head'][] = [
                [
                    '#type' => 'html_tag',
                    '#tag' => 'style',
                    '#attributes' => [
                        'id' => 'solo-css-injector',
                    ],
                    '#value' => \Drupal\Core\Render\Markup::create(_solo_minify_inline_css((string) $css_injector)),
                ],
                'inline_css',
            ];
        }
        // Attach the dynamic CSS/JS assets. CSS is a normal library backed by a
        // public:// URI. JS cannot be a public:// library asset because the locale
        // module parses JS file assets for Drupal.t() strings and rejects
        // stream-wrapper URIs. So we attach the dependency library plus a deferred
        // script tag for the generated JS file itself.
        if (!empty(\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_css_dynamic'), fn() => theme_get_setting('site_css_dynamic'))) && file_exists('public://solo/css/solo-css-dynamic.css')) {
            $variables['#attached']['library'][] = 'solo/solo-css-dynamic';
        }
        if (!empty(\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_js_dynamic'), fn() => theme_get_setting('site_js_dynamic'))) && file_exists('public://solo/js/solo-js-dynamic.js')) {
            $variables['#attached']['library'][] = 'solo/solo-js-dynamic';
            $dynamic_js_url = \Drupal::service('file_url_generator')->generateString('public://solo/js/solo-js-dynamic.js');
            $asset_query_string = \Drupal::service('asset.query_string')->get();
            $separator = str_contains($dynamic_js_url, '?') ? '&' : '?';
            $variables['#attached']['html_head'][] = [
                [
                    '#type' => 'html_tag',
                    '#tag' => 'script',
                    '#attributes' => [
                        'src' => $dynamic_js_url . $separator . $asset_query_string,
                        'defer' => TRUE,
                    ],
                ],
                'solo_js_dynamic',
            ];
        }
        if (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_global_font_size'), fn() => theme_get_setting('site_global_font_size')) || \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_global_regions_gap'), fn() => theme_get_setting('site_global_regions_gap'))) {
            $extra_classes = trim(_get_html_classes());
            if ($extra_classes !== '') {
                $variables['html_attributes']->addClass(explode(' ', $extra_classes));
            }
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
        $request->attributes->set('solo_body_classes', $variables['attributes']['class'] ?? [
        ]);
        $speed_setting = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_toggle_speed'), fn() => theme_get_setting('site_toggle_speed'));
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
        $variables['#attached']['drupalSettings']['solo'] = array_merge($variables['#attached']['drupalSettings']['solo'] ?? [
        ], $speed_values);
    }
    /**
     * Implements hook_preprocess_page() for page templates.
     */
    #[Hook('preprocess_page')]
    public static function preprocessPage(&$variables)
    {
        $keyboard_menus = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('keyboard_navigation_menus'), fn() => theme_get_setting('keyboard_navigation_menus'));
        if ($keyboard_menus) {
            // Filter out unchecked items (checkboxes return 0 for unchecked)
            $enabled_menus = array_filter($keyboard_menus);
            $variables['#attached']['drupalSettings']['solo']['keyboardMenus'] = array_values($enabled_menus);
        }
        // Get current content type.
        $node = \Drupal::routeMatch()->getParameter('node');
        $content_type = $node instanceof \Drupal\node\NodeInterface ? $node->bundle() : NULL;
        // $variables['solo_path'] = &$variables['solo_path'];
        $variables_lists = _get_theme_variables();
        foreach ($variables_lists as $variables_list) {
            foreach ($variables_list as $variable_list_value) {
                $variables[$variable_list_value] = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting($variable_list_value), fn() => theme_get_setting($variable_list_value));
            }
        }
        // Copyright: render formatted text when "use formatted text" is enabled.
        $variables['footer_copyright_formatted_rendered'] = '';
        if (!empty($variables['footer_use_formatted_text'])) {
            $formatted = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('footer_copyright_formatted'), fn() => theme_get_setting('footer_copyright_formatted'));
            if (!empty($formatted['value'])) {
                $format = $formatted['format'] ?? 'basic_html';
                $current_year = \Drupal::service('date.formatter')->format(\Drupal::time()->getRequestTime(), 'custom', 'Y');
                $value = str_replace('%year%', $current_year, $formatted['value']);
                try {
                    $variables['footer_copyright_formatted_rendered'] = [
                        '#type' => 'processed_text',
                        '#text' => $value,
                        '#format' => $format,
                    ];
                } catch (\Exception $e) {
                    \Drupal::logger('solo')->error('Invalid text format %format in footer copyright.', [
                        '%format' => $format,
                    ]);
                }
            }
        }
        if (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_global_breakpoints'), fn() => theme_get_setting('site_global_breakpoints'))) {
            $g_point = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_global_breakpoints'), fn() => theme_get_setting('site_global_breakpoints'));
            $variables['#attached']['library'][] = 'solo/solo-global-breakpoints-' . $g_point;
        }
        if (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_menu_breakpoints'), fn() => theme_get_setting('site_menu_breakpoints'))) {
            $m_point = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_menu_breakpoints'), fn() => theme_get_setting('site_menu_breakpoints'));
            $variables['#attached']['library'][] = 'solo/solo-menu-breakpoints-' . $m_point;
        }
        $keyboard_menus = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('keyboard_navigation_menus'), fn() => theme_get_setting('keyboard_navigation_menus'));
        if ($keyboard_menus && array_filter($keyboard_menus)) {
            $variables['#attached']['library'][] = 'solo/solo-menu-keyboard';
        }
        if ($layout_order = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_mobile_layout_order'), fn() => theme_get_setting('site_mobile_layout_order'))) {
            $layout_class = str_replace('_', '-', $layout_order);
            $variables['main_layout_order'] = $layout_class;
        }
        if (!\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('system_messages_disable_dark'), fn() => theme_get_setting('system_messages_disable_dark'))) {
            $variables['#attached']['library'][] = 'solo/solo-system-messages-dark';
        }
        if (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_name_font_size_s'), fn() => theme_get_setting('site_name_font_size_s'))) {
            $variables['name_size_s'] = 'name-s-' . \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_name_font_size_s'), fn() => theme_get_setting('site_name_font_size_s'));
        }
        if (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_name_font_size_l'), fn() => theme_get_setting('site_name_font_size_l'))) {
            $variables['name_size_l'] = 'name-l-' . \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_name_font_size_l'), fn() => theme_get_setting('site_name_font_size_l'));
        }
        if (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_pagetitle_font_size_s'), fn() => theme_get_setting('site_pagetitle_font_size_s'))) {
            $variables['title_size_s'] = 'title-s-' . \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_pagetitle_font_size_s'), fn() => theme_get_setting('site_pagetitle_font_size_s'));
        }
        if (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_pagetitle_font_size_l'), fn() => theme_get_setting('site_pagetitle_font_size_l'))) {
            $variables['title_size_l'] = 'title-l-' . \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_pagetitle_font_size_l'), fn() => theme_get_setting('site_pagetitle_font_size_l'));
        }
        if (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_pagetitle_center'), fn() => theme_get_setting('site_pagetitle_center'))) {
            $variables['site_pagetitle_center'] = 'pt-center';
        }
        if (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('primary_sidebar_menu_branding'), fn() => theme_get_setting('primary_sidebar_menu_branding'))) {
            $variables['site_logo'] = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('logo.url'), fn() => theme_get_setting('logo.url'));
            $variables['logo_default'] = _set_logo_default($variables);
            $variables['site_name'] = \Drupal::config('system.site')->get('name');
            $variables['logo_alt'] = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('header_logo_image_alt'), fn() => theme_get_setting('header_logo_image_alt')) ?: 'Home';
        }
        $variables['enabled_skip_links'] = _get_enabled_skip_links();
        // Top regions variables.
        $regions = [
            'top' => [
                'first',
                'second',
                'third',
            ],
            'sidebar' => [
                'first',
                'content',
                'second',
            ],
            'bottom' => [
                'first',
                'second',
                'third',
                'fourth',
            ],
            'footer' => [
                'first',
                'second',
                'third',
            ],
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
        $found_main = [
            $has_sidebar_first,
            $has_content,
            $has_sidebar_second,
        ];
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
            } elseif ($count === 2) {
                $layout = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting("{$region}_2col"), fn() => theme_get_setting("{$region}_2col")) ?? 'solo-col-1-1';
                // Smart sidebar layout for main.
                if ($region === 'main') {
                    if (!$found_main[0]) {
                        $layout = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting("{$region}_2col"), fn() => theme_get_setting("{$region}_2col")) ?? 'solo-col-2-1';
                    } elseif (!$found_main[2]) {
                        $layout = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting("{$region}_2col"), fn() => theme_get_setting("{$region}_2col")) ?? 'solo-col-1-2';
                    }
                }
                // Override per content type.
                if ($content_type && \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting("enable_per_type_layout_{$region}"), fn() => theme_get_setting("enable_per_type_layout_{$region}"))) {
                    $layouts = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('solo_layouts'), fn() => theme_get_setting('solo_layouts')) ?? [
                    ];
                    $override = $layouts[$region]['2col'][$content_type] ?? NULL;
                    if (!empty($override)) {
                        $layout = $override;
                    }
                }
            } elseif ($count === 3) {
                $layout = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting("{$region}_3col"), fn() => theme_get_setting("{$region}_3col")) ?? 'solo-col-1-1-1';
                if ($region === 'main') {
                    $layout = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting("{$region}_3col"), fn() => theme_get_setting("{$region}_3col")) ?? 'solo-col-1-2-1';
                }
                if ($content_type && \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting("enable_per_type_layout_{$region}"), fn() => theme_get_setting("enable_per_type_layout_{$region}"))) {
                    $layouts = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('solo_layouts'), fn() => theme_get_setting('solo_layouts')) ?? [
                    ];
                    $override = $layouts[$region]['3col'][$content_type] ?? NULL;
                    if (!empty($override)) {
                        $layout = $override;
                    }
                }
            } elseif ($count === 4) {
                $layout = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting("{$region}_4col"), fn() => theme_get_setting("{$region}_4col")) ?? 'solo-col-1-1-1-1';
                if ($content_type && \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting("enable_per_type_layout_{$region}"), fn() => theme_get_setting("enable_per_type_layout_{$region}"))) {
                    $layouts = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('solo_layouts'), fn() => theme_get_setting('solo_layouts')) ?? [
                    ];
                    $override = $layouts[$region]['4col'][$content_type] ?? NULL;
                    if (!empty($override)) {
                        $layout = $override;
                    }
                }
            }
            $variables["{$region}_layout"] = $layout;
        }
        // Check if the curren user on login/register or reset.
        if (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_login_page_on'), fn() => theme_get_setting('site_login_page_on'))) {
            $curr_path = \Drupal::service('path.current')->getPath();
            $curr_path = ltrim($curr_path, '/');
            if ($curr_path == 'user/login' || $curr_path == 'user/register' || $curr_path == 'user/password') {
                unset($variables['page']['sidebar_first'], $variables['page']['sidebar_second']);
                $variables['#attached']['library'][] = 'solo/solo-user-login-custom';
            }
        }
        _create_region_attributes($variables);
        // Initialize with the global width setting.
        $final_widths = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_global_width'), fn() => theme_get_setting('site_global_width'));
        // Only apply per-content-type and node widths if solo_utilities is active.
        if (\Drupal::moduleHandler()->moduleExists('solo_utilities')) {
            $type_widths = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('enable_custom_widths'), fn() => theme_get_setting('enable_custom_widths'));
            $node_widths_enabled = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('enable_custom_node_width'), fn() => theme_get_setting('enable_custom_node_width'));
            if ($type_widths || $node_widths_enabled) {
                if ($node instanceof \Drupal\node\NodeInterface) {
                    $content_type = $node->bundle();
                    // Check for content type-specific width override.
                    if ($type_widths) {
                        $site_widths = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_widths'), fn() => theme_get_setting('site_widths')) ?? [
                        ];
                        $content_type_width = $site_widths[$content_type] ?? NULL;
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
        if (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('header_popup_login'), fn() => theme_get_setting('header_popup_login'))) {
            $bg_color = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('settings_popup_login_block___r_bg'), fn() => theme_get_setting('settings_popup_login_block___r_bg')) ?: '';
            $bg_rgb = '';
            if ($bg_color && preg_match('/^#[a-fA-F0-9]{6}$/', $bg_color)) {
                $rgb = _solo_hex_to_rgb($bg_color);
                $bg_rgb = implode(',', [
                    $rgb['r'],
                    $rgb['g'],
                    $rgb['b'],
                ]);
            }
            $variables['#attached']['drupalSettings']['solo']['popupLogin'] = [
                'enabled' => (bool) \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('header_popup_login'), fn() => theme_get_setting('header_popup_login')),
                'useInlineStyles' => (bool) \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('popup_login_use_inline_styles'), fn() => theme_get_setting('popup_login_use_inline_styles')),
                'animationDuration' => (int) (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('popup_login_animation_duration'), fn() => theme_get_setting('popup_login_animation_duration')) ?: 300),
                'closeOnEscape' => (bool) \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('popup_login_close_on_escape'), fn() => theme_get_setting('popup_login_close_on_escape')),
                'closeOnOutsideClick' => (bool) \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('popup_login_close_on_outside_click'), fn() => theme_get_setting('popup_login_close_on_outside_click')),
                'focusTrap' => (bool) \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('popup_login_focus_trap'), fn() => theme_get_setting('popup_login_focus_trap')),
                'announceToScreenReaders' => (bool) \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('popup_login_announce_to_screen_readers'), fn() => theme_get_setting('popup_login_announce_to_screen_readers')),
                'returnFocusOnClose' => (bool) \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('popup_login_return_focus_on_close'), fn() => theme_get_setting('popup_login_return_focus_on_close')),
                'customTriggers' => (string) (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('popup_login_custom_triggers'), fn() => theme_get_setting('popup_login_custom_triggers')) ?: ''),
                'zIndex' => (int) (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('popup_login_z_index'), fn() => theme_get_setting('popup_login_z_index')) ?: 10000),
                'overlayOpacity' => (int) (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('popup_login_overlay_opacity'), fn() => theme_get_setting('popup_login_overlay_opacity')) ?: 50),
                'backgroundColorRgb' => $bg_rgb,
            ];
        }
    }
    /**
     * Implements hook_preprocess_HOOK() for node.html.twig.
     */
    #[Hook('preprocess_node')]
    public static function preprocessNode(array &$variables)
    {
        $d_format = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_node_date_format'), fn() => theme_get_setting('site_node_date_format'));
        // Do not override if the user selected "none" (i.e., use Drupal's default).
        if (is_string($d_format) && $d_format !== 'none' && !empty($variables['date']) && !empty($variables['display_submitted']) && $variables['display_submitted'] === TRUE) {
            $variables['date'] = \Drupal::service('date.formatter')->format($variables['node']->getCreatedTime(), $d_format);
        }
        // Remove the "Add new comment" link on teasers or when comment form visible.
        //
        // Drupal 12 NO LONGER SETS $variables['teaser']. template_preprocess_node()
        // is gone entirely; node preprocessing moved to
        // NodeThemeHooks::preprocessNode(), which documents view_mode and not
        // teaser. Reading the key directly raised "Undefined array key \"teaser\""
        // on every node render. Measured on 12.0.0-alpha1, 2026-09-08.
        //
        // The bot patch predates that core change, so this line came back with
        // the conversion and is re-fixed here rather than left to regress.
        $is_teaser = ($variables['view_mode'] ?? '') === 'teaser';
        if ($is_teaser || !empty($variables['content']['comments']['comment_form'])) {
            unset($variables['content']['links']['comment']['#links']['comment-add']);
        }
        // Add reading mode class if enabled.
        $content_type = $variables['node']->getType();
        $reading_mode_enabled = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('site_reading_mode_content_type'), fn() => theme_get_setting('site_reading_mode_content_type'));
        if (is_array($reading_mode_enabled) && in_array($content_type, $reading_mode_enabled, TRUE)) {
            _solo_ensure_class_array($variables);
            $variables['attributes']['class'][] = 'reading-mode';
        }
    }
    /**
     * Implements hook_preprocess_field().
     */
    #[Hook('preprocess_field')]
    public static function preprocessField(&$variables, $hook)
    {
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
                    $variables['attributes']['class'][] = \Drupal\Component\Utility\Html::getClass($class);
                }
            }
        }
    }
    /**
     * Implements hook_preprocess_HOOK().
     */
    #[Hook('preprocess_field__node__created')]
    public static function preprocessFieldNodeCreated(&$variables): void
    {
        array_walk($variables['items'], function (&$item) {
            if (isset($item['content']['#prefix'])) {
                unset($item['content']['#prefix']);
            }
        });
    }
    /**
     * Implements hook_preprocess_HOOK() for setting classes.
     */
    #[Hook('preprocess_filter_caption')]
    public static function preprocessFilterCaption(&$variables): void
    {
        $variables['classes'] = $variables['classes'] ?? '';
        $variables['classes'] .= ' caption';
    }
    /**
     * Implements hook_preprocess_search_result().
     */
    #[Hook('preprocess_search_result')]
    public static function preprocessSearchResult(&$variables): void
    {
        // Check if teaser mode is enabled.
        if (\Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('enable_teaser_mode'), fn() => theme_get_setting('enable_teaser_mode'))) {
            // Render the teaser view mode for the node, if available.
            if (isset($variables['result']['node']) && $variables['result']['node'] instanceof \Drupal\node\NodeInterface) {
                $node = $variables['result']['node'];
                $variables['search_teaser'] = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, 'teaser');
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
    #[Hook('preprocess_comment')]
    public function preprocessComment(array &$variables): void
    {
        /** @var \Drupal\comment\CommentInterface $comment */
        $comment = $variables['elements']['#comment'];
        $comment_id = $comment->id();
        // Get the date formatter service.
        /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
        $date_formatter = \Drupal::service('date.formatter');
        // Get theme settings with defaults.
        $heading_first = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('comment_heading_first'), fn() => theme_get_setting('comment_heading_first')) ?? TRUE;
        $date_format = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('comment_date_format'), fn() => theme_get_setting('comment_date_format')) ?? 'medium';
        $title_tag = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('comment_title_tag'), fn() => theme_get_setting('comment_title_tag')) ?? 'h3';
        $show_permalink = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('comment_show_permalink'), fn() => theme_get_setting('comment_show_permalink')) ?? TRUE;
        $show_author = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('comment_show_author'), fn() => theme_get_setting('comment_show_author')) ?? TRUE;
        $show_date = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('comment_show_date'), fn() => theme_get_setting('comment_show_date')) ?? TRUE;
        $show_picture = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('comment_show_picture'), fn() => theme_get_setting('comment_show_picture')) ?? TRUE;
        $show_new_indicator = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('comment_show_new_indicator'), fn() => theme_get_setting('comment_show_new_indicator')) ?? TRUE;
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
                $request_time = \Drupal\Core\Datetime\DrupalDateTime::createFromTimestamp(\Drupal::time()->getRequestTime());
                $variables['formatted_date'] = $this->t('@time ago', [
                    '@time' => $date_formatter->formatInterval($request_time->getTimestamp() - $created_time),
                ]);
            } else {
                // Use the selected date format.
                $variables['formatted_date'] = $date_formatter->format($created_time, $date_format);
            }
            // Provide ISO 8601 date for the <time> element datetime attribute.
            $variables['datetime'] = $date_formatter->format($created_time, 'custom', 'c');
        }
        // Also update 'created' variable for backwards compatibility.
        $request_time = \Drupal\Core\Datetime\DrupalDateTime::createFromTimestamp(\Drupal::time()->getRequestTime());
        $variables['created'] = $this->t('@time ago', [
            '@time' => $date_formatter->formatInterval($request_time->getTimestamp() - $created_time),
        ]);
        // Generate unique title ID for aria-labelledby reference.
        $variables['title_id'] = 'comment-' . $comment_id . '-title';
        // Check if this comment has replies (child comments).
        /** @var \Drupal\comment\CommentStorageInterface $comment_storage */
        $comment_storage = \Drupal::entityTypeManager()->getStorage('comment');
        // Query for direct child comments only.
        $query = $comment_storage->getQuery()->accessCheck(TRUE)->condition('entity_id', $comment->getCommentedEntityId())->condition('entity_type', $comment->getCommentedEntityTypeId())->condition('field_name', $comment->getFieldName())->condition('pid', $comment->id())->condition('status', 1)->count();
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
    #[Hook('preprocess_block')]
    public static function preprocessBlock(&$variables): void
    {
        // Check if page title block has an actual title.
        if ($variables['plugin_id'] === 'page_title_block') {
            $request = \Drupal::request();
            $route_match = \Drupal::routeMatch();
            $route = $route_match->getRouteObject();
            if ($route) {
                $title = \Drupal::service('title_resolver')->getTitle($request, $route);
                $variables['has_page_title'] = !empty($title);
            } else {
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
     * Implements hook_preprocess_menu().
     */
    #[Hook('preprocess_menu')]
    public static function preprocessMenu(&$variables)
    {
        $keyboard_menus = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('keyboard_navigation_menus'), fn() => theme_get_setting('keyboard_navigation_menus'));
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
    #[Hook('preprocess_form_element')]
    public static function preprocessFormElement(array &$variables): void
    {
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
            $variables['attributes']['class'][] = 'field--' . \Drupal\Component\Utility\Html::getClass($element['#field_name']);
        }
        // Boolean field types.
        $element_type = $element['#type'] ?? NULL;
        if ($element_type !== NULL && in_array($element_type, [
            'checkbox',
            'radio',
        ], TRUE)) {
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
            $date_indicators = [
                'date',
                'created',
                'changed',
                'published',
                'authored',
                'start',
                'end',
                'from',
                'to',
            ];
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
                $variables['label']['#attributes']['class'] = [
                ];
            } elseif (!is_array($variables['label']['#attributes']['class'])) {
                $variables['label']['#attributes']['class'] = [
                    $variables['label']['#attributes']['class'],
                ];
            }
            $variables['label']['#attributes']['class'][] = 'is-disabled';
        }
    }
    /**
     * Implements hook_preprocess_HOOK() for form-element-label.html.twig.
     */
    #[Hook('preprocess_form_element_label')]
    public static function preprocessFormElementLabel(array &$variables): void
    {
        _solo_ensure_class_array($variables);
        $variables['attributes']['class'][] = 'form-item__label';
    }
    /**
     * Implements hook_preprocess_HOOK() for input.html.twig.
     */
    #[Hook('preprocess_input')]
    public function preprocessInput(array &$variables): void
    {
        $element = $variables['element'] ?? [
        ];
        // Ensure attributes class array is defensively initialized.
        _solo_ensure_class_array($variables);
        // Handle title display attribute.
        if (!empty($element['#title_display']) && $element['#title_display'] === 'attribute' && !empty((string) $element['#title'])) {
            $variables['attributes']['title'] = (string) $element['#title'];
        }
        // Safely read input attributes from the element itself first.
        $input_attributes = $element['#attributes'] ?? [
        ];
        $type_api = $element['#type'] ?? '';
        $type_html = (string) ($input_attributes['type'] ?? $variables['attributes']['type'] ?? '');
        $size_attr = $input_attributes['size'] ?? $variables['attributes']['size'] ?? NULL;
        $accept_attr = $input_attributes['accept'] ?? $variables['attributes']['accept'] ?? NULL;
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
            $variables['attributes']['class'][] = \Drupal\Component\Utility\Html::getClass('form-element--type-' . $type_html);
            $variables['attributes']['class'][] = \Drupal\Component\Utility\Html::getClass('form-element--api-' . $type_api);
            // Handle autocomplete message.
            if (!empty($element['#autocomplete_route_name'])) {
                $variables['autocomplete_message'] = $this->t('Loading…');
            }
        }
        // Add classes for boolean inputs.
        if (in_array($type_html, [
            'checkbox',
            'radio',
        ], TRUE)) {
            $variables['attributes']['class'][] = 'form-boolean';
            $variables['attributes']['class'][] = \Drupal\Component\Utility\Html::getClass('form-boolean--type-' . $type_html);
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
    #[Hook('preprocess_menu_local_task')]
    public static function preprocessMenuLocalTask(array &$variables): void
    {
        // Ensure link options attributes class is an array.
        if (!isset($variables['link']['#options']['attributes']['class'])) {
            $variables['link']['#options']['attributes']['class'] = [
            ];
        } elseif (!is_array($variables['link']['#options']['attributes']['class'])) {
            $variables['link']['#options']['attributes']['class'] = [
                $variables['link']['#options']['attributes']['class'],
            ];
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
        if ($url instanceof \Drupal\Core\Url && $url->isRouted()) {
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
    #[Hook('preprocess_field__comment')]
    public static function preprocessFieldComment(array &$variables): void
    {
        // Add a comment_count.
        $variables['comment_count'] = count(array_filter($variables['comments'], 'is_numeric', ARRAY_FILTER_USE_KEY));
        // Add user.compact to field-comment if profile's avatar
        // of current user exists.
        $current_user = \Drupal::currentUser();
        if ($current_user->isAuthenticated()) {
            // Load the full user entity.
            /** @var \Drupal\user\UserInterface|null $user */
            $user = \Drupal::entityTypeManager()->getStorage('user')->load($current_user->id());
            if ($user instanceof \Drupal\user\UserInterface && $user->hasField('user_picture') && !$user->get('user_picture')->isEmpty()) {
                $variables['user_picture'] = \Drupal::entityTypeManager()->getViewBuilder('user')->view($user, 'compact');
            }
            $variables['#cache']['contexts'][] = 'user';
        }
        // Pass the comment form position setting to the template.
        // This works for ALL comment fields (field_comments, field_reviews, etc.)
        // because it's based on field_type, not field_name.
        $variables['comment_form_position'] = \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '11.3.0', fn() => \Drupal::service(\Drupal\Core\Extension\ThemeSettingsProvider::class)->getSetting('comment_form_position'), fn() => theme_get_setting('comment_form_position')) ?? 'before';
    }
    /**
     * Implements hook_preprocess_HOOK() for maintenance-page.html.twig.
     */
    #[Hook('preprocess_maintenance_page')]
    public static function preprocessMaintenancePage(array &$variables): void
    {
        _set_site_name($variables);
        _maintenance_library($variables);
    }
    /**
     * Implements hook_preprocess_HOOK() for fieldset.html.twig.
     */
    #[Hook('preprocess_fieldset')]
    public static function preprocessFieldset(array &$variables): void
    {
        // Ensure attributes class array is defensively initialized.
        _solo_ensure_class_array($variables);
        // DEFENSIVE CHECK: Ensure element exists and is an array before processing.
        if (!isset($variables['element']) || !is_array($variables['element'])) {
            return;
        }
        // Count different field types inside.
        $field_types = [
        ];
        foreach (\Drupal\Core\Render\Element::children($variables['element']) as $child) {
            if (isset($variables['element'][$child]['#type'])) {
                $field_types[$variables['element'][$child]['#type']] = TRUE;
            }
        }
        // Add class if all fields are same type.
        if (count($field_types) === 1) {
            $type = array_key_first($field_types);
            if ($type !== NULL) {
                $variables['attributes']['class'][] = 'fieldset-uniform-' . \Drupal\Component\Utility\Html::getClass($type);
            }
        }
        // Detect inline-friendly fieldsets (2-3 small fields).
        $child_count = count(\Drupal\Core\Render\Element::children($variables['element']));
        if ($child_count >= 2 && $child_count <= 3) {
            $variables['attributes']['class'][] = 'fieldset-inline-ready';
        }
    }
    /**
     * Implements hook_preprocess_HOOK() for form.html.twig.
     */
    #[Hook('preprocess_form')]
    public static function preprocessForm(array &$variables): void
    {
        // DEFENSIVE CHECK: Ensure element exists and is an array before processing.
        if (!isset($variables['element']) || !is_array($variables['element'])) {
            return;
        }
        // Add data attributes for AJAX behaviors.
        _solo_mark_entity_reference_fields($variables['element']);
    }
    /**
     * Implements hook_preprocess_HOOK() for container.html.twig.
     */
    #[Hook('preprocess_container')]
    public static function preprocessContainer(array &$variables): void
    {
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
        $child_count = count(\Drupal\Core\Render\Element::children($variables['element']));
        $variables['attributes']['class'][] = 'container-children-' . $child_count;
        // Detect form actions container.
        $classes = $variables['attributes']['class'] ?? [
        ];
        if (is_array($classes) && in_array('form-actions', $classes, TRUE)) {
            $button_count = 0;
            foreach (\Drupal\Core\Render\Element::children($variables['element']) as $child) {
                $child_type = $variables['element'][$child]['#type'] ?? '';
                if (in_array($child_type, [
                    'submit',
                    'button',
                ], TRUE)) {
                    $button_count++;
                }
            }
            $variables['attributes']['class'][] = 'actions-buttons-' . $button_count;
        }
        // Detect field types within container.
        $field_types_found = [
        ];
        _solo_detect_container_field_types($variables['element'], $field_types_found);
        // Add classes based on field types found.
        foreach ($field_types_found as $type => $count) {
            $variables['attributes']['class'][] = 'contains-' . \Drupal\Component\Utility\Html::getClass($type);
            if ($count > 1) {
                $variables['attributes']['class'][] = 'contains-multiple-' . \Drupal\Component\Utility\Html::getClass($type);
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
     * Implements hook_preprocess_HOOK() for field-multiple-value-form.html.twig.
     */
    #[Hook('preprocess_field_multiple_value_form')]
    public static function preprocessFieldMultipleValueForm(array &$variables): void
    {
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
}
