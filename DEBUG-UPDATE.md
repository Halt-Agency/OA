# Debug: Why Update Isn't Showing

## Current Status
- **Installed Version**: `1.0.0` (in WordPress)
- **GitHub Version**: `1.0.1` (on GitHub)
- **Should Show Update**: ✅ YES (`1.0.1` > `1.0.0`)

## Step-by-Step Debugging

### Step 1: Verify GitHub Version
1. Visit: https://github.com/Halt-Agency/OA/blob/main/style.css
2. Check line 8 shows: `Version: 1.0.1`
3. ✅ If yes, continue to Step 2
4. ❌ If no, you need to push the changes

### Step 2: Check Git Updater Detection
1. In WordPress Admin, go to **Settings > Git Updater**
2. Look for **"Halt Agency"** in the themes list
3. Check it shows:
   - Repository: `https://github.com/Halt-Agency/OA`
   - Installed Version: `1.0.0`
   - Latest Version: `1.0.1` (or blank/unknown)

**If theme is NOT in the list:**
- Git Updater isn't detecting your theme
- Check that theme is activated
- Verify `GitHub Theme URI` header is correct

**If Latest Version shows `1.0.0` or blank:**
- Cache needs refreshing (go to Step 3)

### Step 3: Refresh Cache
1. In **Git Updater > Settings**
2. Click **"Refresh Cache"** button
3. Wait 30-60 seconds
4. Check the theme list again
5. Latest Version should now show `1.0.1`

### Step 4: Check Dashboard > Updates
1. Go to **Dashboard > Updates**
2. Click **"Check Again"** button
3. Look for "Halt Agency" in Themes section
4. Should show: `1.0.0` → `1.0.1`

### Step 5: Check for Errors
1. In **Git Updater > Settings**
2. Look for any red error messages
3. Common errors:
   - **"API rate limit exceeded"** → Need GitHub Personal Access Token
   - **"Repository not found"** → Check repository URL
   - **"Authentication failed"** → Need Personal Access Token for private repo

## Common Issues & Fixes

### Issue 1: API Rate Limit
**Symptom**: No updates showing, no errors visible

**Fix**:
1. Go to **Git Updater > Settings > GitHub** tab
2. Create a GitHub Personal Access Token:
   - Go to: https://github.com/settings/tokens
   - Click "Generate new token (classic)"
   - Select scopes: `repo` and `read:package`
   - Copy the token
3. Paste token in Git Updater GitHub settings
4. Click "Save"
5. Refresh cache again

### Issue 2: Theme Not Detected
**Symptom**: Theme doesn't appear in Git Updater settings

**Fix**:
1. Verify theme is activated: **Appearance > Themes**
2. Check `style.css` has correct headers:
   ```css
   GitHub Theme URI: https://github.com/Halt-Agency/OA
   Primary Branch: main
   ```
3. Deactivate and reactivate the theme
4. Refresh Git Updater cache

### Issue 3: Cache Not Refreshing
**Symptom**: Clicking "Refresh Cache" doesn't update version

**Fix**:
1. Enable **"Bypass WP-Cron Background Processing"** in Git Updater settings
2. Click "Refresh Cache" again
3. Wait longer (up to 2 minutes)
4. Check browser console for JavaScript errors

### Issue 4: Version Comparison Issue
**Symptom**: Versions match but format differs

**Fix**:
- Ensure consistent format: `1.0.0`, `1.0.1`, `1.0.2`
- Avoid: `1.00`, `1.01`, `1.0`

## Quick Test: Force Higher Version

If `1.0.1` still doesn't work, try bumping to `1.0.2`:

1. Change `style.css` version to `1.0.2`
2. Commit and push:
   ```bash
   git add style.css
   git commit -m "Bump to 1.0.2"
   git push origin main
   ```
3. Refresh cache in Git Updater
4. Check for updates

## Still Not Working?

1. **Enable WordPress Debug**:
   Add to `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
   Check `wp-content/debug.log` for Git Updater errors

2. **Check Git Updater Logs**:
   - Some versions have a "Logs" tab in Git Updater settings
   - Look for API errors or connection issues

3. **Verify Repository Access**:
   - Visit: https://github.com/Halt-Agency/OA
   - Make sure repository is public OR you have token configured
   - Check repository exists and is accessible

4. **Test with GitHub Release**:
   - Create a GitHub Release with tag `v1.0.1`
   - Sometimes releases are detected more reliably than branch commits

