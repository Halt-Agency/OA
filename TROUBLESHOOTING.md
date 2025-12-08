# Quick Troubleshooting: Updates Not Showing

## Most Common Issue: Version Numbers Match

**Git Updater only shows updates when the GitHub version is HIGHER than the installed version.**

### Check Your Installed Version

1. In WordPress Admin, go to **Appearance > Themes**
2. Find "Halt Agency" theme
3. Check the version number shown (likely `1.0.1`)

### Check GitHub Version

1. Visit: https://github.com/Halt-Agency/OA/blob/main/style.css
2. Check the `Version:` header (currently `1.0.1`)

### If Versions Match

**No update will show because they're the same!** To test updates:

1. **Increment the version** in `style.css`:
   ```css
   Version: 1.0.2
   ```

2. **Commit and push**:
   ```bash
   git add style.css
   git commit -m "Bump version to 1.0.2"
   git push origin main
   ```

3. **Refresh Git Updater cache**:
   - Go to **Settings > Git Updater**
   - Click **"Refresh Cache"**
   - Wait 10-30 seconds

4. **Check for updates**:
   - Go to **Dashboard > Updates**
   - Click **"Check Again"**
   - Your theme should now show an update available!

## Other Common Issues

### 1. Cache Not Refreshed
- Always click **"Refresh Cache"** in Git Updater settings after pushing to GitHub
- WordPress caches update checks, so manual refresh is needed

### 2. Git Updater Not Detecting Theme
- Go to **Settings > Git Updater**
- Look for "Halt Agency" in the list
- If it's not there, the theme might not be properly installed or activated

### 3. Repository Access Issues
- If repository is private, you need a GitHub Personal Access Token
- Add it in **Git Updater > Settings > GitHub** tab

### 4. Wrong Branch
- Make sure you're pushing to the `main` branch (as specified in `Primary Branch: main`)
- Git Updater checks the branch specified in the header

## Quick Test Steps

1. ✅ Check installed version in WordPress (Appearance > Themes)
2. ✅ Check GitHub version (view style.css on GitHub)
3. ✅ If same, increment version to 1.0.2
4. ✅ Push to GitHub
5. ✅ Refresh cache in Git Updater
6. ✅ Check Dashboard > Updates

If still not working after these steps, check the full troubleshooting guide in README.md

