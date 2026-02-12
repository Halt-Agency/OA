<?php
function dt_team_members_title_placeholder($title) {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ($screen && $screen->post_type === 'team_members') {
        return 'Full Name';
    }
    return $title;
}
add_filter('enter_title_here', 'dt_team_members_title_placeholder');

function dt_sync_team_member_featured_image($post_id) {
    if (get_post_type($post_id) !== 'team_members') {
        return;
    }
    if (!function_exists('get_field')) {
        return;
    }

    $profile = get_field('profile_image', $post_id);
    $attachment_id = 0;
    if (is_array($profile) && isset($profile['ID'])) {
        $attachment_id = (int) $profile['ID'];
    } elseif (is_numeric($profile)) {
        $attachment_id = (int) $profile;
    }

    if ($attachment_id) {
        set_post_thumbnail($post_id, $attachment_id);
    }
}
add_action('acf/save_post', 'dt_sync_team_member_featured_image', 20);

function dt_sync_uk_map_card_team_member_fields($post_id) {
    static $is_syncing = false;
    if ($is_syncing) {
        return;
    }
    if (!function_exists('get_field')) {
        return;
    }

    if (get_post_type($post_id) !== 'page') {
        return;
    }

    $locations = [
        'bedfordshire' => [
            'team' => 'field_about_uk_map_card_bedfordshire_team_member',
            'image' => 'field_about_uk_map_card_bedfordshire_team_member_image',
            'name' => 'field_about_uk_map_card_bedfordshire_team_member_name',
            'email' => 'field_about_uk_map_card_bedfordshire_team_member_email',
        ],
        'buckinghamshire' => [
            'team' => 'field_about_uk_map_card_buckinghamshire_team_member',
            'image' => 'field_about_uk_map_card_buckinghamshire_team_member_image',
            'name' => 'field_about_uk_map_card_buckinghamshire_team_member_name',
            'email' => 'field_about_uk_map_card_buckinghamshire_team_member_email',
        ],
        'cambridgeshire' => [
            'team' => 'field_about_uk_map_card_cambridgeshire_team_member',
            'image' => 'field_about_uk_map_card_cambridgeshire_team_member_image',
            'name' => 'field_about_uk_map_card_cambridgeshire_team_member_name',
            'email' => 'field_about_uk_map_card_cambridgeshire_team_member_email',
        ],
        'hertfordshire' => [
            'team' => 'field_about_uk_map_card_hertfordshire_team_member',
            'image' => 'field_about_uk_map_card_hertfordshire_team_member_image',
            'name' => 'field_about_uk_map_card_hertfordshire_team_member_name',
            'email' => 'field_about_uk_map_card_hertfordshire_team_member_email',
        ],
        'north_london' => [
            'team' => 'field_about_uk_map_card_north_london_team_member',
            'image' => 'field_about_uk_map_card_north_london_team_member_image',
            'name' => 'field_about_uk_map_card_north_london_team_member_name',
            'email' => 'field_about_uk_map_card_north_london_team_member_email',
        ],
    ];

    $acf_post = $_POST['acf'] ?? [];
    $page_content_post = $acf_post['field_about_page_content'] ?? [];
    $uk_map_card_post = is_array($page_content_post) ? ($page_content_post['field_about_uk_map_card'] ?? []) : [];

    $is_syncing = true;
    foreach ($locations as $location_key => $keys) {
        $location_group_key = 'field_about_uk_map_card_' . $location_key;
        $location_post = is_array($uk_map_card_post) ? ($uk_map_card_post[$location_group_key] ?? []) : [];
        $team_field = $location_post[$keys['team']] ?? get_field($keys['team'], $post_id, false);
        if (is_array($team_field)) {
            $team_field = $team_field[0] ?? 0;
        }
        $team_id = (int) $team_field;
        if (!$team_id) {
            continue;
        }

        $profile_image = get_field('profile_image', $team_id, false);
        if (is_array($profile_image) && isset($profile_image['ID'])) {
            $profile_image = (int) $profile_image['ID'];
        }
        $first_name = get_field('first_name', $team_id);
        $last_name = get_field('last_name', $team_id);
        $email = get_field('email', $team_id);

        $name = trim(implode(' ', array_filter([$first_name, $last_name])));

        if (is_array($location_post)) {
            $location_post[$keys['image']] = $profile_image ?: '';
            $location_post[$keys['name']] = $name;
            $location_post[$keys['email']] = $email ?: '';
            $uk_map_card_post[$location_group_key] = $location_post;
        }
    }
    if (is_array($page_content_post)) {
        $page_content_post['field_about_uk_map_card'] = $uk_map_card_post;
        update_field('field_about_page_content', $page_content_post, $post_id);
    }
    $is_syncing = false;
}
add_action('acf/save_post', 'dt_sync_uk_map_card_team_member_fields', 30);

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
