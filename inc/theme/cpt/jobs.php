<?php

function dt_override_oa_job_post_type_args($args, $post_type) {
    if ($post_type !== 'oa_job') {
        return $args;
    }

    $args['has_archive'] = false;

    if (!isset($args['rewrite']) || !is_array($args['rewrite'])) {
        $args['rewrite'] = [];
    }

    $args['rewrite']['slug'] = 'jobs';

    return $args;
}
add_filter('register_post_type_args', 'dt_override_oa_job_post_type_args', 20, 2);
