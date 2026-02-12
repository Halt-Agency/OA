<?php
/**
 * Add duplicate link to post row actions
 */
function dt_add_duplicate_post_link($actions, $post) {
    if ($post->post_type === 'post' && current_user_can('edit_posts')) {
        $url = wp_nonce_url(
            admin_url('admin.php?action=dt_duplicate_post&post=' . $post->ID),
            'dt_duplicate_post_' . $post->ID
        );
        $actions['duplicate'] = '<a href="' . esc_url($url) . '">Duplicate</a>';
    }
    return $actions;
}
add_filter('post_row_actions', 'dt_add_duplicate_post_link', 10, 2);

/**
 * Handle post duplication
 */
function dt_handle_duplicate_post() {
    // Check if post ID is provided
    if (empty($_GET['post'])) {
        wp_die('No post to duplicate.');
    }

    $post_id = absint($_GET['post']);

    // Verify nonce
    if (!wp_verify_nonce($_GET['_wpnonce'], 'dt_duplicate_post_' . $post_id)) {
        wp_die('Security check failed.');
    }

    // Check permissions
    if (!current_user_can('edit_posts')) {
        wp_die('You do not have permission to duplicate posts.');
    }

    // Get the original post
    $original_post = get_post($post_id);

    if (!$original_post || $original_post->post_type !== 'post') {
        wp_die('Invalid post.');
    }

    // Create duplicate post data
    $new_post = array(
        'post_title'     => $original_post->post_title . ' (Copy)',
        'post_content'   => $original_post->post_content,
        'post_status'    => 'draft',
        'post_type'      => $original_post->post_type,
        'post_author'    => get_current_user_id(),
        'post_parent'    => $original_post->post_parent,
        'post_excerpt'   => $original_post->post_excerpt,
        'menu_order'     => $original_post->menu_order,
        'comment_status' => $original_post->comment_status,
        'ping_status'    => $original_post->ping_status
    );

    // Insert the duplicate post
    $new_post_id = wp_insert_post($new_post);

    if (is_wp_error($new_post_id)) {
        wp_die('Failed to create duplicate post.');
    }

    // Copy taxonomies (categories, tags)
    $taxonomies = get_object_taxonomies($original_post->post_type);
    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
        wp_set_object_terms($new_post_id, $terms, $taxonomy);
    }

    // Copy post meta (including ACF fields)
    $post_meta = get_post_meta($post_id);
    foreach ($post_meta as $meta_key => $meta_values) {
        // Skip protected meta keys that shouldn't be copied
        if ($meta_key === '_edit_lock' || $meta_key === '_edit_last') {
            continue;
        }

        foreach ($meta_values as $meta_value) {
            add_post_meta($new_post_id, $meta_key, maybe_unserialize($meta_value));
        }
    }

    // Redirect to edit the new post
    wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
    exit;
}
add_action('admin_action_dt_duplicate_post', 'dt_handle_duplicate_post');
