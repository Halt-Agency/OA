<?php
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
