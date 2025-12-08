# OA - Divi 5 Child Theme

Custom child theme for Divi 5, created by Halt Agency.

## Git Updater Configuration

This theme is configured to work with [Git Updater](https://git-updater.com/). The following headers have been added to `style.css`:

- **GitHub Theme URI**: Required header pointing to your GitHub repository
- **Primary Branch**: Set to `main` (change if your default branch is different)

### Setup Instructions

1. **Update the GitHub Theme URI** in `style.css`:

   - Replace `https://github.com/yourusername/your-repo` with your actual repository URL
   - Format: `https://github.com/owner/repository` (no `.git` extension)

2. **Alternative Git Hosting**:

   - For GitLab: Use `GitLab Theme URI: https://gitlab.com/owner/repository`
   - For Bitbucket: Use `Bitbucket Theme URI: https://bitbucket.org/owner/repository`

3. **Optional Headers** (if needed):

   - `Release Asset: true` - If you want to update via release assets instead of direct Git
   - `Primary Branch: main` - Already set, change if using a different branch name

4. **Install Git Updater Plugin**:
   - Install and activate the Git Updater plugin on your WordPress site
   - For private repositories, add your Personal Access Token in Git Updater settings

## How to Trigger Updates

To trigger an update notification in WordPress, follow these steps:

### Method 1: Version Number Update (Recommended)

1. **Update the Version in `style.css`**:

   - Increment the `Version` header in `style.css` (e.g., from `1.0.0` to `1.0.1`)
   - Git Updater compares version numbers to detect updates

2. **Commit and Push to GitHub**:

   ```bash
   git add style.css
   git commit -m "Bump version to 1.0.1"
   git push origin main
   ```

3. **Refresh Git Updater Cache**:
   - In WordPress Admin, go to **Git Updater > Settings**
   - Click **"Refresh Cache"** button
   - Git Updater will check for the new version and show an update notification

### Method 2: Create a Git Tag/Release (Optional but Recommended)

1. **Create a Git Tag**:

   ```bash
   git tag -a v1.0.1 -m "Version 1.0.1"
   git push origin v1.0.1
   ```

2. **Or Create a GitHub Release**:

   - Go to your GitHub repository
   - Click **"Releases"** → **"Draft a new release"**
   - Enter tag version (e.g., `v1.0.1`)
   - Add release title and description
   - Click **"Publish release"**

   Tagging helps Git Updater identify the latest version more reliably.

### Method 3: Set Up GitHub Webhook (Automatic Updates)

For automatic update checking when you push changes:

1. **In GitHub Repository**:

   - Go to **Settings** → **Webhooks** → **Add webhook**
   - **Payload URL**: `https://yourwebsite.com/wp-json/git-updater/v1/github-webhook/`
   - **Content type**: `application/json`
   - **Events**: Select **"Release"** (or **"Push"** for branch updates)
   - Click **"Add webhook"**

2. **Alternative Webhook URL** (for cron-based checking):
   - **Payload URL**: `https://yourwebsite.com/wp-cron.php`

The webhook will automatically notify WordPress when new releases are published, prompting Git Updater to check for updates.

## How Updates Appear in WordPress UI

After you've pushed an update to GitHub, here's where and how it appears in WordPress:

### Where to See Updates

1. **Dashboard > Updates** (`/wp-admin/update-core.php`):

   - This is the main WordPress updates page
   - Your theme will appear in the **"Themes"** section if an update is available
   - You'll see the current version vs. the new version
   - Click **"Update Themes"** to apply the update

2. **Appearance > Themes** (`/wp-admin/themes.php`):

   - Your theme will show an **"Update Available"** notice
   - The theme card will display the new version number
   - Click **"Update now"** link to update directly from this page

3. **Git Updater > Settings** (`/wp-admin/options-general.php?page=git-updater`):
   - Shows all themes/plugins managed by Git Updater
   - Displays version information and repository details
   - **"Refresh Cache"** button manually checks for updates

### Update Process in WordPress

1. **Automatic Detection** (if webhook is configured):

   - WordPress automatically checks for updates when webhook is triggered
   - Updates appear within minutes of pushing to GitHub

2. **Manual Check** (if no webhook):

   - Go to **Dashboard > Updates**
   - Click **"Check Again"** button
   - Or go to **Git Updater > Settings** and click **"Refresh Cache"**

3. **Applying the Update**:
   - Select your theme in **Dashboard > Updates**
   - Click **"Update Themes"** button
   - WordPress downloads the new version from GitHub
   - Theme files are updated automatically
   - You'll see a success message when complete

### Visual Indicators

- **Update badge**: Red notification badge on "Updates" menu item
- **Version comparison**: Shows "Version X.X.X" → "Version Y.Y.Y"
- **Update notice**: Yellow/blue notice banner on theme card
- **Update button**: "Update now" or "Update Themes" button appears

The update process works just like updating themes from WordPress.org, but the source is your GitHub repository instead.

## Troubleshooting: Updates Not Appearing

If updates aren't showing in WordPress, check these common issues:

### 1. Version Number Check

**Most Common Issue**: The version on GitHub must be HIGHER than the installed version.

- Check installed version: Go to **Appearance > Themes** and look at your theme's version
- Check GitHub version: View `style.css` on GitHub and compare the `Version:` header
- **The GitHub version MUST be higher** (e.g., installed `1.0.0` → GitHub `1.0.1`)

### 2. Refresh Git Updater Cache

1. Go to **Settings > Git Updater** in WordPress Admin
2. Click **"Refresh Cache"** button
3. Wait a few seconds, then check **Dashboard > Updates** again

### 3. Verify Repository Headers

Check that your GitHub `style.css` file has:

- ✅ `GitHub Theme URI: https://github.com/Halt-Agency/OA`
- ✅ `Primary Branch: main` (if your default branch is `main`)
- ✅ `Version: X.X.X` (must be higher than installed version)

### 4. Check Git Updater Settings

1. Go to **Settings > Git Updater**
2. Look for your theme in the list
3. Verify it shows the correct repository URL
4. Check if there are any error messages

### 5. Private Repository Issues

If your repository is **private**, you need:

- A GitHub Personal Access Token with `repo` and `read:package` permissions
- Add the token in **Git Updater > Settings > GitHub** tab

### 6. WP-Cron Issues

If automatic checks aren't working:

1. Go to **Git Updater > Settings**
2. Enable **"Bypass WP-Cron Background Processing for Debugging"**
3. This forces immediate checks instead of waiting for cron

### 7. Manual Version Check

To verify what Git Updater sees:

1. Go to **Git Updater > Settings**
2. Find your theme in the list
3. Check the version numbers shown:
   - **Installed Version**: What's currently on your site
   - **Latest Version**: What Git Updater found on GitHub
   - If they match, no update will show

### 8. Debug Mode

Enable WordPress debugging to see errors:

Add to `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Then check `wp-content/debug.log` for Git Updater errors.

### Quick Checklist

- [ ] Version on GitHub is higher than installed version
- [ ] Changes are committed and pushed to GitHub
- [ ] Clicked "Refresh Cache" in Git Updater settings
- [ ] Repository is public OR Personal Access Token is configured
- [ ] `GitHub Theme URI` header is correct in `style.css` on GitHub
- [ ] Theme is activated in WordPress
- [ ] Git Updater plugin is installed and activated

### Still Not Working?

1. Verify the file on GitHub: Visit `https://github.com/Halt-Agency/OA/blob/main/style.css`
2. Check the version number matches what you expect
3. Try creating a GitHub Release with a tag (e.g., `v1.0.1`)
4. Check Git Updater Settings page for any error messages

## Repository Setup

```bash
git remote set-url origin https://github.com/Halt-Agency/OA.git
```

## Files

- `style.css` - Theme stylesheet with child theme headers
- `functions.php` - Theme functions and asset enqueuing
- `custom.js` - Custom JavaScript (currently empty)

## References

- [Git Updater Required Headers](https://git-updater.com/knowledge-base/required-headers/)
- [Git Updater Usage Guide](https://git-updater.com/knowledge-base/usage/)
