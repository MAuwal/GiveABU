# cPanel Deployment Fix: Styles and Scripts Not Loading

## Problem
Styles and scripts are not rendering in production on cPanel. The page shows unstyled HTML.

## Root Cause
The root `.htaccess` file is rewriting ALL requests (including static assets) to the `public/` folder, which can cause issues with asset loading.

## Solution

### Step 1: Update Root `.htaccess` File

Replace your root `.htaccess` file with this:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Exclude existing files and directories from rewriting
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    # Exclude static assets (CSS, JS, images, fonts) from rewriting
    # These should be served directly from public folder
    RewriteCond %{REQUEST_URI} !^/css/
    RewriteCond %{REQUEST_URI} !^/js/
    RewriteCond %{REQUEST_URI} !^/img/
    RewriteCond %{REQUEST_URI} !^/fonts/
    RewriteCond %{REQUEST_URI} !^/storage/
    RewriteCond %{REQUEST_URI} !^/favicon
    RewriteCond %{REQUEST_URI} !^/icon/
    RewriteCond %{REQUEST_URI} !^/abu_logo\.png$
    RewriteCond %{REQUEST_URI} !\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot|map)$
    
    # Rewrite everything else to public folder
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### Step 2: Verify `public/.htaccess` File

Ensure your `public/.htaccess` file includes rules to serve static assets directly:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Handle X-XSRF-Token Header
    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Allow direct access to static assets (CSS, JS, images, fonts)
    RewriteCond %{REQUEST_URI} \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot|map)$ [NC]
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^ - [L]
    
    # Allow direct access to storage files
    RewriteCond %{REQUEST_URI} ^/storage/
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^ - [L]
    
    # Allow direct access to existing files and directories
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^ - [L]
    
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### Step 3: Verify `.env` Configuration

Ensure your `.env` file has the correct `APP_URL`:

```env
APP_URL=https://abu-endowment.cloud
# or
APP_URL=https://www.abu-endowment.cloud
```

**Important:** Use `https://` if your site uses SSL, or `http://` if not. Include the full domain without trailing slash.

### Step 4: Clear Laravel Cache

After updating files, clear all caches:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 5: Verify File Permissions

Ensure proper file permissions on cPanel:

```bash
# Set permissions for directories
find . -type d -exec chmod 755 {} \;

# Set permissions for files
find . -type f -exec chmod 644 {} \;

# Set permissions for storage and cache
chmod -R 775 storage bootstrap/cache
```

### Step 6: Test Asset URLs

After deployment, test these URLs directly in your browser:

- `https://abu-endowment.cloud/css/bootstrap.min.css`
- `https://abu-endowment.cloud/js/main.js`
- `https://abu-endowment.cloud/img/logo.png`

If these load correctly, the assets are accessible.

## Alternative Solution: Use Absolute URLs

If the above doesn't work, you can modify the layout to use absolute URLs. However, this is not recommended as it's less flexible.

## Troubleshooting

### Issue: Assets still not loading

1. **Check browser console** - Look for 404 errors on asset files
2. **Check file paths** - Verify files exist in `public/css/`, `public/js/`, etc.
3. **Check .htaccess syntax** - Ensure no syntax errors
4. **Check cPanel error logs** - Look for rewrite rule errors

### Issue: 500 Internal Server Error

1. Check `.htaccess` syntax
2. Verify `mod_rewrite` is enabled on your server
3. Check cPanel error logs

### Issue: Assets load but styles don't apply

1. Check browser console for CSS errors
2. Verify CSS files are not corrupted
3. Check for conflicting styles
4. Clear browser cache (Ctrl+F5)

## File Structure Verification

Ensure your file structure looks like this:

```
/
├── .htaccess (root - updated)
├── public/
│   ├── .htaccess (updated)
│   ├── index.php
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── style.css
│   │   └── ...
│   ├── js/
│   │   ├── main.js
│   │   └── ...
│   ├── img/
│   │   └── ...
│   └── storage/ (symlink)
└── ...
```

## Testing Checklist

- [ ] Root `.htaccess` updated
- [ ] `public/.htaccess` updated
- [ ] `.env` has correct `APP_URL`
- [ ] All caches cleared
- [ ] File permissions set correctly
- [ ] Test CSS file loads: `/css/bootstrap.min.css`
- [ ] Test JS file loads: `/js/main.js`
- [ ] Test image loads: `/img/logo.png`
- [ ] Page renders with styles applied
- [ ] No 404 errors in browser console

## Additional Notes

- The root `.htaccess` now excludes static assets from being rewritten
- Static assets are served directly from the `public/` folder
- Laravel routes still work correctly through `public/index.php`
- This solution works with both HTTP and HTTPS

If issues persist after following these steps, check your cPanel error logs for specific error messages.

