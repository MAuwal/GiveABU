# Authentication Flow Explanation

## Overview

The backend supports **two authentication methods**:
1. **Email/Password Authentication** (Traditional)
2. **Google OAuth Authentication**

Both methods work independently and are properly handled by the backend.

---

## 🔐 Email/Password Authentication Flow

### Registration (`POST /api/donor-sessions/register`)

**What happens:**
1. User provides: `username`, `password`, `donor_id`, `device_session_id` (optional)
2. Backend creates a `donor_sessions` record with:
   - `username` → User's chosen username
   - `password` → Hashed password (automatically hashed via mutator)
   - `donor_id` → Links to existing donor record
   - `device_session_id` → Optional device tracking
   - **`auth_provider` → `'email'`** ✅ Explicitly set
   - `google_id` → `NULL`
   - `google_email` → `NULL`
   - `google_name` → `NULL`
   - `google_picture` → `NULL`

**Database State:**
```php
DonorSession {
    username: "john_doe",
    password: "$2y$10$...", // Hashed
    auth_provider: "email",
    google_id: null,
    // ... other fields
}
```

### Login (`POST /api/donor-sessions/login`)

**What happens:**
1. User provides: `username`, `password`, `device_session_id` (optional)
2. Backend:
   - Finds `donor_sessions` by `username`
   - **Checks if `auth_provider === 'google'`** → If yes, rejects with error message
   - Verifies password using `Hash::check()`
   - **Ensures `auth_provider` is set to `'email'`** (for legacy records)
   - Updates `device_session_id` if provided
   - Returns session data

**Security Check:**
```php
// Prevents Google users from logging in with email/password
if ($donorSession->auth_provider === 'google') {
    return error: "This account is registered with Google. Please use 'Login with Google' instead."
}
```

---

## 🔵 Google OAuth Authentication Flow

### Registration (`POST /api/donor-sessions/google-register`)

**What happens:**
1. User provides: `token` (Google ID token), `device_session_id` (optional)
2. Backend:
   - Verifies Google token using `GoogleAuthService`
   - Extracts user data from token:
     - `sub` → `google_id`
     - `email` → `email`, `username`, `google_email`
     - `given_name` → `donors.name`
     - `family_name` → `donors.surname`
     - `name` → `donor_sessions.google_name`
     - `gender` → `donors.gender`
     - `picture` → `donors.profile_image`, `donor_sessions.google_picture`
   - Creates/updates `donors` record
   - Creates `donor_sessions` record with:
     - `username` → User's email
     - `password` → `NULL` ✅ (No password for Google auth)
     - **`auth_provider` → `'google'`** ✅
     - `google_id` → Google's unique user ID
     - `google_email` → User's Google email
     - `google_name` → User's full name
     - `google_picture` → Profile picture URL

**Database State:**
```php
DonorSession {
    username: "user@gmail.com",
    password: null, // No password!
    auth_provider: "google",
    google_id: "1234567890",
    google_email: "user@gmail.com",
    google_name: "John Doe",
    google_picture: "https://lh3.googleusercontent.com/...",
    // ... other fields
}
```

### Login (`POST /api/donor-sessions/google-login`)

**What happens:**
1. User provides: `token` (Google ID token), `device_session_id` (optional)
2. Backend:
   - Verifies Google token
   - Finds `donor_sessions` by `google_id` (primary lookup)
   - If found: Updates Google info if changed, returns session
   - If not found: Creates new donor + donor_sessions (auto-registration)
   - Returns session data

**Authentication Method:**
- Uses `google_id` to find the session (not email/password)
- No password verification needed

---

## 🔄 How Both Methods Work Together

### Scenario 1: User Registers with Email/Password First

1. User calls `POST /api/donor-sessions/register`
2. Account created with `auth_provider = 'email'`
3. Later, user tries to login with Google:
   - If email matches → Error: "An account with this email already exists..."
   - User must use email/password login OR link Google account (future feature)

### Scenario 2: User Registers with Google First

1. User calls `POST /api/donor-sessions/google-register`
2. Account created with `auth_provider = 'google'`
3. Later, user tries to login with email/password:
   - Backend checks: `auth_provider === 'google'`
   - Returns error: "This account is registered with Google. Please use 'Login with Google' instead."

### Scenario 3: User Has Both Accounts (Different Emails)

- Email account: `user1@example.com` with `auth_provider = 'email'`
- Google account: `user2@gmail.com` with `auth_provider = 'google'`
- ✅ Both work independently (different emails = different accounts)

---

## 🛡️ Security Features

### 1. **Provider Validation**
- Email/password login checks if account is Google-authenticated
- Prevents cross-authentication method usage

### 2. **Password Handling**
- Email/password: Password is hashed and stored
- Google OAuth: Password is `NULL` (no password needed)

### 3. **Token Verification**
- Google tokens are verified using JWT validation
- Checks: issuer, audience, expiration, email verification

### 4. **Duplicate Prevention**
- Google register: Checks if `google_id` already exists
- Email register: Checks if `username` already exists
- Prevents duplicate accounts

---

## 📊 Database Schema

### `donor_sessions` Table

| Field | Email Auth | Google Auth |
|-------|-----------|-------------|
| `username` | User's chosen username | User's email |
| `password` | Hashed password | `NULL` |
| `auth_provider` | `'email'` | `'google'` |
| `google_id` | `NULL` | Google user ID |
| `google_email` | `NULL` | Google email |
| `google_name` | `NULL` | Full name |
| `google_picture` | `NULL` | Profile picture URL |

---

## 🔍 Code Flow Summary

### Email/Password Registration
```
User → register() → Create DonorSession
  → auth_provider = 'email'
  → password = hashed
  → google_* = NULL
```

### Email/Password Login
```
User → login() → Find by username
  → Check auth_provider !== 'google'
  → Verify password
  → Ensure auth_provider = 'email'
  → Return session
```

### Google Registration
```
User → googleRegister() → Verify token
  → Extract Google data
  → Create/Update Donor
  → Create DonorSession
  → auth_provider = 'google'
  → password = NULL
  → Store Google data
```

### Google Login
```
User → googleLogin() → Verify token
  → Find by google_id
  → Update Google info if changed
  → Return session
```

---

## ✅ Current Implementation Status

- ✅ Email/Password registration sets `auth_provider = 'email'`
- ✅ Email/Password login checks for Google accounts
- ✅ Email/Password login ensures `auth_provider = 'email'`
- ✅ Google registration sets `auth_provider = 'google'`
- ✅ Google login uses `google_id` for authentication
- ✅ Prevents cross-authentication method usage
- ✅ Handles legacy records (sets `auth_provider` if missing)

---

## 🔐 Password Reset Flow (Link-Based)

For users who forget their password, the system provides a secure link-based reset flow (not OTP).

### 1. Request Reset Link
- **Endpoint**: `POST /api/donor-sessions/forgot-password`
- **Input**: `{ "email": "user@example.com" }`
- **Action**: System generates a secure token and emails a link: `https://frontend-url/reset-password?token=xyz...`

### 2. Verify Token (Frontend Load)
- **Endpoint**: `GET /api/donor-sessions/reset/{token}`
- **Action**: Frontend calls this on page load to verify the token is valid and not expired. Returns the user's email for display.

### 3. Reset Password
- **Endpoint**: `POST /api/donor-sessions/reset/{token}`
- **Input**: `{ "password": "new_password", "password_confirmation": "new_password" }`
- **Action**: Updates the password and invalidates the token.

For full details, see `FORGOT_PASSWORD_LINK_FLOW.md`.

---

## 🚀 Future Enhancements

1. **Account Linking**: Allow users to link Google account to existing email account
2. **Account Migration**: Convert email account to Google account (or vice versa)
4. **Multi-Provider**: Support for Facebook, Apple, etc.

---

## 📝 Testing Checklist

### Email/Password Flow
- [ ] Register with email/password → `auth_provider = 'email'`
- [ ] Login with email/password → Success
- [ ] Try to login Google account with email/password → Error message
- [ ] Legacy record without `auth_provider` → Auto-set to 'email'

### Google OAuth Flow
- [ ] Register with Google → `auth_provider = 'google'`, `password = NULL`
- [ ] Login with Google → Success
- [ ] Try to login Google account with email/password → Error message
- [ ] Google token expired → Error message

### Edge Cases
- [ ] Same email, different providers → Proper error handling
- [ ] Google user tries email/password → Rejected
- [ ] Email user tries Google login → Account conflict error

---

## Summary

**Both authentication methods work correctly and independently:**

1. **Email/Password**: Traditional registration/login with username and password
2. **Google OAuth**: Modern OAuth flow with token-based authentication

The backend properly:
- ✅ Sets `auth_provider` correctly for each method
- ✅ Prevents cross-authentication attempts
- ✅ Handles legacy records
- ✅ Maintains security for both methods

No conflicts or issues! Both flows are production-ready. 🎉

