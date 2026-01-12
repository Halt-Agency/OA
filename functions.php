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
        'menu_icon'             => 'dashicons-groups',
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
 * Marquee Carousel Shortcode
 * Usage: [marquee_carousel repeater="test_repeater" image="image" speed="30"]
 */
function dt_marquee_carousel_shortcode($atts) {
    $atts = shortcode_atts(array(
        'repeater' => 'test_repeater',
        'image' => 'image',
        'speed' => '30',
        'max_width' => '200',
    ), $atts);
    
    $repeater = get_field($atts['repeater']);
    $image_field = $atts['image'];
    
    if (!$repeater || !is_array($repeater)) {
        return '';
    }
    
    ob_start();
    ?>
    <style>
    .marquee-container-<?php echo esc_attr($atts['repeater']); ?> {
        width: 100%;
        overflow: hidden;
        position: relative;
        padding: 40px 0;
    }

    .marquee-wrapper-<?php echo esc_attr($atts['repeater']); ?> {
        display: flex;
        width: 200%;
    }

    .marquee-track-<?php echo esc_attr($atts['repeater']); ?> {
        display: flex;
        gap: 30px;
        animation: marquee-scroll-<?php echo esc_attr($atts['repeater']); ?> <?php echo esc_attr($atts['speed']); ?>s linear infinite;
        width: 50%;
    }

    .marquee-track-duplicate-<?php echo esc_attr($atts['repeater']); ?> {
        display: flex;
        gap: 30px;
        width: 50%;
    }

    .marquee-track-<?php echo esc_attr($atts['repeater']); ?>:hover,
    .marquee-track-duplicate-<?php echo esc_attr($atts['repeater']); ?>:hover {
        animation-play-state: paused;
    }

    .marquee-item-<?php echo esc_attr($atts['repeater']); ?> {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .marquee-item-<?php echo esc_attr($atts['repeater']); ?> img {
        max-width: <?php echo esc_attr($atts['max_width']); ?>px;
        height: auto;
        object-fit: contain;
        filter: grayscale(100%);
        opacity: 0.7;
        transition: all 0.3s ease;
    }

    .marquee-item-<?php echo esc_attr($atts['repeater']); ?> img:hover {
        filter: grayscale(0%);
        opacity: 1;
        transform: scale(1.05);
    }

    @keyframes marquee-scroll-<?php echo esc_attr($atts['repeater']); ?> {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-100%);
        }
    }

    @media (max-width: 768px) {
        .marquee-item-<?php echo esc_attr($atts['repeater']); ?> img {
            max-width: 150px;
        }
        
        .marquee-track-<?php echo esc_attr($atts['repeater']); ?> {
            gap: 20px;
        }
    }
    </style>

    <div class="marquee-container-<?php echo esc_attr($atts['repeater']); ?>">
        <div class="marquee-wrapper-<?php echo esc_attr($atts['repeater']); ?>">
            <div class="marquee-track-<?php echo esc_attr($atts['repeater']); ?>">
                <?php 
                foreach ($repeater as $row) {
                    if (isset($row[$image_field])) {
                        $image = $row[$image_field];
                        // Handle both array format and ID format
                        if (is_array($image) && isset($image['url'])) {
                            $image_url = $image['url'];
                            $image_alt = isset($image['alt']) ? $image['alt'] : '';
                            $image_title = isset($image['title']) ? $image['title'] : '';
                        } elseif (is_numeric($image)) {
                            $image_url = wp_get_attachment_image_url($image, 'full');
                            $image_alt = get_post_meta($image, '_wp_attachment_image_alt', true);
                            $image_title = get_the_title($image);
                        } else {
                            continue;
                        }
                        ?>
                        <div class="marquee-item-<?php echo esc_attr($atts['repeater']); ?>">
                            <img src="<?php echo esc_url($image_url); ?>" 
                                 alt="<?php echo esc_attr($image_alt); ?>"
                                 title="<?php echo esc_attr($image_title); ?>">
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <div class="marquee-track-duplicate-<?php echo esc_attr($atts['repeater']); ?>">
                <?php 
                // Duplicate for seamless loop
                foreach ($repeater as $row) {
                    if (isset($row[$image_field])) {
                        $image = $row[$image_field];
                        if (is_array($image) && isset($image['url'])) {
                            $image_url = $image['url'];
                            $image_alt = isset($image['alt']) ? $image['alt'] : '';
                            $image_title = isset($image['title']) ? $image['title'] : '';
                        } elseif (is_numeric($image)) {
                            $image_url = wp_get_attachment_image_url($image, 'full');
                            $image_alt = get_post_meta($image, '_wp_attachment_image_alt', true);
                            $image_title = get_the_title($image);
                        } else {
                            continue;
                        }
                        ?>
                        <div class="marquee-item-<?php echo esc_attr($atts['repeater']); ?>">
                            <img src="<?php echo esc_url($image_url); ?>" 
                                 alt="<?php echo esc_attr($image_alt); ?>"
                                 title="<?php echo esc_attr($image_title); ?>">
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('marquee_carousel', 'dt_marquee_carousel_shortcode');