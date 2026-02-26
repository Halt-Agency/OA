<?php

function dt_sync_job_selects_to_taxonomies($post_id) {
    if (!is_numeric($post_id)) {
        return;
    }

    $post_id = (int) $post_id;
    if ($post_id <= 0 || get_post_type($post_id) !== 'oa_job') {
        return;
    }

    if (!function_exists('get_field')) {
        return;
    }

    $sector_values = get_field('job_sector', $post_id);
    $contract_values = get_field('job_contract_type', $post_id);

    $sector_values = is_array($sector_values) ? $sector_values : (empty($sector_values) ? [] : [$sector_values]);
    $contract_values = is_array($contract_values) ? $contract_values : (empty($contract_values) ? [] : [$contract_values]);

    $job_field_group = function_exists('acf_get_field_group') ? acf_get_field_group('group_jobs') : null;
    $job_fields = $job_field_group && function_exists('acf_get_fields') ? acf_get_fields($job_field_group) : [];

    $sector_choices = [];
    $contract_choices = [];
    if (is_array($job_fields)) {
        foreach ($job_fields as $field) {
            if (($field['name'] ?? '') === 'job_sector' && !empty($field['choices']) && is_array($field['choices'])) {
                $sector_choices = $field['choices'];
            }
            if (($field['name'] ?? '') === 'job_contract_type' && !empty($field['choices']) && is_array($field['choices'])) {
                $contract_choices = $field['choices'];
            }
        }
    }

    $sector_term_ids = [];
    foreach ($sector_values as $value) {
        $value = (string) $value;
        if ($value === '') {
            continue;
        }

        $label = isset($sector_choices[$value]) ? (string) $sector_choices[$value] : $value;
        $slug = sanitize_title($label);
        $existing = term_exists($slug, 'job_specialism');

        if (!$existing) {
            $created = wp_insert_term($label, 'job_specialism', ['slug' => $slug]);
            if (!is_wp_error($created) && !empty($created['term_id'])) {
                $sector_term_ids[] = (int) $created['term_id'];
            }
            continue;
        }

        if (is_array($existing) && !empty($existing['term_id'])) {
            $sector_term_ids[] = (int) $existing['term_id'];
        } elseif (is_int($existing)) {
            $sector_term_ids[] = $existing;
        }
    }
    wp_set_object_terms($post_id, $sector_term_ids, 'job_specialism', false);

    $contract_term_ids = [];
    foreach ($contract_values as $value) {
        $value = (string) $value;
        if ($value === '') {
            continue;
        }

        $label = isset($contract_choices[$value]) ? (string) $contract_choices[$value] : $value;
        $slug = sanitize_title($label);
        $existing = term_exists($slug, 'contract_type');

        if (!$existing) {
            $created = wp_insert_term($label, 'contract_type', ['slug' => $slug]);
            if (!is_wp_error($created) && !empty($created['term_id'])) {
                $contract_term_ids[] = (int) $created['term_id'];
            }
            continue;
        }

        if (is_array($existing) && !empty($existing['term_id'])) {
            $contract_term_ids[] = (int) $existing['term_id'];
        } elseif (is_int($existing)) {
            $contract_term_ids[] = $existing;
        }
    }
    wp_set_object_terms($post_id, $contract_term_ids, 'contract_type', false);
}
add_action('acf/save_post', 'dt_sync_job_selects_to_taxonomies', 25);
