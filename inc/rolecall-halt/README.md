# Halt Tracker Integration

This plugin captures submissions from Divi forms (server-side hook) or any external client posting to the webhook endpoint and relays them to Tracker RMS using the widget REST API. It also manages a retry queue and basic logging inside WordPress.

## Configuration

1. **Install & Activate**  
   Upload the plugin folder (`RoleCall - powered by Halt`) and activate it in WordPress. Activation creates the queue table and schedules the cron event that processes submissions every five minutes.

2. **Tracker Credentials**

   - Navigate to `Halt Tracker → Settings`.
   - Choose the correct _Environment_ (US, CA, or ROW).
   - Enter your Tracker OAuth `client_id`, `client_secret`, and paste the _refresh token_ obtained via the OAuth authorization-code flow.
   - (Optional) Set the `redirect_uri` if Tracker requires it when issuing new refresh tokens.

3. **Webhook Security**

   - Generate a shared secret and enter it in the _Webhook Shared Secret_ field.
   - Any system posting to `/wp-json/halt-tracker/v1/ingest` must add:
     - `X-Halt-Timestamp`: Unix timestamp (seconds)
     - `X-Halt-Webhook-Signature`: `HMAC_SHA256(timestamp.body, secret)`
   - Requests older than 5 minutes or with invalid signatures are rejected.

4. **Field Mapping**

   - Provide JSON mapping in the _Field Mapping_ textarea.
   - Keys = Tracker field names; values = incoming field keys. Prefix a value with `:` to send a constant.
     ```json
     {
       "firstName": "first_name",
       "lastName": "last_name",
       "email": "email",
       "source": ":Website"
     }
     ```
   - List the required Tracker fields (comma separated). Submissions missing these fields stay in the retry state with an error.

5. **Activities & Attachments (optional)**

   - Enable _Activity Logging_ to create an activity for every successful record. Update the subject/notes templates as needed (`{{form_id}}`, `{{timestamp}}` placeholders available).
   - If webhook payloads include base64 file data, specify the field key under _Attachment Field Key_ (feature assumes `files[field]` format for now).

6. **Divi Hook Path**

   - Any Divi contact form automatically triggers the queue whenever there are no validation errors.
   - Use the form’s unique ID (CSS ID) as part of your field mapping if needed (`form_id` is stored alongside each payload for templating).

7. **Webhook Path**

   - POST JSON to `/wp-json/halt-tracker/v1/ingest` with:
     ```json
     {
       "form_id": "careers-form",
       "fields": {
         "first_name": "Jane",
         "last_name": "Doe",
         "email": "jane@example.com"
       },
       "meta": {
         "utm_source": "linkedin"
       },
       "files": [
         {
           "field": "resume",
           "name": "jane-doe.pdf",
           "mime": "application/pdf",
           "content": "<base64>",
           "encoding": "base64"
         }
       ]
     }
     ```

8. **Queue Management**
   - Processing runs automatically via cron.
   - Visit `Halt Tracker → Dashboard` to trigger a manual run or view summary stats.
   - The Sync Log page shows the last three runs with per-item statuses.
   - Use _Clear Sync Lock_ only if a run is stuck (it simply removes the transient lock so the next cron can proceed).

## Notes & Limitations

- The plugin currently focuses on create operations (Resource/Contact/Lead) and does not perform dedupe/update logic.
- Attachments are only processed when webhook payloads include base64 content; Divi file uploads need an additional handler (future work).
- Logging is stored via wp_options (last 10 reports). For larger installations consider piping logs into an external system.
- Ensure WP-Cron is firing reliably or configure a real cron to hit `wp-cron.php` for consistent processing.
