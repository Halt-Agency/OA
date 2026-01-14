<?php
/**
 * Main Page Template for RoleCall
 *
 * This template renders the dashboard shown when visiting the
 * "RoleCall" top‑level menu item. It displays the last sync
 * statistics, information about when automatic syncs occur, a button for
 * running a manual sync and a modal that appears while the sync is in
 * progress. The `$data` array passed from the plugin contains summary
 * values and a `synced` flag indicating whether a manual sync has just
 * completed.
 *
 * @var array $data
 */

$synced = $data['synced'] ?? 0;
?>

<div class="halt-jobsync-container">
    <div class="halt-banner">
        <div class="halt-brand-text">Halt Tracker.</div>
        <div class="halt-logo-container">
            <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) . '/../halt-tracker.php' ) ); ?>assets/halt-logo.png" alt="Halt Logo" class="halt-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <h1 class="halt-logo" style="display: none;">HALT</h1>
        </div>
    </div>
    <div class="halt-main-content">
        <div class="halt-section">
            <h2>Webhook &amp; Divi Queue</h2>
            <?php if ( $synced ) : ?>
                <div class="halt-message halt-message-success">
                    Manual queue run completed.
                </div>
            <?php endif; ?>
            <!-- Sync Stats Table -->
            <div class="halt-stats-table">
                <h3>Recent Activity</h3>
                <table class="halt-stats">
                    <tr>
                        <td><strong>Last Queue Run:</strong></td>
                        <td id="last-sync-time"><?php echo esc_html( $data['last_sync_time'] ?? 'Never' ); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Submissions Sent:</strong></td>
                        <td id="jobs-created"><?php echo esc_html( $data['jobs_created'] ?? '0' ); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Pending Retries:</strong></td>
                        <td id="jobs-updated"><?php echo esc_html( $data['jobs_updated'] ?? '0' ); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Failed (permanent):</strong></td>
                        <td id="jobs-trashed"><?php echo esc_html( $data['jobs_failed'] ?? '0' ); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Errors (last run):</strong></td>
                        <td id="sync-errors"><?php echo esc_html( $data['sync_errors'] ?? '0' ); ?></td>
                    </tr>
                </table>
            </div>
            <div class="halt-countdown-container">
                <p>Queue processing runs every five minutes via WP-Cron. Manual runs can be triggered below for urgent submissions.</p>
                <div class="halt-countdown">
                    <h3>Next Automatic Run</h3>
                    <div class="countdown-timer" id="countdown-timer">
                        <span id="countdown-text">Calculating...</span>
                    </div>
                </div>
            </div>
            <hr class="main-page-section-hr">
            <div class="manual-sync-container">
                <p>Need to flush the queue right now? Trigger a manual run.</p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="halt-sync-form">
                    <?php wp_nonce_field( 'halt_tracker_manual_sync' ); ?>
                    <input type="hidden" name="action" value="halt_tracker_manual_sync" />
                    <button type="submit" class="halt-button" id="halt-sync-button">Manual Sync Now</button>
                </form>
            </div>
        </div>

        <!-- Job Sync Section -->
        <div class="halt-section" style="margin-top: 30px;">
            <h2>Job Sync (TrackerRMS ↔ WordPress)</h2>
            <?php if ( isset( $_GET['jobs_synced'] ) ) : ?>
                <div class="halt-message halt-message-success">
                    Job sync completed! Check the Jobs post type.
                </div>
            <?php endif; ?>
            
            <div class="halt-stats-table">
                <h3>Job Sync Status</h3>
                <table class="halt-stats">
                    <tr>
                        <td><strong>Last Job Sync:</strong></td>
                        <td><?php echo esc_html( get_option( 'halt_tracker_last_job_sync', 'Never' ) ); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Job Sync Enabled:</strong></td>
                        <td><?php echo get_option( 'halt_tracker_job_sync_enabled' ) ? '✓ Yes' : '✗ No'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Jobs in WordPress:</strong></td>
                        <td><?php echo wp_count_posts( 'halt_job' )->publish ?? 0; ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="halt-countdown-container">
                <p>Job sync runs automatically every 15 minutes, pulling published opportunities from TrackerRMS.</p>
                <p><strong>Note:</strong> Only jobs with "Publish Online" enabled in TrackerRMS will be synced.</p>
            </div>
            
            <hr class="main-page-section-hr">
            
            <div class="manual-sync-container">
                <p>Force a job sync right now to pull the latest opportunities from TrackerRMS.</p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'halt_tracker_manual_job_sync' ); ?>
                    <input type="hidden" name="action" value="halt_tracker_sync_jobs" />
                    <button type="submit" class="halt-button">Sync Jobs Now</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Sync Progress Modal -->
<div id="halt-sync-modal" class="halt-sync-modal">
    <div class="halt-modal-content">
        <div class="halt-spinner"></div>
        <h3 class="halt-modal-title">Sync in Progress</h3>
        <p class="halt-modal-text">
            <strong>Please do not close this window!</strong><br>
            The sync is currently running and will complete shortly.
        </p>
        <div class="halt-modal-info">
            <p>
                <strong>What's happening:</strong><br>
                • Pulling pending Divi/webhook submissions<br>
                • Mapping fields to Tracker payloads<br>
                • Sending create requests to Tracker RMS<br>
                • Logging activity + retries<br>
                • Updating sync report
            </p>
        </div>
    </div>
</div>