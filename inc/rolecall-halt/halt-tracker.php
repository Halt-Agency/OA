<?php
/**
 * Halt Tracker Integration - Theme Integration
 * Captures Divi form submissions or webhook payloads and forwards them to Tracker RMS via the REST API. Provides queueing, logging, and manual retries.
 * 
 * This is integrated into the theme rather than as a standalone plugin.
 * 
 * @package OA_Theme
 * @version 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Prevent fatal errors if the plugin version is still active/loaded elsewhere.
if ( class_exists( 'Halt_Tracker_Plugin', false ) ) {
    return;
}

final class Halt_Tracker_Plugin {
    const OPTION_GROUP           = 'halt_tracker_settings';
    const OPT_ENVIRONMENT        = 'halt_tracker_environment';
    const OPT_CLIENT_ID          = 'halt_tracker_client_id';
    const OPT_CLIENT_SECRET      = 'halt_tracker_client_secret';
    const OPT_REFRESH_TOKEN      = 'halt_tracker_refresh_token';
    const OPT_REDIRECT_URI       = 'halt_tracker_redirect_uri';
    const OPT_SHARED_SECRET      = 'halt_tracker_shared_secret';
    const OPT_POST_TYPE          = 'halt_tracker_post_type';
    const OPT_FIELD_MAP          = 'halt_tracker_field_map';
    const OPT_REQUIRED_FIELDS    = 'halt_tracker_required_fields';
    const OPT_RECORD_TYPE        = 'halt_tracker_record_type';
    const OPT_MAX_RESULTS        = 'halt_tracker_max_results';
    const OPT_ONLY_MY_RECORDS    = 'halt_tracker_only_my_records';
    const OPT_INCLUDE_CUSTOM     = 'halt_tracker_include_custom_fields';
    const OPT_MAX_ATTEMPTS       = 'halt_tracker_max_attempts';
    const OPT_ATTACHMENT_FIELD   = 'halt_tracker_attachment_field';
    const OPT_ACTIVITY_ENABLED   = 'halt_tracker_activity_enabled';
    const OPT_ACTIVITY_SUBJECT   = 'halt_tracker_activity_subject';
    const OPT_ACTIVITY_NOTES     = 'halt_tracker_activity_notes';

    const OPT_JOB_SYNC_ENABLED   = 'halt_tracker_job_sync_enabled';
    const OPT_LAST_JOB_SYNC      = 'halt_tracker_last_job_sync';

    const OPT_FORM_ROUTING_RESOURCE = 'halt_tracker_form_routing_resource';
    const OPT_FORM_ROUTING_LEAD     = 'halt_tracker_form_routing_lead';
    const OPT_FORM_ROUTING_CONTACT  = 'halt_tracker_form_routing_contact';

    const OPT_REPORTS        = 'halt_tracker_reports';
    const OPT_LAST_SYNC_TIME = 'halt_tracker_last_sync_time';
    const OPT_JOBS_CREATED   = 'halt_tracker_jobs_created';
    const OPT_JOBS_UPDATED   = 'halt_tracker_jobs_updated';
    const OPT_JOBS_FAILED    = 'halt_tracker_jobs_failed';
    const OPT_SYNC_ERRORS    = 'halt_tracker_sync_errors';

    const LOCK_KEY               = 'halt_tracker_sync_lock';
    const TRANSIENT_ACCESS_TOKEN = 'halt_tracker_access_token';
    const TRANSIENT_JWT          = 'halt_tracker_jwt';
    const REST_NAMESPACE         = 'halt-tracker/v1';
    const REST_ROUTE             = '/ingest';
    const PROCESS_HOOK           = 'halt_tracker_process_queue';

    const STATUS_QUEUED  = 'queued';
    const STATUS_RETRY   = 'retry';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED  = 'failed';

    private static $instance = null;

    /**
     * @var string
     */
    private $queue_table;

    /**
     * Boot plugin.
     */
    public static function init() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
    }

    /**
     * Constructor hooks.
     */
    private function __construct() {
        global $wpdb;
        $this->queue_table = $wpdb->prefix . 'halt_tracker_queue';

        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
        add_filter( 'wp_resource_hints', [ $this, 'add_font_preconnect_hints' ], 10, 2 );
        add_filter( 'cron_schedules', [ $this, 'register_cron_schedules' ] );

        add_action( 'admin_post_halt_tracker_manual_sync', [ $this, 'handle_manual_sync' ] );
        add_action( 'admin_post_halt_tracker_clear_lock', [ $this, 'handle_clear_lock' ] );
        add_action( 'admin_post_halt_tracker_sync_jobs', [ $this, 'handle_manual_job_sync' ] );

        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
        add_action( 'et_pb_contact_form_submit', [ $this, 'handle_divi_submission' ], 10, 2 );
        add_action( self::PROCESS_HOOK, [ $this, 'process_queue' ] );
        
        add_action( 'init', [ $this, 'register_job_post_type' ] );
        add_action( 'halt_tracker_sync_jobs', [ $this, 'sync_jobs_from_tracker' ] );
    }

    /**
     * Activation tasks.
     */
    public static function activate() {
        $plugin = new self();
        $plugin->create_queue_table();
        if ( ! wp_next_scheduled( self::PROCESS_HOOK ) ) {
            wp_schedule_event( time() + MINUTE_IN_SECONDS, 'halt_tracker_five_minutes', self::PROCESS_HOOK );
        }
        // Schedule job sync daily as fallback (webhooks are primary)
        if ( ! wp_next_scheduled( 'halt_tracker_sync_jobs' ) ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'halt_tracker_sync_jobs' );
        }
        // Flush rewrite rules for custom post type
        $plugin->register_job_post_type();
        flush_rewrite_rules();
    }

    /**
     * Deactivation tasks.
     */
    public static function deactivate() {
        wp_clear_scheduled_hook( self::PROCESS_HOOK );
        wp_clear_scheduled_hook( 'halt_tracker_sync_jobs' );
    }

    /**
     * Register cron interval.
     */
    public function register_cron_schedules( $schedules ) {
        if ( ! isset( $schedules['halt_tracker_five_minutes'] ) ) {
            $schedules['halt_tracker_five_minutes'] = [
                'interval' => 5 * MINUTE_IN_SECONDS,
                'display'  => __( 'Every 5 minutes (Halt Tracker)', 'halt-tracker' ),
            ];
        }
        // Job sync uses daily schedule (webhooks handle real-time updates)
        return $schedules;
    }

    /**
     * Create queue table.
     */
    private function create_queue_table() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $sql     = "CREATE TABLE {$this->queue_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            source varchar(50) NOT NULL,
            form_id varchar(190) DEFAULT '' NOT NULL,
            payload longtext NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'queued',
            attempts smallint(5) unsigned NOT NULL DEFAULT 0,
            last_error text NULL,
            PRIMARY KEY (id),
            KEY status (status),
            KEY form_id (form_id),
            KEY attempts (attempts)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Admin menu.
     */
    public function admin_menu() {
        add_menu_page(
            __( 'Halt Tracker', 'halt-tracker' ),
            __( 'Halt Tracker', 'halt-tracker' ),
            'manage_options',
            'halt-tracker',
            [ $this, 'render_main_page' ],
            '',
            26
        );

        add_submenu_page(
            'halt-tracker',
            __( 'Sync Log', 'halt-tracker' ),
            __( 'Sync Log', 'halt-tracker' ),
            'manage_options',
            'halt-tracker-sync-log',
            [ $this, 'render_sync_log_page' ]
        );

        add_submenu_page(
            'halt-tracker',
            __( 'Settings', 'halt-tracker' ),
            __( 'Settings', 'halt-tracker' ),
            'manage_options',
            'halt-tracker-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Register settings.
     */
    public function register_settings() {
        $boolean = [ $this, 'sanitize_boolean' ];
        register_setting( self::OPTION_GROUP, self::OPT_ENVIRONMENT, [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_environment' ],
            'default'           => 'row',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_CLIENT_ID, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_CLIENT_SECRET, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_REFRESH_TOKEN, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_REDIRECT_URI, [
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_SHARED_SECRET, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_POST_TYPE, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_key',
            'default'           => 'post',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_FIELD_MAP, [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_json' ],
            'default'           => '{"firstName":"first_name","surname":"last_name","email":"email","mobile":"phone","source":":Website"}',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_REQUIRED_FIELDS, [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_csv' ],
            'default'           => 'firstName,lastName,email',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_RECORD_TYPE, [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_record_type' ],
            'default'           => 'Resource',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_MAX_RESULTS, [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 100,
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_ONLY_MY_RECORDS, [
            'type'              => 'boolean',
            'sanitize_callback' => $boolean,
            'default'           => 0,
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_INCLUDE_CUSTOM, [
            'type'              => 'boolean',
            'sanitize_callback' => $boolean,
            'default'           => 1,
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_MAX_ATTEMPTS, [
            'type'              => 'integer',
            'sanitize_callback' => function( $value ) {
                $value = absint( $value );
                return $value > 0 ? $value : 6;
            },
            'default' => 6,
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_ATTACHMENT_FIELD, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_key',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_ACTIVITY_ENABLED, [
            'type'              => 'boolean',
            'sanitize_callback' => $boolean,
            'default'           => 0,
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_ACTIVITY_SUBJECT, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'Website enquiry - {{form_id}}',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_ACTIVITY_NOTES, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default'           => 'Submitted via WordPress at {{timestamp}}',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_JOB_SYNC_ENABLED, [
            'type'              => 'boolean',
            'sanitize_callback' => $boolean,
            'default'           => 1,
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_FORM_ROUTING_RESOURCE, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default'           => '',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_FORM_ROUTING_LEAD, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default'           => '',
        ] );
        register_setting( self::OPTION_GROUP, self::OPT_FORM_ROUTING_CONTACT, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default'           => '',
        ] );
    }

    /**
     * JSON sanitation.
     */
    public function sanitize_json( $value ) {
        $value = trim( $value );
        if ( '' === $value ) {
            return '';
        }
        json_decode( $value );
        return ( json_last_error() === JSON_ERROR_NONE ) ? $value : '';
    }

    /**
     * CSV sanitize.
     */
    public function sanitize_csv( $value ) {
        $parts = array_filter( array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', (string) $value ) ) ) );
        return implode( ',', $parts );
    }

    /**
     * Boolean sanitize.
     */
    public function sanitize_boolean( $value ) {
        return (int) (bool) $value;
    }

    /**
     * Environment sanitize.
     */
    public function sanitize_environment( $value ) {
        $value = strtolower( sanitize_text_field( $value ) );
        $allowed = [ 'us', 'ca', 'row' ];
        return in_array( $value, $allowed, true ) ? $value : 'row';
    }

    /**
     * Record type sanitize.
     */
    public function sanitize_record_type( $value ) {
        $value   = ucfirst( strtolower( sanitize_text_field( $value ) ) );
        $allowed = [ 'Resource', 'Contact', 'Lead' ];
        return in_array( $value, $allowed, true ) ? $value : 'Resource';
    }

    /**
     * Enqueue admin assets.
     */
    public function enqueue_admin_assets( $hook ) {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'halt-tracker' ) === false ) {
            return;
        }
        $plugin_url = trailingslashit( get_stylesheet_directory_uri() . '/inc/rolecall-halt' );
        $css_file   = get_stylesheet_directory() . '/inc/rolecall-halt/assets/halt-jobsync.css';
        $js_file    = get_stylesheet_directory() . '/inc/rolecall-halt/assets/halt-jobsync.js';
        $css_ver    = file_exists( $css_file ) ? filemtime( $css_file ) : '1.0';
        $js_ver     = file_exists( $js_file ) ? filemtime( $js_file ) : '1.0';
        wp_enqueue_style( 'halt-tracker-style', $plugin_url . 'assets/halt-jobsync.css', [], $css_ver );
        wp_enqueue_script( 'halt-tracker-script', $plugin_url . 'assets/halt-jobsync.js', [], $js_ver, true );
    }

    /**
     * Frontend assets for typography consistency.
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'halt-tracker-fonts',
            'https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap',
            [],
            null
        );
        $css = ".halt-tracker-summary, .halt-tracker-summary * { font-family: 'Instrument Sans', sans-serif !important; }";
        wp_add_inline_style( 'halt-tracker-fonts', $css );
    }

    /**
     * Preconnect hints for Google Fonts.
     */
    public function add_font_preconnect_hints( $urls, $relation_type ) {
        if ( 'preconnect' === $relation_type ) {
            $urls[] = 'https://fonts.googleapis.com';
            $urls[] = [
                'href'        => 'https://fonts.gstatic.com',
                'crossorigin' => 'anonymous',
            ];
        }
        return $urls;
    }

    /**
     * Render dashboard.
     */
    public function render_main_page() {
        $data = [
            'last_sync_time' => get_option( self::OPT_LAST_SYNC_TIME, 'Never' ),
            'jobs_created'   => get_option( self::OPT_JOBS_CREATED, 0 ),
            'jobs_updated'   => get_option( self::OPT_JOBS_UPDATED, 0 ),
            'jobs_failed'    => get_option( self::OPT_JOBS_FAILED, 0 ),
            'sync_errors'    => get_option( self::OPT_SYNC_ERRORS, 0 ),
            'synced'         => isset( $_GET['synced'] ) ? intval( $_GET['synced'] ) : 0,
        ];
        $this->load_template( 'main-page.php', $data );
    }

    /**
     * Render log.
     */
    public function render_sync_log_page() {
        $reports = get_option( self::OPT_REPORTS, [] );
        $data    = [ 'sync_reports' => $reports ];
        $this->load_template( 'sync-log-page.php', $data );
    }

    /**
     * Render settings.
     */
    public function render_settings_page() {
        $data = [
            'lock_cleared' => isset( $_GET['lock_cleared'] ) ? intval( $_GET['lock_cleared'] ) : 0,
        ];
        $this->load_template( 'settings-page.php', $data );
    }

    /**
     * Load template helper.
     */
    private function load_template( $template, $data = [] ) {
        $template_file = __DIR__ . '/templates/' . $template;
        if ( file_exists( $template_file ) ) {
            include $template_file;
        } else {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html( sprintf( 'Template missing: %s', $template ) )
            );
        }
    }

    /**
     * Manual sync handler.
     */
    public function handle_manual_sync() {
        check_admin_referer( 'halt_tracker_manual_sync' );
        $this->process_queue( true );
        wp_redirect( admin_url( 'admin.php?page=halt-tracker&synced=1' ) );
        exit;
    }

    /**
     * Clear lock (unused but kept for UI parity).
     */
    public function handle_clear_lock() {
        check_admin_referer( 'halt_tracker_clear_lock' );
        delete_transient( self::LOCK_KEY );
        wp_redirect( admin_url( 'admin.php?page=halt-tracker-settings&lock_cleared=1' ) );
        exit;
    }

    /**
     * Manual job sync handler.
     */
    public function handle_manual_job_sync() {
        check_admin_referer( 'halt_tracker_manual_job_sync' );
        $this->sync_jobs_from_tracker();
        wp_redirect( admin_url( 'admin.php?page=halt-tracker&jobs_synced=1' ) );
        exit;
    }

    /**
     * Register REST routes.
     */
    public function register_rest_routes() {
        register_rest_route(
            self::REST_NAMESPACE,
            self::REST_ROUTE,
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'handle_webhook_submission' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'form_id' => [
                        'type'     => 'string',
                        'required' => true,
                    ],
                    'fields' => [
                        'type'     => 'array',
                        'required' => true,
                    ],
                ],
            ]
        );

        // TrackerRMS webhook receiver for real-time job sync
        register_rest_route(
            self::REST_NAMESPACE,
            '/tracker-webhook',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'handle_tracker_webhook' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * Webhook handler.
     */
    public function handle_webhook_submission( WP_REST_Request $request ) {
        $raw_body = $request->get_body();
        $secret   = get_option( self::OPT_SHARED_SECRET );

        if ( ! empty( $secret ) && ! $this->verify_webhook_signature( $request, $raw_body, $secret ) ) {
            return new WP_REST_Response( [ 'message' => 'Invalid signature' ], 401 );
        }

        $payload = [
            'form_id'   => sanitize_text_field( $request->get_param( 'form_id' ) ),
            'fields'    => $this->sanitize_field_array( $request->get_param( 'fields' ) ),
            'meta'      => $this->sanitize_field_array( (array) $request->get_param( 'meta' ) ),
            'files'     => $this->sanitize_files_payload( (array) $request->get_param( 'files' ) ),
            'source_ip' => $request->get_header( 'x-forwarded-for' ) ?: $request->get_client_ip(),
        ];

        $queued = $this->queue_submission( 'webhook', $payload );
        if ( is_wp_error( $queued ) ) {
            return new WP_REST_Response( [ 'message' => $queued->get_error_message() ], 500 );
        }

        return new WP_REST_Response( [ 'queued' => true, 'queue_id' => $queued ], 202 );
    }

    /**
     * Handle TrackerRMS webhook for real-time job sync.
     */
    public function handle_tracker_webhook( WP_REST_Request $request ) {
        // Get webhook payload
        $payload = $request->get_json_params();
        
        if ( empty( $payload ) ) {
            return new WP_REST_Response( [ 'message' => 'Invalid payload' ], 400 );
        }

        // Log the webhook for debugging
        error_log( 'Halt Tracker: Received webhook - ' . wp_json_encode( $payload ) );

        // Extract event details
        $record_type = isset( $payload['recordType'] ) ? $payload['recordType'] : '';
        $action      = isset( $payload['action'] ) ? $payload['action'] : '';
        $record_id   = isset( $payload['recordId'] ) ? intval( $payload['recordId'] ) : 0;

        // Only process Opportunity webhooks
        if ( 'Opportunity' !== $record_type ) {
            return new WP_REST_Response( [ 'message' => 'Not an Opportunity webhook' ], 200 );
        }

        // Handle different actions
        switch ( $action ) {
            case 'Created':
            case 'Updated':
                $this->sync_single_job_from_tracker( $record_id );
                break;
            
            case 'Deleted':
                $this->delete_job_by_tracker_id( $record_id );
                break;
            
            default:
                return new WP_REST_Response( [ 'message' => 'Unknown action: ' . $action ], 400 );
        }

        return new WP_REST_Response( [ 'success' => true, 'action' => $action, 'recordId' => $record_id ], 200 );
    }

    /**
     * Sync a single job from TrackerRMS by ID.
     */
    private function sync_single_job_from_tracker( $opportunity_id ) {
        $jwt = $this->get_jwt_token();
        if ( is_wp_error( $jwt ) ) {
            error_log( 'Halt Tracker: JWT Error - ' . $jwt->get_error_message() );
            return;
        }

        // Fetch the specific opportunity
        $url = trailingslashit( $this->get_new_api_base_url() ) . 'api/v1/Opportunity/' . $opportunity_id;
        
        $response = wp_remote_get(
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $jwt,
                    'Content-Type'  => 'application/json',
                ],
                'timeout' => 20,
            ]
        );

        if ( is_wp_error( $response ) ) {
            error_log( 'Halt Tracker: Failed to fetch opportunity ' . $opportunity_id . ': ' . $response->get_error_message() );
            return;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code ) {
            error_log( 'Halt Tracker: Failed to fetch opportunity ' . $opportunity_id . ': HTTP ' . $code );
            return;
        }

        $opportunity = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $opportunity ) ) {
            return;
        }

        // Check if job should be published
        $is_published = isset( $opportunity['publishOnline'] ) && in_array( strtolower( (string) $opportunity['publishOnline'] ), [ 'y', 'yes', '1', true ], true );
        
        $tracker_id = $opportunity['opportunityId'] ?? null;
        if ( ! $tracker_id ) {
            return;
        }

        $post_id = $this->get_job_post_id_by_tracker_id( $tracker_id );

        if ( $is_published ) {
            // Create or update job
            $job_data = $this->map_opportunity_to_wp_post( $opportunity );
            if ( $post_id ) {
                // Update existing job
                $job_data['ID'] = $post_id;
                wp_update_post( $job_data );
            } else {
                // Create new job
                $post_id = wp_insert_post( $job_data );
            }
            if ( $post_id && ! is_wp_error( $post_id ) ) {
                $this->update_job_meta( $post_id, $opportunity );
            }
        } else {
            // Job is no longer published, trash it
            if ( $post_id ) {
                wp_trash_post( $post_id );
            }
        }
    }

    /**
     * Delete a job post by TrackerRMS ID.
     */
    private function delete_job_by_tracker_id( $tracker_id ) {
        $post_id = $this->get_job_post_id_by_tracker_id( $tracker_id );
        if ( $post_id ) {
            wp_trash_post( $post_id );
        }
    }

    /**
     * Verify webhook HMAC signature.
     */
    private function verify_webhook_signature( WP_REST_Request $request, $raw_body, $secret ) {
        $signature = $request->get_header( 'x-halt-webhook-signature' );
        $timestamp = $request->get_header( 'x-halt-timestamp' );
        if ( empty( $signature ) || empty( $timestamp ) ) {
            return false;
        }
        $ts = intval( $timestamp );
        if ( abs( time() - $ts ) > 300 ) {
            return false;
        }
        $expected = hash_hmac( 'sha256', $timestamp . '.' . $raw_body, $secret );
        return hash_equals( $expected, $signature );
    }

    /**
     * Sanitize arbitrary field array.
     */
    private function sanitize_field_array( $fields ) {
        $clean = [];
        foreach ( (array) $fields as $key => $value ) {
            if ( is_array( $value ) ) {
                $clean[ sanitize_key( $key ) ] = $this->sanitize_field_array( $value );
            } else {
                $clean[ sanitize_key( $key ) ] = sanitize_text_field( (string) $value );
            }
        }
        return $clean;
    }

    /**
     * Sanitize files payload.
     */
    private function sanitize_files_payload( array $files ) {
        $clean = [];
        foreach ( $files as $file ) {
            $clean[] = [
                'field'    => isset( $file['field'] ) ? sanitize_text_field( $file['field'] ) : '',
                'name'     => isset( $file['name'] ) ? sanitize_text_field( $file['name'] ) : '',
                'mime'     => isset( $file['mime'] ) ? sanitize_text_field( $file['mime'] ) : '',
                'content'  => isset( $file['content'] ) ? sanitize_text_field( $file['content'] ) : '',
                'encoding' => isset( $file['encoding'] ) ? sanitize_text_field( $file['encoding'] ) : 'base64',
            ];
        }
        return $clean;
    }

    /**
     * Handle Divi submissions.
     */
    public function handle_divi_submission( $processed_fields_values, $errors ) {
        if ( ! empty( $errors ) ) {
            return;
        }
        $form_id = isset( $_POST['et_pb_contact_form_unique_id'] ) ? sanitize_text_field( wp_unslash( $_POST['et_pb_contact_form_unique_id'] ) ) : 'divi';
        $fields  = $this->sanitize_field_array( $processed_fields_values );
        $payload = [
            'form_id' => $form_id,
            'fields'  => $fields,
            'meta'    => [
                'referer'  => isset( $_POST['_wp_http_referer'] ) ? esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) ) : '',
                'user_ip'  => $this->get_user_ip(),
                'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
            ],
        ];
        $this->queue_submission( 'divi', $payload );
    }

    /**
     * Obtain best-effort IP.
     */
    private function get_user_ip() {
        $keys = [ 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ];
        foreach ( $keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = explode( ',', $_SERVER[ $key ] );
                return trim( $ip[0] );
            }
        }
        return '';
    }

    /**
     * Queue payload.
     */
    private function queue_submission( $source, array $payload ) {
        global $wpdb;
        $insert = $wpdb->insert(
            $this->queue_table,
            [
                'created_at' => current_time( 'mysql' ),
                'updated_at' => current_time( 'mysql' ),
                'source'     => sanitize_key( $source ),
                'form_id'    => isset( $payload['form_id'] ) ? sanitize_text_field( $payload['form_id'] ) : '',
                'payload'    => wp_json_encode( $payload ),
                'status'     => self::STATUS_QUEUED,
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%s' ]
        );

        if ( false === $insert ) {
            return new WP_Error( 'queue_insert_failed', __( 'Unable to queue submission.', 'halt-tracker' ) );
        }

        return $wpdb->insert_id;
    }

    /**
     * Process queue.
     */
    public function process_queue( $manual_trigger = false ) {
        global $wpdb;

        if ( get_transient( self::LOCK_KEY ) ) {
            return;
        }
        set_transient( self::LOCK_KEY, 1, 5 * MINUTE_IN_SECONDS );

        $max_attempts = (int) get_option( self::OPT_MAX_ATTEMPTS, 6 );
        if ( $max_attempts < 1 ) {
            $max_attempts = 6;
        }

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->queue_table} WHERE status IN (%s,%s) AND attempts < %d ORDER BY id ASC LIMIT 10",
                self::STATUS_QUEUED,
                self::STATUS_RETRY,
                $max_attempts
            ),
            ARRAY_A
        );

        if ( empty( $rows ) ) {
            delete_transient( self::LOCK_KEY );
            return;
        }

        $report = [
            'timestamp' => current_time( 'mysql' ),
            'processed' => 0,
            'success'   => 0,
            'failed'    => 0,
            'jobs'      => [],
        ];

        foreach ( $rows as $row ) {
            $payload = json_decode( $row['payload'], true );
            $result  = $this->dispatch_submission_to_tracker( $payload, $row );
            $report['processed']++;

            if ( is_wp_error( $result ) ) {
                $is_recoverable = $this->is_recoverable_error( $result );
                $new_status     = $is_recoverable ? self::STATUS_RETRY : self::STATUS_FAILED;
                $wpdb->update(
                    $this->queue_table,
                    [
                        'status'     => $new_status,
                        'attempts'   => $row['attempts'] + 1,
                        'updated_at' => current_time( 'mysql' ),
                        'last_error' => $result->get_error_message(),
                    ],
                    [ 'id' => $row['id'] ],
                    [ '%s', '%d', '%s', '%s' ],
                    [ '%d' ]
                );

                if ( ! $is_recoverable ) {
                    $report['failed']++;
                }

                $report['jobs'][] = [
                    'id'       => $row['id'],
                    'form_id'  => $row['form_id'],
                    'status'   => 'error',
                    'message'  => $result->get_error_message(),
                    'attempts' => $row['attempts'] + 1,
                ];
            } else {
                $wpdb->update(
                    $this->queue_table,
                    [
                        'status'     => self::STATUS_SUCCESS,
                        'attempts'   => $row['attempts'] + 1,
                        'updated_at' => current_time( 'mysql' ),
                        'last_error' => '',
                    ],
                    [ 'id' => $row['id'] ],
                    [ '%s', '%d', '%s', '%s' ],
                    [ '%d' ]
                );

                $report['success']++;
                $report['jobs'][] = [
                    'id'       => $row['id'],
                    'form_id'  => $row['form_id'],
                    'status'   => 'success',
                    'message'  => 'Submission sent to Tracker',
                    'attempts' => $row['attempts'] + 1,
                ];
            }
        }

        $this->store_report( $report );
        delete_transient( self::LOCK_KEY );
    }

    /**
     * Determine if error is recoverable.
     */
    private function is_recoverable_error( WP_Error $error ) {
        $code = $error->get_error_code();
        if ( in_array( $code, [ 'http_500', 'http_502', 'http_503', 'http_request_failed' ], true ) ) {
            return true;
        }
        return false;
    }

    /**
     * Get record type based on form routing settings.
     */
    private function get_record_type_for_form( $form_id ) {
        // Get form routing settings
        $resource_forms = get_option( self::OPT_FORM_ROUTING_RESOURCE, '' );
        $lead_forms     = get_option( self::OPT_FORM_ROUTING_LEAD, '' );
        $contact_forms  = get_option( self::OPT_FORM_ROUTING_CONTACT, '' );

        // Parse form IDs (one per line)
        $resource_ids = array_filter( array_map( 'trim', explode( "\n", $resource_forms ) ) );
        $lead_ids     = array_filter( array_map( 'trim', explode( "\n", $lead_forms ) ) );
        $contact_ids  = array_filter( array_map( 'trim', explode( "\n", $contact_forms ) ) );

        // Check which list contains this form ID
        if ( in_array( $form_id, $resource_ids, true ) ) {
            return 'Resource';
        }
        if ( in_array( $form_id, $lead_ids, true ) ) {
            return 'Lead';
        }
        if ( in_array( $form_id, $contact_ids, true ) ) {
            return 'Contact';
        }

        // Fallback to default record type setting
        return get_option( self::OPT_RECORD_TYPE, 'Resource' );
    }

    /**
     * Build contactDetails structure from flat mapped fields.
     * Also handles field name compatibility (lastName → surname).
     */
    private function build_contact_details( $mapped ) {
        // Handle lastName → surname compatibility
        if ( isset( $mapped['lastName'] ) && ! isset( $mapped['surname'] ) ) {
            $mapped['surname'] = $mapped['lastName'];
            unset( $mapped['lastName'] );
        }

        $contact_details = [];
        $contact_field_map = [
            'email'       => 'email',
            'mobile'      => 'mobilePhone',
            'mobilePhone' => 'mobilePhone',
            'telephone'   => 'telephone',
            'phone'       => 'telephone',
            'fax'         => 'fax',
        ];

        foreach ( $contact_field_map as $old_key => $new_key ) {
            if ( isset( $mapped[ $old_key ] ) && ! empty( $mapped[ $old_key ] ) ) {
                $contact_details[ $new_key ] = $mapped[ $old_key ];
                unset( $mapped[ $old_key ] );
            }
        }

        if ( ! empty( $contact_details ) ) {
            $mapped['contactDetails'] = $contact_details;
        }

        return $mapped;
    }

    /**
     * Search for existing contact by email using NEW REST API.
     */
    private function search_contact_by_email( $email, $jwt ) {
        if ( empty( $email ) ) {
            return null;
        }

        $url = trailingslashit( $this->get_new_api_base_url() ) . 'api/v1/Contact/Search';
        $search_body = [
            'email'      => $email,
            'maxResults' => 1,
            'pageNumber' => 1,
        ];

        $response = wp_remote_post(
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $jwt,
                    'Content-Type'  => 'application/json',
                ],
                'body'    => wp_json_encode( $search_body ),
                'timeout' => 20,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return null;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code ) {
            return null;
        }

        $contacts = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! empty( $contacts ) && is_array( $contacts ) && isset( $contacts[0]['contactId'] ) ) {
            return $contacts[0];
        }

        return null;
    }

    /**
     * Create contact using NEW REST API.
     */
    private function create_contact_for_lead( $data, $jwt ) {
        $url = trailingslashit( $this->get_new_api_base_url() ) . 'api/v1/Contact';

        // Ensure contactDetails structure
        if ( ! isset( $data['contactDetails'] ) ) {
            $data = $this->build_contact_details( $data );
        }

        // Validate marketingPreference
        if ( isset( $data['marketingPreference'] ) ) {
            $valid_prefs = [ 'No Preference', 'Opted In', 'Opted Out' ];
            if ( ! in_array( $data['marketingPreference'], $valid_prefs, true ) ) {
                $data['marketingPreference'] = 'No Preference';
            }
        }

        $response = wp_remote_post(
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $jwt,
                    'Content-Type'  => 'application/json',
                ],
                'body'    => wp_json_encode( $data ),
                'timeout' => 20,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 201 !== $code && 200 !== $code ) {
            return new WP_Error( 'contact_creation_failed', 'Failed to create contact: ' . wp_remote_retrieve_body( $response ) );
        }

        return $body;
    }

    /**
     * Dispatch to Tracker using NEW REST API.
     */
    private function dispatch_submission_to_tracker( array $payload, array $row ) {
        $mapped = $this->map_fields_to_tracker( $payload );

        if ( is_wp_error( $mapped ) ) {
            return $mapped;
        }

        // Determine record type based on form routing
        $form_id     = isset( $payload['form_id'] ) ? $payload['form_id'] : '';
        $record_type = $this->get_record_type_for_form( $form_id );

        $jwt = $this->get_jwt_token();
        if ( is_wp_error( $jwt ) ) {
            return $jwt;
        }

        // Build contactDetails structure for all record types
        $mapped = $this->build_contact_details( $mapped );

        // Validate marketingPreference for Contacts
        if ( 'Contact' === $record_type && isset( $mapped['marketingPreference'] ) ) {
            $valid_prefs = [ 'No Preference', 'Opted In', 'Opted Out' ];
            if ( ! in_array( $mapped['marketingPreference'], $valid_prefs, true ) ) {
                $mapped['marketingPreference'] = 'No Preference';
            }
        }

        // Add default currencyCode for Leads
        if ( 'Lead' === $record_type && empty( $mapped['currencyCode'] ) ) {
            $mapped['currencyCode'] = 'GBP';
        }

        // Special handling for Leads - need to link to Contact
        if ( 'Lead' === $record_type ) {
            $email = null;
            if ( isset( $mapped['contactDetails']['email'] ) ) {
                $email = $mapped['contactDetails']['email'];
            }

            if ( $email ) {
                // Search for existing contact
                $existing_contact = $this->search_contact_by_email( $email, $jwt );

                if ( $existing_contact ) {
                    // Use existing contact
                    $contact_id = $existing_contact['contactId'];
                } else {
                    // Create new contact
                    $contact_data = [
                        'source' => $mapped['source'] ?? 'Website',
                    ];

                    // Copy name fields if available
                    foreach ( [ 'firstName', 'surname', 'company', 'jobTitle' ] as $field ) {
                        if ( isset( $mapped[ $field ] ) ) {
                            $contact_data[ $field ] = $mapped[ $field ];
                        }
                    }

                    // Copy contactDetails
                    if ( isset( $mapped['contactDetails'] ) ) {
                        $contact_data['contactDetails'] = $mapped['contactDetails'];
                    }

                    $contact_response = $this->create_contact_for_lead( $contact_data, $jwt );
                    if ( is_wp_error( $contact_response ) ) {
                        return $contact_response;
                    }

                    $contact_id = $contact_response['id'] ?? null;
                }

                // Link Lead to Contact
                if ( $contact_id ) {
                    $mapped['associations'] = [
                        'contacts' => [
                            [ 'id' => intval( $contact_id ) ],
                        ],
                    ];
                }

                // Remove contactDetails from Lead (stored in Contact)
                unset( $mapped['contactDetails'] );
            }
        }

        // Use NEW REST API
        $endpoint = $this->get_rest_endpoint_for_record_type( $record_type );
        $response = $this->post_to_new_api( $endpoint, $mapped, [
            'Authorization' => 'Bearer ' . $jwt,
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $record_id = $this->extract_rest_api_record_id( $response );

        if ( get_option( self::OPT_ACTIVITY_ENABLED ) && $record_id ) {
            $this->create_tracker_activity_new_api( $record_type, $record_id, $payload, $jwt );
        }

        // If this is a Resource and has a job_id, link to opportunity
        if ( 'Resource' === $record_type && $record_id && isset( $payload['fields']['job_id'] ) ) {
            $wp_job_id = intval( $payload['fields']['job_id'] );
            if ( $wp_job_id > 0 ) {
                $this->link_resource_to_opportunity( $record_id, $wp_job_id, $jwt );
            }
        }

        return $response;
    }

    /**
     * Link a resource to an opportunity longlist.
     */
    private function link_resource_to_opportunity( $resource_id, $wp_job_id, $jwt ) {
        // Get TrackerRMS opportunity ID from job post meta
        $tracker_job_id = get_post_meta( $wp_job_id, '_tracker_job_id', true );
        
        if ( empty( $tracker_job_id ) ) {
            // Job post doesn't have a TrackerRMS ID
            return;
        }

        // Use new REST API to add resource to opportunity longlist
        $url = trailingslashit( $this->get_new_api_base_url() ) . 'api/v1/Opportunity/' . $tracker_job_id . '/longlist/resource/' . $resource_id;
        
        $response = wp_remote_post(
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $jwt,
                    'Content-Type'  => 'application/json',
                ],
                'body'    => wp_json_encode( [] ),
                'timeout' => 20,
            ]
        );

        if ( is_wp_error( $response ) ) {
            error_log( 'Halt Tracker: Failed to link resource ' . $resource_id . ' to opportunity ' . $tracker_job_id . ': ' . $response->get_error_message() );
            return;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code && 201 !== $code ) {
            error_log( 'Halt Tracker: Failed to link resource ' . $resource_id . ' to opportunity ' . $tracker_job_id . ': HTTP ' . $code . ' - ' . wp_remote_retrieve_body( $response ) );
        }
    }

    /**
     * Extract record ID from NEW REST API response.
     */
    private function extract_rest_api_record_id( $response ) {
        if ( ! is_array( $response ) ) {
            return null;
        }
        // NEW API returns {id: 123, success: true}
        if ( isset( $response['id'] ) ) {
            return intval( $response['id'] );
        }
        // Try other possible ID fields
        if ( isset( $response['resourceId'] ) ) {
            return intval( $response['resourceId'] );
        }
        if ( isset( $response['contactId'] ) ) {
            return intval( $response['contactId'] );
        }
        if ( isset( $response['leadId'] ) ) {
            return intval( $response['leadId'] );
        }
        return null;
    }

    /**
     * Extract record ID from response (legacy Widget API format).
     */
    private function extract_record_id( $response ) {
        if ( ! is_array( $response ) ) {
            return null;
        }
        foreach ( $response as $key => $value ) {
            if ( is_array( $value ) && isset( $value['success'] ) && ! empty( $value['id'] ) ) {
                return intval( $value['id'] );
            }
        }
        return null;
    }

    /**
     * Get NEW REST API endpoint for record type.
     */
    private function get_rest_endpoint_for_record_type( $record_type ) {
        switch ( $record_type ) {
            case 'Contact':
                return 'api/v1/Contact';
            case 'Lead':
                return 'api/v1/Lead';
            case 'Resource':
            default:
                return 'api/v1/Resource';
        }
    }

    /**
     * Create activity using NEW REST API.
     */
    private function create_tracker_activity_new_api( $record_type, $record_id, array $payload, $jwt ) {
        $subject_template = get_option( self::OPT_ACTIVITY_SUBJECT, 'Website enquiry' );
        $notes_template   = get_option( self::OPT_ACTIVITY_NOTES, 'Submitted via WordPress' );

        $replacements = [
            '{{form_id}}'  => $payload['form_id'] ?? '',
            '{{timestamp}}'=> current_time( 'mysql' ),
        ];

        $subject = strtr( $subject_template, $replacements );
        $notes   = strtr( $notes_template, $replacements );

        $activity_data = [
            'recordType' => $record_type,
            'recordId'   => $record_id,
            'subject'    => $subject,
            'note'       => $notes,
        ];

        $url = trailingslashit( $this->get_new_api_base_url() ) . 'api/v1/Activity';

        wp_remote_post(
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $jwt,
                    'Content-Type'  => 'application/json',
                ],
                'body'    => wp_json_encode( $activity_data ),
                'timeout' => 20,
            ]
        );
    }

    /**
     * Create activity in Tracker (legacy Widget API).
     */
    private function create_tracker_activity( $record_type, $record_id, array $payload, $jwt ) {
        $subject_template = get_option( self::OPT_ACTIVITY_SUBJECT, 'Website enquiry' );
        $notes_template   = get_option( self::OPT_ACTIVITY_NOTES, 'Submitted via WordPress' );

        $replacements = [
            '{{form_id}}'  => $payload['form_id'] ?? '',
            '{{timestamp}}'=> current_time( 'mysql' ),
        ];

        $subject = strtr( $subject_template, $replacements );
        $notes   = strtr( $notes_template, $replacements );

        $body = [
            'trackerrms' => [
                'createActivity' => [
                    'credentials' => [
                        'oauthtoken' => 'Bearer ' . $jwt,
                    ],
                    'data' => [
                        'recordType' => $record_type,
                        'recordId'   => $record_id,
                        'subject'    => $subject,
                        'notes'      => $notes,
                    ],
                ],
            ],
        ];

        $this->post_to_tracker( 'api/widget/createActivity', $body, [
            'Authorization' => 'Bearer ' . $jwt,
        ] );
    }

    /**
     * Determine operation key.
     */
    private function get_operation_for_record_type( $record_type ) {
        switch ( $record_type ) {
            case 'Contact':
                return 'createContact';
            case 'Lead':
                return 'createLead';
            case 'Resource':
            default:
                return 'createResource';
        }
    }

    /**
     * Determine endpoint.
     */
    private function get_widget_endpoint_for_record_type( $record_type ) {
        switch ( $record_type ) {
            case 'Contact':
                return 'api/widget/createContact';
            case 'Lead':
                return 'api/widget/createLead';
            case 'Resource':
            default:
                return 'api/widget/createResource';
        }
    }

    /**
     * Map submission fields.
     */
    private function map_fields_to_tracker( array $payload ) {
        $field_map = json_decode( get_option( self::OPT_FIELD_MAP, '{}' ), true );
        if ( empty( $field_map ) || ! is_array( $field_map ) ) {
            return new WP_Error( 'field_map_missing', __( 'Field mapping is not configured.', 'halt-tracker' ) );
        }

        $fields  = $payload['fields'] ?? [];
        $mapped  = [];

        foreach ( $field_map as $tracker_key => $source_key ) {
            if ( ! is_string( $tracker_key ) ) {
                continue;
            }
            $mapped_value = $this->resolve_mapped_value( $source_key, $fields, $payload );
            if ( null !== $mapped_value && $mapped_value !== '' ) {
                $mapped[ $tracker_key ] = $mapped_value;
            }
        }

        $required_keys = array_filter( array_map( 'trim', explode( ',', get_option( self::OPT_REQUIRED_FIELDS, '' ) ) ) );
        foreach ( $required_keys as $required ) {
            if ( ! isset( $mapped[ $required ] ) || '' === trim( (string) $mapped[ $required ] ) ) {
                return new WP_Error( 'missing_field', sprintf( __( 'Required field missing: %s', 'halt-tracker' ), $required ) );
            }
        }

        return $mapped;
    }

    /**
     * Resolve mapped value.
     */
    private function resolve_mapped_value( $source_key, array $fields, array $payload ) {
        $source_key = (string) $source_key;
        if ( strpos( $source_key, ':' ) === 0 ) {
            return substr( $source_key, 1 );
        }

        if ( strpos( $source_key, '.' ) !== false ) {
            $parts = explode( '.', $source_key );
            $value = $fields;
            foreach ( $parts as $part ) {
                if ( is_array( $value ) && array_key_exists( $part, $value ) ) {
                    $value = $value[ $part ];
                } else {
                    return null;
                }
            }
            return $value;
        }

        if ( isset( $fields[ $source_key ] ) ) {
            return $fields[ $source_key ];
        }

        if ( isset( $payload['meta'][ $source_key ] ) ) {
            return $payload['meta'][ $source_key ];
        }

        return null;
    }

    /**
     * Store report.
     */
    private function store_report( array $report ) {
        $reports   = get_option( self::OPT_REPORTS, [] );
        $reports[] = $report;
        if ( count( $reports ) > 10 ) {
            $reports = array_slice( $reports, -10 );
        }
        update_option( self::OPT_REPORTS, $reports );
        update_option( self::OPT_LAST_SYNC_TIME, $report['timestamp'] );
        update_option( self::OPT_JOBS_CREATED, (int) $report['success'] );
        update_option( self::OPT_JOBS_UPDATED, 0 );
        update_option( self::OPT_JOBS_FAILED, (int) $report['failed'] );
        update_option( self::OPT_SYNC_ERRORS, (int) $report['failed'] );
    }

    /**
     * Base URL per environment (OLD Widget API).
     */
    private function get_base_url() {
        $env = get_option( self::OPT_ENVIRONMENT, 'row' );
        switch ( $env ) {
            case 'us':
                return 'https://evoapius.tracker-rms.com/';
            case 'ca':
                return 'https://evoapica.tracker-rms.com/';
            case 'row':
            default:
                return 'https://evoapi.tracker-rms.com/';
        }
    }

    /**
     * Base URL for NEW REST API per environment.
     */
    private function get_new_api_base_url() {
        $env = get_option( self::OPT_ENVIRONMENT, 'row' );
        switch ( $env ) {
            case 'us':
                return 'https://evousapi.tracker-rms.com/';
            case 'ca':
                return 'https://evocaapi.tracker-rms.com/';
            case 'row':
            default:
                return 'https://evoglapi.tracker-rms.com/';
        }
    }

    /**
     * Fetch JWT token.
     */
    private function get_jwt_token() {
        $jwt = get_transient( self::TRANSIENT_JWT );
        if ( $jwt ) {
            return $jwt;
        }

        $access_token = $this->get_access_token();
        if ( is_wp_error( $access_token ) ) {
            return $access_token;
        }

        // Use NEW API base for JWT exchange
        $new_api_base = $this->get_new_api_base_url();
        $url = trailingslashit( $new_api_base ) . 'api/Auth/ExchangeToken';
        
        // Per swagger.json: token must be in request body as "bearerToken"
        $response = wp_remote_post(
            $url,
            [
                'headers' => [
                    'Content-Type'  => 'application/json',
                ],
                'body' => wp_json_encode( [
                    'bearerToken' => $access_token,
                ] ),
                'timeout' => 20,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( 200 !== $code || empty( $body['token'] ) ) {
            return new WP_Error( 'jwt_error', __( 'Unable to exchange token for JWT.', 'halt-tracker' ) );
        }

        set_transient( self::TRANSIENT_JWT, $body['token'], 50 * MINUTE_IN_SECONDS );
        return $body['token'];
    }

    /**
     * Fetch access token via refresh token.
     */
    private function get_access_token() {
        $token = get_transient( self::TRANSIENT_ACCESS_TOKEN );
        if ( $token ) {
            return $token;
        }

        $client_id     = get_option( self::OPT_CLIENT_ID );
        $client_secret = get_option( self::OPT_CLIENT_SECRET );
        $refresh_token = get_option( self::OPT_REFRESH_TOKEN );
        $redirect_uri  = get_option( self::OPT_REDIRECT_URI );

        if ( empty( $client_id ) || empty( $client_secret ) || empty( $refresh_token ) ) {
            return new WP_Error( 'missing_credentials', __( 'Tracker OAuth credentials are incomplete.', 'halt-tracker' ) );
        }

        $url  = trailingslashit( $this->get_base_url() ) . 'oAuth2/Token';
        $body = [
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh_token,
        ];

        if ( $redirect_uri ) {
            $body['redirect_uri'] = $redirect_uri;
        }

        $response = wp_remote_post(
            $url,
            [
                'body'    => $body,
                'timeout' => 20,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 !== $code || empty( $data['access_token'] ) ) {
            return new WP_Error( 'oauth_error', __( 'Unable to refresh access token.', 'halt-tracker' ) );
        }

        $expires_in = isset( $data['expires_in'] ) ? intval( $data['expires_in'] ) : 3000;
        set_transient( self::TRANSIENT_ACCESS_TOKEN, $data['access_token'], max( 60, $expires_in - 60 ) );

        if ( ! empty( $data['refresh_token'] ) && $data['refresh_token'] !== $refresh_token ) {
            update_option( self::OPT_REFRESH_TOKEN, sanitize_textarea_field( $data['refresh_token'] ) );
        }

        return $data['access_token'];
    }

    /**
     * Perform POST to Tracker.
     */
    private function post_to_tracker( $endpoint, array $body, array $headers = [] ) {
        $url = trailingslashit( $this->get_base_url() ) . ltrim( $endpoint, '/' );
        $response = wp_remote_post(
            $url,
            [
                'headers' => array_merge(
                    [
                        'Content-Type' => 'application/json',
                    ],
                    $headers
                ),
                'body'    => wp_json_encode( $body ),
                'timeout' => 30,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code < 200 || $code >= 300 ) {
            return new WP_Error( 'http_' . $code, sprintf( __( 'Tracker API error (%d)', 'halt-tracker' ), $code ), $data );
        }

        return $data;
    }

    /**
     * Register custom post type for jobs.
     */
    public function register_job_post_type() {
        register_post_type( 'halt_job', [
            'labels' => [
                'name'               => __( 'Jobs', 'halt-tracker' ),
                'singular_name'      => __( 'Job', 'halt-tracker' ),
                'add_new'            => __( 'Add New Job', 'halt-tracker' ),
                'add_new_item'       => __( 'Add New Job', 'halt-tracker' ),
                'edit_item'          => __( 'Edit Job', 'halt-tracker' ),
                'new_item'           => __( 'New Job', 'halt-tracker' ),
                'view_item'          => __( 'View Job', 'halt-tracker' ),
                'search_items'       => __( 'Search Jobs', 'halt-tracker' ),
                'not_found'          => __( 'No jobs found', 'halt-tracker' ),
                'not_found_in_trash' => __( 'No jobs found in trash', 'halt-tracker' ),
            ],
            'public'             => true,
            'has_archive'        => true,
            'show_in_rest'       => true,
            'menu_icon'          => 'dashicons-portfolio',
            'supports'           => [ 'title', 'editor', 'excerpt', 'custom-fields' ],
            'rewrite'            => [ 'slug' => 'jobs' ],
        ] );
    }

    /**
     * Sync jobs from TrackerRMS.
     */
    public function sync_jobs_from_tracker() {
        if ( ! get_option( self::OPT_JOB_SYNC_ENABLED ) ) {
            return;
        }

        $jwt = $this->get_jwt_token();
        if ( is_wp_error( $jwt ) ) {
            error_log( 'Halt Tracker: JWT error during job sync - ' . $jwt->get_error_message() );
            return;
        }

        $last_sync = get_option( self::OPT_LAST_JOB_SYNC );
        $search_params = [
            // NOTE: Don't filter by publishOnline here - we check it per-job in sync_single_job()
            // This allows us to trash unpublished jobs if they were previously published
            'maxResults'    => 100,
            'pageNumber'    => 1,
            'searchTerm'    => '',
            'onlyMyRecords' => false,
            'includeCustomFields' => true,
            'state'         => 'open', // Only fetch open jobs
        ];

        // Incremental sync: only fetch jobs updated since last sync
        if ( $last_sync ) {
            $search_params['updatedAfter'] = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $last_sync ) );
        }

        $new_api_base = $this->get_new_api_base_url();
        $url = trailingslashit( $new_api_base ) . 'api/v1/Opportunity/Search';

        $response = wp_remote_post(
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $jwt,
                    'Content-Type'  => 'application/json',
                ],
                'body'    => wp_json_encode( $search_params ),
                'timeout' => 30,
            ]
        );

        if ( is_wp_error( $response ) ) {
            error_log( 'Halt Tracker: Job sync HTTP error - ' . $response->get_error_message() );
            return;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code ) {
            error_log( 'Halt Tracker: Job sync API error - HTTP ' . $code );
            return;
        }

        $opportunities = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! is_array( $opportunities ) ) {
            return;
        }

        $synced = 0;
        foreach ( $opportunities as $opp ) {
            if ( $this->sync_single_job( $opp ) ) {
                $synced++;
            }
        }

        update_option( self::OPT_LAST_JOB_SYNC, current_time( 'mysql' ) );
        error_log( sprintf( 'Halt Tracker: Synced %d job(s) from TrackerRMS', $synced ) );
    }

    /**
     * Sync a single job to WordPress.
     */
    private function sync_single_job( array $opp ) {
        $opp_id       = $opp['opportunityId'] ?? 0;
        $publish_flag = $opp['publishOnline'] ?? 'No';

        if ( ! $opp_id ) {
            return false;
        }

        // Check if job already exists
        $existing = get_posts( [
            'post_type'   => 'halt_job',
            'meta_key'    => '_tracker_opportunity_id',
            'meta_value'  => $opp_id,
            'post_status' => 'any',
            'numberposts' => 1,
        ] );

        $post_id = $existing ? $existing[0]->ID : 0;

        // Check if published - TrackerRMS returns 'y', 'yes', true, or 'Yes'
        $publish_lower = strtolower( (string) $publish_flag );
        $is_published  = in_array( $publish_lower, [ 'y', 'yes', '1', 'true' ], true ) || $publish_flag === true;

        // If not published, trash the post
        if ( ! $is_published ) {
            if ( $post_id ) {
                wp_trash_post( $post_id );
            }
            return false;
        }

        // Prepare post data
        $title       = $opp['publishTitle'] ?? $opp['opportunityName'] ?? 'Untitled Job';
        $description = $opp['publishDescription'] ?? $opp['opportunityDescription'] ?? '';
        $location    = $opp['publishLocation'] ?? $opp['location'] ?? '';

        $post_data = [
            'post_title'   => sanitize_text_field( $title ),
            'post_content' => wp_kses_post( $description ),
            'post_type'    => 'halt_job',
            'post_status'  => 'publish',
        ];

        if ( $post_id ) {
            $post_data['ID'] = $post_id;
            wp_update_post( $post_data );
        } else {
            $post_id = wp_insert_post( $post_data );
        }

        if ( ! $post_id || is_wp_error( $post_id ) ) {
            return false;
        }

        // Store metadata
        update_post_meta( $post_id, '_tracker_opportunity_id', $opp_id );
        update_post_meta( $post_id, '_tracker_location', sanitize_text_field( $location ) );
        update_post_meta( $post_id, '_tracker_work_type', sanitize_text_field( $opp['publishWorkType'] ?? '' ) );
        update_post_meta( $post_id, '_tracker_sector', sanitize_text_field( $opp['publishSector'] ?? '' ) );
        update_post_meta( $post_id, '_tracker_salary_from', sanitize_text_field( $opp['publishSalaryFrom'] ?? '' ) );
        update_post_meta( $post_id, '_tracker_salary_to', sanitize_text_field( $opp['publishSalaryTo'] ?? '' ) );
        update_post_meta( $post_id, '_tracker_salary_per', sanitize_text_field( $opp['publishSalaryPer'] ?? '' ) );
        update_post_meta( $post_id, '_tracker_benefits', sanitize_textarea_field( $opp['publishBenefits'] ?? '' ) );
        update_post_meta( $post_id, '_tracker_skills', sanitize_textarea_field( $opp['publishSkills'] ?? '' ) );
        update_post_meta( $post_id, '_tracker_reference', sanitize_text_field( $opp['publishReference'] ?? '' ) );
        update_post_meta( $post_id, '_tracker_synced_at', current_time( 'mysql' ) );

        return true;
    }
}

// Initialization is handled by the loader file

