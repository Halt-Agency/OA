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
    
    // Build client logo dataset for JS-driven carousels
    $client_logos = [];
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
                    continue;
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

                $client_logos[] = [
                    'white_url'  => $white_url,
                    'colour_url' => $colour_url,
                    'alt'        => $white_alt !== '' ? $white_alt : $colour_alt,
                    'title'      => $white_title !== '' ? $white_title : $colour_title,
                    'terms'      => $terms,
                ];
            }
            wp_reset_postdata();
        }
    }

    // Convert false to empty array for JSON encoding
    if ($acf_data === false) {
        $acf_data = array();
    }
    
    // Always output the data (even if empty) so JavaScript knows it's available
    echo '<script type="text/javascript">';
    echo 'window.dtACFData = ' . json_encode($acf_data) . ';';
    echo 'window.oaClientLogos = ' . json_encode($client_logos) . ';';
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
