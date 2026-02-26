<?php

function dt_parse_csv_array($value) {
    if (is_array($value)) {
        $items = $value;
    } else {
        $items = array_filter(array_map('trim', explode(',', (string) $value)));
    }

    return array_values(array_filter(array_map('sanitize_text_field', $items)));
}

function dt_get_job_select_choices($field_name) {
    static $cache = null;

    if ($cache === null) {
        $cache = [];
        if (function_exists('acf_get_field_group') && function_exists('acf_get_fields')) {
            $group = acf_get_field_group('group_jobs');
            if ($group) {
                $fields = acf_get_fields($group);
                if (is_array($fields)) {
                    foreach ($fields as $field) {
                        if (!empty($field['name']) && isset($field['choices']) && is_array($field['choices'])) {
                            $cache[$field['name']] = $field['choices'];
                        }
                    }
                }
            }
        }
    }

    return isset($cache[$field_name]) && is_array($cache[$field_name]) ? $cache[$field_name] : [];
}


function dt_build_select_meta_filter($meta_key, array $values) {
    $values = array_values(array_filter(array_map('sanitize_text_field', $values)));
    if (empty($values)) {
        return [];
    }

    if (count($values) === 1) {
        return [
            'key'     => $meta_key,
            'value'   => '"' . $values[0] . '"',
            'compare' => 'LIKE',
        ];
    }

    $or = ['relation' => 'OR'];
    foreach ($values as $value) {
        $or[] = [
            'key'     => $meta_key,
            'value'   => '"' . $value . '"',
            'compare' => 'LIKE',
        ];
    }

    return $or;
}


function dt_get_available_job_cities() {
    $ids = get_posts([
        'post_type'      => 'oa_job',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);

    $cities = [];
    foreach ($ids as $post_id) {
        $city = function_exists('get_field') ? (string) get_field('job_city', $post_id) : '';
        $city = trim($city);
        if ($city !== '') {
            $cities[$city] = $city;
        }
    }

    natcasesort($cities);

    return array_values($cities);
}

function dt_get_job_card_payload($post_id) {
    $job_title = function_exists('get_field') ? (string) get_field('job_title', $post_id) : '';
    $job_reference = function_exists('get_field') ? (string) get_field('job_reference', $post_id) : '';
    $job_location = function_exists('get_field') ? (string) get_field('job_location', $post_id) : '';
    $job_city = function_exists('get_field') ? (string) get_field('job_city', $post_id) : '';
    $job_country = function_exists('get_field') ? (string) get_field('job_country', $post_id) : '';
    $job_hours = function_exists('get_field') ? (string) get_field('job_hours', $post_id) : '';

    $contract_choices = dt_get_job_select_choices('job_contract_type');
    $contract_value = function_exists('get_field') ? get_field('job_contract_type', $post_id) : [];
    $contract_values = is_array($contract_value) ? array_values(array_filter(array_map('strval', $contract_value))) : [(string) $contract_value];
    $contract_labels = [];
    foreach ($contract_values as $value) {
        if ($value === '') {
            continue;
        }
        $contract_labels[] = isset($contract_choices[$value]) ? (string) $contract_choices[$value] : $value;
    }

    $salary_hour = function_exists('get_field') ? (string) get_field('salary_per_hour', $post_id) : '';
    $salary_annum = function_exists('get_field') ? (string) get_field('salary_per_annum', $post_id) : '';
    $excerpt = function_exists('get_field') ? wp_strip_all_tags((string) get_field('job_description', $post_id)) : '';

    if ($excerpt === '') {
        $excerpt = wp_strip_all_tags((string) get_post_field('post_content', $post_id));
    }

    return [
        'id'            => (int) $post_id,
        'title'         => $job_title !== '' ? $job_title : get_the_title($post_id),
        'reference'     => $job_reference,
        'location'      => $job_location,
        'city'          => $job_city,
        'country'       => $job_country,
        'hours'         => $job_hours,
        'contract_type' => implode(', ', $contract_labels),
        'salary_hour'   => $salary_hour,
        'salary_annum'  => $salary_annum,
        'excerpt'       => wp_trim_words($excerpt, 24, '...'),
        'permalink'     => get_permalink($post_id),
    ];
}

function dt_ajax_filter_jobs() {
    $keyword = isset($_POST['keyword']) ? sanitize_text_field(wp_unslash($_POST['keyword'])) : '';
    $city = isset($_POST['city']) ? sanitize_text_field(wp_unslash($_POST['city'])) : '';
    $sectors = isset($_POST['sectors']) ? dt_parse_csv_array(wp_unslash($_POST['sectors'])) : [];
    $contract_types = isset($_POST['contract_types']) ? dt_parse_csv_array(wp_unslash($_POST['contract_types'])) : [];
    $page = isset($_POST['page']) ? max(1, absint($_POST['page'])) : 1;
    $per_page = isset($_POST['per_page']) ? max(1, min(24, absint($_POST['per_page']))) : 12;

    $meta_query = [];

    if ($city !== '') {
        $meta_query[] = [
            'key'     => 'job_city',
            'value'   => $city,
            'compare' => '=',
        ];
    }

    $sector_clause = dt_build_select_meta_filter('job_sector', $sectors);
    if (!empty($sector_clause)) {
        $meta_query[] = $sector_clause;
    }

    $contract_clause = dt_build_select_meta_filter('job_contract_type', $contract_types);
    if (!empty($contract_clause)) {
        $meta_query[] = $contract_clause;
    }

    if (count($meta_query) > 1) {
        $meta_query = array_merge(['relation' => 'AND'], $meta_query);
    }

    $query_args = [
        'post_type'      => 'oa_job',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => [
            'date'  => 'DESC',
            'title' => 'ASC',
        ],
        's'              => $keyword,
    ];

    if (!empty($meta_query)) {
        $query_args['meta_query'] = $meta_query;
    }

    $jobs_query = new WP_Query($query_args);

    $items = [];
    if ($jobs_query->have_posts()) {
        while ($jobs_query->have_posts()) {
            $jobs_query->the_post();
            $items[] = dt_get_job_card_payload(get_the_ID());
        }
        wp_reset_postdata();
    }

    wp_send_json_success([
        'items' => $items,
        'available_filters' => [
            'cities'         => dt_get_available_job_cities(),
            'sectors'        => dt_get_job_select_choices('job_sector'),
            'contract_types' => dt_get_job_select_choices('job_contract_type'),
        ],
        'pagination' => [
            'page'        => (int) $page,
            'per_page'    => (int) $per_page,
            'total_items' => (int) $jobs_query->found_posts,
            'total_pages' => (int) $jobs_query->max_num_pages,
        ],
        'filters' => [
            'keyword'        => $keyword,
            'city'           => $city,
            'sectors'        => $sectors,
            'contract_types' => $contract_types,
        ],
    ]);
}
add_action('wp_ajax_oa_filter_jobs', 'dt_ajax_filter_jobs');
add_action('wp_ajax_nopriv_oa_filter_jobs', 'dt_ajax_filter_jobs');
