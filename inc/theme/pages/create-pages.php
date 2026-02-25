<?php
/**
 * Create pages on theme activation
 * Based on setup.json structure
 */
function dt_create_pages_on_activation() {
    // Top level pages
    $pages = array(
        'Home' => 0,
        'About' => 0,
        'Work for us' => 0,
        'Candidates' => 0,
        'Clients' => 0,
        'Meet the Team' => 0,
        'Contact' => 0,
        'Register Brief' => 0,
        'Register CV' => 0,
        'Events and networking' => 0,
        'Resource Hub' => 0,
    );
    
    // Create top level pages
    foreach ($pages as $title => $parent_id) {
        dt_create_page_if_not_exists($title, $parent_id);
    }
    
    // Locations parent and children
    $locations_id = dt_create_page_if_not_exists('Locations', 0);
    $location_children = array('Buckinghamshire', 'Bedfordshire', 'Cambridgeshire', 'Hertfordshire', 'North London', 'Onsite');
    foreach ($location_children as $child) {
        dt_create_page_if_not_exists($child, $locations_id);
    }
    
    // Specialisms parent and children
    $specialisms_id = dt_create_page_if_not_exists('Specialisms', 0);
    $specialism_children = array('Office & Commercial', 'Warehousing & Distribution', 'Manufacturing', 'Events & Hospitality', 'Engineering');
    foreach ($specialism_children as $child) {
        dt_create_page_if_not_exists($child, $specialisms_id);
    }
    
    // Solutions parent and children
    $solutions_id = dt_create_page_if_not_exists('Solutions', 0);
    $solution_children = array('Permanent', 'Temporary', 'Embedded', 'Executive Search', 'High-Volume Perm', 'High-Volume Temp');
    foreach ($solution_children as $child) {
        dt_create_page_if_not_exists($child, $solutions_id);
    }
    
    // More parent and children
    $more_id = dt_create_page_if_not_exists('More', 0);
    $more_children = array('Employer Branding', 'On-Boarding & Training', 'Outplacement', 'Microsites');
    foreach ($more_children as $child) {
        dt_create_page_if_not_exists($child, $more_id);
    }
    
    flush_rewrite_rules();
}

/**
 * Helper function to create a page if it doesn't exist
 * Checks by both slug and title to prevent duplicates
 */
function dt_create_page_if_not_exists($title, $parent_id = 0) {
    $page_slug = sanitize_title($title);
    
    // First check by slug
    $existing_page = get_page_by_path($page_slug);
    
    // Also check by exact title to catch duplicates with different slugs
    if (!$existing_page) {
        global $wpdb;
        $existing_page_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} 
             WHERE post_type = 'page' 
             AND post_status = 'publish' 
             AND post_title = %s 
             LIMIT 1",
            $title
        ));
        
        if ($existing_page_id) {
            $existing_page = get_post($existing_page_id);
        }
    }
    
    if ($existing_page) {
        // Update parent if needed
        if ($existing_page->post_parent != $parent_id && $parent_id > 0) {
            wp_update_post(array(
                'ID' => $existing_page->ID,
                'post_parent' => $parent_id
            ));
        }
        return $existing_page->ID;
    }
    
    // Page doesn't exist, create it
    $page_data = array(
        'post_title'    => $title,
        'post_name'     => $page_slug,
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_parent'   => $parent_id,
        'post_author'   => 1,
    );
    
    $page_id = wp_insert_post($page_data);
    
    return $page_id && !is_wp_error($page_id) ? $page_id : 0;
}

/**
 * Run page creation on theme activation
 */
add_action('after_switch_theme', 'dt_create_pages_on_activation');

/**
 * TEMPORARY: Find duplicate pages by similar slugs and matching titles
 * COMMENTED OUT - Cleanup completed
 */
if (false) { // Disable duplicate finder script
function dt_find_duplicate_pages() {
    global $wpdb;
    
    // Get all published pages
    $pages = $wpdb->get_results(
        "SELECT ID, post_title, post_name, post_date, post_parent
         FROM {$wpdb->posts} 
         WHERE post_type = 'page' 
         AND post_status = 'publish'
         ORDER BY post_title, post_date ASC"
    );
    
    $duplicates_by_title = array();
    $duplicates_by_slug = array();
    
    // Group by title (exact match)
    foreach ($pages as $page) {
        $title_key = strtolower(trim($page->post_title));
        if (!isset($duplicates_by_title[$title_key])) {
            $duplicates_by_title[$title_key] = array();
        }
        $duplicates_by_title[$title_key][] = $page;
    }
    
    // Group by slug (similar slugs)
    foreach ($pages as $page) {
        $slug_key = strtolower(trim($page->post_name));
        if (!isset($duplicates_by_slug[$slug_key])) {
            $duplicates_by_slug[$slug_key] = array();
        }
        $duplicates_by_slug[$slug_key][] = $page;
    }
    
    // Filter to only show duplicates
    $title_duplicates = array_filter($duplicates_by_title, function($group) {
        return count($group) > 1;
    });
    
    $slug_duplicates = array_filter($duplicates_by_slug, function($group) {
        return count($group) > 1;
    });
    
    // Also check for similar slugs (e.g., "about" and "about-2")
    $similar_slugs = array();
    foreach ($pages as $page) {
        $base_slug = preg_replace('/-\d+$/', '', $page->post_name);
        if (!isset($similar_slugs[$base_slug])) {
            $similar_slugs[$base_slug] = array();
        }
        $similar_slugs[$base_slug][] = $page;
    }
    
    $similar_slug_duplicates = array_filter($similar_slugs, function($group) {
        return count($group) > 1;
    });
    
    return array(
        'by_title' => $title_duplicates,
        'by_slug' => $slug_duplicates,
        'by_similar_slug' => $similar_slug_duplicates,
        'total_pages' => count($pages)
    );
}

/**
 * Add admin menu for duplicate checker
 */
function dt_add_duplicate_checker_menu() {
    add_management_page(
        'Find Duplicate Pages',
        'Find Duplicate Pages',
        'manage_options',
        'dt-find-duplicates',
        'dt_find_duplicates_page'
    );
}
// add_action('admin_menu', 'dt_add_duplicate_checker_menu');

/**
 * Admin page for finding duplicates
 */
function dt_find_duplicates_page() {
    $results = dt_find_duplicate_pages();
    
    ?>
    <div class="wrap">
        <h1>Find Duplicate Pages</h1>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Summary</h2>
            <p><strong>Total Pages:</strong> <?php echo esc_html($results['total_pages']); ?></p>
            <p><strong>Duplicate Titles:</strong> <?php echo esc_html(count($results['by_title'])); ?> group(s)</p>
            <p><strong>Duplicate Slugs:</strong> <?php echo esc_html(count($results['by_slug'])); ?> group(s)</p>
            <p><strong>Similar Slugs:</strong> <?php echo esc_html(count($results['by_similar_slug'])); ?> group(s)</p>
        </div>
        
        <?php if (!empty($results['by_title'])): ?>
        <div class="card" style="margin-top: 20px;">
            <h2>Pages with Duplicate Titles</h2>
            <?php foreach ($results['by_title'] as $title => $pages): ?>
                <h3><?php echo esc_html($title); ?> (<?php echo count($pages); ?> pages)</h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Date</th>
                            <th>Parent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $index => $page): ?>
                            <tr style="<?php echo $index === 0 ? 'background-color: #d4edda;' : ''; ?>">
                                <td><?php echo esc_html($page->ID); ?></td>
                                <td><strong><?php echo esc_html($page->post_title); ?></strong></td>
                                <td><?php echo esc_html($page->post_name); ?></td>
                                <td><?php echo esc_html($page->post_date); ?></td>
                                <td><?php 
                                    if ($page->post_parent > 0) {
                                        $parent = get_post($page->post_parent);
                                        echo $parent ? esc_html($parent->post_title) : 'N/A';
                                    } else {
                                        echo '—';
                                    }
                                ?></td>
                                <td>
                                    <a href="<?php echo admin_url('post.php?post=' . $page->ID . '&action=edit'); ?>">Edit</a> | 
                                    <a href="<?php echo get_permalink($page->ID); ?>" target="_blank">View</a>
                                    <?php if ($index > 0): ?>
                                        | <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=dt_delete_page&page_id=' . $page->ID), 'delete_page_' . $page->ID); ?>" 
                                             onclick="return confirm('Are you sure you want to delete this page? This cannot be undone.');"
                                             style="color: #dc3545;">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><em>Green row = oldest page (kept), others are duplicates</em></p>
                <hr style="margin: 20px 0;">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($results['by_similar_slug'])): ?>
        <div class="card" style="margin-top: 20px;">
            <h2>Pages with Similar Slugs</h2>
            <?php foreach ($results['by_similar_slug'] as $base_slug => $pages): ?>
                <h3><?php echo esc_html($base_slug); ?> (<?php echo count($pages); ?> pages)</h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $index => $page): ?>
                            <tr style="<?php echo $index === 0 ? 'background-color: #d4edda;' : ''; ?>">
                                <td><?php echo esc_html($page->ID); ?></td>
                                <td><strong><?php echo esc_html($page->post_title); ?></strong></td>
                                <td><?php echo esc_html($page->post_name); ?></td>
                                <td><?php echo esc_html($page->post_date); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('post.php?post=' . $page->ID . '&action=edit'); ?>">Edit</a> | 
                                    <a href="<?php echo get_permalink($page->ID); ?>" target="_blank">View</a>
                                    <?php if ($index > 0): ?>
                                        | <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=dt_delete_page&page_id=' . $page->ID), 'delete_page_' . $page->ID); ?>" 
                                             onclick="return confirm('Are you sure you want to delete this page? This cannot be undone.');"
                                             style="color: #dc3545;">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><em>Green row = oldest page (kept), others are duplicates</em></p>
                <hr style="margin: 20px 0;">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if (empty($results['by_title']) && empty($results['by_similar_slug'])): ?>
            <div class="notice notice-success">
                <p><strong>No duplicate pages found!</strong></p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Handle page deletion
 */
function dt_handle_delete_page() {
    if (!current_user_can('delete_pages')) {
        wp_die('You do not have permission to delete pages.');
    }
    
    $page_id = isset($_GET['page_id']) ? intval($_GET['page_id']) : 0;
    
    if (!$page_id) {
        wp_die('Invalid page ID.');
    }
    
    check_admin_referer('delete_page_' . $page_id);
    
    wp_delete_post($page_id, true); // true = force delete
    
    wp_redirect(admin_url('tools.php?page=dt-find-duplicates&deleted=1'));
    exit;
}
add_action('admin_post_dt_delete_page', 'dt_handle_delete_page');
} // End of disabled duplicate finder script
