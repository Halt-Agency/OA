<?php
/**
 * One-off Team Members importer.
 *
 * Usage:
 *   wp eval-file wp-content/themes/OA/scripts/import-team-members.php
 *   wp eval-file wp-content/themes/OA/scripts/import-team-members.php --dry-run=1
 */

if (!defined('ABSPATH')) {
    echo "Run this via WP-CLI with `wp eval-file`.\n";
    return;
}

$dry_run = false;

if (getenv('TEAM_IMPORT_DRY_RUN') === '1') {
    $dry_run = true;
}

$runtime_args = [];
if (isset($args) && is_array($args)) {
    $runtime_args = array_merge($runtime_args, $args);
}
if (isset($_SERVER['argv']) && is_array($_SERVER['argv'])) {
    $runtime_args = array_merge($runtime_args, $_SERVER['argv']);
}

foreach ($runtime_args as $runtime_arg) {
    if (in_array((string) $runtime_arg, ['--dry-run', '--dry-run=1', 'dry-run', 'dry-run=1'], true)) {
        $dry_run = true;
        break;
    }
}
$profile_image_url = 'http://oa2.local/wp-content/uploads/2026/02/Component-268-%E2%80%93-1.png';
$profile_image_id = 0;

$resolved_id = attachment_url_to_postid($profile_image_url);
if ($resolved_id > 0) {
    $profile_image_id = (int) $resolved_id;
}

if ($profile_image_id === 0) {
    $uploads = wp_get_upload_dir();
    if (!empty($uploads['baseurl']) && strpos($profile_image_url, $uploads['baseurl']) === 0) {
        $relative_path = ltrim(str_replace($uploads['baseurl'], '', $profile_image_url), '/');
        $decoded_relative_path = urldecode($relative_path);
        $attachment_query = new WP_Query([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'meta_key'       => '_wp_attached_file',
            'meta_value'     => $decoded_relative_path,
            'fields'         => 'ids',
        ]);
        if (!empty($attachment_query->posts[0])) {
            $profile_image_id = (int) $attachment_query->posts[0];
        }
    }
}

if ($profile_image_id === 0) {
    $warn("Could not resolve attachment ID for profile image URL: {$profile_image_url}");
}

$ordered_names = [
    "Andrew Hepburn",
    "Atty Pasha",
    "Besira Balajeva",
    "Besira Balajeva",
    "Chloe Jago",
    "Christian Johnson",
    "Claire Kitchener",
    "Claudia Avigliano",
    "Emma Mist",
    "Erin Piper",
    "Evette Arthur",
    "Faye Etherton",
    "Freya Haggerty",
    "Georgina Hockman",
    "Hannah Burge",
    "Iveta Gedvilaite",
    "Jake Singh",
    "Jo Houston",
    "Joanna Chudziak",
    "John O'Sullivan",
    "Kady Brian",
    "Katarzyna Szubiak",
    "Kay Rafferty",
    "Kelli Parkins",
    "Kelly Rowland",
    "Laura Clark",
    "Lily Gregson",
    "Lottie Cook",
    "Louie Newman",
    "Lucy Bailey",
    "Michelle Bacon",
    "Monisa Hussain",
    "Natasha Holek",
    "Olivia Filler",
    "Olivia Rogers",
    "Paige Leggett",
    "Paulina Durak",
    "Raluca Maga",
    "Shannon Adams",
    "Sophie Whishaw",
    "Talya Hansson",
    "Tammy Slater",
    "Teresa Lazzara",
    "Tom Krupa",
];

$job_titles = [
    'Recruitment',
    'Account Manager',
    'Recruitment Consultant',
    'Candidate Relationship Consultant',
    'Client Development Manager',
    'Senior Recruitment Consultant',
    'Candidate Relationship Consultant',
    'Managing Consultant',
    'Management Accountant',
    'Marketing Executive',
];

$log = static function ($message) {
    if (class_exists('WP_CLI')) {
        \WP_CLI::log($message);
        return;
    }
    echo $message . "\n";
};

$warn = static function ($message) {
    if (class_exists('WP_CLI')) {
        \WP_CLI::warning($message);
        return;
    }
    echo "WARNING: " . $message . "\n";
};

$find_team_member_by_title = static function ($title) {
    global $wpdb;

    $post_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID
             FROM {$wpdb->posts}
             WHERE post_type = %s
               AND post_title = %s
               AND post_status IN ('publish','draft','pending','private')
             ORDER BY ID ASC
             LIMIT 1",
            'team_members',
            $title
        )
    );

    return $post_id ? (int) $post_id : 0;
};

$split_name = static function ($full_name) {
    $parts = preg_split('/\s+/', trim($full_name), 2);
    $first = $parts[0] ?? '';
    $last = $parts[1] ?? '';
    return [$first, $last];
};

$created = 0;
$updated = 0;
$skipped_duplicates = 0;
$seen = [];

foreach ($ordered_names as $index => $full_name) {
    $order = $index + 1;

    if (isset($seen[$full_name])) {
        $skipped_duplicates++;
        $warn("Duplicate name in list at position {$order}: {$full_name} (skipped)");
        continue;
    }
    $seen[$full_name] = true;

    [$first_name, $last_name] = $split_name($full_name);
    $existing_id = $find_team_member_by_title($full_name);
    $job_title = $job_titles[$index % count($job_titles)];

    if ($existing_id > 0) {
        if ($dry_run) {
            $log("[dry-run] Update #{$existing_id}: {$full_name} (menu_order {$order})");
            continue;
        }

        wp_update_post([
            'ID'         => $existing_id,
            'post_title' => $full_name,
            'menu_order' => $order,
            'post_status'=> 'publish',
        ]);

        if (function_exists('update_field')) {
            update_field('first_name', $first_name, $existing_id);
            update_field('last_name', $last_name, $existing_id);
            update_field('job_title', $job_title, $existing_id);
            if ($profile_image_id > 0) {
                update_field('field_team_profile_image', $profile_image_id, $existing_id);
            }
        } else {
            update_post_meta($existing_id, 'first_name', $first_name);
            update_post_meta($existing_id, 'last_name', $last_name);
            update_post_meta($existing_id, 'job_title', $job_title);
            if ($profile_image_id > 0) {
                update_post_meta($existing_id, 'profile_image', $profile_image_id);
                update_post_meta($existing_id, '_profile_image', 'field_team_profile_image');
            }
        }

        if ($profile_image_id > 0) {
            set_post_thumbnail($existing_id, $profile_image_id);
        }

        $updated++;
        $log("Updated #{$existing_id}: {$full_name}");
        continue;
    }

    if ($dry_run) {
        $log("[dry-run] Create: {$full_name} (menu_order {$order})");
        continue;
    }

    $new_id = wp_insert_post([
        'post_type'   => 'team_members',
        'post_status' => 'publish',
        'post_title'  => $full_name,
        'menu_order'  => $order,
    ], true);

    if (is_wp_error($new_id)) {
        $warn("Failed to create {$full_name}: " . $new_id->get_error_message());
        continue;
    }

    if (function_exists('update_field')) {
        update_field('first_name', $first_name, $new_id);
        update_field('last_name', $last_name, $new_id);
        update_field('job_title', $job_title, $new_id);
        if ($profile_image_id > 0) {
            update_field('field_team_profile_image', $profile_image_id, $new_id);
        }
    } else {
        update_post_meta($new_id, 'first_name', $first_name);
        update_post_meta($new_id, 'last_name', $last_name);
        update_post_meta($new_id, 'job_title', $job_title);
        if ($profile_image_id > 0) {
            update_post_meta($new_id, 'profile_image', $profile_image_id);
            update_post_meta($new_id, '_profile_image', 'field_team_profile_image');
        }
    }

    if ($profile_image_id > 0) {
        set_post_thumbnail($new_id, $profile_image_id);
    }

    $created++;
    $log("Created #{$new_id}: {$full_name}");
}

$log("Done. Created: {$created}, Updated: {$updated}, Duplicate rows skipped: {$skipped_duplicates}");
