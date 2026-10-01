# Google Console Origin Error Fix

## 🔍 Error from Frontend

```
[GSI_LOGGER]: The given origin is not allowed for the given client ID.
```

## ✅ Solution: Add Authorized JavaScript Origins

### Step 1: Go to Google Cloud Console

1. Open: https://console.cloud.google.com/apis/credentials
2. Find your OAuth 2.0 Client ID: `470253699627-a50centdev8a3ahhq0e01oiakatu3qh4`
3. Click on it to edit

### Step 2: Add Your Frontend Origin

**In "Authorized JavaScript origins" section, add:**

For **Local Development:**
```
http://localhost:3000
```

For **Production:**
```
https://abu-endowment-mobile.vercel.app
```

**Important:**
- ✅ Include protocol (`http://` or `https://`)
- ✅ Include port if not default (`:3000`)
- ✅ No trailing slash
- ✅ Exact match with your frontend URL

### Step 3: Save Changes

Click **"Save"** at the bottom of the page.

### Step 4: Wait a Few Minutes

Google's changes can take 1-5 minutes to propagate.

---

## 🔍 Current Configuration (From Your Screenshot)

You already have:
- ✅ `http://localhost:3000` (URIs 1)
- ✅ `https://abu-endowment-mobile.vercel.app` (URIs 2)

**But the error suggests the origin still isn't recognized. Check:**

1. **Exact URL match:**
   - Is your frontend running on exactly `http://localhost:3000`?
   - No `www.` or other subdomains?

2. **Protocol match:**
   - Using `http://` not `https://` for localhost?

3. **Port match:**
   - Is your React app on port 3000?

---

## 🧪 Test After Fixing

1. **Clear browser cache** (Ctrl+Shift+Delete)
2. **Hard refresh** (Ctrl+F5)
3. **Try Google sign-in again**

The error should disappear once the origin is properly configured.

---

## 📝 Common Issues

### Issue: Still Getting Origin Error After Adding

**Solutions:**
- Wait 5 minutes for Google to update
- Clear browser cache
- Check exact URL match (case-sensitive)
- Verify no typos in the origin URL

### Issue: Different Port

If your React app runs on a different port (e.g., `3001`), add that too:
```
http://localhost:3001
```

### Issue: Production Domain Changed

If your production domain changed, update it in Google Console.

---

## ✅ Verification

After adding the origin and waiting a few minutes:

1. The `[GSI_LOGGER]` error should disappear
2. Google sign-in button should work
3. No 403 errors from `accounts.google.com`

---

## 🚀 Next: Fix Backend 401 Error

Once the origin error is fixed, you still need to fix the backend 401 error by:

1. **Clearing config cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. **Restarting server**

3. **Checking backend logs** for detailed error

See `GOOGLE_OAUTH_401_FIX.md` for backend fix steps.

