<?php
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
