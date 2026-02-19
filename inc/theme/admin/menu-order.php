<?php

function dt_remove_admin_menus() {
    remove_menu_page('edit-comments.php');
    remove_menu_page('edit.php?post_type=project');
    remove_menu_page('edit.php?post_type=projects');
}
add_action('admin_menu', 'dt_remove_admin_menus', 999);

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
