# Fix: Theme Not Appearing in Git Updater

## The Problem

Git Updater requires the **theme directory name** to match the **repository name** (in lowercase). This is a critical requirement that's often missed.

## Current Setup
- **Repository Name**: `OA` (on GitHub - uppercase)
- **Theme Name**: `Halt Agency` (in style.css)
- **Theme Directory**: Unknown (check in WordPress)

## Solution: Match Directory Name to Repository (Case-Sensitive!)

### Step 1: Check Your Current Theme Directory Name

In WordPress, your theme is located at:
```
wp-content/themes/[THEME-DIRECTORY-NAME]/
```

**Find out what this directory is called:**
1. Go to **Appearance > Themes** in WordPress
2. Find "Halt Agency" theme
3. Hover over it or check the theme details
4. Note the directory name (it might be `oa`, `OA`, `halt-agency`, `halt_agency`, etc.)

### Step 2: Rename Theme Directory (If Needed)

**Git Updater requires EXACT case matching!**

Since your repository is `OA` (uppercase), try these in order:

1. **First try**: `OA` (uppercase, exact match)
2. **If that doesn't work**: `oa` (lowercase - some systems normalize to lowercase)

The directory **MUST** match the repository name exactly, including case.

**If your directory is NOT named `oa`:**

1. **In WordPress Admin:**
   - Go to **Appearance > Themes**
   - Activate a different theme temporarily (like Divi parent)
   - This allows you to rename the directory

2. **Via FTP/File Manager:**
   - Navigate to `wp-content/themes/`
   - Rename your theme directory to `oa`
   - Example: `halt-agency` → `oa`

3. **Reactivate Theme:**
   - Go back to **Appearance > Themes**
   - Activate "Halt Agency" again
   - WordPress will find it in the new `oa` directory

### Step 3: Verify Headers Are Correct

Your `style.css` should have:
```css
/*
Theme Name: Halt Agency
GitHub Theme URI: https://github.com/Halt-Agency/OA
Primary Branch: main
Version: 1.0.2
*/
```

✅ These look correct!

### Step 4: Refresh Git Updater

1. Go to **Settings > Git Updater**
2. Click **"Refresh Cache"**
3. Wait 30-60 seconds
4. Check if "Halt Agency" now appears in the themes list

### Step 5: Alternative - Add Theme Slug Header

If renaming doesn't work, you can explicitly tell Git Updater the slug:

Add this header to `style.css`:
```css
/*
Theme Name: Halt Agency
GitHub Theme URI: https://github.com/Halt-Agency/OA
Primary Branch: main
Version: 1.0.2
Requires at least: 5.0
Tested up to: 6.4
*/
```

But the directory name matching is usually the key issue.

## Why This Happens

Git Updater uses the directory name to:
1. Identify which themes to check
2. Match them to GitHub repositories
3. Store update information

If the directory name doesn't match the repository name, Git Updater can't link them together.

## Verification Checklist

After renaming:
- [ ] Theme directory is named `oa` (lowercase)
- [ ] Theme is activated in WordPress
- [ ] `style.css` has `GitHub Theme URI: https://github.com/Halt-Agency/OA`
- [ ] Git Updater cache has been refreshed
- [ ] Theme appears in Git Updater Settings > Themes list

## Still Not Working?

1. **Check File Permissions:**
   - Theme directory: `755`
   - `style.css`: `644`

2. **Verify Theme is Active:**
   - Git Updater only shows active themes by default
   - Make sure "Halt Agency" is activated

3. **Check for PHP Errors:**
   - Enable `WP_DEBUG` in `wp-config.php`
   - Check `wp-content/debug.log` for errors

4. **Try Deactivating/Reactivating:**
   - Sometimes WordPress needs to re-scan themes
   - Deactivate theme, wait 10 seconds, reactivate

5. **Check Git Updater Plugin Version:**
   - Make sure Git Updater is up to date
   - Older versions might have detection issues

## Quick Test

After renaming directory to `oa`:
1. Refresh Git Updater cache
2. Check Settings > Git Updater
3. You should see:
   - **Theme**: Halt Agency
   - **Repository**: https://github.com/Halt-Agency/OA
   - **Installed Version**: 1.0.0 (or whatever is installed)
   - **Latest Version**: 1.0.2 (after cache refresh)

