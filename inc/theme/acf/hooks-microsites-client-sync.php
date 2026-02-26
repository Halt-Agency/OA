<?php

function dt_acf_image_to_attachment_id($value) {
    if (is_numeric($value)) {
        return (int) $value;
    }

    if (is_array($value)) {
        if (!empty($value['ID']) && is_numeric($value['ID'])) {
            return (int) $value['ID'];
        }
        if (!empty($value['id']) && is_numeric($value['id'])) {
            return (int) $value['id'];
        }
    }

    return 0;
}

function dt_sync_microsite_logos_to_client_post($post_id) {
    if (!is_numeric($post_id)) {
        return;
    }

    $post_id = (int) $post_id;
    if ($post_id <= 0 || get_post_type($post_id) !== 'microsite') {
        return;
    }

    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id)) {
        return;
    }

    $should_create = function_exists('get_field')
        ? (bool) get_field('create_client_logo_post', $post_id)
        : (bool) get_post_meta($post_id, 'create_client_logo_post', true);

    if (!$should_create) {
        return;
    }

    $microsite_title = get_the_title($post_id);
    if (!is_string($microsite_title) || $microsite_title === '') {
        $microsite_title = 'Client';
    }

    $linked_client_id = (int) get_post_meta($post_id, '_microsite_client_post_id', true);
    if ($linked_client_id > 0 && get_post_type($linked_client_id) !== 'clients') {
        $linked_client_id = 0;
    }

    if ($linked_client_id <= 0) {
        $linked_client_id = wp_insert_post([
            'post_type'   => 'clients',
            'post_status' => 'publish',
            'post_title'  => $microsite_title,
        ], true);

        if (is_wp_error($linked_client_id) || !$linked_client_id) {
            return;
        }

        $linked_client_id = (int) $linked_client_id;
        update_post_meta($post_id, '_microsite_client_post_id', $linked_client_id);
    } else {
        wp_update_post([
            'ID'         => $linked_client_id,
            'post_title' => $microsite_title,
        ]);
    }

    $logo_white = function_exists('get_field') ? get_field('client_logo', $post_id) : get_post_meta($post_id, 'client_logo', true);
    $logo_colour = function_exists('get_field') ? get_field('client_logo_colour', $post_id) : get_post_meta($post_id, 'client_logo_colour', true);

    $logo_white_id = dt_acf_image_to_attachment_id($logo_white);
    $logo_colour_id = dt_acf_image_to_attachment_id($logo_colour);

    if (function_exists('update_field')) {
        update_field('client_logo', $logo_white_id ?: '', $linked_client_id);
        update_field('client_logo_colour', $logo_colour_id ?: '', $linked_client_id);
    } else {
        update_post_meta($linked_client_id, 'client_logo', $logo_white_id);
        update_post_meta($linked_client_id, 'client_logo_colour', $logo_colour_id);
    }
}
add_action('acf/save_post', 'dt_sync_microsite_logos_to_client_post', 30);
