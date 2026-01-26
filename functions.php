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
 * Force Gutenberg editor canvas to a minimal height on pages.
 */
function dt_admin_collapse_editor_canvas($hook) {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }

    wp_register_script('dt-admin-editor-hack', false, ['wp-data'], null, true);
    wp_enqueue_script('dt-admin-editor-hack');
    wp_add_inline_script(
        'dt-admin-editor-hack',
        "(function(){\n"
        . "  if (!document.body.classList.contains('block-editor-page')) {\n"
        . "    return;\n"
        . "  }\n"
        . "  var styleId = 'dt-admin-editor-hack-style';\n"
        . "  function injectStyle(){\n"
        . "    if (document.getElementById(styleId)) {\n"
        . "      return;\n"
        . "    }\n"
        . "    var style = document.createElement('style');\n"
        . "    style.id = styleId;\n"
        . "    style.textContent = ''\n"
        . "      + 'body.block-editor-page .edit-post-layout__content{padding-top:0;display:flex;flex-direction:column;}'\n"
        . "      + 'body.block-editor-page .edit-post-layout__content .editor-styles-wrapper::after{content:none !important;height:0 !important;display:none !important;}'\n"
        . "      + 'body.block-editor-page .edit-post-layout__metaboxes{margin-top:0;flex:1 1 auto;}'\n"
        . "      + 'body.block-editor-page #postdivrich,body.block-editor-page #post-status-info{display:none;}'\n"
        . "      + 'body.block-editor-page #post-body-content{margin-bottom:0;}';\n"
        . "    document.head.appendChild(style);\n"
        . "  }\n"
        . "  function apply(){\n"
        . "    var container = document.querySelector('.components-resizable-box__container.editor-resizable-editor');\n"
        . "    if (container) {\n"
        . "      container.style.height = '1px';\n"
        . "      container.style.minHeight = '1px';\n"
        . "      container.style.maxHeight = '1px';\n"
        . "      container.style.flex = '0 0 1px';\n"
        . "      container.style.overflow = 'hidden';\n"
        . "      var inner = container.firstElementChild;\n"
        . "      if (inner) {\n"
        . "        inner.style.height = '1px';\n"
        . "        inner.style.minHeight = '1px';\n"
        . "        inner.style.maxHeight = '1px';\n"
        . "        inner.style.overflow = 'hidden';\n"
        . "      }\n"
        . "    }\n"
        . "    var visual = document.querySelector('.editor-visual-editor, .edit-post-visual-editor');\n"
        . "    if (visual) {\n"
        . "      visual.style.height = '1px';\n"
        . "      visual.style.minHeight = '1px';\n"
        . "      visual.style.maxHeight = '1px';\n"
        . "      visual.style.overflow = 'hidden';\n"
        . "    }\n"
        . "  }\n"
        . "  var raf = null;\n"
        . "  function schedule(){\n"
        . "    if (raf) {\n"
        . "      return;\n"
        . "    }\n"
        . "    raf = requestAnimationFrame(function(){\n"
        . "      raf = null;\n"
        . "      injectStyle();\n"
        . "      apply();\n"
        . "    });\n"
        . "  }\n"
        . "  schedule();\n"
        . "  var observer = new MutationObserver(schedule);\n"
        . "  observer.observe(document.body, { childList: true, subtree: true, attributes: true });\n"
        . "  if (window.wp && wp.data && wp.data.subscribe) {\n"
        . "    var last = 0;\n"
        . "    wp.data.subscribe(function(){\n"
        . "      var now = Date.now();\n"
        . "      if (now - last < 200) {\n"
        . "        return;\n"
        . "      }\n"
        . "      last = now;\n"
        . "      schedule();\n"
        . "    });\n"
        . "  }\n"
        . "})();\n"
    );
}
add_action('admin_enqueue_scripts', 'dt_admin_collapse_editor_canvas');

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

function dt_disable_comments_sitewide() {
    // Close comments on the front-end.
    add_filter('comments_open', '__return_false', 20, 2);
    add_filter('pings_open', '__return_false', 20, 2);
    // Hide existing comments from the front-end.
    add_filter('comments_array', '__return_empty_array', 10, 2);
}
add_action('init', 'dt_disable_comments_sitewide');

function dt_disable_comments_admin() {
    // Disable support for comments on all post types.
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }

    // Redirect comment management pages.
    if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'comment') {
        wp_safe_redirect(admin_url());
        exit;
    }
}
add_action('admin_init', 'dt_disable_comments_admin');

function dt_disable_comments_admin_bar() {
    global $wp_admin_bar;
    if (is_object($wp_admin_bar)) {
        $wp_admin_bar->remove_node('comments');
    }
}
add_action('admin_bar_menu', 'dt_disable_comments_admin_bar', 999);

function dt_remove_admin_menus() {
    remove_menu_page('edit-comments.php');
    remove_menu_page('edit.php?post_type=project');
    remove_menu_page('edit.php?post_type=projects');
}
add_action('admin_menu', 'dt_remove_admin_menus', 999);

function dt_rename_posts_to_resources() {
    $labels = get_post_type_object('post')->labels;
    $labels->name = 'Resources';
    $labels->singular_name = 'Resource';
    $labels->add_new = 'Add Resource';
    $labels->add_new_item = 'Add New Resource';
    $labels->edit_item = 'Edit Resource';
    $labels->new_item = 'Resource';
    $labels->view_item = 'View Resource';
    $labels->search_items = 'Search Resources';
    $labels->not_found = 'No resources found';
    $labels->not_found_in_trash = 'No resources found in Trash';
    $labels->all_items = 'All Resources';
    $labels->menu_name = 'Resources';
    $labels->name_admin_bar = 'Resource';
}
add_action('init', 'dt_rename_posts_to_resources');

function dt_rename_posts_menu_label() {
    global $menu, $submenu;
    foreach ($menu as &$item) {
        if (isset($item[2]) && $item[2] === 'edit.php') {
            $item[0] = 'Resources';
            break;
        }
    }
    if (isset($submenu['edit.php'])) {
        $submenu['edit.php'][5][0] = 'All Resources';
        $submenu['edit.php'][10][0] = 'Add Resource';
    }
}
add_action('admin_menu', 'dt_rename_posts_menu_label', 999);

function dt_custom_menu_order($menu_order) {
    if (!$menu_order) {
        return true;
    }

    $preferred = array(
        'index.php',
        'edit.php?post_type=page',
        'edit.php?post_type=oa_job',
        'edit.php',
        'edit.php?post_type=events',
        'edit.php?post_type=team_members',
        'edit.php?post_type=microsite',
        'edit.php?post_type=clients',
        'upload.php',
        'halt-tracker',
    );

    $sorted = array();
    foreach ($preferred as $item) {
        if (in_array($item, $menu_order, true)) {
            $sorted[] = $item;
        }
    }

    foreach ($menu_order as $item) {
        if (!in_array($item, $sorted, true)) {
            $sorted[] = $item;
        }
    }

    return $sorted;
}
add_filter('custom_menu_order', '__return_true');
add_filter('menu_order', 'dt_custom_menu_order');

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
 * Include Divi 5 Client Logos module extension.
 */
require_once get_stylesheet_directory() . '/inc/divi-extensions/client-logos/client-logos-extension.php';

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
