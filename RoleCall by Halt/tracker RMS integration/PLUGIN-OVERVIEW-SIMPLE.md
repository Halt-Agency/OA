# WordPress ↔ Tracker RMS Integration
## Simple Overview for Non-Technical Users

**What is this?**  
A WordPress plugin that automatically sends form submissions from your website to Tracker RMS, so you don't have to manually copy and paste data.

---

## What Does It Do?

### The Big Picture

When someone fills out a form on your website (like a CV upload, job application, or event registration), the plugin automatically:

1. **Captures** the form data
2. **Sends** it to Tracker RMS
3. **Creates** a new record (Contact, Candidate, or Lead) in Tracker RMS
4. **Logs** what happened (so you can see if something went wrong)

**Result:** No more manual data entry! Everything happens automatically in the background.

---

## What Types of Forms Work?

The plugin works with **Divi contact forms** (the form builder that comes with many WordPress themes). You can use it for:

- ✅ **CV Uploads** → Creates a Candidate record in Tracker RMS
- ✅ **Job Applications** → Creates a Candidate record linked to the job
- ✅ **Event Registrations** → Creates a Contact record with the event name as a tag
- ✅ **Newsletter Signups** → Creates a Contact record with marketing preferences
- ✅ **Content Downloads** → Creates a Lead record (for sales follow-up)
- ✅ **General Contact Forms** → Creates a Contact record

---

## How It Works (Simple Version)

### Step-by-Step Process

1. **Someone fills out a form** on your website
   - Example: John submits his CV through the careers page

2. **The plugin receives the data**
   - It knows which form was submitted
   - It knows what fields were filled in

3. **The plugin matches the form to a "Form Profile"**
   - Each form type has settings that tell the plugin:
     - What type of record to create (Candidate, Contact, or Lead)
     - Which website fields map to which Tracker RMS fields
     - Whether to create an activity note
     - Whether to attach files (like CVs)

4. **The plugin sends data to Tracker RMS**
   - It connects to Tracker RMS using secure authentication
   - It creates the record with all the form data
   - If there's a file (like a CV), it attaches it to the record

5. **The plugin logs what happened**
   - Success: "Record created successfully"
   - Error: "Failed - here's why" (so you can fix it)

**That's it!** The person's information now appears in Tracker RMS automatically.

---

## What Information Gets Sent?

The plugin sends whatever fields you configure. Common examples:

**For CV Uploads:**
- Name, email, phone number
- CV file (PDF, Word doc, etc.)
- Marketing preferences (if they checked boxes)
- Source: "Website - CV Upload"

**For Event Registrations:**
- Name, email, phone
- Event name (as a tag, so you can find all attendees later)
- Dietary requirements (stored in notes)
- Marketing preferences

**For Job Applications:**
- Name, email, phone
- CV file
- Which job they applied for (linked automatically)
- Source: "Website - Job Application"

---

## What About GDPR and Privacy?

The plugin respects privacy settings:

- ✅ **Marketing Preferences**: Stores whether someone opted in to emails, SMS, phone calls
- ✅ **Consent Tracking**: Records what they consented to
- ✅ **Data Handling**: Only sends data you've configured to send
- ✅ **Logging**: Can hide sensitive information in logs

**Important:** The plugin doesn't automatically delete old data. That's your responsibility as the data controller (see GDPR documentation for details).

---

## Event Management (Special Feature)

### How Event Registrations Work

1. **You create an Event** in WordPress (like "Summer Conference 2025")

2. **You add a contact form** to the event page

3. **When someone registers:**
   - A Contact is created in Tracker RMS
   - The event name is added as a tag (e.g., "Summer Conference 2025")
   - Their marketing preferences are saved

4. **To see who's attending:**
   - Go to the plugin's "Event Attendees" page
   - Select the event from a dropdown
   - Click "Load Attendees"
   - See a table of everyone who registered
   - Mark people as "Attending" if needed
   - Export to CSV if you need a list

**Note:** The attendee list is updated when you click "Load Attendees" - it doesn't update automatically. This is by design, so you control when to check for new registrations.

---

## Jobs on Your Website (Bonus Feature)

The plugin can also keep your job listings on the website in sync with Tracker RMS:

### How It Works:

1. **You create a job** in Tracker RMS and mark it "Publish Online"

2. **The plugin automatically:**
   - Creates a job listing on your website
   - Updates it if you change it in Tracker RMS
   - Removes it if you delete it in Tracker RMS

3. **When someone applies:**
   - Their application creates a Candidate record in Tracker RMS
   - The Candidate is automatically linked to the job they applied for
   - They appear in the job's applicant list in Tracker RMS

**Result:** Your website always shows current job openings, and applications automatically link to the right job.

---

## What If Something Goes Wrong?

The plugin has built-in reliability features:

### Automatic Retries
- If Tracker RMS is temporarily unavailable, the plugin waits and tries again
- It will retry up to 6 times over several hours
- You don't need to do anything - it happens automatically

### Logging
- Every form submission is logged
- You can see:
  - What was sent
  - Whether it succeeded or failed
  - Error messages (if something went wrong)
  - When it happened

### Manual Review
- You can view logs in the WordPress admin
- You can manually retry failed submissions
- You can see what data was sent to Tracker RMS

---

## What You Need to Do (Setup)

### Initial Setup (One Time)

1. **Install the plugin** in WordPress

2. **Connect to Tracker RMS:**
   - Enter your Tracker RMS region (US, Canada, or UK/ROW)
   - Enter your API credentials (provided by Tracker RMS)
   - Click "Connect" to authorize

3. **Create Form Profiles:**
   - For each type of form (CV upload, event registration, etc.)
   - Tell the plugin:
     - What type of record to create (Candidate, Contact, or Lead)
     - Which website fields go to which Tracker RMS fields
     - Whether to create activity notes
     - Whether to attach files

4. **Configure Divi Forms:**
   - For each form, set the webhook URL (the plugin provides this)
   - Match the form ID to the Form Profile you created

### Ongoing Use

- **Nothing!** It runs automatically
- Check logs occasionally to make sure everything is working
- Update Form Profiles if you change your forms

---

## What You Can't Do (Limitations)

The plugin has some limitations by design:

- ❌ **Can't automatically sync Contacts FROM Tracker RMS TO WordPress** (only one direction: website → Tracker RMS)
- ❌ **Can't automatically add people to email campaigns** (you still need to do this manually in Tracker RMS or use Mailchimp)
- ❌ **Can't search Tracker RMS data from WordPress** (except for event attendees, which is on-demand)
- ❌ **Can't edit Tracker RMS records from WordPress** (only create new ones)

**Why?** The plugin is designed to be simple and reliable - it does one thing well: send form data to Tracker RMS.

---

## Common Questions

### "Will it create duplicate contacts?"

The plugin can check if a contact already exists (by email) before creating a new one. You can configure this per form.

### "What if someone submits the form twice?"

The plugin will create two records unless you've enabled duplicate checking. This is usually fine - you can merge duplicates in Tracker RMS if needed.

### "How fast does it work?"

Usually within a few seconds. If Tracker RMS is slow or unavailable, the plugin will retry automatically.

### "Can I see what data was sent?"

Yes! Check the logs in the WordPress admin. You can see the exact data that was sent to Tracker RMS.

### "What if I change a form?"

Update the Form Profile to match your new form fields. The plugin will use the new mapping for future submissions.

### "Does it work with other form builders?"

Currently only Divi forms are supported. Other form builders (like Contact Form 7) are not supported in the initial release.

---

## Summary

**What it does:** Automatically sends form submissions from your website to Tracker RMS.

**Why it's useful:** Saves time, reduces errors, ensures nothing gets missed.

**How it works:** Form submitted → Plugin sends to Tracker RMS → Record created.

**What you need to do:** Set it up once, then it runs automatically.

**What to watch:** Check logs occasionally to make sure everything is working.

---

## Need More Details?

For technical implementation details, see: `tracker-wordpress-integration-requirements.md`

For GDPR compliance information, see: `GDPR-DATA-RETENTION-REALITY.txt`

For marketing preferences details, see: `MARKETING-PREFERENCES-DEEP-DIVE.txt`

