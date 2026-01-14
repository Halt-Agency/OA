<?php
/**
 * Sync Log Page Template for RoleCall
 *
 * Shows up to the last three sync reports stored by the plugin. Each report
 * can be expanded to reveal detailed information about every job processed
 * during that sync, including the vacancy reference, post ID, action taken
 * and both the raw API data and mapped data. A simple accordion pattern is
 * used for collapsing/expanding reports and individual job details.
 *
 * @var array $data
 */

$sync_reports = $data['sync_reports'] ?? [];
?>

<div class="halt-jobsync-container">
    <div class="halt-banner">
        <div class="halt-brand-text">Halt Tracker.</div>
        <div class="halt-logo-container">
            <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) . '/../halt-tracker.php' ) ); ?>assets/halt-logo.png" alt="Halt Logo" class="halt-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <h1 class="halt-logo" style="display: none;">HALT</h1>
        </div>
    </div>
    <div class="halt-sync-log-content">
        <div class="halt-section">
            <h2>Sync Log</h2>
            <p>Review the last three queue runs to see which submissions were sent to Tracker and where retries are pending.</p>
            <?php if ( empty( $sync_reports ) ) : ?>
                <p>No sync reports available yet. Run a manual sync to generate the first report.</p>
            <?php else : ?>
                <?php
                $recent_reports = array_slice( $sync_reports, -3 );
                foreach ( array_reverse( $recent_reports ) as $index => $report ) :
                    ?>
                    <div class="sync-report">
                        <div class="sync-report-header" onclick="toggleSyncReport(<?php echo esc_attr( $index ); ?>)">
                            <div>
                                <h3>
                                    Sync Report - <?php echo esc_html( $report['timestamp'] ); ?>
                                </h3>
                                <p>
                                    <strong>Summary:</strong>
                                    <?php echo esc_html( $report['success'] ?? 0 ); ?> success,
                                    <?php echo esc_html( $report['failed'] ?? 0 ); ?> failed,
                                    <?php echo esc_html( $report['processed'] ?? 0 ); ?> processed total
                                </p>
                            </div>
                            <div class="sync-report-toggle" id="toggle-<?php echo esc_attr( $index ); ?>">▼</div>
                        </div>
                        <div class="sync-report-content" id="content-<?php echo esc_attr( $index ); ?>">
                            <?php if ( ! empty( $report['jobs'] ) ) : ?>
                                <?php foreach ( $report['jobs'] as $job_index => $job ) : ?>
                                    <div class="job-item">
                                        <div class="job-item-header" onclick="toggleJobItem(<?php echo esc_attr( $index ); ?>, <?php echo esc_attr( $job_index ); ?>)">
                                            <div>
                                                <h4>
                                                    Queue #<?php echo esc_html( $job['id'] ); ?> (Form: <?php echo esc_html( $job['form_id'] ?? 'N/A' ); ?>)
                                                </h4>
                                                <p>
                                                    <strong>Status:</strong> <?php echo esc_html( $job['status'] ?? 'unknown' ); ?> |
                                                    <strong>Attempts:</strong> <?php echo esc_html( $job['attempts'] ?? 0 ); ?>
                                                </p>
                                            </div>
                                            <div class="job-item-toggle" id="job-toggle-<?php echo esc_attr( $index ); ?>-<?php echo esc_attr( $job_index ); ?>">▼</div>
                                        </div>
                                        <div class="job-item-content" id="job-content-<?php echo esc_attr( $index ); ?>-<?php echo esc_attr( $job_index ); ?>">
                                            <div class="job-details">
                                                <div class="detail-row">
                                                    <strong>Message:</strong> <?php echo esc_html( $job['message'] ?? '' ); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>