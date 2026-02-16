<?php
function dt_enqueue_assets() {
    // Parent theme style
    if (file_exists(get_template_directory() . '/style.css')) {
        $parent_theme = wp_get_theme(get_template());
        wp_enqueue_style(
            'divi-style',
            get_template_directory_uri() . '/style.css',
            [],
            $parent_theme->get('Version')
        );
    }

    // Child theme style
    if (file_exists(get_stylesheet_directory() . '/style.css')) {
        $child_theme = wp_get_theme();
        wp_enqueue_style(
            'child-style',
            get_stylesheet_uri(),
            ['divi-style'],
            $child_theme->get('Version')
        );
    }

    $pseudo_classes_path = get_stylesheet_directory() . '/pseudo-classes.css';
    if (file_exists($pseudo_classes_path)) {
        wp_enqueue_style(
            'child-pseudo-classes',
            get_stylesheet_directory_uri() . '/pseudo-classes.css',
            ['child-style'],
            filemtime($pseudo_classes_path)
        );
    }

    $number_animation_css_path = get_stylesheet_directory() . '/assets/css/number-animation.css';
    if (file_exists($number_animation_css_path)) {
        wp_enqueue_style(
            'child-number-animation',
            get_stylesheet_directory_uri() . '/assets/css/number-animation.css',
            ['child-style'],
            filemtime($number_animation_css_path)
        );
    }

    // Custom JS script
    $custom_js_path = get_stylesheet_directory() . '/custom.js';
    if (file_exists($custom_js_path)) {
        wp_enqueue_script(
            'custom-scripts',
            get_stylesheet_directory_uri() . '/custom.js',
            ['jquery'], 
            filemtime($custom_js_path),
            true
        );
    }

    $number_animation_js_path = get_stylesheet_directory() . '/assets/js/number-animation.js';
    if (file_exists($number_animation_js_path)) {
        wp_enqueue_script(
            'child-number-animation',
            get_stylesheet_directory_uri() . '/assets/js/number-animation.js',
            [],
            filemtime($number_animation_js_path),
            true
        );
    }

    $map_tooltips_js_path = get_stylesheet_directory() . '/assets/js/map-tooltips.js';
    if (file_exists($map_tooltips_js_path)) {
        wp_enqueue_script(
            'child-map-tooltips',
            get_stylesheet_directory_uri() . '/assets/js/map-tooltips.js',
            [],
            filemtime($map_tooltips_js_path),
            true
        );
    }

    $timeline_css_path = get_stylesheet_directory() . '/assets/css/timeline-carousel.css';
    if (file_exists($timeline_css_path)) {
        wp_enqueue_style(
            'child-timeline-carousel',
            get_stylesheet_directory_uri() . '/assets/css/timeline-carousel.css',
            ['child-style'],
            filemtime($timeline_css_path)
        );
    }

    $timeline_js_path = get_stylesheet_directory() . '/assets/js/timeline-carousel.js';
    if (file_exists($timeline_js_path)) {
        wp_enqueue_script(
            'child-timeline-carousel',
            get_stylesheet_directory_uri() . '/assets/js/timeline-carousel.js',
            [],
            filemtime($timeline_js_path),
            true
        );
    }

    $oa_client_logos_css_path = get_stylesheet_directory() . '/assets/css/oa-client-logos.css';
    if (file_exists($oa_client_logos_css_path)) {
        wp_enqueue_style(
            'child-oa-client-logos',
            get_stylesheet_directory_uri() . '/assets/css/oa-client-logos.css',
            ['child-style'],
            filemtime($oa_client_logos_css_path)
        );
    }

    $oa_client_logos_js_path = get_stylesheet_directory() . '/assets/js/oa-client-logos.js';
    if (file_exists($oa_client_logos_js_path)) {
        wp_enqueue_script(
            'child-oa-client-logos',
            get_stylesheet_directory_uri() . '/assets/js/oa-client-logos.js',
            [],
            filemtime($oa_client_logos_js_path),
            true
        );
    }

    $oa_advanced_tabs_css_path = get_stylesheet_directory() . '/assets/css/oa-advanced-tabs.css';
    if (file_exists($oa_advanced_tabs_css_path)) {
        wp_enqueue_style(
            'child-oa-advanced-tabs',
            get_stylesheet_directory_uri() . '/assets/css/oa-advanced-tabs.css',
            ['child-style'],
            filemtime($oa_advanced_tabs_css_path)
        );
    }

    $oa_advanced_tabs_js_path = get_stylesheet_directory() . '/assets/js/oa-advanced-tabs.js';
    if (file_exists($oa_advanced_tabs_js_path)) {
        wp_enqueue_script(
            'child-oa-advanced-tabs',
            get_stylesheet_directory_uri() . '/assets/js/oa-advanced-tabs.js',
            [],
            filemtime($oa_advanced_tabs_js_path),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'dt_enqueue_assets');
