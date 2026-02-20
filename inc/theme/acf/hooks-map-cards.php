<?php

function dt_uk_map_card_sync_locked($set = null) {
    static $locked = false;
    if ($set !== null) {
        $locked = (bool) $set;
    }
    return $locked;
}

function dt_extract_team_member_id($value) {
    if (is_array($value)) {
        $first = $value[0] ?? 0;
        return dt_extract_team_member_id($first);
    }
    if (is_object($value) && isset($value->ID)) {
        return (int) $value->ID;
    }
    if (is_numeric($value)) {
        return (int) $value;
    }
    return 0;
}

function dt_uk_map_card_sync_configs() {
    return [
        [
            'page_content_field_key' => 'field_about_page_content',
            'map_card_field_key' => 'field_about_uk_map_card',
            'field_prefix' => 'field_about_uk_map_card_',
            'use_global_field_key' => 'field_about_uk_use_global_settings',
        ],
        [
            'page_content_field_key' => 'field_candidates_page_content',
            'map_card_field_key' => 'field_candidates_uk_map_card',
            'field_prefix' => 'field_candidates_uk_map_card_',
            'use_global_field_key' => 'field_candidates_uk_use_global_settings',
        ],
    ];
}

function dt_sync_single_uk_map_card_config($post_id, array $config, array $acf_post, array $global_contacts) {
    $locations = ['bedfordshire', 'buckinghamshire', 'cambridgeshire', 'hertfordshire', 'north_london'];

    $page_content_post = $acf_post[$config['page_content_field_key']] ?? get_field($config['page_content_field_key'], $post_id, false);
    if (!is_array($page_content_post)) {
        return;
    }

    $uk_map_card_post = $page_content_post[$config['map_card_field_key']] ?? [];
    if (!is_array($uk_map_card_post)) {
        return;
    }

    $use_global = $page_content_post[$config['use_global_field_key']] ?? get_field($config['use_global_field_key'], $post_id, false);
    $use_global = in_array((string) $use_global, ['1', 'true'], true) || $use_global === true || (int) $use_global === 1;

    foreach ($locations as $location_key) {
        $location_group_key = $config['field_prefix'] . $location_key;
        $location_post = is_array($uk_map_card_post) ? ($uk_map_card_post[$location_group_key] ?? []) : [];

        $team_key = $location_group_key . '_team_member';
        $image_key = $location_group_key . '_team_member_image';
        $name_key = $location_group_key . '_team_member_name';
        $email_key = $location_group_key . '_team_member_email';

        if ($use_global) {
            $global_team = $global_contacts[$location_key] ?? 0;
            $team_field = dt_extract_team_member_id($global_team);
        } else {
            $team_field = $location_post[$team_key] ?? get_field($team_key, $post_id, false);
        }
        $team_id = dt_extract_team_member_id($team_field);

        if (!is_array($location_post)) {
            continue;
        }

        if ($use_global) {
            $location_post[$team_key] = $team_id ? [$team_id] : [];
        }

        if (!$team_id) {
            $location_post[$image_key] = '';
            $location_post[$name_key] = '';
            $location_post[$email_key] = '';
            $uk_map_card_post[$location_group_key] = $location_post;
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

        $location_post[$image_key] = $profile_image ?: '';
        $location_post[$name_key] = $name;
        $location_post[$email_key] = $email ?: '';
        $uk_map_card_post[$location_group_key] = $location_post;
    }

    $page_content_post[$config['map_card_field_key']] = $uk_map_card_post;
    update_field($config['page_content_field_key'], $page_content_post, $post_id);
}

function dt_sync_uk_map_card_team_member_fields($post_id) {
    if (dt_uk_map_card_sync_locked()) {
        return;
    }
    if (!function_exists('get_field')) {
        return;
    }
    if (get_post_type($post_id) !== 'page') {
        return;
    }

    $acf_post = $_POST['acf'] ?? [];
    $global_contacts = get_field('field_uk_coverage_contacts_group', 'option');
    if (!is_array($global_contacts)) {
        $global_contacts = [];
    }

    dt_uk_map_card_sync_locked(true);
    foreach (dt_uk_map_card_sync_configs() as $config) {
        dt_sync_single_uk_map_card_config($post_id, $config, $acf_post, $global_contacts);
    }
    dt_uk_map_card_sync_locked(false);
}
add_action('acf/save_post', 'dt_sync_uk_map_card_team_member_fields', 30);

function dt_sync_uk_map_cards_when_global_options_change($post_id) {
    if (dt_uk_map_card_sync_locked()) {
        return;
    }
    if (!is_string($post_id) || strpos($post_id, 'options') !== 0) {
        return;
    }
    if (!function_exists('get_field')) {
        return;
    }

    $global_contacts = get_field('field_uk_coverage_contacts_group', 'option');
    if (!is_array($global_contacts)) {
        $global_contacts = [];
    }

    $target_page_ids = [9, 11];
    dt_uk_map_card_sync_locked(true);
    foreach ($target_page_ids as $page_id) {
        if (get_post_type($page_id) !== 'page') {
            continue;
        }
        foreach (dt_uk_map_card_sync_configs() as $config) {
            dt_sync_single_uk_map_card_config($page_id, $config, [], $global_contacts);
        }
    }
    dt_uk_map_card_sync_locked(false);
}
add_action('acf/save_post', 'dt_sync_uk_map_cards_when_global_options_change', 40);
