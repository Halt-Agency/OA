<?php
/**
 * Register Team Members Post Type
 */
function dt_register_team_members() {
    $labels = array(
        'name'                  => 'Team Members',
        'singular_name'         => 'Team Member',
        'menu_name'             => 'Team Members',
        'name_admin_bar'        => 'Team Member',
        'archives'              => 'Team Member Archives',
        'attributes'            => 'Team Member Attributes',
        'parent_item_colon'     => 'Parent Team Member:',
        'all_items'             => 'All Team Members',
        'add_new_item'          => 'Add New Team Member',
        'add_new'               => 'Add New',
        'new_item'              => 'New Team Member',
        'edit_item'             => 'Edit Team Member',
        'update_item'           => 'Update Team Member',
        'view_item'             => 'View Team Member',
        'view_items'            => 'View Team Members',
        'search_items'          => 'Search Team Members',
        'not_found'             => 'Not found',
        'not_found_in_trash'    => 'Not found in Trash',
        'featured_image'        => 'Featured Image',
        'set_featured_image'    => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image'    => 'Use as featured image',
        'insert_into_item'      => 'Insert into team member',
        'uploaded_to_this_item' => 'Uploaded to this team member',
        'items_list'            => 'Team Members list',
        'items_list_navigation' => 'Team Members list navigation',
        'filter_items_list'     => 'Filter team members list',
    );
    
    $args = array(
        'label'                 => 'Team Member',
        'description'           => 'Team Members post type',
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 21,
        'menu_icon'             => 'dashicons-groups',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array('slug' => 'meet-the-team'),
    );
    
    register_post_type('team_members', $args);
}
add_action('init', 'dt_register_team_members', 0);

function dt_register_team_taxonomies() {
    $specialism_labels = array(
        'name'              => 'Specialisms',
        'singular_name'     => 'Specialism',
        'search_items'      => 'Search Specialisms',
        'all_items'         => 'All Specialisms',
        'edit_item'         => 'Edit Specialism',
        'update_item'       => 'Update Specialism',
        'add_new_item'      => 'Add New Specialism',
        'new_item_name'     => 'New Specialism Name',
        'menu_name'         => 'Specialisms',
    );

    $solution_labels = array(
        'name'              => 'Solutions',
        'singular_name'     => 'Solution',
        'search_items'      => 'Search Solutions',
        'all_items'         => 'All Solutions',
        'edit_item'         => 'Edit Solution',
        'update_item'       => 'Update Solution',
        'add_new_item'      => 'Add New Solution',
        'new_item_name'     => 'New Solution Name',
        'menu_name'         => 'Solutions',
    );
    $location_labels = array(
        'name'              => 'Locations',
        'singular_name'     => 'Location',
        'search_items'      => 'Search Locations',
        'all_items'         => 'All Locations',
        'edit_item'         => 'Edit Location',
        'update_item'       => 'Update Location',
        'add_new_item'      => 'Add New Location',
        'new_item_name'     => 'New Location Name',
        'menu_name'         => 'Locations',
    );

    $tax_args = array(
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
    );

    register_taxonomy('specialism', array('team_members', 'oa_job'), $tax_args + array('labels' => $specialism_labels, 'rewrite' => array('slug' => 'specialism')));
    register_taxonomy('solution', array('team_members'), $tax_args + array('labels' => $solution_labels, 'rewrite' => array('slug' => 'solution')));
    register_taxonomy('location', array('team_members'), $tax_args + array('labels' => $location_labels, 'rewrite' => array('slug' => 'location')));
}
add_action('init', 'dt_register_team_taxonomies', 0);

function dt_seed_team_taxonomies_once() {
    if (get_option('dt_team_taxonomies_seeded_v2')) {
        return;
    }

    $specialisms = array(
        'Engineering',
        'Office & Commercial',
        'Events & Hospitality',
        'Manufacturing',
        'Warehousing & Distribution',
    );
    foreach ($specialisms as $name) {
        $slug = sanitize_title($name);
        if (!term_exists($slug, 'specialism')) {
            wp_insert_term($name, 'specialism', array('slug' => $slug));
        }
    }

    $solutions = array(
        'Permanent',
        'Temporary',
        'Embedded',
        'Executive Search',
        'Executive Search',
        'High-Volume Temp',
    );
    foreach ($solutions as $name) {
        $slug = sanitize_title($name);
        if (!term_exists($slug, 'solution')) {
            wp_insert_term($name, 'solution', array('slug' => $slug));
        }
    }

    $locations = array(
        'Buckinghamshire',
        'Bedfordshire',
        'Cambridgeshire',
        'Hertfordshire',
        'North London',
        'Onsite',
    );
    foreach ($locations as $name) {
        $slug = sanitize_title($name);
        if (!term_exists($slug, 'location')) {
            wp_insert_term($name, 'location', array('slug' => $slug));
        }
    }

    update_option('dt_team_taxonomies_seeded_v2', 1);
}
add_action('init', 'dt_seed_team_taxonomies_once', 11);

function dt_remove_team_taxonomy_metaboxes() {
    remove_meta_box('specialismdiv', 'team_members', 'side');
    remove_meta_box('solutiondiv', 'team_members', 'side');
    remove_meta_box('locationdiv', 'team_members', 'side');
    remove_meta_box('specialismdiv', 'oa_job', 'side');
}
add_action('add_meta_boxes', 'dt_remove_team_taxonomy_metaboxes', 11);
