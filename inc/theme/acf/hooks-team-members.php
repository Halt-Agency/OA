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
