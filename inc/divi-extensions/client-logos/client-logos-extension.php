<?php
/**
 * Client Logos Marquee Divi 5 module extension.
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

define( 'OA_CLIENT_LOGOS_EXTENSION_PATH', get_stylesheet_directory() . '/inc/divi-extensions/client-logos/' );
define( 'OA_CLIENT_LOGOS_EXTENSION_JSON_PATH', OA_CLIENT_LOGOS_EXTENSION_PATH . 'modules-json/' );
define( 'OA_CLIENT_LOGOS_EXTENSION_URL', get_stylesheet_directory_uri() . '/inc/divi-extensions/client-logos/' );

function oa_register_client_logos_divi_module_fallback() {
    if ( ! class_exists( '\ET\Builder\Packages\ModuleLibrary\ModuleRegistration' ) ) {
        return;
    }

    require_once OA_CLIENT_LOGOS_EXTENSION_PATH . 'modules/ClientLogosMarquee/ClientLogosMarquee.php';

    $module_json_paths = [
        OA_CLIENT_LOGOS_EXTENSION_JSON_PATH . 'client-logos-marquee/',
    ];

    foreach ( $module_json_paths as $module_json_folder_path ) {
        \ET\Builder\Packages\ModuleLibrary\ModuleRegistration::register_module(
            $module_json_folder_path,
            [
                'render_callback' => [ \OA\Modules\ClientLogosMarquee\ClientLogosMarquee::class, 'render_callback' ],
            ]
        );
    }
}
add_action( 'init', 'oa_register_client_logos_divi_module_fallback', 20 );

function oa_client_logos_enqueue_vb_scripts() {
    if ( function_exists( 'et_builder_d5_enabled' ) && et_builder_d5_enabled() && function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) {
        $bundle_path = OA_CLIENT_LOGOS_EXTENSION_PATH . 'scripts/bundle.js';
        $bundle_ver  = file_exists( $bundle_path ) ? (string) filemtime( $bundle_path ) : '1.0.0';
        $vb_style_path = OA_CLIENT_LOGOS_EXTENSION_PATH . 'styles/vb-bundle.css';
        $vb_style_ver  = file_exists( $vb_style_path ) ? (string) filemtime( $vb_style_path ) : '1.0.0';

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
            [
                'name'   => 'oa-client-logos-builder-bundle-script',
                'version' => $bundle_ver,
                'script' => [
                    'src' => OA_CLIENT_LOGOS_EXTENSION_URL . 'scripts/bundle.js',
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
                'name'   => 'oa-client-logos-builder-vb-bundle-style',
                'version' => $vb_style_ver,
                'style' => [
                    'src' => OA_CLIENT_LOGOS_EXTENSION_URL . 'styles/vb-bundle.css',
                    'deps'               => [],
                    'enqueue_top_window' => false,
                    'enqueue_app_window' => true,
                ],
            ]
        );
    }
}
add_action( 'divi_visual_builder_assets_before_enqueue_scripts', 'oa_client_logos_enqueue_vb_scripts' );

function oa_client_logos_enqueue_frontend_styles() {
    $style_path = OA_CLIENT_LOGOS_EXTENSION_PATH . 'styles/bundle.css';
    $style_ver  = file_exists( $style_path ) ? (string) filemtime( $style_path ) : '1.0.0';
    wp_enqueue_style( 'oa-client-logos-bundle-style', OA_CLIENT_LOGOS_EXTENSION_URL . 'styles/bundle.css', [], $style_ver );
}
add_action( 'wp_enqueue_scripts', 'oa_client_logos_enqueue_frontend_styles' );
