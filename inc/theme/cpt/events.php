<?php
/**
 * Register Events post type and taxonomy.
 */
function dt_register_events_post_type() {
    $labels = array(
        'name'                  => 'Events',
        'singular_name'         => 'Event',
        'menu_name'             => 'Events',
        'name_admin_bar'        => 'Event',
        'archives'              => 'Event Archives',
        'attributes'            => 'Event Attributes',
        'parent_item_colon'     => 'Parent Event:',
        'all_items'             => 'All Events',
        'add_new_item'          => 'Add New Event',
        'add_new'               => 'Add New',
        'new_item'              => 'New Event',
        'edit_item'             => 'Edit Event',
        'update_item'           => 'Update Event',
        'view_item'             => 'View Event',
        'view_items'            => 'View Events',
        'search_items'          => 'Search Events',
        'not_found'             => 'Not found',
        'not_found_in_trash'    => 'Not found in Trash',
        'featured_image'        => 'Featured Image',
        'set_featured_image'    => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image'    => 'Use as featured image',
        'insert_into_item'      => 'Insert into event',
        'uploaded_to_this_item' => 'Uploaded to this event',
        'items_list'            => 'Events list',
        'items_list_navigation' => 'Events list navigation',
        'filter_items_list'     => 'Filter events list',
    );

    $args = array(
        'label'                 => 'Event',
        'description'           => 'Event post type',
        'labels'                => $labels,
        'supports'              => array('title', 'thumbnail'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 21,
        'menu_icon'             => 'dashicons-calendar',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array('slug' => 'event'),
        'taxonomies'            => array('event_type'),
    );

    register_post_type('events', $args);
}
add_action('init', 'dt_register_events_post_type', 0);

function dt_register_event_type_taxonomy() {
    $labels = array(
        'name'              => 'Event Types',
        'singular_name'     => 'Event Type',
        'search_items'      => 'Search Event Types',
        'all_items'         => 'All Event Types',
        'edit_item'         => 'Edit Event Type',
        'update_item'       => 'Update Event Type',
        'add_new_item'      => 'Add New Event Type',
        'new_item_name'     => 'New Event Type Name',
        'menu_name'         => 'Event Types',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'event-type'),
    );

    register_taxonomy('event_type', array('events'), $args);
}
add_action('init', 'dt_register_event_type_taxonomy', 0);

function dt_seed_event_type_terms() {
    $terms = array('Internal event', 'Industry event', 'Networking event', 'Webinar');
    foreach ($terms as $term_name) {
        if (!term_exists($term_name, 'event_type')) {
            wp_insert_term($term_name, 'event_type');
        }
    }
}
add_action('init', 'dt_seed_event_type_terms', 11);

/**
 * Keep event timing meta in sync for conditional fields.
 */
function dt_update_event_is_past($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }
    if (get_post_type($post_id) !== 'events') {
        return;
    }

    $start = get_post_meta($post_id, 'event_start', true);
    $end = get_post_meta($post_id, 'event_end', true);
    $date_value = $end ?: $start;

    if (!$date_value) {
        update_post_meta($post_id, 'is_past', 0);
        return;
    }

    $event_ts = strtotime($date_value);
    if ($event_ts === false) {
        return;
    }

    $is_past = $event_ts < current_time('timestamp');
    update_post_meta($post_id, 'is_past', $is_past ? 1 : 0);
}
add_action('save_post', 'dt_update_event_is_past');

function dt_load_event_is_past_value($value, $post_id, $field) {
    $start = get_post_meta($post_id, 'event_start', true);
    $end = get_post_meta($post_id, 'event_end', true);
    $date_value = $end ?: $start;

    if (!$date_value) {
        return 0;
    }

    $event_ts = strtotime($date_value);
    if ($event_ts === false) {
        return 0;
    }

    return $event_ts < current_time('timestamp') ? 1 : 0;
}
add_filter('acf/load_value/name=is_past', 'dt_load_event_is_past_value', 10, 3);

function dt_force_event_is_past_value($value, $post_id, $field) {
    $start = get_post_meta($post_id, 'event_start', true);
    $end = get_post_meta($post_id, 'event_end', true);
    $date_value = $end ?: $start;

    if (!$date_value) {
        return 0;
    }

    $event_ts = strtotime($date_value);
    if ($event_ts === false) {
        return 0;
    }

    return $event_ts < current_time('timestamp') ? 1 : 0;
}
add_filter('acf/update_value/name=is_past', 'dt_force_event_is_past_value', 10, 3);

function dt_events_recap_admin_notice() {
    if (!function_exists('get_current_screen')) {
        return;
    }
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'events') {
        return;
    }
    if (!in_array($screen->base, array('post', 'post-new'), true)) {
        return;
    }
    if (!function_exists('get_field')) {
        return;
    }

    $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
    if (!$post_id) {
        return;
    }

    $is_past = (bool) get_field('is_past', $post_id);
    $show_recap = (bool) get_field('show_in_recap_grid', $post_id);

    if ($is_past && !$show_recap) {
        echo '<div class="notice notice-warning"><p>'
            . esc_html__('This event is in the past. Enable "Show in Recap Grid" to display it in the recap tab.', 'oa')
            . '</p></div>';
    }
}
add_action('admin_notices', 'dt_events_recap_admin_notice');

function dt_hide_event_is_past_field() {
    if (!function_exists('get_current_screen')) {
        return;
    }
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'events') {
        return;
    }
    echo '<style>.acf-field.is-past-hidden{display:none !important;}</style>';
}
add_action('admin_head', 'dt_hide_event_is_past_field');

function dt_remove_events_taxonomy_metabox() {
    remove_meta_box('event_typediv', 'events', 'side');
}
add_action('add_meta_boxes', 'dt_remove_events_taxonomy_metabox', 11);

function dt_disable_gutenberg_for_events($use_block_editor, $post_type) {
    if (in_array($post_type, array('events', 'team_members', 'oa_job'), true)) {
        return false;
    }
    return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'dt_disable_gutenberg_for_events', 10, 2);
add_filter('use_block_editor_for_post', '__return_false', 10);



function dt_remove_events_editor_support() {
    remove_post_type_support('events', 'editor');
    remove_post_type_support('events', 'excerpt');
}
add_action('init', 'dt_remove_events_editor_support', 100);

function dt_remove_team_members_editor_support() {
    remove_post_type_support('team_members', 'editor');
    remove_post_type_support('team_members', 'excerpt');
}
add_action('init', 'dt_remove_team_members_editor_support', 100);

function dt_remove_jobs_editor_support() {
    remove_post_type_support('oa_job', 'editor');
    remove_post_type_support('oa_job', 'excerpt');
}
add_action('init', 'dt_remove_jobs_editor_support', 100);

function dt_remove_posts_editor_support() {
    remove_post_type_support('post', 'editor');
    remove_post_type_support('post', 'excerpt');
}
add_action('init', 'dt_remove_posts_editor_support', 100);

