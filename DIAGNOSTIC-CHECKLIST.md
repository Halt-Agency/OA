# Diagnostic Checklist: Theme Not in Git Updater UI

## Current Problem
Theme "Halt Agency" does NOT appear in Git Updater Settings at all - no repository link shown.

## Step-by-Step Diagnostic

### ✅ Step 1: Verify Theme is Installed & Activated

1. Go to **Appearance > Themes** in WordPress
2. Is "Halt Agency" visible? ✅ / ❌
3. Is "Halt Agency" **activated** (not just installed)? ✅ / ❌
   - **CRITICAL**: Git Updater only shows **active** themes by default!

**If not activated:**
- Activate the theme
- Go back to Git Updater Settings
- Click "Refresh Cache"
- Check again

---

### ✅ Step 2: Check Theme Directory Name

**What is your theme directory name?**

1. Via FTP/File Manager, check: `wp-content/themes/[WHAT-IS-HERE]/`
2. Or check WordPress theme details - it shows the directory name

**Current repository name**: `OA`

**Directory MUST be:**
- `OA` (uppercase) - **try this first**
- OR `oa` (lowercase) - if system normalizes

**If directory is NOT `OA` or `oa`:**
- Activate a different theme (like Divi)
- Rename directory to `OA` (or `oa`)
- Reactivate "Halt Agency"
- Refresh Git Updater cache

---

### ✅ Step 3: Verify style.css Headers

Check your `style.css` file on the **server** (not just local):

1. Via FTP/File Manager, open: `wp-content/themes/OA/style.css`
2. Verify it contains:

```css
/*
Theme Name: Halt Agency
GitHub Theme URI: https://github.com/Halt-Agency/OA
Primary Branch: main
Version: 1.0.2
*/
```

**Check:**
- [ ] `GitHub Theme URI` header exists
- [ ] URL is exactly: `https://github.com/Halt-Agency/OA` (no `.git`)
- [ ] No typos or extra spaces
- [ ] Headers are in the comment block at the top

**Common mistakes:**
- ❌ `GitHub Theme URI: https://github.com/Halt-Agency/OA.git` (no `.git`)
- ❌ Missing colon after `GitHub Theme URI`
- ❌ Headers outside the comment block

---

### ✅ Step 4: Check Git Updater Plugin Status

1. Go to **Plugins > Installed Plugins**
2. Is "Git Updater" installed? ✅ / ❌
3. Is "Git Updater" **activated**? ✅ / ❌

**If not activated:**
- Activate Git Updater
- Go to Settings > Git Updater
- Click "Refresh Cache"

---

### ✅ Step 5: Check Git Updater Settings

1. Go to **Settings > Git Updater**
2. What tabs/sections do you see?
3. Is there a "Themes" tab or section?
4. Do you see ANY themes listed? (even other themes)

**If you see other themes but not "Halt Agency":**
- Directory name mismatch (go back to Step 2)
- Headers issue (go back to Step 3)

**If you see NO themes at all:**
- Git Updater might not be scanning properly
- Try deactivating/reactivating Git Updater plugin
- Check for plugin conflicts

---

### ✅ Step 6: Check Repository Accessibility

1. Visit: https://github.com/Halt-Agency/OA
2. Is the repository **public**? ✅ / ❌

**If repository is PRIVATE:**
- You MUST add a GitHub Personal Access Token
- Go to **Git Updater > Settings > GitHub** tab
- Add token with `repo` and `read:package` permissions
- Click "Save"
- Refresh cache

**If repository is PUBLIC:**
- Should work without token
- But adding a token helps avoid API rate limits

---

### ✅ Step 7: Force Theme Rescan

Sometimes WordPress needs to rescan themes:

1. **Deactivate** "Halt Agency" theme
2. Activate a different theme (like Divi)
3. Wait 10 seconds
4. **Reactivate** "Halt Agency"
5. Go to **Git Updater > Settings**
6. Click **"Refresh Cache"**
7. Wait 30-60 seconds
8. Check if theme appears

---

### ✅ Step 8: Check for Plugin Conflicts

1. **Deactivate ALL plugins** except Git Updater
2. Go to **Git Updater > Settings**
3. Click **"Refresh Cache"**
4. Check if theme appears

**If theme appears:**
- Reactivate plugins one by one
- Find which plugin conflicts
- Contact plugin developer or Git Updater support

---

### ✅ Step 9: Enable Debugging

Add to `wp-config.php` (before "That's all, stop editing!"):

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Then:
1. Go to Git Updater Settings
2. Click "Refresh Cache"
3. Check `wp-content/debug.log` for errors
4. Look for Git Updater related errors

---

### ✅ Step 10: Verify File Permissions

Check via FTP/File Manager:

- Theme directory (`wp-content/themes/OA/`): Should be `755`
- `style.css` file: Should be `644`

**If wrong permissions:**
- Fix permissions
- Refresh Git Updater cache

---

## Quick Test: Create Minimal Test

If nothing works, try this minimal test:

1. Create a new test theme directory: `wp-content/themes/test-oa/`
2. Copy your `style.css` to that directory
3. Make sure `style.css` has:
   ```css
   /*
   Theme Name: Test OA
   GitHub Theme URI: https://github.com/Halt-Agency/OA
   Version: 1.0.0
   */
   ```
4. Activate "Test OA" theme
5. Go to Git Updater Settings
6. Click "Refresh Cache"
7. Does "Test OA" appear?

**If yes:** Your original directory name is the issue.
**If no:** There's a deeper configuration problem.

---

## Most Likely Issues (In Order)

1. **Theme not activated** (Git Updater only shows active themes)
2. **Directory name mismatch** (must be `OA` or `oa`)
3. **Headers not on server** (local file different from server file)
4. **Private repository without token** (needs authentication)
5. **Plugin conflict** (another plugin interfering)

---

## Still Not Working?

1. Check Git Updater version - make sure it's up to date
2. Check WordPress version - make sure it's compatible
3. Check PHP version - Git Updater requires PHP 7.4+
4. Contact Git Updater support with:
   - WordPress version
   - Git Updater version
   - PHP version
   - Screenshot of Git Updater Settings page
   - Contents of `style.css` header section

