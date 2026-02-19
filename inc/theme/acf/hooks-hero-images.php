<?php

// Store a URL meta key for Divi custom meta use (ACF image fields save IDs).
add_action('acf/save_post', function($post_id) {
    if (!function_exists('get_field')) {
        return;
    }

    if (get_post_type($post_id) !== 'page') {
        return;
    }

    $group = get_field('page_content', $post_id);
    if (!is_array($group)) {
        return;
    }

    $image_url = isset($group['hero_background_image']) ? $group['hero_background_image'] : '';
    if (is_array($image_url) && isset($image_url['url'])) {
        $image_url = $image_url['url'];
    }

    if (is_string($image_url) && $image_url !== '') {
        update_post_meta($post_id, 'page_content_hero_background_image_url', $image_url);
    } else {
        delete_post_meta($post_id, 'page_content_hero_background_image_url');
    }
}, 20);
