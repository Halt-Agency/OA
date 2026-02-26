<?php
// Inject ACF data into page for JavaScript access
add_action('wp_footer', function() {
    if (!function_exists('get_field')) {
        return;
    }
    
    global $post;
    $acf_data = array();
    $post_id = 0;
    $debug_info = array();
    
    // Handle preview pages first (they might not be detected as singular)
    if (isset($_GET['preview_id']) && is_numeric($_GET['preview_id'])) {
        $post_id = intval($_GET['preview_id']);
        $debug_info[] = 'Preview ID detected: ' . $post_id;
        $acf_data = get_fields($post_id);
        $debug_info[] = 'Fields retrieved: ' . (is_array($acf_data) ? count($acf_data) . ' fields' : 'false/empty');
    }
    // Get post ID from various contexts
    elseif (is_singular() && $post) {
        $post_id = $post->ID;
        $debug_info[] = 'Singular post detected: ' . $post_id;
        $acf_data = get_fields($post_id);
        $debug_info[] = 'Fields retrieved: ' . (is_array($acf_data) ? count($acf_data) . ' fields' : 'false/empty');
    } elseif (is_home() || is_front_page()) {
        // For home/front page, try to get the page ID
        $page_id = get_option('page_for_posts');
        if (is_front_page()) {
            $page_id = get_option('page_on_front');
        }
        if ($page_id) {
            $post_id = $page_id;
            $debug_info[] = 'Front/Home page detected: ' . $post_id;
            $acf_data = get_fields($post_id);
            $debug_info[] = 'Fields retrieved: ' . (is_array($acf_data) ? count($acf_data) . ' fields' : 'false/empty');
        }
    }
    
    $build_client_logo_entry = static function($post_id_item) {
        $logo_white = function_exists('get_field') ? get_field('client_logo', $post_id_item) : null;
        $logo_colour = function_exists('get_field') ? get_field('client_logo_colour', $post_id_item) : null;

        $white_url = '';
        $white_alt = '';
        $white_title = '';
        if (is_array($logo_white) && isset($logo_white['url'])) {
            $white_url = $logo_white['url'];
            $white_alt = $logo_white['alt'] ?? '';
            $white_title = $logo_white['title'] ?? '';
        } elseif (is_numeric($logo_white)) {
            $white_url = wp_get_attachment_image_url((int) $logo_white, 'full') ?: '';
            $white_alt = get_post_meta((int) $logo_white, '_wp_attachment_image_alt', true);
            $white_title = get_the_title((int) $logo_white);
        }

        $colour_url = '';
        $colour_alt = '';
        $colour_title = '';
        if (is_array($logo_colour) && isset($logo_colour['url'])) {
            $colour_url = $logo_colour['url'];
            $colour_alt = $logo_colour['alt'] ?? '';
            $colour_title = $logo_colour['title'] ?? '';
        } elseif (is_numeric($logo_colour)) {
            $colour_url = wp_get_attachment_image_url((int) $logo_colour, 'full') ?: '';
            $colour_alt = get_post_meta((int) $logo_colour, '_wp_attachment_image_alt', true);
            $colour_title = get_the_title((int) $logo_colour);
        }

        if ($white_url === '' && $colour_url === '') {
            return null;
        }

        $terms = [];
        $term_objects = get_the_terms($post_id_item, 'client_category');
        if (is_array($term_objects)) {
            foreach ($term_objects as $term) {
                if (!empty($term->slug)) {
                    $terms[] = $term->slug;
                }
            }
        }

        return [
            'white_url'  => $white_url,
            'colour_url' => $colour_url,
            'alt'        => $white_alt !== '' ? $white_alt : $colour_alt,
            'title'      => $white_title !== '' ? $white_title : $colour_title,
            'terms'      => $terms,
        ];
    };

    // Build client logo dataset for JS-driven carousels
    $client_logos = [];
    $trusted_by_logos = [];
    $trusted_by_logo_variant = 'white';
    $jobs_microsite_logos = [];
    if (post_type_exists('clients')) {
        $logo_query = new WP_Query([
            'post_type'      => 'clients',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => [
                'menu_order' => 'ASC',
                'title'      => 'ASC',
            ],
        ]);

        if ($logo_query->have_posts()) {
            while ($logo_query->have_posts()) {
                $logo_query->the_post();
                $post_id_item = get_the_ID();
                $logo_entry = $build_client_logo_entry($post_id_item);
                if ($logo_entry) {
                    $client_logos[] = $logo_entry;
                }
            }
            wp_reset_postdata();
        }

        // Build page-level Trusted By dataset from ACF selection.
        $trusted_by_ids = [];
        if (isset($acf_data['page_content']) && is_array($acf_data['page_content'])) {
            $page_content = $acf_data['page_content'];
            $use_all = isset($page_content['trusted_by_use_all']) ? (bool) $page_content['trusted_by_use_all'] : true;
            $trusted_by_logo_variant = !empty($page_content['trusted_by_use_colour_logos']) ? 'colour' : 'white';

            if ($use_all) {
                $trusted_by_query = new WP_Query([
                    'post_type'      => 'clients',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'orderby'        => [
                        'menu_order' => 'ASC',
                        'title'      => 'ASC',
                    ],
                ]);

                if ($trusted_by_query->have_posts()) {
                    while ($trusted_by_query->have_posts()) {
                        $trusted_by_query->the_post();
                        $trusted_by_ids[] = get_the_ID();
                    }
                    wp_reset_postdata();
                }
            } elseif (!empty($page_content['trusted_by_clients']) && is_array($page_content['trusted_by_clients'])) {
                $trusted_by_ids = array_values(array_filter(array_map('intval', $page_content['trusted_by_clients'])));
            }
        }

        foreach ($trusted_by_ids as $trusted_by_id) {
            $logo_entry = $build_client_logo_entry($trusted_by_id);
            if ($logo_entry) {
                $trusted_by_logos[] = $logo_entry;
            }
        }
    }

    // Build Jobs page microsite logo cards dataset (white logos + microsite link).
    if (post_type_exists('microsite')) {
        $jobs_logo_microsite_ids = [];
        if (isset($acf_data['page_content']) && is_array($acf_data['page_content'])) {
            $jobs_page_content = $acf_data['page_content'];
            $jobs_use_all_microsites = isset($jobs_page_content['top_logos_use_all']) ? (bool) $jobs_page_content['top_logos_use_all'] : true;

            if ($jobs_use_all_microsites) {
                $microsite_query = new WP_Query([
                    'post_type'      => 'microsite',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'orderby'        => [
                        'menu_order' => 'ASC',
                        'title'      => 'ASC',
                    ],
                    'fields'         => 'ids',
                    'no_found_rows'  => true,
                ]);

                if ($microsite_query->have_posts()) {
                    $jobs_logo_microsite_ids = array_map('intval', $microsite_query->posts);
                }
            } elseif (!empty($jobs_page_content['top_logos_microsites']) && is_array($jobs_page_content['top_logos_microsites'])) {
                $jobs_logo_microsite_ids = array_values(array_filter(array_map('intval', $jobs_page_content['top_logos_microsites'])));
            }
        }

        foreach ($jobs_logo_microsite_ids as $microsite_id) {
            $microsite_logo = get_field('client_logo', $microsite_id);
            $logo_url = '';
            $logo_alt = '';

            if (is_array($microsite_logo) && isset($microsite_logo['url'])) {
                $logo_url = (string) $microsite_logo['url'];
                $logo_alt = isset($microsite_logo['alt']) ? (string) $microsite_logo['alt'] : '';
            } elseif (is_numeric($microsite_logo)) {
                $logo_url = wp_get_attachment_image_url((int) $microsite_logo, 'full') ?: '';
                $logo_alt = (string) get_post_meta((int) $microsite_logo, '_wp_attachment_image_alt', true);
            } elseif (is_string($microsite_logo)) {
                $logo_url = $microsite_logo;
            }

            if ($logo_url === '') {
                $featured_id = get_post_thumbnail_id($microsite_id);
                if ($featured_id) {
                    $logo_url = wp_get_attachment_image_url((int) $featured_id, 'full') ?: '';
                    $logo_alt = (string) get_post_meta((int) $featured_id, '_wp_attachment_image_alt', true);
                }
            }

            if ($logo_url === '') {
                continue;
            }

            $jobs_microsite_logos[] = [
                'id'    => $microsite_id,
                'title' => (string) get_the_title($microsite_id),
                'url'   => (string) get_permalink($microsite_id),
                'logo'  => [
                    'url' => $logo_url,
                    'alt' => $logo_alt,
                ],
            ];
        }
    }

    // Build team carousel/directory datasets for selected team members.
    $team_carousel = [];
    $team_directory_members = [];
    $team_directory_locations = [];
    $team_directory_sectors = [];
    $team_member_ids = [];
    $page_content = [];
    if (isset($acf_data['page_content']) && is_array($acf_data['page_content'])) {
        $page_content = $acf_data['page_content'];
        $use_all = isset($page_content['team_use_all']) ? (bool) $page_content['team_use_all'] : true;
        if ($use_all) {
            $team_query = new WP_Query([
                'post_type'      => 'team_members',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => [
                    'menu_order' => 'ASC',
                    'title'      => 'ASC',
                ],
            ]);
            if ($team_query->have_posts()) {
                while ($team_query->have_posts()) {
                    $team_query->the_post();
                    $team_member_ids[] = get_the_ID();
                }
                wp_reset_postdata();
            }
        } elseif (!empty($page_content['team_members']) && is_array($page_content['team_members'])) {
            $team_member_ids = array_map('intval', $page_content['team_members']);
        }
    }

    foreach ($team_member_ids as $member_id) {
        $first_name = function_exists('get_field') ? (string) get_field('first_name', $member_id) : '';
        $last_name = function_exists('get_field') ? (string) get_field('last_name', $member_id) : '';
        $job_title = function_exists('get_field') ? (string) get_field('job_title', $member_id) : '';
        $profile_image = function_exists('get_field') ? get_field('profile_image', $member_id) : '';

        if (is_array($profile_image) && isset($profile_image['url'])) {
            $image_url = $profile_image['url'];
        } elseif (is_numeric($profile_image)) {
            $image_url = wp_get_attachment_image_url((int) $profile_image, 'full') ?: '';
        } else {
            $image_url = is_string($profile_image) ? $profile_image : '';
        }

        $name = trim($first_name . ' ' . $last_name);
        if ($name === '') {
            $name = get_the_title($member_id);
        }

        $team_carousel[] = [
            'id'        => $member_id,
            'name'      => $name,
            'job_title' => $job_title,
            'image'     => $image_url,
            'link'      => get_permalink($member_id),
        ];

        $locations = [];
        $location_terms = get_the_terms($member_id, 'location');
        if (is_array($location_terms)) {
            foreach ($location_terms as $term) {
                if (empty($term->slug)) {
                    continue;
                }
                $locations[] = [
                    'slug'  => (string) $term->slug,
                    'label' => (string) $term->name,
                ];
                $team_directory_locations[$term->slug] = (string) $term->name;
            }
        }

        $sectors = [];
        $sector_terms = get_the_terms($member_id, 'specialism');
        if (is_array($sector_terms)) {
            foreach ($sector_terms as $term) {
                if (empty($term->slug)) {
                    continue;
                }
                $sectors[] = [
                    'slug'  => (string) $term->slug,
                    'label' => (string) $term->name,
                ];
                $team_directory_sectors[$term->slug] = (string) $term->name;
            }
        }

        $team_directory_members[] = [
            'id'        => $member_id,
            'name'      => $name,
            'job_title' => $job_title,
            'image'     => $image_url,
            'link'      => get_permalink($member_id),
            'locations' => $locations,
            'sectors'   => $sectors,
        ];
    }

    $team_directory_location_options = [];
    foreach ($team_directory_locations as $slug => $label) {
        $team_directory_location_options[] = [
            'slug'  => (string) $slug,
            'label' => (string) $label,
        ];
    }
    usort($team_directory_location_options, static function ($a, $b) {
        return strcasecmp((string) $a['label'], (string) $b['label']);
    });

    $team_directory_sector_options = [];
    foreach ($team_directory_sectors as $slug => $label) {
        $team_directory_sector_options[] = [
            'slug'  => (string) $slug,
            'label' => (string) $label,
        ];
    }
    usort($team_directory_sector_options, static function ($a, $b) {
        return strcasecmp((string) $a['label'], (string) $b['label']);
    });

    $team_directory_settings = [
        'heading'                      => isset($page_content['directory_heading']) && $page_content['directory_heading'] !== '' ? (string) $page_content['directory_heading'] : 'Search the team',
        'placeholder_name'             => isset($page_content['search_placeholder_name']) && $page_content['search_placeholder_name'] !== '' ? (string) $page_content['search_placeholder_name'] : 'Name/Job Title',
        'placeholder_location'         => isset($page_content['search_placeholder_location']) && $page_content['search_placeholder_location'] !== '' ? (string) $page_content['search_placeholder_location'] : 'Location',
        'placeholder_sector'           => isset($page_content['search_placeholder_sector']) && $page_content['search_placeholder_sector'] !== '' ? (string) $page_content['search_placeholder_sector'] : 'Select Sector',
        'search_button_text'           => isset($page_content['search_button_text']) && $page_content['search_button_text'] !== '' ? (string) $page_content['search_button_text'] : 'Search',
        'empty_state_text'             => isset($page_content['empty_state_text']) && $page_content['empty_state_text'] !== '' ? (string) $page_content['empty_state_text'] : 'No team members found.',
        'pagination_prev_text'         => isset($page_content['pagination_prev_text']) && $page_content['pagination_prev_text'] !== '' ? (string) $page_content['pagination_prev_text'] : 'Previous',
        'pagination_next_text'         => isset($page_content['pagination_next_text']) && $page_content['pagination_next_text'] !== '' ? (string) $page_content['pagination_next_text'] : 'Next',
        'cards_per_page'               => 16,
    ];

    $team_directory = [
        'members'  => $team_directory_members,
        'filters'  => [
            'locations' => $team_directory_location_options,
            'sectors'   => $team_directory_sector_options,
        ],
        'settings' => $team_directory_settings,
    ];

    // Build single team member profile stack dataset.
    $team_member_profile_stack = [];
    $team_member_get_to_know = [];
    if ($post_id && get_post_type($post_id) === 'team_members') {
        $profile_image = get_field('profile_image', $post_id);
        $profile_image_url = '';
        if (is_array($profile_image) && isset($profile_image['url'])) {
            $profile_image_url = (string) $profile_image['url'];
        } elseif (is_numeric($profile_image)) {
            $profile_image_url = wp_get_attachment_image_url((int) $profile_image, 'full') ?: '';
        } elseif (is_string($profile_image)) {
            $profile_image_url = $profile_image;
        }

        $first_name = (string) get_field('first_name', $post_id);
        $last_name = (string) get_field('last_name', $post_id);
        $full_name = trim($first_name . ' ' . $last_name);
        if ($full_name === '') {
            $full_name = get_the_title($post_id);
        }

        $job_title = (string) get_field('job_title', $post_id);

        $specialisms = [];
        $specialism_terms = get_the_terms($post_id, 'specialism');
        if (is_array($specialism_terms)) {
            foreach ($specialism_terms as $term) {
                if (!empty($term->name)) {
                    $term_link = get_term_link($term);
                    $specialisms[] = [
                        'label' => (string) $term->name,
                        'url'   => is_wp_error($term_link) ? '' : (string) $term_link,
                    ];
                }
            }
        }

        $solutions = [];
        $solution_terms = get_the_terms($post_id, 'solution');
        if (is_array($solution_terms)) {
            foreach ($solution_terms as $term) {
                if (!empty($term->name)) {
                    $term_link = get_term_link($term);
                    $solutions[] = [
                        'label' => (string) $term->name,
                        'url'   => is_wp_error($term_link) ? '' : (string) $term_link,
                    ];
                }
            }
        }

        $team_member_profile_stack = [
            'image'                 => $profile_image_url,
            'name'                  => $full_name,
            'job_title'             => $job_title,
            'specialisms_heading'   => 'Specialisms',
            'solutions_heading'     => 'Solutions',
            'specialisms'           => $specialisms,
            'solutions'             => $solutions,
        ];

        $get_to_know_heading = (string) get_field('get_to_know_heading', 'option');
        if ($get_to_know_heading === '') {
            $get_to_know_heading = 'Get to know';
        }

        $team_member_get_to_know = [
            'heading'    => $get_to_know_heading,
            'first_name' => $first_name !== '' ? $first_name : $full_name,
        ];
    }

    // Build global UK coverage contacts dataset for map tooltips.
    $uk_coverage_contacts = [];
    $uk_location_meta = [
        'bedfordshire'   => ['title' => 'Bedfordshire', 'link' => '/locations/bedfordshire'],
        'buckinghamshire'=> ['title' => 'Buckinghamshire', 'link' => '/locations/buckinghamshire'],
        'cambridgeshire' => ['title' => 'Cambridgeshire', 'link' => '/locations/cambridgeshire'],
        'hertfordshire'  => ['title' => 'Hertfordshire', 'link' => '/locations/hertfordshire'],
        'north_london'   => ['title' => 'North London', 'link' => '/locations/north-london'],
    ];

    $global_contacts = get_field('field_uk_coverage_contacts_group', 'option');
    if (!is_array($global_contacts)) {
        $global_contacts = get_field('uk_coverage_contacts', 'option');
    }
    if (!is_array($global_contacts)) {
        $global_contacts = [];
    }

    foreach ($uk_location_meta as $location_key => $meta) {
        $team_member_value = $global_contacts[$location_key] ?? 0;
        if (is_array($team_member_value)) {
            $team_member_value = $team_member_value[0] ?? 0;
        } elseif (is_object($team_member_value) && isset($team_member_value->ID)) {
            $team_member_value = (int) $team_member_value->ID;
        }

        $team_member_id = is_numeric($team_member_value) ? (int) $team_member_value : 0;
        $name = '';
        $email = '';
        $image_url = '';

        if ($team_member_id > 0) {
            $first_name = (string) get_field('first_name', $team_member_id);
            $last_name = (string) get_field('last_name', $team_member_id);
            $email = (string) get_field('email', $team_member_id);
            $name = trim($first_name . ' ' . $last_name);
            if ($name === '') {
                $name = get_the_title($team_member_id);
            }

            $profile_image = get_field('profile_image', $team_member_id, false);
            if (is_array($profile_image) && isset($profile_image['url'])) {
                $image_url = (string) $profile_image['url'];
            } elseif (is_array($profile_image) && isset($profile_image['ID'])) {
                $image_url = wp_get_attachment_image_url((int) $profile_image['ID'], 'full') ?: '';
            } elseif (is_numeric($profile_image)) {
                $image_url = wp_get_attachment_image_url((int) $profile_image, 'full') ?: '';
            } elseif (is_string($profile_image)) {
                $image_url = $profile_image;
            }
        }

        $uk_coverage_contacts[$location_key] = [
            'location_title'    => $meta['title'],
            'link_url'          => $meta['link'],
            'link_text'         => 'Find out more',
            'team_member_name'  => $name,
            'team_member_email' => $email,
            'team_member_image' => $image_url,
        ];
    }

    // Convert false to empty array for JSON encoding
    if ($acf_data === false) {
        $acf_data = array();
    }
    
    // Always output the data (even if empty) so JavaScript knows it's available
    echo '<script type="text/javascript">';
    echo 'window.dtACFData = ' . json_encode($acf_data) . ';';
    echo 'window.oaClientLogos = ' . json_encode($client_logos) . ';';
    echo 'window.oaTrustedByLogos = ' . json_encode($trusted_by_logos) . ';';
    echo 'window.oaTrustedByLogoVariant = ' . json_encode($trusted_by_logo_variant) . ';';
    echo 'window.oaJobsMicrositeLogos = ' . json_encode($jobs_microsite_logos) . ';';
    echo 'window.oaTeamCarousel = ' . json_encode($team_carousel) . ';';
    echo 'window.oaTeamDirectory = ' . json_encode($team_directory) . ';';
    echo 'window.oaTeamMemberProfileStack = ' . json_encode($team_member_profile_stack) . ';';
    echo 'window.oaTeamMemberGetToKnow = ' . json_encode($team_member_get_to_know) . ';';
    echo 'window.oaUkCoverageContacts = ' . json_encode($uk_coverage_contacts) . ';';
    echo 'window.dtPostId = ' . intval($post_id) . ';';
    echo 'window.dtAjaxUrl = "' . admin_url('admin-ajax.php') . '";';
    // Add debug info in development
    if (defined('WP_DEBUG') && WP_DEBUG) {
        echo 'window.dtACFDebug = ' . json_encode($debug_info) . ';';
        echo 'console.log("ACF Data Injection Debug:", ' . json_encode($debug_info) . ');';
        echo 'console.log("ACF Data:", window.dtACFData);';
    }
    echo '</script>';
});

/**
 * Custom AJAX endpoint to fetch ACF data
 * This works even if ACF REST API is not enabled
 */
add_action('wp_ajax_dt_get_acf_data', 'dt_ajax_get_acf_data');
add_action('wp_ajax_nopriv_dt_get_acf_data', 'dt_ajax_get_acf_data');
function dt_ajax_get_acf_data() {
    if (!function_exists('get_fields')) {
        wp_send_json_error(array('message' => 'ACF not available'));
        return;
    }
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    if (!$post_id) {
        wp_send_json_error(array('message' => 'No post ID provided'));
        return;
    }
    
    $acf_data = get_fields($post_id);
    
    if ($acf_data === false) {
        $acf_data = array();
    }
    
    wp_send_json_success(array(
        'acf_data' => $acf_data,
        'post_id' => $post_id,
        'field_count' => is_array($acf_data) ? count($acf_data) : 0,
        'field_keys' => is_array($acf_data) ? array_keys($acf_data) : array()
    ));
}

/**
 * Include Marquee Carousel functionality
 */
require_once get_stylesheet_directory() . '/inc/marquee-carousel.php';

/**
 * Include Divi 5 Client Logos module extension.
 */
require_once get_stylesheet_directory() . '/inc/divi-extensions/client-logos/client-logos-extension.php';
require_once get_stylesheet_directory() . '/inc/divi-extensions/halt-advanced-tabs/halt-advanced-tabs-extension.php';
