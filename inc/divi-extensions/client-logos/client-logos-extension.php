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

require_once OA_CLIENT_LOGOS_EXTENSION_PATH . 'modules/ClientLogosMarquee/ClientLogosMarquee.php';
require_once OA_CLIENT_LOGOS_EXTENSION_PATH . 'modules/Modules.php';

function oa_client_logos_enqueue_vb_scripts() {
    if ( function_exists( 'et_builder_d5_enabled' ) && et_builder_d5_enabled() && function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) {
        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
            [
                'name'   => 'oa-client-logos-builder-bundle-script',
                'version' => '1.0.0',
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
                'version' => '1.0.0',
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
    wp_enqueue_style( 'oa-client-logos-bundle-style', OA_CLIENT_LOGOS_EXTENSION_URL . 'styles/bundle.css', [], '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'oa_client_logos_enqueue_frontend_styles' );
