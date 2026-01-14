# Plugin Updates Summary - NEW REST API Integration

## ✅ What Was Updated

### 1. **Switched from Widget API to NEW REST API**
- **Old:** `/api/widget/createResource` (broken - "user not found" errors)
- **New:** `/api/v1/Resource`, `/api/v1/Contact`, `/api/v1/Lead` ✅

### 2. **Added Nested contactDetails Structure**
- Email and phone numbers now stored in `contactDetails` object
- Automatically restructures flat field mappings into nested structure
- **Field mapping:** `"email": "email"` → Becomes `contactDetails.email`

### 3. **Lead Creation with Contact Linking**
- Leads now automatically search for existing Contacts by email
- If Contact doesn't exist, creates one first
- Links Lead to Contact via `associations`
- Result: Lead shows full contact information in TrackerRMS UI

### 4. **Contact Search/Upsert**
- Searches TrackerRMS by email before creating new contacts
- Reuses existing contacts (prevents duplicates)
- **GDPR compliant** - one contact record per person

### 5. **Field Validation**
- `marketingPreference` validated: "No Preference", "Opted In", or "Opted Out"
- `currencyCode` defaults to "GBP" for Leads
- `lastName` → `surname` compatibility (works with either)

### 6. **Webhook-Based Job Sync**
- Real-time job updates via TrackerRMS webhooks
- Daily fallback cron sync
- Endpoint: `/wp-json/halt-tracker/v1/tracker-webhook`

### 7. **Form Routing System**
- Map Divi form IDs to record types (Resource/Contact/Lead)
- Simple text areas - one form ID per line
- Unmapped forms use default record type setting

---

## 📋 How It Works Now

### **Form Submission Flow:**

```
1. Divi form submitted
   ↓
2. Plugin checks form routing settings
   - Is form ID in Resources list? → Resource
   - Is form ID in Leads list? → Lead  
   - Is form ID in Contacts list? → Contact
   - Not listed? → Use default record type
   ↓
3. Maps form fields using field mapping
   ↓
4. Restructures into NEW REST API format:
   - Builds contactDetails object (email, phone)
   - Handles lastName → surname
   - Validates special fields
   ↓
5. For LEADS only:
   a. Searches for existing Contact by email
   b. If not found, creates Contact first
   c. Creates Lead linked to Contact
   ↓
6. For RESOURCES and CONTACTS:
   a. Creates record directly with contactDetails
   ↓
7. If job_id present (job application):
   a. Links Resource to Opportunity longlist
   ↓
8. Optionally creates Activity
```

---

## 🔧 Configuration Required

### **Settings → Form Routing**

```
Resources (Candidates):
cv-upload-form
candidate-registration
job-application-form

Leads (Sales):
content-download-form
client-brief-form
rfp-form

Contacts (General):
newsletter-signup
event-registration-form
general-contact-form
```

### **Settings → Field Mapping** (Global Default)

```json
{
  "firstName": "first_name",
  "surname": "last_name",
  "email": "email",
  "mobile": "phone",
  "source": ":Website"
}
```

**Note:** Email and mobile will be automatically moved into `contactDetails` structure.

---

## 📝 Field Mapping Examples

### **CV Upload Form:**
```json
{
  "firstName": "first_name",
  "surname": "last_name",
  "email": "email",
  "mobile": "phone",
  "jobTitle": "job_title",
  "currentSalaryNum": "current_salary",
  "desiredSalaryNum": "desired_salary",
  "noticePeriod": "notice_period",
  "availableForWork": ":true",
  "source": ":Website - CV Upload",
  "tagText": ":Website Candidate, CV Upload"
}
```

### **Event Registration Form:**
```json
{
  "firstName": "first_name",
  "surname": "last_name",
  "email": "email",
  "mobile": "phone",
  "company": "company_name",
  "marketingPreference": ":Opted In",
  "source": ":Website - Event Registration",
  "tagText": ":Event Attendee",
  "note": "dietary_requirements"
}
```

### **Content Download Form (Lead):**
```json
{
  "firstName": "first_name",
  "surname": "last_name",
  "email": "email",
  "mobile": "phone",
  "company": "company_name",
  "jobTitle": "job_title",
  "source": ":Website - Content Download",
  "tagText": ":Hot Lead, Content Download",
  "description": "download_name",
  "potentialValue": ":5000",
  "currencyCode": ":GBP"
}
```

---

## 🔗 Job Application Linking

### **Divi Form on Job Post Page:**

```html
<form id="job-application-form">
  <input type="text" name="first_name" />
  <input type="text" name="last_name" />
  <input type="email" name="email" />
  <input type="tel" name="phone" />
  
  <!-- Hidden field with WordPress job post ID -->
  <input type="hidden" name="job_id" value="<?php echo get_the_ID(); ?>" />
  
  <button type="submit">Apply</button>
</form>
```

**What Happens:**
1. Creates Resource in TrackerRMS
2. Reads `_tracker_job_id` meta from WordPress job post
3. Adds Resource to Opportunity longlist
4. Candidate appears in job's applicant list!

---

## 🎯 TrackerRMS Webhook Setup

### **For Real-Time Job Sync:**

1. Log into TrackerRMS
2. Go to Settings → Webhooks (or use API)
3. Create webhook:
   - **URL:** `https://yoursite.com/wp-json/halt-tracker/v1/tracker-webhook`
   - **Record Type:** Opportunity
   - **Actions:** Created, Updated, Deleted

**Result:** Jobs update instantly when changed in TrackerRMS!

**Fallback:** Daily cron runs as backup if webhooks fail.

---

## ✅ Tested & Verified

### **Test Results:**

| Record Type | Test ID | Email Saved | Phone Saved | Notes |
|-------------|---------|-------------|-------------|-------|
| Resource | 109 | ✅ | ✅ | Direct contactDetails works |
| Contact | 787 | ✅ | ✅ | Direct contactDetails works |
| Lead | 14 | ✅ | ✅ | Via linked Contact (ID 788) |

### **Lead Contact Linking:**
- ✅ Searches for existing Contact by email
- ✅ Creates Contact if not found
- ✅ Links Lead to Contact
- ✅ Lead shows contact details in TrackerRMS UI
- ✅ Contact shows associated Leads

---

## 🚨 Important Changes

### **API Endpoints Changed:**
- Widget API (`/api/widget/*`) → NEW REST API (`/api/v1/*`)
- Uses NEW API base: `https://evoglapi.tracker-rms.com/`
- Widget API still available for legacy but not recommended

### **Request Structure Changed:**
**Old (Widget API):**
```json
{
  "trackerrms": {
    "createResource": {
      "credentials": {"oauthtoken": "Bearer ..."},
      "data": {"firstName": "John", "email": "..."}
    }
  }
}
```

**New (REST API):**
```json
{
  "firstName": "John",
  "contactDetails": {
    "email": "john@example.com",
    "mobilePhone": "+44 123"
  }
}
```

### **Authentication:**
- NEW REST API requires JWT (already implemented)
- JWT obtained from `/api/auth/exchangetoken`

---

## 🎉 What This Enables

### **For Different Form Types:**

✅ **CV Uploads** → Creates proper Resources with all contact details  
✅ **Event Registrations** → Creates Contacts (ready for Attendee CPT)  
✅ **Content Downloads** → Creates Contact + Lead (shows contact info)  
✅ **Client Briefs** → Creates Contact + Lead (full sales pipeline)  
✅ **Newsletter Signups** → Creates Contact (ready for manual Send List)  
✅ **Job Applications** → Creates Resource + Links to Opportunity  

### **For Data Quality:**

✅ **No duplicate contacts** - Email search prevents duplicates  
✅ **Complete contact history** - One Contact, multiple Leads/interactions  
✅ **GDPR compliant** - Single contact record to manage  
✅ **Better insights** - See all interactions per contact  

---

## 📞 Next Steps

1. ✅ **Plugin Updated** - All changes complete
2. ⏳ **Build Frontend** - Create Divi forms
3. ⏳ **Configure Form Routing** - Map form IDs to record types
4. ⏳ **Setup Webhooks** - Register TrackerRMS webhook for job sync
5. ⏳ **Test Live Forms** - Verify submissions work
6. ⏳ **Build Events/Attendees CPTs** - When ready for event registration

---

## 🔍 Reference Test File

**Kept:** `test-new-api-records.php`
- Shows working examples for all three record types
- Use as reference for correct data structure
- Run anytime to verify API access

