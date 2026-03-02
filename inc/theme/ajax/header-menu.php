<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function () {
    register_rest_route('oa/v1', '/header-menu', [
        'methods'  => WP_REST_Server::READABLE,
        'callback' => function () {
            return rest_ensure_response([
                'html' => function_exists('oa_get_header_menu_markup') ? oa_get_header_menu_markup() : '',
            ]);
        },
        'permission_callback' => '__return_true',
    ]);
});
