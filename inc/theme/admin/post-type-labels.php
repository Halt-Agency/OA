<?php

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

function dt_force_show_acf_metaboxes($hidden, $screen) {
    if (!$screen || $screen->base !== 'post' || $screen->post_type !== 'page') {
        return $hidden;
    }

    if (!is_array($hidden)) {
        return $hidden;
    }

    return array_values(array_filter($hidden, function($box_id) {
        return strpos((string) $box_id, 'acf-group_') !== 0;
    }));
}
add_filter('hidden_meta_boxes', 'dt_force_show_acf_metaboxes', 10, 2);
