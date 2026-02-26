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

    $map_tooltips_css_path = get_stylesheet_directory() . '/assets/css/map-tooltips.css';
    if (file_exists($map_tooltips_css_path)) {
        wp_enqueue_style(
            'child-map-tooltips',
            get_stylesheet_directory_uri() . '/assets/css/map-tooltips.css',
            ['child-style'],
            filemtime($map_tooltips_css_path)
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

    $text_limit_js_path = get_stylesheet_directory() . '/assets/js/text-limit.js';
    if (file_exists($text_limit_js_path)) {
        wp_enqueue_script(
            'child-text-limit',
            get_stylesheet_directory_uri() . '/assets/js/text-limit.js',
            [],
            filemtime($text_limit_js_path),
            true
        );
    }

    $internal_jobs_visibility_js_path = get_stylesheet_directory() . '/assets/js/internal-jobs-visibility.js';
    if (file_exists($internal_jobs_visibility_js_path)) {
        wp_enqueue_script(
            'child-internal-jobs-visibility',
            get_stylesheet_directory_uri() . '/assets/js/internal-jobs-visibility.js',
            [],
            filemtime($internal_jobs_visibility_js_path),
            true
        );
    }

    $map_tooltips_common_js_path = get_stylesheet_directory() . '/assets/js/map-tooltips/common.js';
    if (file_exists($map_tooltips_common_js_path)) {
        wp_enqueue_script(
            'child-map-tooltips-common',
            get_stylesheet_directory_uri() . '/assets/js/map-tooltips/common.js',
            [],
            filemtime($map_tooltips_common_js_path),
            true
        );
    }

    $map_tooltips_legacy_js_path = get_stylesheet_directory() . '/assets/js/map-tooltips/legacy-icon-tooltips.js';
    if (file_exists($map_tooltips_legacy_js_path)) {
        wp_enqueue_script(
            'child-map-tooltips-legacy',
            get_stylesheet_directory_uri() . '/assets/js/map-tooltips/legacy-icon-tooltips.js',
            ['child-map-tooltips-common'],
            filemtime($map_tooltips_legacy_js_path),
            true
        );
    }

    $map_tooltips_dynamic_js_path = get_stylesheet_directory() . '/assets/js/map-tooltips/dynamic-cards.js';
    if (file_exists($map_tooltips_dynamic_js_path)) {
        wp_enqueue_script(
            'child-map-tooltips-dynamic',
            get_stylesheet_directory_uri() . '/assets/js/map-tooltips/dynamic-cards.js',
            ['child-map-tooltips-common'],
            filemtime($map_tooltips_dynamic_js_path),
            true
        );
    }

    $map_tooltips_bootstrap_js_path = get_stylesheet_directory() . '/assets/js/map-tooltips.js';
    if (file_exists($map_tooltips_bootstrap_js_path)) {
        wp_enqueue_script(
            'child-map-tooltips-bootstrap',
            get_stylesheet_directory_uri() . '/assets/js/map-tooltips.js',
            ['child-map-tooltips-legacy', 'child-map-tooltips-dynamic'],
            filemtime($map_tooltips_bootstrap_js_path),
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

    $oa_advanced_tabs_solutions_js_path = get_stylesheet_directory() . '/assets/js/oa-advanced-tabs-solutions.js';
    if (file_exists($oa_advanced_tabs_solutions_js_path)) {
        wp_enqueue_script(
            'child-oa-advanced-tabs-solutions',
            get_stylesheet_directory_uri() . '/assets/js/oa-advanced-tabs-solutions.js',
            [],
            filemtime($oa_advanced_tabs_solutions_js_path),
            true
        );
    }

    $team_carousel_css_path = get_stylesheet_directory() . '/assets/css/team-carousel.css';
    if (file_exists($team_carousel_css_path)) {
        wp_enqueue_style(
            'child-team-carousel',
            get_stylesheet_directory_uri() . '/assets/css/team-carousel.css',
            ['child-style'],
            filemtime($team_carousel_css_path)
        );
    }

    $team_carousel_js_path = get_stylesheet_directory() . '/assets/js/team-carousel.js';
    if (file_exists($team_carousel_js_path)) {
        wp_enqueue_script(
            'child-team-carousel',
            get_stylesheet_directory_uri() . '/assets/js/team-carousel.js',
            [],
            filemtime($team_carousel_js_path),
            true
        );
    }

    $oa_sectors_carousel_css_path = get_stylesheet_directory() . '/assets/css/oa-sectors-carousel.css';
    if (file_exists($oa_sectors_carousel_css_path)) {
        wp_enqueue_style(
            'child-oa-sectors-carousel',
            get_stylesheet_directory_uri() . '/assets/css/oa-sectors-carousel.css',
            ['child-style'],
            filemtime($oa_sectors_carousel_css_path)
        );
    }

    $oa_sectors_carousel_js_path = get_stylesheet_directory() . '/assets/js/oa-sectors-carousel.js';
    if (file_exists($oa_sectors_carousel_js_path)) {
        wp_enqueue_script(
            'child-oa-sectors-carousel',
            get_stylesheet_directory_uri() . '/assets/js/oa-sectors-carousel.js',
            [],
            filemtime($oa_sectors_carousel_js_path),
            true
        );
    }

    $oa_team_grid_css_path = get_stylesheet_directory() . '/assets/css/oa-team-grid.css';
    if (file_exists($oa_team_grid_css_path)) {
        wp_enqueue_style(
            'child-oa-team-grid',
            get_stylesheet_directory_uri() . '/assets/css/oa-team-grid.css',
            ['child-style'],
            filemtime($oa_team_grid_css_path)
        );
    }

    $oa_team_grid_js_path = get_stylesheet_directory() . '/assets/js/oa-team-grid.js';
    if (file_exists($oa_team_grid_js_path)) {
        wp_enqueue_script(
            'child-oa-team-grid',
            get_stylesheet_directory_uri() . '/assets/js/oa-team-grid.js',
            [],
            filemtime($oa_team_grid_js_path),
            true
        );
    }

    $oa_team_member_profile_stack_css_path = get_stylesheet_directory() . '/assets/css/oa-team-member-profile-stack.css';
    if (file_exists($oa_team_member_profile_stack_css_path)) {
        wp_enqueue_style(
            'child-oa-team-member-profile-stack',
            get_stylesheet_directory_uri() . '/assets/css/oa-team-member-profile-stack.css',
            ['child-style'],
            filemtime($oa_team_member_profile_stack_css_path)
        );
    }

    $oa_team_member_profile_stack_js_path = get_stylesheet_directory() . '/assets/js/oa-team-member-profile-stack.js';
    if (file_exists($oa_team_member_profile_stack_js_path)) {
        wp_enqueue_script(
            'child-oa-team-member-profile-stack',
            get_stylesheet_directory_uri() . '/assets/js/oa-team-member-profile-stack.js',
            [],
            filemtime($oa_team_member_profile_stack_js_path),
            true
        );
    }

    $oa_team_member_get_to_know_js_path = get_stylesheet_directory() . '/assets/js/oa-team-member-get-to-know.js';
    if (file_exists($oa_team_member_get_to_know_js_path)) {
        wp_enqueue_script(
            'child-oa-team-member-get-to-know',
            get_stylesheet_directory_uri() . '/assets/js/oa-team-member-get-to-know.js',
            [],
            filemtime($oa_team_member_get_to_know_js_path),
            true
        );
    }

    $oa_team_member_get_to_know_css_path = get_stylesheet_directory() . '/assets/css/oa-team-member-get-to-know.css';
    if (file_exists($oa_team_member_get_to_know_css_path)) {
        wp_enqueue_style(
            'child-oa-team-member-get-to-know',
            get_stylesheet_directory_uri() . '/assets/css/oa-team-member-get-to-know.css',
            ['child-style'],
            filemtime($oa_team_member_get_to_know_css_path)
        );
    }

    $oa_jobs_board_css_path = get_stylesheet_directory() . '/assets/css/oa-jobs-board.css';
    if (file_exists($oa_jobs_board_css_path)) {
        wp_enqueue_style(
            'child-oa-jobs-board',
            get_stylesheet_directory_uri() . '/assets/css/oa-jobs-board.css',
            ['child-style'],
            filemtime($oa_jobs_board_css_path)
        );
    }

    $oa_jobs_board_js_path = get_stylesheet_directory() . '/assets/js/oa-jobs-board.js';
    if (file_exists($oa_jobs_board_js_path)) {
        wp_enqueue_script(
            'child-oa-jobs-board',
            get_stylesheet_directory_uri() . '/assets/js/oa-jobs-board.js',
            [],
            filemtime($oa_jobs_board_js_path),
            true
        );
    }

    $oa_jobs_microsite_logos_css_path = get_stylesheet_directory() . '/assets/css/oa-jobs-microsite-logos.css';
    if (file_exists($oa_jobs_microsite_logos_css_path)) {
        wp_enqueue_style(
            'child-oa-jobs-microsite-logos',
            get_stylesheet_directory_uri() . '/assets/css/oa-jobs-microsite-logos.css',
            ['child-style'],
            filemtime($oa_jobs_microsite_logos_css_path)
        );
    }

    $oa_jobs_microsite_logos_js_path = get_stylesheet_directory() . '/assets/js/oa-jobs-microsite-logos.js';
    if (file_exists($oa_jobs_microsite_logos_js_path)) {
        wp_enqueue_script(
            'child-oa-jobs-microsite-logos',
            get_stylesheet_directory_uri() . '/assets/js/oa-jobs-microsite-logos.js',
            [],
            filemtime($oa_jobs_microsite_logos_js_path),
            true
        );
    }

    $oa_quick_exit_css_path = get_stylesheet_directory() . '/assets/css/oa-quick-exit.css';
    if (file_exists($oa_quick_exit_css_path)) {
        wp_enqueue_style(
            'child-oa-quick-exit',
            get_stylesheet_directory_uri() . '/assets/css/oa-quick-exit.css',
            ['child-style'],
            filemtime($oa_quick_exit_css_path)
        );
    }

    $oa_quick_exit_js_path = get_stylesheet_directory() . '/assets/js/oa-quick-exit.js';
    if (file_exists($oa_quick_exit_js_path)) {
        wp_enqueue_script(
            'child-oa-quick-exit',
            get_stylesheet_directory_uri() . '/assets/js/oa-quick-exit.js',
            [],
            filemtime($oa_quick_exit_js_path),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'dt_enqueue_assets');
