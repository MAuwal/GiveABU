# Stage 2: Security hardening

Branch: `hardening/stage-2-security`. Built on Stage 1 because Stage 1 has not been merged. Review the Stage 2 diff against `hardening/stage-1-payment-integrity`; merge Stage 1 first and retarget Stage 2 to main. No automatic merges or deployment.

## Confirmed issues and changes

Critical: public registration could overwrite an existing password; numeric session IDs and device fingerprints acted as credentials. Registration now rejects existing identities and requires a chosen password. Sign-in returns random access tokens stored as SHA-256 hashes, expiring after 30 days. Logout revokes the current token; password changes/resets revoke all tokens. Reset consumption is transactional and single use.

High: profile writes, inboxes, donor/alumni lookups, SMS and donor-tier writes lacked consistent authorization. API middleware enforces authenticated donor ownership and administrator access regardless of duplicate route declarations. Web admin and subsequent Livewire requests require the admin role. Arbitrary profile-image paths and donor-tier assignment are prohibited. Device recognition cannot return credentials or another donor's identity.

High: Google login validates configured audience, issuer, expiry, subject and verified email. Password accounts cannot be silently converted to Google accounts. Third-party email claims cannot claim existing donor profiles unless Google is authoritative for that email. The existing Google tokeninfo server dependency remains; cached-key verification with Google's supported library is a future reliability improvement. Google documents tokeninfo primarily for debugging: https://developers.google.com/identity/gsi/web/guides/verify-google-id-token.

Medium: authentication, verification and general API requests have IP quotas; web password login also has a credential/IP quota. OTP delivery/guess quotas are shared by recipient across IPs. Webhooks are excluded from user IP quotas. Debug endpoints return 404 and legacy passwordless login endpoints return 410. Sensitive directory fields retain response keys with null values for other donors. Tokens and verification secrets are hidden from model serialization; touched authentication logs no longer include request bodies or raw provider errors. CORS no longer trusts every Vercel tenant.

Hardcoded SMS credentials and seeded account passwords have no source defaults. Ozeki includes use require_once so repeated verification calls do not redeclare classes.

## Mobile and web compatibility

No mobile source, mobile settings or .env file was changed. Existing payment behavior from Stage 1 is retained. Login/registration response keys including token, session_token and session_id remain available.

Security-required client changes: save the token returned by password/Google login and send `Authorization: Bearer <token>` or `X-Device-Session: <token>` on protected requests. A session_id identifies a record but does not authenticate. Existing numeric/fingerprint/device tokens expire immediately as credentials; users must sign in again. Public registration requires password (minimum 8 characters) and existing users must log in or recover their password. Passwordless session routes and debug registration are unavailable. Normal donors cannot call admin-only endpoints; finance/executive users need a separately reviewed permission policy before accessing admin routes. Other-donor NIN/address/location fields are null. Clients must handle 401, 403, 409, 410 and 429.

Web donor login, registration, cookie identity lookups and logout use the same token service. Mobile end-to-end verification requires the mobile repository/build, which is absent from this workspace.

## Deployment

1. Merge and deploy Stage 1 first, following its payment preflight. Coordinate the updated mobile token flow before exposing Stage 2.
2. Back up the database and run `php artisan migrate --force` before serving the new authentication code. The forward migration creates donor_access_tokens with a unique hash and expiring donor-session foreign key; no payment or donor data is rewritten. Never roll back authentication schema while this code is serving requests.
3. Configure all intended Google client IDs, explicit allowed CORS origins and `RESET_CALLBACK_URLS` (comma-separated exact reset callback URLs); `FRONTEND_URL` falls back to APP_URL. Stage 1 payment callback settings still apply. Rebuild configuration caches after changing environment settings.
4. Use strong `SEED_ADMIN_PASSWORD`, `SEED_FINANCE_PASSWORD`, `SEED_EXECUTIVE_PASSWORD` values (minimum 12 characters) only when deliberately creating initial users. Seeders skip user creation when absent; they do not rotate existing passwords. Rotate existing seeded passwords, old phone-derived donor passwords and all previously committed SMS credentials externally before release; source removal does not revoke exposed credentials.
5. Confirm admin roles, own-profile/inbox denial for other IDs, web registration/login/logout, mobile token headers, reset links, Google client audiences and payment callbacks in staging. Use a shared rate-limit cache across application nodes in Stage 3; configure trusted proxies correctly for client IP quotas.

Rollback means returning to insecure legacy authentication. Prefer fixing forward. The new token table can remain if code must be reverted; issue fresh tokens after recovery. Existing password reset/email verification storage has not been migrated in this stage.

## Validation

17 API security tests and 32 payment integrity tests pass (49 tests, 196 assertions), using isolated SQLite schemas and mocked provider calls. All modified PHP files pass php -l; focused new/security files pass Pint; git diff --check passes.

The full suite reports 50 passed, 9 failed (198 assertions). These same nine failures existed before Stage 2: eight auth tests stop in the older 2025_01_15 donor_sessions migration (empty SQLite INSERT caused by migration order), and the homepage example lacks the projects table. Historical migration repair is outside this security change. Real MySQL deployment, browser and mobile end-to-end checks remain deployment requirements.
