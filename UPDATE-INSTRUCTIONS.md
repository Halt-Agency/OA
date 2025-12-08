# Update Instructions - Version Format Issue

## The Problem

Your installed version is `1.00` but GitHub had `1.0.1`. Git Updater may not properly compare these different formats:
- `1.00` (two decimal places)
- `1.0.1` (three-part semantic version)

## The Solution

I've updated the version to `1.0.2` which uses consistent semantic versioning format and is clearly higher than `1.00`.

## Steps to Trigger Update

1. **Commit and push the version change**:
   ```bash
   git add style.css
   git commit -m "Bump version to 1.0.2 - fix version format"
   git push origin main
   ```

2. **In WordPress Admin**:
   - Go to **Settings > Git Updater**
   - Click **"Refresh Cache"** button
   - Wait 10-30 seconds for the cache to refresh

3. **Check for updates**:
   - Go to **Dashboard > Updates**
   - Click **"Check Again"** button
   - Your "Halt Agency" theme should now show:
     - Current: `1.00`
     - Available: `1.0.2`
     - **"Update Themes"** button should appear

4. **Apply the update**:
   - Select "Halt Agency" theme
   - Click **"Update Themes"** button
   - Wait for the update to complete

## If Still Not Working

### Check Git Updater Settings

1. Go to **Settings > Git Updater**
2. Look for "Halt Agency" in the themes list
3. Check if it shows:
   - Repository: `https://github.com/Halt-Agency/OA`
   - Installed Version: `1.00`
   - Latest Version: `1.0.2` (after cache refresh)

### Force Cache Refresh

If cache refresh doesn't work:
1. Go to **Git Updater > Settings**
2. Look for **"Bypass WP-Cron Background Processing"** option
3. Enable it temporarily
4. Click **"Refresh Cache"** again

### Check for API Rate Limits

If you're hitting GitHub API limits:
1. Go to **Git Updater > Settings > GitHub** tab
2. Add a **Personal Access Token** with `repo` and `read:package` permissions
3. This increases your API rate limit from 60/hour to 5000/hour

## Version Format Best Practice

Going forward, always use **semantic versioning** format:
- ✅ `1.0.0`, `1.0.1`, `1.0.2` (three numbers)
- ❌ `1.00`, `1.01` (two decimal places)
- ❌ `1.0` (missing patch number)

This ensures Git Updater can properly compare versions.

