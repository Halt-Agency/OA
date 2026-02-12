<?php
/**
 * Register Microsites Post Type
 */
function dt_register_microsites() {
    $labels = array(
        'name'                  => 'Microsites',
        'singular_name'         => 'Microsite',
        'menu_name'             => 'Microsites',
        'name_admin_bar'        => 'Microsite',
        'archives'              => 'Microsite Archives',
        'attributes'            => 'Microsite Attributes',
        'parent_item_colon'     => 'Parent Microsite:',
        'all_items'             => 'All Microsites',
        'add_new_item'          => 'Add New Microsite',
        'add_new'               => 'Add New',
        'new_item'              => 'New Microsite',
        'edit_item'             => 'Edit Microsite',
        'update_item'           => 'Update Microsite',
        'view_item'             => 'View Microsite',
        'view_items'            => 'View Microsites',
        'search_items'          => 'Search Microsites',
        'not_found'             => 'Not found',
        'not_found_in_trash'    => 'Not found in Trash',
        'featured_image'        => 'Featured Image',
        'set_featured_image'    => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image'    => 'Use as featured image',
        'insert_into_item'      => 'Insert into microsite',
        'uploaded_to_this_item' => 'Uploaded to this microsite',
        'items_list'            => 'Microsites list',
        'items_list_navigation' => 'Microsites list navigation',
        'filter_items_list'     => 'Filter microsites list',
    );
    
    $args = array(
        'label'                 => 'Microsite',
        'description'           => 'Microsites post type',
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 23,
        'menu_icon'             => 'dashicons-welcome-widgets-menus',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array('slug' => 'microsites'),
    );
    
    register_post_type('microsite', $args);
}
add_action('init', 'dt_register_microsites', 0);

