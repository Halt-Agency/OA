# TrackerRMS WordPress Integration - Setup Complete ✓

## Current Status

✅ **Authentication Working** - Successfully connecting to TrackerRMS NEW REST API  
✅ **Job Detected** - JOB-1026 (Account Manager/Testing Lead) is published and ready to sync  
✅ **WordPress Plugin Updated** - Fixed to correctly detect published jobs  

---

## Your Published Job

**JOB-1026: Account Manager / Testing Lead**
- Reference: JOB-1026
- Status: Published Online ✓
- Publish Date: 2025-12-03
- Ready to sync to WordPress!

---

## Next Steps

### 1. Upload Updated Plugin to WordPress

Upload the **entire** `RoleCall - powered by Halt` folder to your WordPress server:

```
/wp-content/plugins/RoleCall - powered by Halt/
```

### 2. Activate/Reactivate the Plugin

In WordPress admin:
1. Go to **Plugins**
2. **Deactivate** the "Halt Tracker Integration" plugin
3. **Activate** it again (this registers the custom post type and schedules cron jobs)

### 3. Verify Settings

Go to **Halt Tracker → Settings** and confirm:

- ✓ Environment: **ROW** (UK/Rest of World)
- ✓ Client ID: `EvoApi_1.0`
- ✓ Client Secret: `2c318fae-8b98-11e9-bc42-526af7764f64`
- ✓ Refresh Token: `ecffd89dcde0461cb022aa66b337511e`
- ✓ Redirect URI: `https://chrisc714.sg-host.com/tracker-oauth-callback/`
- ✓ Job Sync Enabled: **Checked** ✓

Click **Save Settings**

### 4. Run Manual Job Sync

1. Go to **Halt Tracker → Dashboard**
2. Scroll down to the **"Job Sync (TrackerRMS ↔ WordPress)"** section
3. Click **"Sync Jobs Now"**
4. You should see: "Job sync completed! Check the Jobs post type."

### 5. View Your Job

Go to **Posts → Jobs** in WordPress admin. You should see:
- **Testing Lead** (JOB-1026)

### 6. Display Jobs on Your Website

The jobs are now available as `halt_job` custom posts.

**Option A: Use Divi Query Loop**
1. Add a **Blog** or **Post Grid** module
2. In module settings, set **Post Type** to `halt_job`
3. Customize the layout as needed

**Option B: Create a Custom Template**
```php
<?php
$jobs = new WP_Query([
    'post_type' => 'halt_job',
    'posts_per_page' => -1,
    'post_status' => 'publish',
]);

if ($jobs->have_posts()) :
    while ($jobs->have_posts()) : $jobs->the_post();
        $location = get_post_meta(get_the_ID(), '_tracker_location', true);
        $work_type = get_post_meta(get_the_ID(), '_tracker_work_type', true);
        $salary_from = get_post_meta(get_the_ID(), '_tracker_salary_from', true);
        $salary_to = get_post_meta(get_the_ID(), '_tracker_salary_to', true);
        $reference = get_post_meta(get_the_ID(), '_tracker_reference', true);
        ?>
        
        <div class="job-listing">
            <h2><?php the_title(); ?></h2>
            <?php if ($reference) : ?>
                <p class="job-reference">Ref: <?php echo esc_html($reference); ?></p>
            <?php endif; ?>
            <?php if ($location) : ?>
                <p class="job-location">📍 <?php echo esc_html($location); ?></p>
            <?php endif; ?>
            <?php if ($work_type) : ?>
                <p class="job-type">💼 <?php echo esc_html($work_type); ?></p>
            <?php endif; ?>
            <?php if ($salary_from || $salary_to) : ?>
                <p class="job-salary">
                    💰 <?php echo esc_html($salary_from); ?>
                    <?php if ($salary_to) : ?>
                        - <?php echo esc_html($salary_to); ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <div class="job-description">
                <?php the_content(); ?>
            </div>
        </div>
        
        <?php
    endwhile;
endif;
wp_reset_postdata();
?>
```

---

## Publishing More Jobs

To publish additional jobs (JOB-1025, JOB-1024, etc.):

1. **Log into TrackerRMS**
2. **Open the job** (Opportunity record)
3. **Go to "Advert Details" tab**
4. **Check "Publish to Website"** ✓
5. **Fill in fields:**
   - Publish Title (required)
   - Publish Description (required)
   - Publish Location (optional but recommended)
   - Work Type (Permanent/Contract/Temporary)
   - Salary range
   - Skills/Benefits
6. **Save**
7. **Wait 15 minutes** for automatic sync, OR click **"Sync Jobs Now"** in WordPress

---

## Available Job Metadata Fields

When displaying jobs, you can access these custom fields:

- `_tracker_opportunity_id` - TrackerRMS opportunity ID
- `_tracker_location` - Job location
- `_tracker_work_type` - Permanent/Contract/Temporary
- `_tracker_sector` - Industry sector
- `_tracker_salary_from` - Minimum salary
- `_tracker_salary_to` - Maximum salary
- `_tracker_salary_per` - Salary period (per annum, per day, etc.)
- `_tracker_benefits` - Job benefits
- `_tracker_skills` - Required skills
- `_tracker_reference` - Job reference (e.g., JOB-1026)
- `_tracker_synced_at` - Last sync timestamp

---

## How It Works

### Automatic Sync (Every 15 Minutes)
1. WordPress fetches all open opportunities from TrackerRMS
2. For each opportunity with `publishOnline = 'y'`:
   - Creates new `halt_job` post (if doesn't exist)
   - Updates existing post (if already exists)
   - Syncs all metadata
3. For unpublished opportunities:
   - Moves to trash (if previously published)

### Job Lifecycle
- ✅ **Publish in TrackerRMS** → Job appears on WordPress
- ✅ **Update in TrackerRMS** → Job updates on WordPress (next sync)
- ✅ **Unpublish in TrackerRMS** → Job moves to trash on WordPress
- ✅ **Close in TrackerRMS** → Job stays on WordPress (only open jobs sync)

---

## Troubleshooting

### Jobs Not Appearing?

**Check:**
1. Is "Publish to Website" enabled in TrackerRMS?
2. Is "Job Sync Enabled" checked in WordPress settings?
3. Run manual sync: **Halt Tracker → Dashboard → Sync Jobs Now**
4. Check **Posts → Jobs → All Posts** (including Trash)

### Authentication Errors?

**Check:**
1. Verify all credentials in **Halt Tracker → Settings**
2. Make sure refresh token hasn't expired
3. Check error logs: **Halt Tracker → Sync Log**

### Jobs Not Updating?

**Solution:**
- Automatic sync only fetches jobs updated since last sync
- Use **"Sync Jobs Now"** to force full refresh

---

## Technical Details

### API Endpoints Used

**OLD Widget API** (for candidate submissions):
- Base: `https://evoapi.tracker-rms.com/`
- OAuth: `/oAuth2/Token`
- Create Resource: `/api/widget/createResource`
- Create Activity: `/api/widget/createActivity`

**NEW REST API** (for job sync):
- Base: `https://evoglapi.tracker-rms.com/`
- JWT Exchange: `/api/Auth/ExchangeToken`
- Search Opportunities: `/api/v1/Opportunity/Search`

### Authentication Flow

1. Get OAuth access token (OLD API)
2. Exchange for JWT token (NEW API)
3. Use JWT for all opportunity requests (NEW API)
4. Tokens cached in WordPress transients

### Cron Schedules

- **Candidate Queue**: Every 5 minutes
- **Job Sync**: Every 15 minutes

---

## What's Next?

1. ✅ Upload updated plugin to WordPress
2. ✅ Run manual sync
3. ✅ View JOB-1026 in WordPress
4. ✅ Add more jobs by publishing them in TrackerRMS
5. ✅ Create job listing page/template in Divi
6. ✅ Style and customize as needed!

---

**Support:** If you need help, check the WordPress admin logs or TrackerRMS API documentation.

**Last Updated:** December 3, 2025

