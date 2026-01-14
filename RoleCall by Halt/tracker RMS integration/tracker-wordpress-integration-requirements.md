# WordPress ↔ Tracker RMS Integration (Requirements)

**Date:** 2025-11-27  
**Owner:** George Cook (Halt)  
**Working Name:** `halt-tracker` (WordPress plugin)  
**Target stack:** WordPress (PHP 8.1+), Divi, WP REST API, cURL/HTTP, Composer optional

---

## 1) Purpose & Scope

Build a WordPress plugin that integrates Divi forms with Tracker RMS via their REST API. The plugin must:

- Authenticate with Tracker using OAuth 2.0 and send Bearer tokens on requests.
- On Divi form submission, create or update records in Tracker (e.g., Resource/Candidate, Contact, Lead) and optionally create an Activity and attach a document (CV).
- Provide an admin UI for API configuration, environment selection (US/CA/ROW), and field mappings from Divi fields → Tracker fields.
- Provide robust logging, retries, and a background queue for reliability.
- Offer two integration paths (choose per site):
  - **A. Divi Webhook (Recommended)**: Configure Divi forms to POST to plugin webhook endpoint `/wp-json/halt-tracker/v1/ingest` (official Divi feature, more reliable).
  - **B. Action Hook (Fallback)**: Capture Divi form submissions server-side via Divi's `et_pb_contact_form_submit` action (if webhook unavailable).
- Ship as a self-contained plugin; no external SaaS needed.

**Data Flow Direction:**

- **WordPress → Tracker**: Real-time (immediate API calls on form submission via hooks)
- **Tracker → WordPress**:
  - **Jobs/Opportunities**: Real-time webhooks (if configured in Tracker) + daily fallback cron sync
  - **Contacts/Resources/Leads**: On-demand API calls only (no automatic sync)
  - **Event Attendees**: On-demand API calls when admin clicks "Load Attendees" (no webhooks)

**Out of scope (initial release):**

- Automatic bi-directional sync for Contacts/Resources/Leads (Tracker → WordPress).
- Full CRUD UIs for Tracker data inside WP (beyond logs).
- Complex validation rules beyond required fields and simple patterns.
- Non-Divi form builders (CF7, Gravity)(not needed)

---

## 2) External References (Authoritative)

- **Tracker RMS – Authentication + Endpoints (Help)**: OAuth2 Authorization Code flow; Bearer token in requests; region base URLs (US/CA/ROW).  
  Source: Tracker Academy “Help Topic: Authentication”.
- **Tracker RMS – Widget/REST Endpoints (Help)**: Examples and endpoint names for create/get, activities, documents.  
  Source: Tracker Academy lessons for `createResource`, `getIndividualRecord`, `getRecords`, `createActivity`, `attachDocument`.
- **Divi – Form Hook**: `et_pb_contact_form_submit` action fires after contact form submission (since 4.13.1). Use to capture processed field values.

(Exact citations live in the project README and code comments.)
Full documentaion available in swagger.json

---

## 3) Environments & Endpoints

**Regions (per Tracker docs):**

- US: `https://evoapius.tracker-rms.com/`
- Canada: `https://evoapica.tracker-rms.com/`
- UK/Rest of World: `https://evoapi.tracker-rms.com/`

**OAuth 2.0 endpoints (relative to region base):**

- Authorize: `GET /oAuth2/Authorize`
- Token: `POST /oAuth2/Token`
  - `client_id`: `EvoApi_1.0` (plus client-specific secret issued by Tracker)
  - `grant_type`: `authorization_code` and `refresh_token`
  - `redirect_uri`: admin callback URL
  - Response: access token; use as `Authorization: Bearer <token>`

**Core API endpoints (v1 – names per Tracker Widget/REST docs):**

- Create/Upsert Resource (Candidate): `POST /api/widget/createResource`
- Create Activity: `POST /api/widget/createActivity`
- Attach Document to record: `POST /api/widget/attachDocument`
- Get one record: `POST /api/widget/getIndividualRecord`
- Get records: `POST /api/widget/getRecords`

> NOTE: Exact request/response schemas are defined in Tracker’s Swagger; field lists evolve. Treat schemas as authoritative and validate at runtime.

---

## 4) High-Level Flows

### 4.1 Divi → Tracker (Webhook Method - RECOMMENDED) ⭐

**Primary Method: Use Divi's Built-in Webhook Feature**

1. Admin configures Divi form to send webhook to: `POST /wp-json/halt-tracker/v1/ingest`
2. User submits Divi contact form.
3. Divi POSTs form data to plugin webhook endpoint (official Divi feature, more reliable than hooking internal functions).
4. Plugin receives webhook payload with form fields, form ID, and optional file attachments.
5. Plugin validates: required fields present (first/last name, email, plus consent if required).
6. Map Divi fields → Tracker payload(s) using Form Profile matching form ID.
7. Ensure valid access token (refresh if needed).
8. Call `createResource` (or `createContact`/`createLead` if enabled per mapping).
9. If CV file present, upload via `attachDocument` with returned record ID.
10. Optionally create an `Activity` linked to the new record (source=Website Form, subject from form name).
11. Log success; enqueue retry on recoverable failure.

**Benefits of Webhook Method:**

- ✅ Official Divi feature (supported, documented)
- ✅ Less likely to break with Divi updates
- ✅ Simpler implementation (no need to hook internal functions)
- ✅ Works consistently across Divi versions
- ✅ Can handle file uploads via webhook payload

### 4.2 Divi → Tracker (Action Hook Method - Fallback)

**Alternative Method: Hook into Divi's Internal Action**

1. User submits Divi contact form.
2. WordPress fires `et_pb_contact_form_submit( array $processed_fields_values, array $et_contact_error )`.
3. Plugin hooks into this action: `add_action( 'et_pb_contact_form_submit', ... )`.
4. Plugin validates: no `$et_contact_error`, required fields present (first/last name, email, plus consent if required).
5. Map Divi fields → Tracker payload(s).
6. Ensure valid access token (refresh if needed).
7. Call `createResource` (or `createContact`/`createLead` if enabled per mapping).
8. If CV file present, upload via `attachDocument` with returned record ID.
9. Optionally create an `Activity` linked to the new record (source=Website Form, subject from form name).
10. Log success; enqueue retry on recoverable failure.

**When to Use Action Hook:**

- If Divi webhook feature is unavailable or not working
- For sites that prefer server-side hooks over HTTP webhooks
- As a fallback if webhook delivery fails

**Note:** Webhook method (4.1) is preferred for reliability and future-proofing.

### 4.3 External → Tracker (Generic Webhook)

1. External client (non-Divi form, custom form, etc.) POSTs JSON to `POST /wp-json/halt-tracker/v1/ingest` with a form identifier, fields, and files (multipart or base64 for attachments).
2. The plugin verifies a shared secret header and nonce (optional), rate limits per IP, then runs steps 5–11 from 4.1 above.

### 4.4 Event Registration → Tracker (MVP)

1. User submits contact form on an Event post (Custom Post Type: `halt_event`).
2. Form submission triggers webhook (preferred) or action hook (fallback) as per 4.1 or 4.2.
3. Plugin detects event context:
   - Checks if form is on `halt_event` post type, OR
   - Form Profile setting indicates "Is Event Form", OR
   - Webhook payload includes event post ID in meta
4. Map Divi fields → Tracker Contact payload.
5. Extract event title from current post: `get_the_title($event_post_id)` or from webhook meta.
6. Create Contact in Tracker with tag matching event title: `tagText = "{event_title}"`.
7. Log success with event association.

### 4.5 Tracker → WordPress (Jobs/Opportunities)

**Real-time Webhook (Primary Method):**

1. Tracker RMS sends webhook to `POST /wp-json/halt-tracker/v1/tracker-webhook` when Opportunity is created/updated/deleted.
2. Plugin verifies webhook payload (recordType = "Opportunity").
3. For Created/Updated: Plugin calls Tracker API to fetch full Opportunity details, creates/updates WordPress job post.
4. For Deleted: Plugin deletes corresponding WordPress job post.
5. Logs webhook receipt and sync result.

**Daily Fallback Sync (If Webhooks Not Configured):**

1. WordPress cron job runs daily (configurable schedule).
2. Plugin queries Tracker API: `POST /api/v1/Opportunity/Search` for all published opportunities.
3. Compares with existing WordPress job posts.
4. Creates/updates/deletes WordPress posts to match Tracker data.
5. Logs sync summary.

**Event Attendee Management Flow:**

1. Admin navigates to **Tracker → Event Attendees** dashboard page.
2. Admin selects an event from dropdown (populated from `halt_event` CPT).
3. Admin clicks "Load Attendees" button (triggers on-demand API call).
4. Plugin queries Tracker API: `POST /api/v1/Contact/Search` with tag filter matching selected event title.
5. Plugin retrieves full contact details for each match.
6. Plugin caches results in transient (5-minute TTL) to reduce API calls.
7. Plugin displays results in table: Name, Email, Phone, Company, Registration Date, Marketing Preferences.
8. Admin can mark contacts as "Attending" (stored in WordPress meta, not Tracker).
9. Table supports export (CSV) and filtering.
10. "Refresh" button re-queries Tracker API (bypasses cache).

**Note:** This is an on-demand query, not real-time. Data is current as of the last "Load Attendees" click. No webhooks or automatic polling.

---

## 5) Data Mapping & Field Strategy

### 5.1 Mappings

- Admin UI supports multiple **Form Profiles** keyed by:
  - Divi Module CSS ID or Form ID (preferred)
  - WP REST custom “source” key for the webhook path
- Each Profile defines:
  - **Target record type**: Resource (candidate) [default], Contact, Lead
  - **Field map**: Divi field keys → Tracker field keys (free-text keys to match Swagger)
  - **Attachment map**: which Divi field is the file upload (CV), MIME types allowed
  - **Activity options**: create activity? subject template, notes template, include UTM/session data
  - **Deduplication**: choose by email+last name, or always create
  - **Consent flags**: GDPR, marketing
  - **Custom constants**: e.g., `source = "Website"`, `originChannel = "Divi"`

### 5.2 Deduplication

- Before create, optionally `getRecords` with filter on email (and/or last name).
- If found, **update** via `createResource` semantics if supported; otherwise skip or create Activity only.
- Log paths clearly (created vs updated vs skipped).

### 5.3 Attachments

- Accept WP Uploaded file object (native hook) or base64/multipart (webhook).
- Enforce size limit (configurable; default 10 MB) and extension allowlist.
- Call `attachDocument` after record creation; link to the new record ID returned by the API.

### 5.4 Event-Specific Mapping

- **Event Detection**: Plugin detects event context via:
  - Form Profile setting: "Is Event Form" checkbox, OR
  - Current post type check: `get_post_type() === 'halt_event'`
- **Tag Generation**: Event title used as Tracker tag:
  - Extract: `get_the_title($event_post_id)` or form field value
  - Sanitize: remove special chars, trim whitespace
  - Format: Use exact title (e.g., "Summer Conference 2025")
  - Append to existing tags if Contact already exists
- **Form Profile for Events**: Can specify:
  - Target record type: Contact (default for events)
  - Tag field: Auto-populated from event title
  - Marketing preferences: Optional checkboxes on event form
  - Activity note: Include event name and registration date

---

## 6) Authentication & Token Handling

- OAuth 2.0 Authorization Code Flow.
- Settings for: region, client_id, client_secret, redirect URI (auto-generated), and optional API key if provided by Tracker.
- Store tokens securely in `wp_options` (autoload off) and rotate via refresh token.
- Include capability checks; only admins can view secrets.
- Token middleware ensures valid token for every API call, refreshing if 401/expired.

---

## 7) Error Handling & Reliability

- **HTTP status** classification:
  - 2xx: success
  - 4xx: mapping/data/validation issues → log as “failed (permanent)”
  - 5xx/network: transient → enqueue retry with exponential backoff (1m, 5m, 30m, 2h, 6h; max 6 attempts)
- **Queue**: custom table `wp_halt_tracker_queue` or Action Scheduler. Store payload, attempt count, last error.
- **Logging**: custom CPT `halt_tracker_log` (or table) for searchable logs with filters: date, form, outcome, record type, Tracker ID.
- **Alerts**: optional email to admin on repeated failures, with last error message and payload snippet (redacted).

---

## 8) Security

- Nonces on admin forms; capability `manage_options` required.
- Validate/sanitize all inbound fields; strict type casting for known fields.
- Limit outbound payload to mapped fields only.
- Webhook endpoint requires shared secret header `X-Halt-Webhook-Signature` (HMAC SHA256 over body) and timestamp `X-Halt-Timestamp`, with replay window (default 5 minutes).
- File scanning: extension + MIME sniffing; reject executables; size cap.
- Use WP HTTP API with TLS; disallow self-signed certs in production.
- PII in logs redacted by default (emails partially masked).

---

## 9) Admin UI

**Pages:**

1. **Settings → Tracker**
   - Environment: US/CA/ROW
   - OAuth: client_id, client_secret, authorize/connect button, token status, force refresh
   - Webhook: shared secret view/regenerate
   - Retry settings: max attempts, backoff multipliers
   - Logging toggles, PII redaction toggle
2. **Form Profiles** (List/Add/Edit)
   - Profile name, Source key (for external webhook), Divi form identifier (Form ID for webhook method, CSS ID for action hook method)
   - Target record type
   - Field mapping repeater (Divi key, Tracker key, required toggle)
   - Attachment field
   - Activity options
   - Deduplication strategy
   - Test run button (dry-run hits Tracker sandbox if configured)
   - **Webhook Setup Instructions**: Display webhook URL (`/wp-json/halt-tracker/v1/ingest`) and instructions for configuring in Divi form settings

**Developer aides:**

- "Inspect last submission" tool showing raw Divi payload and resolved map.
- "Send test payload" to sandbox with sample JSON.

3. **Event Attendees** (MVP)
   - Event selector dropdown (populated from `halt_event` CPT)
   - "Load Attendees" button queries Tracker for contacts with matching tag
   - Table display: Name, Email, Phone, Company, Registration Date, Marketing Preferences
   - "Mark as Attending" checkbox per contact (stored in WP meta: `halt_event_attending_{event_id}_{contact_id}`)
   - Export to CSV button
   - Refresh button to re-query Tracker
   - Status indicator: "X contacts found" / "Y marked as attending"

---

## 10) Developer Notes (Cursor-friendly)

### 10.1 File/Folder Structure

```
wp-content/plugins/halt-tracker/
  halt-tracker.php                # Plugin bootstrap
  readme.txt
  /includes
    class-plugin.php              # Service container, init, hooks
    class-admin.php               # Settings pages, forms, assets
    class-rest.php                # WP REST routes (/halt-tracker/v1/*)
    class-divi-listener.php       # Hooks: et_pb_contact_form_submit
    class-tracker-client.php      # HTTP client, auth, retries
    class-mapper.php              # Field mapping & validation
    class-queue.php               # Queue and cron/Action Scheduler integration
    class-logger.php              # Log writer; CPT or table
    class-events.php              # Event CPT registration, attendee management
    helpers.php
  /assets
    admin.css
    admin.js
  /views
    settings.php
    profile-edit.php
    event-attendees.php           # Event attendees dashboard page
```

### 10.2 Key Hooks

**Primary (Webhook Method):**

- `register_rest_route( 'halt-tracker/v1', '/ingest', [ 'methods' => 'POST', 'callback' => [ Rest::class, 'ingest' ], 'permission_callback' => '__return_true' ] );` - Receives Divi webhook POSTs

**Fallback (Action Hook Method):**

- `add_action( 'et_pb_contact_form_submit', 'Halt\Tracker\Divi_Listener::handle', 10, 2 );` - Hooks into Divi's internal action (use only if webhook unavailable)

**Other Routes:**

- `register_rest_route( 'halt-tracker/v1', '/tracker-webhook', [ 'methods' => 'POST', 'callback' => [ Rest::class, 'handle_tracker_webhook' ], 'permission_callback' => '__return_true' ] );` - Receives Tracker → WordPress webhooks for jobs

**Cron/Actions:**

- `add_action( 'halt_tracker_process_queue', [ Queue::class, 'run' ] );` + schedule via `wp_cron` or Action Scheduler.
- `add_action( 'halt_tracker_daily_sync', [ Jobs::class, 'sync_all_jobs' ] );` + schedule daily cron for fallback job sync.

### 10.3 Tracker Client (Pseudo)

```php
$client = new Tracker_Client( $base_url, $client_id, $client_secret );
$token  = $client->token(); // refreshes if needed
$res    = $client->post('/api/widget/createResource', $payload);
$recId  = $res['data']['id'] ?? null;
if ( $hasCv && $recId ) {
    $client->upload('/api/widget/attachDocument', [
        'recordId' => $recId,
        'fileName' => $name,
        'mimeType' => $mime,
        'content'  => base64_encode($bytes),
    ]);
}
```

### 10.4 Mapping Resolution (Pseudo)

```php
$mapped = Mapper::apply( $profile, $divi_fields );
Validator::ensure_required( $mapped, $profile->required );
```

### 10.5 Queue Record (Schema)

```
id (pk), created_at, updated_at, source, profile_id, payload_json, attempts, last_error, status(enum: queued, success, failed_permanent)
```

### 10.6 Event CPT & Attendee Management

- **Custom Post Type**: `halt_event` (public, supports editor, title, custom fields)
- **Event Meta**: Standard WP post meta for event date, capacity, etc.
- **Attendee Tracking**: WP post meta `halt_event_attending_{event_id}_{contact_id}` = boolean
- **Tag Strategy**: Event title used as Tracker tag (sanitized, e.g., "Summer Conference 2025" → tag: "Summer Conference 2025")
- **Contact Search**: Uses Tracker `POST /api/v1/Contact/Search` with `tagText` filter
- **Attendee Table**: Renders via `class-events.php`, queries Tracker on-demand (no webhooks, no automatic polling)
- **Caching**: Results cached in transient with 5-minute TTL to reduce API calls on repeated views
- **Sync Method**: Manual refresh only - admin must click "Load Attendees" to get latest data from Tracker

---

## 11) Edge Cases & Decisions

- Multi-file uploads: support first file only in v1 (configurable), error if >1 unless allowed.
- Large attachments: if > limit, create record without document and log warning; optionally include a public URL if we host the file.
- Partial failures: if create succeeds but attach fails → queue the attach with record ID.
- Dedup false positives: allow “Always create new” toggle.
- Rate limits/quotas: backoff on HTTP 429; respect `Retry-After` if present.

---

## 12) Testing Plan

- Unit tests for Mapper and Validator (PHPUnit).
- Mock HTTP for Tracker client (status 200/400/401/429/500 paths).
- Integration test hitting Tracker Sandbox (create Resource with fake data; purge after).
- Divi form submission test (manual) ensuring hook fires and data reaches queue when Tracker is offline (simulated).
- Webhook ingestion test with HMAC sig and replay protection.
- GDPR sanity test: redaction in logs, opt-in flags propagated.

---

## 13) Deployment & Config

- Ship as standard WP plugin zip.
- On activation: create tables (queue, logs), register CPT if used, schedule cron.
- Admin sets region, OAuth creds; click “Connect” to complete OAuth → token stored.
- Create a Form Profile and map fields (min: first name, last name, email).
- **For Webhook Method (Recommended):** Configure Divi form to send webhook to `/wp-json/halt-tracker/v1/ingest` and match Form Profile by form ID.
- **For Action Hook Method (Fallback):** Put the Divi Contact Form CSS ID into the profile to link.
- Optional: configure Webhook secret for external webhook submissions (non-Divi forms).

---

## 14) Acceptance Criteria (MVP)

**Core Integration:**

- Can authenticate and store/refresh token.
- On Divi form submission (via webhook or action hook), creates a Resource/Contact/Lead in Tracker with mapped fields.
- Webhook method (recommended) properly receives and processes Divi webhook payloads.
- Action hook method (fallback) works if webhook unavailable.
- Optional Activity created and linked.
- If CV uploaded, file appears on the created record in Tracker.
- Logs show success or actionable errors.
- Transient network outage recovers via retry without user intervention.
- Admin can test against Tracker Sandbox and see the raw request/response.
- No fatal PHP errors; PHPCS passes basic checks; works on PHP 8.1+.

**Events MVP:**

- Custom Post Type `halt_event` registered and accessible.
- Event posts can have Divi contact forms in their template.
- Form submission on event post creates Contact in Tracker with tag matching event title.
- Event Attendees dashboard page accessible under **Tracker → Event Attendees**.
- Admin can select event from dropdown and load attendees from Tracker.
- Attendee table displays: Name, Email, Phone, Company, Registration Date, Marketing Preferences.
- Admin can mark contacts as "Attending" (persisted in WP meta).
- Table supports CSV export.
- Tag matching is case-insensitive and handles special characters gracefully.

---

## 15) Data Synchronization Strategy

**WordPress → Tracker (Real-time):**

- Form submissions trigger immediate API calls to Tracker
- Uses queue system with retries for reliability
- No polling needed - event-driven

**Tracker → WordPress:**

- **Jobs/Opportunities**:
  - Primary: Real-time webhooks from Tracker (if configured)
  - Fallback: Daily cron job syncs all jobs
  - Endpoint: `POST /wp-json/halt-tracker/v1/tracker-webhook`
- **Contacts/Resources/Leads**:
  - On-demand API calls only
  - No webhooks, no automatic polling
  - Queried when admin explicitly requests (e.g., "Load Attendees")
- **Event Attendees**:
  - On-demand API calls when admin clicks "Load Attendees"
  - Results cached for 5 minutes
  - No automatic sync - manual refresh required

## 16) Open Questions

- Exact field names per chosen Tracker object (confirm via live Swagger for the target tenant).
- Whether `createResource` supports pure "update by key" vs explicit update endpoint (fallback is get+create).
- Preferred dedupe strategy from the business (email only vs email+name).
- Standard set of Activities (subject templates) desired by stakeholders.
- Event tag format: exact match vs. prefix match (e.g., "Summer Conference 2025" vs. "Event Attendee - Summer Conference 2025").
- Whether to store Tracker Contact IDs in WordPress for faster lookups vs. always querying by tag.

---

## 17) Example Payload Stubs

> These are **stubs**. Use Swagger schemas as the source of truth in code.

**Create Resource (Candidate)**

```json
{
  "trackerrms": {
    "createResource": {
      "credentials": { "oauthtoken": "Bearer <access-token>" },
      "data": {
        "firstName": "Jane",
        "lastName": "Doe",
        "email": "jane@example.com",
        "mobile": "+44 7700 900123",
        "consentMarketing": true,
        "source": "Website"
      }
    }
  }
}
```

**Create Activity**

```json
{
  "trackerrms": {
    "createActivity": {
      "credentials": { "oauthtoken": "Bearer <access-token>" },
      "data": {
        "recordType": "Resource",
        "recordId": 123456,
        "subject": "Website enquiry - {{form_name}}",
        "notes": "Submitted from {{url}} at {{timestamp}}"
      }
    }
  }
}
```

**Attach Document**

```json
{
  "trackerrms": {
    "attachDocument": {
      "credentials": { "oauthtoken": "Bearer <access-token>" },
      "data": {
        "recordType": "Resource",
        "recordId": 123456,
        "fileName": "cv.pdf",
        "mimeType": "application/pdf",
        "contentBase64": "<base64-bytes>"
      }
    }
  }
}
```

**Create Contact (Event Registration)**

```json
{
  "firstName": "Sarah",
  "surname": "Jones",
  "contactDetails": {
    "email": "sarah@example.com",
    "telephone": "+44 20 7946 0000"
  },
  "marketingPreferences": {
    "marketingPreference": "Opted In",
    "emailOptIn": true,
    "textOptIn": false,
    "telephoneOptIn": true
  },
  "tagText": "Summer Conference 2025",
  "source": "Website - Event Registration",
  "note": "Registered for Summer Conference 2025. Dietary: Vegetarian"
}
```

**Search Contacts by Tag (Event Attendees)**

```json
POST /api/v1/Contact/Search
{
  "tagText": "Summer Conference 2025",
  "maxResults": 1000
}
```

---

## 18) Timeline (suggested)

**Core Integration:**

- Day 1–2: Scaffolding, settings UI, OAuth flow
- Day 3–4: Divi hook path + mapper + Tracker client
- Day 5: Attachments + Activity + logging
- Day 6: Queue/retry + webhook endpoint
- Day 7: Sandbox tests, docs, packaging

**Events MVP (Additional):**

- Day 8: Event CPT registration, form detection on event posts
- Day 9: Event Attendees dashboard page, Tracker search by tag, table rendering
- Day 10: Attendee marking, CSV export, testing

---

## 19) Deliverables

- Plugin zip + source.
- README with setup and environment notes.
- Example JSONs and Postman collection for sandbox.
- Minimal screencast/GIF showing admin config and a successful submission end-to-end.
