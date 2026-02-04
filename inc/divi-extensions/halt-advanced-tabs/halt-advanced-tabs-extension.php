<?php
/**
 * Halt Advanced Tabs Divi 5 module extension.
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

define( 'HALT_ADVANCED_TABS_EXTENSION_PATH', get_stylesheet_directory() . '/inc/divi-extensions/halt-advanced-tabs/' );
define( 'HALT_ADVANCED_TABS_EXTENSION_JSON_PATH', HALT_ADVANCED_TABS_EXTENSION_PATH . 'modules-json/' );
define( 'HALT_ADVANCED_TABS_EXTENSION_URL', get_stylesheet_directory_uri() . '/inc/divi-extensions/halt-advanced-tabs/' );

function oa_register_halt_advanced_tabs_divi_module_fallback() {
    if ( ! class_exists( '\ET\Builder\Packages\ModuleLibrary\ModuleRegistration' ) ) {
        return;
    }

    require_once HALT_ADVANCED_TABS_EXTENSION_PATH . 'modules/HaltAdvancedTabs/HaltAdvancedTabs.php';

    $module_json_paths = [
        HALT_ADVANCED_TABS_EXTENSION_JSON_PATH . 'halt-advanced-tabs/',
    ];

    foreach ( $module_json_paths as $module_json_folder_path ) {
        \ET\Builder\Packages\ModuleLibrary\ModuleRegistration::register_module(
            $module_json_folder_path,
            [
                'render_callback' => [ \OA\Modules\HaltAdvancedTabs\HaltAdvancedTabs::class, 'render_callback' ],
            ]
        );
    }
}
add_action( 'init', 'oa_register_halt_advanced_tabs_divi_module_fallback', 20 );

function oa_halt_advanced_tabs_enqueue_vb_scripts() {
    if ( function_exists( 'et_builder_d5_enabled' ) && et_builder_d5_enabled() && function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) {
        $bundle_path = HALT_ADVANCED_TABS_EXTENSION_PATH . 'scripts/bundle.js';
        $bundle_ver  = file_exists( $bundle_path ) ? (string) filemtime( $bundle_path ) : '1.0.0';
        $vb_style_path = HALT_ADVANCED_TABS_EXTENSION_PATH . 'styles/vb-bundle.css';
        $vb_style_ver  = file_exists( $vb_style_path ) ? (string) filemtime( $vb_style_path ) : '1.0.0';

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
            [
                'name'   => 'halt-advanced-tabs-builder-bundle-script',
                'version' => $bundle_ver,
                'script' => [
                    'src' => HALT_ADVANCED_TABS_EXTENSION_URL . 'scripts/bundle.js',
                    'deps'               => [
                        'divi-module-library',
                        'divi-vendor-wp-hooks',
                    ],
                    'enqueue_top_window' => false,
                    'enqueue_app_window' => true,
                ],
            ]
        );

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
            [
                'name'   => 'halt-advanced-tabs-builder-vb-bundle-style',
                'version' => $vb_style_ver,
                'style' => [
                    'src' => HALT_ADVANCED_TABS_EXTENSION_URL . 'styles/vb-bundle.css',
                    'deps'               => [],
                    'enqueue_top_window' => false,
                    'enqueue_app_window' => true,
                ],
            ]
        );
    }
}
add_action( 'divi_visual_builder_assets_before_enqueue_scripts', 'oa_halt_advanced_tabs_enqueue_vb_scripts' );

function oa_halt_advanced_tabs_enqueue_frontend_styles() {
    $style_path = HALT_ADVANCED_TABS_EXTENSION_PATH . 'styles/bundle.css';
    $style_ver  = file_exists( $style_path ) ? (string) filemtime( $style_path ) : '1.0.0';
    wp_enqueue_style( 'halt-advanced-tabs-bundle-style', HALT_ADVANCED_TABS_EXTENSION_URL . 'styles/bundle.css', [], $style_ver );
}
add_action( 'wp_enqueue_scripts', 'oa_halt_advanced_tabs_enqueue_frontend_styles' );
