# Fix: Directory Listing Showing Instead of Laravel App

## Problem
You're seeing a directory listing (Index of /) instead of your Laravel application.

## Root Cause
The `.htaccess` rewrite rules aren't working, or the document root isn't set correctly in cPanel.

## Solution 1: Fix .htaccess (Recommended)

### Root `.htaccess` File
Place this in your project root (same level as `public/` folder):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Disable directory listing
    Options -Indexes
    
    # Redirect all requests to public folder (except if already in public)
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### Verify `public/.htaccess` File
Make sure your `public/.htaccess` file exists and contains the standard Laravel rules (should already be correct).

## Solution 2: Change Document Root in cPanel (If .htaccess doesn't work)

If the `.htaccess` approach doesn't work, you need to change the document root in cPanel:

### Steps:
1. **Login to cPanel**
2. **Go to "Domains" or "Subdomains"**
3. **Find your domain:** `abu-endowment.cloud`
4. **Click "Manage" or "Edit"**
5. **Change Document Root from:**
   ```
   /home/username/public_html
   ```
   **To:**
   ```
   /home/username/public_html/public
   ```
6. **Save changes**
7. **Wait a few minutes for changes to propagate**

### Alternative: If using Subdomain
1. Go to **"Subdomains"** in cPanel
2. Find `abu-endowment.cloud`
3. Click **"Manage"**
4. Change **Document Root** to point to `public` folder
5. Save

## Solution 3: Create index.php Redirect (Temporary Fix)

If you can't change document root, create an `index.php` in your root folder:

```php
<?php
// Redirect to public folder
header('Location: /public/');
exit;
```

**Note:** This is a temporary workaround. Solution 1 or 2 is preferred.

## Verification Steps

After applying the fix:

1. **Clear browser cache** (Ctrl+F5)
2. **Visit:** `https://abu-endowment.cloud`
3. **Should see:** Your Laravel welcome page or home page
4. **Should NOT see:** Directory listing

## Common Issues

### Issue: Still seeing directory listing
**Solution:**
- Check if `.htaccess` file exists in root
- Verify file permissions (should be 644)
- Check if `mod_rewrite` is enabled in cPanel
- Try Solution 2 (change document root)

### Issue: 500 Internal Server Error
**Solution:**
- Check `.htaccess` syntax (no typos)
- Check cPanel error logs
- Verify `mod_rewrite` is enabled

### Issue: Assets (CSS/JS) not loading
**Solution:**
- This is normal if document root is changed to `public/`
- Assets should load from root (e.g., `/css/bootstrap.min.css`)
- If using root `.htaccess`, assets are handled automatically

## Testing

After fixing, test these URLs:

1. **Home page:** `https://abu-endowment.cloud` ✅ Should show Laravel app
2. **CSS file:** `https://abu-endowment.cloud/css/bootstrap.min.css` ✅ Should load CSS
3. **JS file:** `https://abu-endowment.cloud/js/main.js` ✅ Should load JS

## File Structure

Your structure should be:
```
/home/username/public_html/  (or wherever your domain points)
├── .htaccess (root - redirects to public/)
├── public/
│   ├── .htaccess (Laravel standard)
│   ├── index.php
│   ├── css/
│   ├── js/
│   └── ...
└── app/
└── ...
```

## Quick Checklist

- [ ] Root `.htaccess` file exists and has correct rules
- [ ] `public/.htaccess` file exists
- [ ] Document root points to `public/` folder (if using Solution 2)
- [ ] `mod_rewrite` is enabled in cPanel
- [ ] File permissions are correct (644 for .htaccess)
- [ ] Cleared browser cache
- [ ] Tested home page loads correctly

## Still Not Working?

If none of the above works:

1. **Check cPanel Error Logs:**
   - Go to cPanel → Errors
   - Look for `.htaccess` related errors

2. **Contact Hosting Support:**
   - Ask them to:
     - Enable `mod_rewrite`
     - Set document root to `public/` folder
     - Verify `.htaccess` files are being read

3. **Alternative: Use Subdomain:**
   - Create subdomain pointing to `public/` folder
   - Use that for your Laravel app

---

**Most Common Fix:** Change document root in cPanel to point to `public/` folder. This is the most reliable solution for cPanel hosting.

