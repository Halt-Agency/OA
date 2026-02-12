<?php
/**
 * Register Clients Post Type
 */
function dt_register_clients_post_type() {
    $labels = array(
        'name'                  => 'Clients',
        'singular_name'        => 'Client',
        'menu_name'             => 'Clients',
        'name_admin_bar'        => 'Client',
        'archives'              => 'Client Archives',
        'attributes'            => 'Client Attributes',
        'parent_item_colon'     => 'Parent Client:',
        'all_items'             => 'All Clients',
        'add_new_item'          => 'Add New Client',
        'add_new'               => 'Add New',
        'new_item'              => 'New Client',
        'edit_item'             => 'Edit Client',
        'update_item'           => 'Update Client',
        'view_item'             => 'View Client',
        'view_items'            => 'View Clients',
        'search_items'          => 'Search Client',
        'not_found'             => 'Not found',
        'not_found_in_trash'    => 'Not found in Trash',
        'featured_image'        => 'Featured Image',
        'set_featured_image'    => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image'    => 'Use as featured image',
        'insert_into_item'      => 'Insert into client',
        'uploaded_to_this_item' => 'Uploaded to this client',
        'items_list'            => 'Clients list',
        'items_list_navigation' => 'Clients list navigation',
        'filter_items_list'     => 'Filter clients list',
    );
    
    $args = array(
        'label'                 => 'Client',
        'description'           => 'Client post type',
        'labels'                => $labels,
        'supports'              => array('title', 'thumbnail'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-format-image',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'      => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'taxonomies'            => array( 'client_category' ),
    );
    
    register_post_type('clients', $args);
}
add_action('init', 'dt_register_clients_post_type', 0);

/**
 * Register Client taxonomy.
 */
function dt_register_client_taxonomy() {
    $labels = array(
        'name'              => 'Client Categories',
        'singular_name'     => 'Client Category',
        'search_items'      => 'Search Client Categories',
        'all_items'         => 'All Client Categories',
        'parent_item'       => 'Parent Client Category',
        'parent_item_colon' => 'Parent Client Category:',
        'edit_item'         => 'Edit Client Category',
        'update_item'       => 'Update Client Category',
        'add_new_item'      => 'Add New Client Category',
        'new_item_name'     => 'New Client Category',
        'menu_name'         => 'Client Categories',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'client-category' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'client_category', array( 'clients' ), $args );
}
add_action( 'init', 'dt_register_client_taxonomy', 0 );

/**
 * Allow REST filtering of Clients by client_category taxonomy.
 */
function dt_rest_clients_allow_tax_query( $params ) {
    $params['client_category'] = array(
        'description'       => 'Limit results to client_category term slugs.',
        'type'              => 'array',
        'items'             => array(
            'type' => 'string',
        ),
        'sanitize_callback' => 'wp_parse_slug_list',
    );

    return $params;
}
add_filter( 'rest_clients_collection_params', 'dt_rest_clients_allow_tax_query' );

function dt_rest_clients_apply_tax_query( $args, $request ) {
    $terms = $request['client_category'] ?? [];
    if ( ! empty( $terms ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'client_category',
                'field'    => 'slug',
                'terms'    => is_array( $terms ) ? $terms : wp_parse_slug_list( (string) $terms ),
            ),
        );
    }

    return $args;
}
add_filter( 'rest_clients_query', 'dt_rest_clients_apply_tax_query', 10, 2 );

