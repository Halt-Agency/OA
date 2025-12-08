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
