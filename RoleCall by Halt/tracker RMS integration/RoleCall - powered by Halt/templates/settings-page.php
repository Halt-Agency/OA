<?php
/**
 * Settings Page Template for RoleCall
 *
 * Displays API credential fields, post type and field mapping configuration
 * along with a utility to clear the sync lock. The `$data` array
 * contains a `lock_cleared` flag used to show a confirmation message.
 *
 * @var array $data
 */

$lock_cleared = $data['lock_cleared'] ?? 0;
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
            <h2>Settings</h2>
            <?php if ( $lock_cleared ) : ?>
                <div class="halt-message halt-message-success">
                    Queue lock cleared successfully.
                </div>
            <?php endif; ?>
            <form method="post" action="options.php">
                <?php
                settings_fields( Halt_Tracker_Plugin::OPTION_GROUP );
                do_settings_sections( Halt_Tracker_Plugin::OPTION_GROUP );
                ?>
                <table class="halt-form-table">
                    <tr>
                        <th scope="row"><label for="halt_tracker_environment">Environment</label></th>
                        <td>
                            <?php $env = get_option( Halt_Tracker_Plugin::OPT_ENVIRONMENT, 'row' ); ?>
                            <select id="halt_tracker_environment" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_ENVIRONMENT ); ?>">
                                <option value="us" <?php selected( $env, 'us' ); ?>>US (evoapius)</option>
                                <option value="ca" <?php selected( $env, 'ca' ); ?>>Canada (evoapica)</option>
                                <option value="row" <?php selected( $env, 'row' ); ?>>UK/ROW (evoapi)</option>
                            </select>
                            <div class="halt-description">Choose the Tracker RMS region matching your tenant.</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_client_id">Client ID</label></th>
                        <td>
                            <input id="halt_tracker_client_id" type="text" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_CLIENT_ID ); ?>" value="<?php echo esc_attr( get_option( Halt_Tracker_Plugin::OPT_CLIENT_ID ) ); ?>" />
                            <div class="halt-description">Tracker OAuth client ID (usually EvoApi_1.0 or tenant-specific).</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_client_secret">Client Secret</label></th>
                        <td>
                            <input id="halt_tracker_client_secret" type="text" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_CLIENT_SECRET ); ?>" value="<?php echo esc_attr( get_option( Halt_Tracker_Plugin::OPT_CLIENT_SECRET ) ); ?>" />
                            <div class="halt-description">Tracker OAuth client secret issued by Tracker support.</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_refresh_token">Refresh Token</label></th>
                        <td>
                            <textarea id="halt_tracker_refresh_token" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_REFRESH_TOKEN ); ?>" rows="3" cols="60"><?php echo esc_textarea( get_option( Halt_Tracker_Plugin::OPT_REFRESH_TOKEN ) ); ?></textarea>
                            <div class="halt-description">Paste the long-lived refresh token obtained during OAuth authorization.</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_redirect_uri">Redirect URI</label></th>
                        <td>
                            <input id="halt_tracker_redirect_uri" type="url" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_REDIRECT_URI ); ?>" value="<?php echo esc_attr( get_option( Halt_Tracker_Plugin::OPT_REDIRECT_URI ) ); ?>" />
                            <div class="halt-description">The OAuth redirect URI registered with Tracker (optional if using native tool).</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_shared_secret">Webhook Shared Secret</label></th>
                        <td>
                            <input id="halt_tracker_shared_secret" type="text" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_SHARED_SECRET ); ?>" value="<?php echo esc_attr( get_option( Halt_Tracker_Plugin::OPT_SHARED_SECRET ) ); ?>" />
                            <div class="halt-description">Used to verify incoming POSTs to <code>/wp-json/halt-tracker/v1/ingest</code> via HMAC (X-Halt headers).</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_record_type">Tracker Record Type</label></th>
                        <td>
                            <?php $record_type = get_option( Halt_Tracker_Plugin::OPT_RECORD_TYPE, 'Resource' ); ?>
                            <select id="halt_tracker_record_type" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_RECORD_TYPE ); ?>">
                                <option value="Resource" <?php selected( $record_type, 'Resource' ); ?>>Resource (Candidate)</option>
                                <option value="Contact" <?php selected( $record_type, 'Contact' ); ?>>Contact</option>
                                <option value="Lead" <?php selected( $record_type, 'Lead' ); ?>>Lead</option>
                            </select>
                            <div class="halt-description">Determines which Tracker widget endpoint is called.</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_field_map">Field Mapping JSON</label></th>
                        <td>
                            <textarea id="halt_tracker_field_map" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_FIELD_MAP ); ?>" rows="8" cols="60"><?php echo esc_textarea( get_option( Halt_Tracker_Plugin::OPT_FIELD_MAP ) ); ?></textarea>
                            <div class="halt-description">JSON object where keys are Tracker fields and values are form field keys. Prefix values with ":" to pass constants. Example: <code>{"firstName":"first_name","source":":Website"}</code></div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_required_fields">Required Tracker Fields</label></th>
                        <td>
                            <input id="halt_tracker_required_fields" type="text" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_REQUIRED_FIELDS ); ?>" value="<?php echo esc_attr( get_option( Halt_Tracker_Plugin::OPT_REQUIRED_FIELDS, 'firstName,lastName,email' ) ); ?>" />
                            <div class="halt-description">Comma-separated list of mapped keys that must be present before queueing to Tracker.</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_attachment_field">Attachment Field Key</label></th>
                        <td>
                            <input id="halt_tracker_attachment_field" type="text" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_ATTACHMENT_FIELD ); ?>" value="<?php echo esc_attr( get_option( Halt_Tracker_Plugin::OPT_ATTACHMENT_FIELD ) ); ?>" />
                            <div class="halt-description">Optional form field key that supplies a base64 file (from webhook) to send via attachDocument.</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Activity Logging</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_ACTIVITY_ENABLED ); ?>" value="1" <?php checked( get_option( Halt_Tracker_Plugin::OPT_ACTIVITY_ENABLED ), 1 ); ?> />
                                Create Tracker activity after record creation
                            </label>
                            <div class="halt-description">Subject template</div>
                            <input type="text" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_ACTIVITY_SUBJECT ); ?>" value="<?php echo esc_attr( get_option( Halt_Tracker_Plugin::OPT_ACTIVITY_SUBJECT ) ); ?>" />
                            <div class="halt-description">Notes template</div>
                            <textarea name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_ACTIVITY_NOTES ); ?>" rows="3" cols="60"><?php echo esc_textarea( get_option( Halt_Tracker_Plugin::OPT_ACTIVITY_NOTES ) ); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_max_attempts">Max Retry Attempts</label></th>
                        <td>
                            <input id="halt_tracker_max_attempts" type="number" min="1" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_MAX_ATTEMPTS ); ?>" value="<?php echo esc_attr( get_option( Halt_Tracker_Plugin::OPT_MAX_ATTEMPTS, 6 ) ); ?>" />
                            <div class="halt-description">Total attempts before marking a submission as permanently failed.</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_post_type">Default Post Type</label></th>
                        <td>
                            <input id="halt_tracker_post_type" type="text" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_POST_TYPE ); ?>" value="<?php echo esc_attr( get_option( Halt_Tracker_Plugin::OPT_POST_TYPE, 'post' ) ); ?>" />
                            <div class="halt-description">Optional reference post type for front-end display (used for typography styling).</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" colspan="2" style="background: #f0f0f1; padding: 10px;"><strong>Form Routing</strong></th>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="halt-description" style="margin-bottom: 20px;">
                                <strong>Route different Divi forms to different TrackerRMS endpoints.</strong> Enter one form ID per line.
                                <br>Forms not listed will use the default "Tracker Record Type" setting above.
                                <br><em>Tip: You can find the form ID in the Divi form module settings or by inspecting the form element in your browser.</em>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_form_routing_resource">Resources (Candidates)</label></th>
                        <td>
                            <textarea id="halt_tracker_form_routing_resource" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_FORM_ROUTING_RESOURCE ); ?>" rows="5" cols="60" placeholder="cv-upload-form&#10;candidate-registration&#10;job-application-form"><?php echo esc_textarea( get_option( Halt_Tracker_Plugin::OPT_FORM_ROUTING_RESOURCE, '' ) ); ?></textarea>
                            <div class="halt-description">Forms listed here will create <strong>Resources</strong> (candidates) in TrackerRMS.</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_form_routing_lead">Leads (Sales)</label></th>
                        <td>
                            <textarea id="halt_tracker_form_routing_lead" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_FORM_ROUTING_LEAD ); ?>" rows="5" cols="60" placeholder="content-download-form&#10;client-brief-form&#10;rfp-form"><?php echo esc_textarea( get_option( Halt_Tracker_Plugin::OPT_FORM_ROUTING_LEAD, '' ) ); ?></textarea>
                            <div class="halt-description">Forms listed here will create <strong>Leads</strong> (sales opportunities) in TrackerRMS.</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_form_routing_contact">Contacts (General)</label></th>
                        <td>
                            <textarea id="halt_tracker_form_routing_contact" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_FORM_ROUTING_CONTACT ); ?>" rows="5" cols="60" placeholder="newsletter-signup&#10;general-contact-form"><?php echo esc_textarea( get_option( Halt_Tracker_Plugin::OPT_FORM_ROUTING_CONTACT, '' ) ); ?></textarea>
                            <div class="halt-description">Forms listed here will create <strong>Contacts</strong> in TrackerRMS.</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" colspan="2" style="background: #f0f0f1; padding: 10px;"><strong>Job Sync Settings</strong></th>
                    </tr>
                    <tr>
                        <th scope="row"><label for="halt_tracker_job_sync_enabled">Enable Job Sync</label></th>
                        <td>
                            <label>
                                <input id="halt_tracker_job_sync_enabled" type="checkbox" name="<?php echo esc_attr( Halt_Tracker_Plugin::OPT_JOB_SYNC_ENABLED ); ?>" value="1" <?php checked( get_option( Halt_Tracker_Plugin::OPT_JOB_SYNC_ENABLED, 1 ) ); ?> />
                                Enable job sync from TrackerRMS
                            </label>
                            <div class="halt-description">
                                When enabled, jobs marked as "Publish Online" in TrackerRMS will be synced to WordPress as <code>halt_job</code> posts.
                                <br><strong>Sync Method:</strong> Real-time via webhooks + daily fallback sync.
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">TrackerRMS Webhook Setup</th>
                        <td>
                            <div style="background: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 4px;">
                                <p style="margin: 0 0 10px 0;"><strong>For real-time job updates, register this webhook in TrackerRMS:</strong></p>
                                <ol style="margin: 0 0 15px 20px;">
                                    <li>Log in to TrackerRMS</li>
                                    <li>Navigate to Settings → Webhooks (or use the API)</li>
                                    <li>Create a new webhook with these settings:
                                        <ul style="margin: 5px 0 0 20px;">
                                            <li><strong>URL:</strong> <input type="text" readonly value="<?php echo esc_url( rest_url( 'halt-tracker/v1/tracker-webhook' ) ); ?>" style="width: 100%; max-width: 500px; font-family: monospace; font-size: 12px;" onclick="this.select();" /></li>
                                            <li><strong>Record Type:</strong> Opportunity</li>
                                            <li><strong>Action:</strong> Created, Updated, Deleted (select all three)</li>
                                        </ul>
                                    </li>
                                </ol>
                                <p style="margin: 0; color: #666; font-size: 13px;">
                                    <strong>Note:</strong> If webhooks aren't configured, the plugin will still sync jobs daily as a fallback.
                                    Webhooks provide instant updates when jobs are created, updated, or deleted in TrackerRMS.
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
                <button type="submit" class="halt-button">Save Settings</button>
            </form>
            <hr style="margin: 40px 0; border: none; border-top: 1px solid #ddd;" />
            <h3>Sync Management</h3>
            <p>Use these tools to manage the sync process.</p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'halt_tracker_clear_lock' ); ?>
                <input type="hidden" name="action" value="halt_tracker_clear_lock" />
                <button type="submit" class="halt-button halt-button-secondary">Clear Sync Lock</button>
            </form>
            <p class="halt-description">Clear the sync lock if a sync appears to be stuck. This should only be used if you're certain no sync is currently running.</p>
        </div>
    </div>
</div>