<?php

function dt_register_job_contract_type_taxonomy() {
    $labels = [
        'name'              => 'Contract Types',
        'singular_name'     => 'Contract Type',
        'search_items'      => 'Search Contract Types',
        'all_items'         => 'All Contract Types',
        'edit_item'         => 'Edit Contract Type',
        'update_item'       => 'Update Contract Type',
        'add_new_item'      => 'Add New Contract Type',
        'new_item_name'     => 'New Contract Type Name',
        'menu_name'         => 'Contract Types',
    ];

    register_taxonomy('contract_type', ['oa_job'], [
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'contract-type'],
    ]);
}
add_action('init', 'dt_register_job_contract_type_taxonomy', 0);

function dt_register_job_specialism_taxonomy() {
    $labels = [
        'name'              => 'Specialisms',
        'singular_name'     => 'Specialism',
        'search_items'      => 'Search Specialisms',
        'all_items'         => 'All Specialisms',
        'edit_item'         => 'Edit Specialism',
        'update_item'       => 'Update Specialism',
        'add_new_item'      => 'Add New Specialism',
        'new_item_name'     => 'New Specialism Name',
        'menu_name'         => 'Specialisms',
    ];

    register_taxonomy('job_specialism', ['oa_job'], [
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'job-specialism'],
    ]);
}
add_action('init', 'dt_register_job_specialism_taxonomy', 0);

function dt_remove_job_contract_type_taxonomy_metabox() {
    remove_meta_box('contract_typediv', 'oa_job', 'side');
    remove_meta_box('job_specialismdiv', 'oa_job', 'side');
}
add_action('add_meta_boxes', 'dt_remove_job_contract_type_taxonomy_metabox', 12);
