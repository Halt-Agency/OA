<?php

function dt_team_template_globals_field_names() {
    return [
        'landing_heading',
        'get_to_know_heading',
        'role_at_oa_heading',
        'why_oa_heading',
        'biggest_work_win_heading',
        'office_reputation_heading',
        'back_to_team_button_text',
        'roles_im_hiring_for_text',
        'view_all_jobs_button_text',
        'view_all_jobs_button_link',
        'insights_heading_prefix',
        'insights_heading_emphasis',
        'insights_heading_suffix',
        'insights_button_text',
        'insights_button_link',
    ];
}

function dt_team_template_globals_option_values() {
    $values = [];

    foreach (dt_team_template_globals_field_names() as $field_name) {
        $value = function_exists('get_field') ? get_field($field_name, 'option') : get_option($field_name, '');
        if (is_array($value) && isset($value['url'])) {
            $value = $value['url'];
        }
        $values[$field_name] = is_scalar($value) ? (string) $value : '';
    }

    return $values;
}

function dt_apply_team_template_globals_to_member($post_id, $values) {
    foreach ($values as $field_name => $value) {
        if (function_exists('update_field')) {
            update_field($field_name, $value, $post_id);
        } else {
            update_post_meta($post_id, $field_name, $value);
        }
    }
}

function dt_sync_team_template_globals_mirror($post_id) {
    if (!is_string($post_id) || strpos($post_id, 'options') !== 0) {
        return;
    }

    $values = dt_team_template_globals_option_values();

    $team_member_ids = get_posts([
        'post_type'      => 'team_members',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);

    foreach ($team_member_ids as $team_member_id) {
        dt_apply_team_template_globals_to_member($team_member_id, $values);
    }
}
add_action('acf/save_post', 'dt_sync_team_template_globals_mirror', 30);

function dt_seed_team_template_globals_on_member_save($post_id, $post, $update) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }
    if (!$post || $post->post_type !== 'team_members') {
        return;
    }

    dt_apply_team_template_globals_to_member($post_id, dt_team_template_globals_option_values());
}
add_action('save_post_team_members', 'dt_seed_team_template_globals_on_member_save', 20, 3);

function dt_is_team_member_admin_screen() {
    if (!is_admin()) {
        return false;
    }

    if (function_exists('get_current_screen')) {
        $screen = get_current_screen();
        if ($screen && isset($screen->post_type) && $screen->post_type === 'team_members') {
            return true;
        }
    }

    if (isset($_GET['post_type']) && $_GET['post_type'] === 'team_members') {
        return true;
    }

    if (isset($_GET['post'])) {
        return get_post_type((int) $_GET['post']) === 'team_members';
    }

    return false;
}

function dt_lock_team_template_globals_mirror_fields($field) {
    if (!is_array($field) || empty($field['key'])) {
        return $field;
    }
    if (strpos($field['key'], 'field_team_mirror_') !== 0) {
        return $field;
    }
    if (!dt_is_team_member_admin_screen()) {
        return $field;
    }
    if (isset($field['type']) && $field['type'] === 'tab') {
        return $field;
    }

    $field['readonly'] = 1;
    $field['disabled'] = 1;
    $field['instructions'] = trim(($field['instructions'] ?? '') . ' Locked here. Edit Team Page Globals instead.');

    return $field;
}
add_filter('acf/prepare_field', 'dt_lock_team_template_globals_mirror_fields', 20);

function dt_hide_team_template_globals_mirror_metabox() {
    if (!dt_is_team_member_admin_screen()) {
        return;
    }
    ?>
    <style>
        #acf-group_team_member_template_globals_mirror,
        .acf-postbox[data-key="group_team_member_template_globals_mirror"],
        .acf-field-group[data-key="group_team_member_template_globals_mirror"] {
            display: none !important;
        }
    </style>
    <script>
        (function() {
            function hideMirrorBox() {
                var selectors = [
                    '#acf-group_team_member_template_globals_mirror',
                    '.acf-postbox[data-key="group_team_member_template_globals_mirror"]',
                    '.acf-field-group[data-key="group_team_member_template_globals_mirror"]'
                ];

                selectors.forEach(function(selector) {
                    document.querySelectorAll(selector).forEach(function(el) {
                        el.style.display = 'none';
                    });
                });

                document.querySelectorAll('.acf-postbox').forEach(function(box) {
                    var titleEl = box.querySelector('h2, .hndle');
                    var title = titleEl ? titleEl.textContent : '';
                    if (title && title.indexOf('Team Template Globals (Mirror)') !== -1) {
                        box.style.display = 'none';
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', hideMirrorBox);
            } else {
                hideMirrorBox();
            }
            setTimeout(hideMirrorBox, 200);
            setTimeout(hideMirrorBox, 800);
        })();
    </script>
    <?php
}
add_action('acf/input/admin_head', 'dt_hide_team_template_globals_mirror_metabox');
add_action('admin_head', 'dt_hide_team_template_globals_mirror_metabox');

function dt_collapse_team_template_globals_mirror_metabox() {
    if (!dt_is_team_member_admin_screen()) {
        return;
    }
    ?>
    <script>
        (function() {
            function collapseMirrorBox() {
                var box = document.querySelector('#acf-group_team_member_template_globals_mirror')
                    || document.querySelector('.acf-postbox[data-key="group_team_member_template_globals_mirror"]');
                if (!box) {
                    return;
                }
                box.classList.add('closed');
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', collapseMirrorBox);
            } else {
                collapseMirrorBox();
            }
        })();
    </script>
    <?php
}
add_action('acf/input/admin_footer', 'dt_collapse_team_template_globals_mirror_metabox');
