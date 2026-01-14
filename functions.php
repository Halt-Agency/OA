<?php
function dt_enqueue_assets() {
    // Parent theme style
    if (file_exists(get_template_directory() . '/style.css')) {
        $parent_theme = wp_get_theme(get_template());
        wp_enqueue_style(
            'divi-style',
            get_template_directory_uri() . '/style.css',
            [],
            $parent_theme->get('Version')
        );
    }

    // Child theme style
    if (file_exists(get_stylesheet_directory() . '/style.css')) {
        $child_theme = wp_get_theme();
        wp_enqueue_style(
            'child-style',
            get_stylesheet_uri(),
            ['divi-style'],
            $child_theme->get('Version')
        );
    }

    // Custom JS script
    $custom_js_path = get_stylesheet_directory() . '/custom.js';
    if (file_exists($custom_js_path)) {
        wp_enqueue_script(
            'custom-scripts',
            get_stylesheet_directory_uri() . '/custom.js',
            ['jquery'], 
            filemtime($custom_js_path),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'dt_enqueue_assets');

/**
 * ACF JSON - Save and load field groups from JSON files
 */
function dt_acf_json_save_point($path) {
    // Update path to child theme acf-json directory
    $path = get_stylesheet_directory() . '/acf-json';
    return $path;
}
add_filter('acf/settings/save_json', 'dt_acf_json_save_point');

function dt_acf_json_load_point($paths) {
    // Remove original path
    unset($paths[0]);
    // Add child theme path
    $paths[] = get_stylesheet_directory() . '/acf-json';
    return $paths;
}
add_filter('acf/settings/load_json', 'dt_acf_json_load_point');

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
    );
    
    register_post_type('clients', $args);
}
add_action('init', 'dt_register_clients_post_type', 0);

/**
 * Remove editor support for clients post type (if already registered)
 */
function dt_remove_clients_editor() {
    remove_post_type_support('clients', 'editor');
}
add_action('init', 'dt_remove_clients_editor', 100);

/**
 * Enable Merge Tags in Divi Code Modules
 * 
 * Usage examples:
 * {acf:field_name} - ACF field value
 * {acf:repeater_name:0:sub_field} - Repeater field (row 0, sub field)
 * {acf:repeater_name:0:image_field} - Repeater image field (returns image URL)
 * {acf:repeater_name:0:image_field:url} - Repeater image URL
 * {acf:repeater_name:0:image_field:alt} - Repeater image alt text
 * {meta:field_name} - Post meta value
 * {post_title} - Post title
 * {post_content} - Post content
 * {post_excerpt} - Post excerpt
 * {post_date} - Post date
 * {post_url} - Post URL
 * {site_url} - Site URL
 * {site_name} - Site name
 * {author_name} - Author name
 * {featured_image} - Featured image URL
 */
function dt_process_merge_tags($content) {
    if (empty($content) || !is_string($content)) {
        return $content;
    }
    
    global $post;
    
    // Get current post if available
    $current_post = $post;
    if (!$current_post && is_singular()) {
        $current_post = get_queried_object();
    }
    
    // Process merge tags
    $content = preg_replace_callback('/\{([^}]+)\}/', function($matches) use ($current_post) {
        $tag = $matches[1];
        $value = '';
        
        // ACF Fields: {acf:field_name} or {acf:repeater:row:sub_field:property}
        if (strpos($tag, 'acf:') === 0) {
            $field_path = str_replace('acf:', '', $tag);
            
            if (function_exists('get_field')) {
                // Check if this is a repeater field (has colons for row:sub_field)
                if (strpos($field_path, ':') !== false) {
                    // Repeater field: repeater_name:row_index:sub_field:property
                    $parts = explode(':', $field_path);
                    $repeater_name = $parts[0];
                    $row_index = isset($parts[1]) ? intval($parts[1]) : 0;
                    $sub_field = isset($parts[2]) ? $parts[2] : '';
                    $property = isset($parts[3]) ? $parts[3] : '';
                    
                    // Get repeater field
                    $repeater = get_field($repeater_name);
                    
                    if ($repeater && is_array($repeater) && isset($repeater[$row_index])) {
                        $row = $repeater[$row_index];
                        
                        if ($sub_field && isset($row[$sub_field])) {
                            $sub_value = $row[$sub_field];
                            
                            // Handle image fields in repeaters
                            if (is_array($sub_value) && isset($sub_value['url'])) {
                                // Image field - return specific property or URL by default
                                if ($property && isset($sub_value[$property])) {
                                    $value = $sub_value[$property];
                                } else {
                                    $value = $sub_value['url']; // Default to URL
                                }
                            } else {
                                $value = $sub_value;
                            }
                        }
                    }
                } else {
                    // Regular ACF field
                    $field_value = get_field($field_path);
                    if (is_array($field_value)) {
                        // Handle image fields
                        if (isset($field_value['url'])) {
                            $value = $field_value['url'];
                        } else {
                            $value = implode(', ', $field_value);
                        }
                    } else {
                        $value = $field_value;
                    }
                }
            }
        }
        // Post Meta: {meta:field_name}
        elseif (strpos($tag, 'meta:') === 0) {
            $field_name = str_replace('meta:', '', $tag);
            if ($current_post) {
                $value = get_post_meta($current_post->ID, $field_name, true);
            }
        }
        // Post Title: {post_title}
        elseif ($tag === 'post_title') {
            if ($current_post) {
                $value = get_the_title($current_post->ID);
            }
        }
        // Post Content: {post_content}
        elseif ($tag === 'post_content') {
            if ($current_post) {
                $value = apply_filters('the_content', $current_post->post_content);
            }
        }
        // Post Excerpt: {post_excerpt}
        elseif ($tag === 'post_excerpt') {
            if ($current_post) {
                $value = get_the_excerpt($current_post->ID);
            }
        }
        // Post Date: {post_date}
        elseif ($tag === 'post_date') {
            if ($current_post) {
                $value = get_the_date('', $current_post->ID);
            }
        }
        // Post URL: {post_url}
        elseif ($tag === 'post_url') {
            if ($current_post) {
                $value = get_permalink($current_post->ID);
            }
        }
        // Site URL: {site_url}
        elseif ($tag === 'site_url') {
            $value = home_url();
        }
        // Site Name: {site_name}
        elseif ($tag === 'site_name') {
            $value = get_bloginfo('name');
        }
        // Author Name: {author_name}
        elseif ($tag === 'author_name') {
            if ($current_post) {
                $value = get_the_author_meta('display_name', $current_post->post_author);
            }
        }
        // Featured Image: {featured_image}
        elseif ($tag === 'featured_image') {
            if ($current_post) {
                $image_id = get_post_thumbnail_id($current_post->ID);
                if ($image_id) {
                    $value = wp_get_attachment_image_url($image_id, 'full');
                }
            }
        }
        // Direct ACF field (without prefix): {field_name}
        else {
            // Try as ACF field first
            if (function_exists('get_field')) {
                $field_value = get_field($tag);
                if ($field_value !== false && $field_value !== null) {
                    if (is_array($field_value)) {
                        if (isset($field_value['url'])) {
                            $value = $field_value['url'];
                        } else {
                            $value = implode(', ', $field_value);
                        }
                    } else {
                        $value = $field_value;
                    }
                }
            }
            
            // If no ACF value, try post meta
            if (empty($value) && $current_post) {
                $value = get_post_meta($current_post->ID, $tag, true);
            }
        }
        
        return $value !== '' ? $value : $matches[0]; // Return original tag if no value found
    }, $content);
    
    return $content;
}

// Filter Divi code module output - Try multiple hooks for compatibility
add_filter('et_pb_module_content', 'dt_process_merge_tags', 10, 1);
add_filter('et_module_shortcode_output', 'dt_process_merge_tags', 10, 1);
add_filter('et_builder_render_layout_content', 'dt_process_merge_tags', 10, 1);

// Process shortcodes in Divi code modules
add_filter('et_pb_module_content', 'do_shortcode', 11, 1);
add_filter('et_module_shortcode_output', 'do_shortcode', 11, 1);

// Filter the_content for code modules (Divi 5 compatibility)
add_filter('the_content', function($content) {
    // Only process if we're in a Divi context
    if (function_exists('et_is_builder_plugin_active') || defined('ET_BUILDER_PLUGIN_ACTIVE')) {
        $content = dt_process_merge_tags($content);
        $content = do_shortcode($content);
    }
    return $content;
}, 20);

// Alternative: Hook into Divi's code module shortcode rendering
add_filter('et_pb_code_content', 'dt_process_merge_tags', 10, 1);
add_filter('et_pb_code_content', 'do_shortcode', 11, 1);

// Divi 5 specific hook
add_filter('et_builder_module_content', function($content, $props, $attrs, $render_slug) {
    if ($render_slug === 'et_pb_code') {
        $content = dt_process_merge_tags($content);
        $content = do_shortcode($content);
    }
    return $content;
}, 10, 4);

// Inject ACF data into page for JavaScript access
add_action('wp_footer', function() {
    if (!function_exists('get_field')) {
        return;
    }
    
    global $post;
    $acf_data = array();
    $post_id = 0;
    $debug_info = array();
    
    // Handle preview pages first (they might not be detected as singular)
    if (isset($_GET['preview_id']) && is_numeric($_GET['preview_id'])) {
        $post_id = intval($_GET['preview_id']);
        $debug_info[] = 'Preview ID detected: ' . $post_id;
        $acf_data = get_fields($post_id);
        $debug_info[] = 'Fields retrieved: ' . (is_array($acf_data) ? count($acf_data) . ' fields' : 'false/empty');
    }
    // Get post ID from various contexts
    elseif (is_singular() && $post) {
        $post_id = $post->ID;
        $debug_info[] = 'Singular post detected: ' . $post_id;
        $acf_data = get_fields($post_id);
        $debug_info[] = 'Fields retrieved: ' . (is_array($acf_data) ? count($acf_data) . ' fields' : 'false/empty');
    } elseif (is_home() || is_front_page()) {
        // For home/front page, try to get the page ID
        $page_id = get_option('page_for_posts');
        if (is_front_page()) {
            $page_id = get_option('page_on_front');
        }
        if ($page_id) {
            $post_id = $page_id;
            $debug_info[] = 'Front/Home page detected: ' . $post_id;
            $acf_data = get_fields($post_id);
            $debug_info[] = 'Fields retrieved: ' . (is_array($acf_data) ? count($acf_data) . ' fields' : 'false/empty');
        }
    }
    
    // Convert false to empty array for JSON encoding
    if ($acf_data === false) {
        $acf_data = array();
    }
    
    // Always output the data (even if empty) so JavaScript knows it's available
    echo '<script type="text/javascript">';
    echo 'window.dtACFData = ' . json_encode($acf_data) . ';';
    echo 'window.dtPostId = ' . intval($post_id) . ';';
    echo 'window.dtAjaxUrl = "' . admin_url('admin-ajax.php') . '";';
    // Add debug info in development
    if (defined('WP_DEBUG') && WP_DEBUG) {
        echo 'window.dtACFDebug = ' . json_encode($debug_info) . ';';
        echo 'console.log("ACF Data Injection Debug:", ' . json_encode($debug_info) . ');';
        echo 'console.log("ACF Data:", window.dtACFData);';
    }
    echo '</script>';
});

/**
 * Custom AJAX endpoint to fetch ACF data
 * This works even if ACF REST API is not enabled
 */
add_action('wp_ajax_dt_get_acf_data', 'dt_ajax_get_acf_data');
add_action('wp_ajax_nopriv_dt_get_acf_data', 'dt_ajax_get_acf_data');
function dt_ajax_get_acf_data() {
    if (!function_exists('get_fields')) {
        wp_send_json_error(array('message' => 'ACF not available'));
        return;
    }
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    if (!$post_id) {
        wp_send_json_error(array('message' => 'No post ID provided'));
        return;
    }
    
    $acf_data = get_fields($post_id);
    
    if ($acf_data === false) {
        $acf_data = array();
    }
    
    wp_send_json_success(array(
        'acf_data' => $acf_data,
        'post_id' => $post_id,
        'field_count' => is_array($acf_data) ? count($acf_data) : 0,
        'field_keys' => is_array($acf_data) ? array_keys($acf_data) : array()
    ));
}

/**
 * Include Marquee Carousel functionality
 */
require_once get_stylesheet_directory() . '/inc/marquee-carousel.php';

/**
 * Include RoleCall by Halt - Tracker RMS Integration
 */
require_once get_stylesheet_directory() . '/inc/rolecall-halt/loader.php';

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
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array('slug' => 'team-members'),
    );
    
    register_post_type('team_members', $args);
}
add_action('init', 'dt_register_team_members', 0);

/**
 * Register Jobs Post Type
 */
function dt_register_jobs() {
    $labels = array(
        'name'                  => 'Jobs',
        'singular_name'         => 'Job',
        'menu_name'             => 'Jobs',
        'name_admin_bar'        => 'Job',
        'archives'              => 'Job Archives',
        'attributes'            => 'Job Attributes',
        'parent_item_colon'     => 'Parent Job:',
        'all_items'             => 'All Jobs',
        'add_new_item'          => 'Add New Job',
        'add_new'               => 'Add New',
        'new_item'              => 'New Job',
        'edit_item'             => 'Edit Job',
        'update_item'           => 'Update Job',
        'view_item'             => 'View Job',
        'view_items'            => 'View Jobs',
        'search_items'          => 'Search Jobs',
        'not_found'             => 'Not found',
        'not_found_in_trash'    => 'Not found in Trash',
        'featured_image'        => 'Featured Image',
        'set_featured_image'    => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image'    => 'Use as featured image',
        'insert_into_item'      => 'Insert into job',
        'uploaded_to_this_item' => 'Uploaded to this job',
        'items_list'            => 'Jobs list',
        'items_list_navigation' => 'Jobs list navigation',
        'filter_items_list'     => 'Filter jobs list',
    );
    
    $args = array(
        'label'                 => 'Job',
        'description'           => 'Jobs post type',
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 22,
        'menu_icon'             => 'dashicons-portfolio',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array('slug' => 'jobs'),
    );
    
    register_post_type('jobs', $args);
}
add_action('init', 'dt_register_jobs', 0);

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

/**
 * Create pages on theme activation
 * Based on setup.json structure
 */
function dt_create_pages_on_activation() {
    // Top level pages
    $pages = array(
        'Home' => 0,
        'About' => 0,
        'Work for us' => 0,
        'Candidates' => 0,
        'Clients' => 0,
        'Contact' => 0,
        'Register Brief' => 0,
        'Register CV' => 0,
        'Events and networking' => 0,
        'Resource Hub' => 0,
    );
    
    // Create top level pages
    foreach ($pages as $title => $parent_id) {
        dt_create_page_if_not_exists($title, $parent_id);
    }
    
    // Locations parent and children
    $locations_id = dt_create_page_if_not_exists('Locations', 0);
    $location_children = array('Buckinghamshire', 'Bedfordshire', 'Cambridgeshire', 'Hertfordshire', 'North London', 'Onsite');
    foreach ($location_children as $child) {
        dt_create_page_if_not_exists($child, $locations_id);
    }
    
    // Specialisms parent and children
    $specialisms_id = dt_create_page_if_not_exists('Specialisms', 0);
    $specialism_children = array('Office & Commercial', 'Warehousing & Distribution', 'Manufacturing', 'Events & Hospitality', 'Engineering');
    foreach ($specialism_children as $child) {
        dt_create_page_if_not_exists($child, $specialisms_id);
    }
    
    // Solutions parent and children
    $solutions_id = dt_create_page_if_not_exists('Solutions', 0);
    $solution_children = array('Permanent', 'Temporary', 'Embedded', 'Executive Search', 'High-Volume Perm', 'High-Volume Temp');
    foreach ($solution_children as $child) {
        dt_create_page_if_not_exists($child, $solutions_id);
    }
    
    // More parent and children
    $more_id = dt_create_page_if_not_exists('More', 0);
    $more_children = array('Employer Branding', 'On-Boarding & Training', 'Outplacement', 'Microsites');
    foreach ($more_children as $child) {
        dt_create_page_if_not_exists($child, $more_id);
    }
    
    flush_rewrite_rules();
}

/**
 * Helper function to create a page if it doesn't exist
 * Checks by both slug and title to prevent duplicates
 */
function dt_create_page_if_not_exists($title, $parent_id = 0) {
    $page_slug = sanitize_title($title);
    
    // First check by slug
    $existing_page = get_page_by_path($page_slug);
    
    // Also check by exact title to catch duplicates with different slugs
    if (!$existing_page) {
        global $wpdb;
        $existing_page_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} 
             WHERE post_type = 'page' 
             AND post_status = 'publish' 
             AND post_title = %s 
             LIMIT 1",
            $title
        ));
        
        if ($existing_page_id) {
            $existing_page = get_post($existing_page_id);
        }
    }
    
    if ($existing_page) {
        // Update parent if needed
        if ($existing_page->post_parent != $parent_id && $parent_id > 0) {
            wp_update_post(array(
                'ID' => $existing_page->ID,
                'post_parent' => $parent_id
            ));
        }
        return $existing_page->ID;
    }
    
    // Page doesn't exist, create it
    $page_data = array(
        'post_title'    => $title,
        'post_name'     => $page_slug,
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_parent'   => $parent_id,
        'post_author'   => 1,
    );
    
    $page_id = wp_insert_post($page_data);
    
    return $page_id && !is_wp_error($page_id) ? $page_id : 0;
}

/**
 * Run page creation on theme activation
 */
add_action('after_switch_theme', 'dt_create_pages_on_activation');

/**
 * TEMPORARY: Find duplicate pages by similar slugs and matching titles
 * COMMENTED OUT - Cleanup completed
 */
if (false) { // Disable duplicate finder script
function dt_find_duplicate_pages() {
    global $wpdb;
    
    // Get all published pages
    $pages = $wpdb->get_results(
        "SELECT ID, post_title, post_name, post_date, post_parent
         FROM {$wpdb->posts} 
         WHERE post_type = 'page' 
         AND post_status = 'publish'
         ORDER BY post_title, post_date ASC"
    );
    
    $duplicates_by_title = array();
    $duplicates_by_slug = array();
    
    // Group by title (exact match)
    foreach ($pages as $page) {
        $title_key = strtolower(trim($page->post_title));
        if (!isset($duplicates_by_title[$title_key])) {
            $duplicates_by_title[$title_key] = array();
        }
        $duplicates_by_title[$title_key][] = $page;
    }
    
    // Group by slug (similar slugs)
    foreach ($pages as $page) {
        $slug_key = strtolower(trim($page->post_name));
        if (!isset($duplicates_by_slug[$slug_key])) {
            $duplicates_by_slug[$slug_key] = array();
        }
        $duplicates_by_slug[$slug_key][] = $page;
    }
    
    // Filter to only show duplicates
    $title_duplicates = array_filter($duplicates_by_title, function($group) {
        return count($group) > 1;
    });
    
    $slug_duplicates = array_filter($duplicates_by_slug, function($group) {
        return count($group) > 1;
    });
    
    // Also check for similar slugs (e.g., "about" and "about-2")
    $similar_slugs = array();
    foreach ($pages as $page) {
        $base_slug = preg_replace('/-\d+$/', '', $page->post_name);
        if (!isset($similar_slugs[$base_slug])) {
            $similar_slugs[$base_slug] = array();
        }
        $similar_slugs[$base_slug][] = $page;
    }
    
    $similar_slug_duplicates = array_filter($similar_slugs, function($group) {
        return count($group) > 1;
    });
    
    return array(
        'by_title' => $title_duplicates,
        'by_slug' => $slug_duplicates,
        'by_similar_slug' => $similar_slug_duplicates,
        'total_pages' => count($pages)
    );
}

/**
 * Add admin menu for duplicate checker
 */
function dt_add_duplicate_checker_menu() {
    add_management_page(
        'Find Duplicate Pages',
        'Find Duplicate Pages',
        'manage_options',
        'dt-find-duplicates',
        'dt_find_duplicates_page'
    );
}
// add_action('admin_menu', 'dt_add_duplicate_checker_menu');

/**
 * Admin page for finding duplicates
 */
function dt_find_duplicates_page() {
    $results = dt_find_duplicate_pages();
    
    ?>
    <div class="wrap">
        <h1>Find Duplicate Pages</h1>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Summary</h2>
            <p><strong>Total Pages:</strong> <?php echo esc_html($results['total_pages']); ?></p>
            <p><strong>Duplicate Titles:</strong> <?php echo esc_html(count($results['by_title'])); ?> group(s)</p>
            <p><strong>Duplicate Slugs:</strong> <?php echo esc_html(count($results['by_slug'])); ?> group(s)</p>
            <p><strong>Similar Slugs:</strong> <?php echo esc_html(count($results['by_similar_slug'])); ?> group(s)</p>
        </div>
        
        <?php if (!empty($results['by_title'])): ?>
        <div class="card" style="margin-top: 20px;">
            <h2>Pages with Duplicate Titles</h2>
            <?php foreach ($results['by_title'] as $title => $pages): ?>
                <h3><?php echo esc_html($title); ?> (<?php echo count($pages); ?> pages)</h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Date</th>
                            <th>Parent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $index => $page): ?>
                            <tr style="<?php echo $index === 0 ? 'background-color: #d4edda;' : ''; ?>">
                                <td><?php echo esc_html($page->ID); ?></td>
                                <td><strong><?php echo esc_html($page->post_title); ?></strong></td>
                                <td><?php echo esc_html($page->post_name); ?></td>
                                <td><?php echo esc_html($page->post_date); ?></td>
                                <td><?php 
                                    if ($page->post_parent > 0) {
                                        $parent = get_post($page->post_parent);
                                        echo $parent ? esc_html($parent->post_title) : 'N/A';
                                    } else {
                                        echo '—';
                                    }
                                ?></td>
                                <td>
                                    <a href="<?php echo admin_url('post.php?post=' . $page->ID . '&action=edit'); ?>">Edit</a> | 
                                    <a href="<?php echo get_permalink($page->ID); ?>" target="_blank">View</a>
                                    <?php if ($index > 0): ?>
                                        | <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=dt_delete_page&page_id=' . $page->ID), 'delete_page_' . $page->ID); ?>" 
                                             onclick="return confirm('Are you sure you want to delete this page? This cannot be undone.');"
                                             style="color: #dc3545;">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><em>Green row = oldest page (kept), others are duplicates</em></p>
                <hr style="margin: 20px 0;">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($results['by_similar_slug'])): ?>
        <div class="card" style="margin-top: 20px;">
            <h2>Pages with Similar Slugs</h2>
            <?php foreach ($results['by_similar_slug'] as $base_slug => $pages): ?>
                <h3><?php echo esc_html($base_slug); ?> (<?php echo count($pages); ?> pages)</h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $index => $page): ?>
                            <tr style="<?php echo $index === 0 ? 'background-color: #d4edda;' : ''; ?>">
                                <td><?php echo esc_html($page->ID); ?></td>
                                <td><strong><?php echo esc_html($page->post_title); ?></strong></td>
                                <td><?php echo esc_html($page->post_name); ?></td>
                                <td><?php echo esc_html($page->post_date); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('post.php?post=' . $page->ID . '&action=edit'); ?>">Edit</a> | 
                                    <a href="<?php echo get_permalink($page->ID); ?>" target="_blank">View</a>
                                    <?php if ($index > 0): ?>
                                        | <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=dt_delete_page&page_id=' . $page->ID), 'delete_page_' . $page->ID); ?>" 
                                             onclick="return confirm('Are you sure you want to delete this page? This cannot be undone.');"
                                             style="color: #dc3545;">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><em>Green row = oldest page (kept), others are duplicates</em></p>
                <hr style="margin: 20px 0;">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if (empty($results['by_title']) && empty($results['by_similar_slug'])): ?>
            <div class="notice notice-success">
                <p><strong>No duplicate pages found!</strong></p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Handle page deletion
 */
function dt_handle_delete_page() {
    if (!current_user_can('delete_pages')) {
        wp_die('You do not have permission to delete pages.');
    }
    
    $page_id = isset($_GET['page_id']) ? intval($_GET['page_id']) : 0;
    
    if (!$page_id) {
        wp_die('Invalid page ID.');
    }
    
    check_admin_referer('delete_page_' . $page_id);
    
    wp_delete_post($page_id, true); // true = force delete
    
    wp_redirect(admin_url('tools.php?page=dt-find-duplicates&deleted=1'));
    exit;
}
add_action('admin_post_dt_delete_page', 'dt_handle_delete_page');
} // End of disabled duplicate finder script
