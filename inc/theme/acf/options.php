<?php

add_action('acf/init', function() {
    if (!function_exists('acf_add_options_sub_page')) {
        return;
    }

    acf_add_options_sub_page([
        'page_title'  => 'UK Coverage Contacts',
        'menu_title'  => 'UK Coverage Contacts',
        'menu_slug'   => 'uk-coverage-contacts',
        'parent_slug' => 'edit.php?post_type=team_members',
        'capability'  => 'edit_posts',
        'redirect'    => false,
    ]);
});
