# Stage 2: Security hardening

Branch: `hardening/stage-2-security`, rebased onto latest origin/main (994e04f), which includes the final Stage 1 review fixes. Review the final diff against main. No automatic merges or deployment.

## Confirmed issues and changes

Critical: public registration could overwrite an existing password; numeric session IDs and device fingerprints acted as credentials. Registration now rejects existing identities and requires a chosen password. Sign-in returns random access tokens stored as SHA-256 hashes, expiring after 30 days. Logout revokes the current token; password changes/resets revoke all tokens. Reset consumption is transactional and single use.

High: profile writes, inboxes, donor/alumni lookups, SMS and donor-tier writes lacked consistent authorization. API middleware enforces authenticated donor ownership and administrator access regardless of duplicate route declarations. Web admin and subsequent Livewire requests require the admin role. Arbitrary profile-image paths and donor-tier assignment are prohibited. Device recognition cannot return credentials or another donor's identity.

High: Google login validates configured audience, issuer, expiry, subject and verified email. Password accounts cannot be silently converted to Google accounts. Third-party email claims cannot claim existing donor profiles unless Google is authoritative for that email. The existing Google tokeninfo server dependency remains; cached-key verification with Google's supported library is a future reliability improvement. Google documents tokeninfo primarily for debugging: https://developers.google.com/identity/gsi/web/guides/verify-google-id-token.

Medium: authentication, verification and general API requests have IP quotas; web password login also has a credential/IP quota. OTP delivery/guess quotas are shared by recipient across IPs. Webhooks are excluded from user IP quotas. Debug endpoints return 404 and legacy passwordless login endpoints return 410. Sensitive directory fields retain response keys with null values for other donors. Tokens and verification secrets are hidden from model serialization; touched authentication logs no longer include request bodies or raw provider errors. CORS no longer trusts every Vercel tenant.

Hardcoded SMS credentials and seeded account passwords have no source defaults. SMS sending uses Kudi exclusively.

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

## KudiSMS provider correction

All active SMS sending uses KudiSMS. SmsService delegates verification, welcome, donation-confirmation and password-reset messages to KudiSmsService; API/admin sending uses the same provider. Ozeki configuration and runtime includes are removed. The unused bundled Ozeki files and Twilio SDK dependency have been removed. SMS history reads existing local SmsLog records (written by admin and API SMS flows) instead of querying Twilio. No historical remote messages are imported.

Keep KUDI_SMS_KEY and KUDI_SMS_URL in the environment; optional KUDI_SMS_SENDER_ID defaults to ABU and must be approved in Kudi. Existing /api/intcomposesms configuration is preserved and now sends country_id=234; /api/sms sends gateway=2. Requests use HTTPS POST form data, strict success/error-code validation and bounded timeouts without automatic retries. Acceptance does not guarantee handset delivery. Tokens, OTP contents and raw provider errors are not logged/returned. Existing verification response keys and locally generated OTP checks are retained. sms:test sends only to the explicitly supplied phone; its configuration check no longer sends a billable dummy SMS. No .env or mobile settings were modified.

Official contract: https://www.kudisms.net/docs/sms/ and https://www.kudisms.net/docs/authentication/. Five mocked Kudi regression tests verify OTP routing, existing message helpers, missing configuration, sanitized failures and both endpoint formats. No real SMS was sent. Clear/rebuild configuration cache on deployment; no new migration is required.

Follow-up verification: eight Kudi tests now cover admin/API sending, local history and the single-send CLI test in addition to the provider tests. Composer dependency removal is limited to twilio/sdk. A user-authorized live SMS was attempted; Kudi returned HTTP 200 with a non-success payload and the message was not accepted. A subsequent read-only sender-status check returned provider code 300. Live delivery is not confirmed; verify the configured endpoint, key and approved sender against the merchant dashboard before deployment. No further live sends were performed.

## Verified Kudi request contract and payment receipts

Comparison with the earlier implementation showed it used GET with token, senderID, recipients, message and gateway=2. The POST conversion was incompatible with the configured merchant endpoint: a new user-authorized test with the restored GET request was accepted by Kudi and returned a message ID. This supersedes the failed POST test above. The user confirmed receipt on the designated phone, completing the live delivery check. Query URLs contain credentials and message contents, so never log outgoing URLs or raw exceptions; automatic retries remain disabled. The active environment was not edited.

Squad and Interswitch send Kudi donation receipts only after server-verified completion commits; the existing Paystack completion paths also call the same receipt service. A separate unique sms.claimed event gives at-most-once attempts across callbacks/webhooks/retries. sms.accepted or sms.failed records the provider outcome. Missing donor phone skips delivery. SMS and email are independent: SMS failure cannot undo payment, and email failure cannot suppress the SMS attempt. A process crash or ambiguous timeout after claiming requires manual reconciliation; queue/outbox recovery remains Stage 3.

New references are ABU_ZARIA_SQUAD_<year>_<8 lowercase hex> and ABU_ZARIA_INTERSWITCH_<year>_<8 lowercase hex>, generated with bin2hex(random_bytes(4)). The unique donation reference index remains authoritative. A collision retries creation with a fresh reference up to five attempts using transactions/savepoints. Existing references are unchanged; no additional migration is required. Keep Stage 1 migration requirements.

Stage 1 review commit 81b19e3 was carried into this branch before adding receipts, preserving the reviewed Interswitch outage and amount checks. Relevant regression tests now include both gateway SMS outcomes, email failure isolation, duplicate sends, pending-payment suppression and short-reference collision recovery.

## Stage 2 PR review revisions

Rebased onto merged Stage 1 main without conflicts; the already-applied Interswitch review cherry-pick was skipped. SquadPaymentService and payments:preflight match main. Payment controller differences retain only the previously requested short references and post-commit SMS additions. The separate SMS branch was not rewritten or merged.

CORS allowed_origins is parsed solely from comma-separated CORS_ALLOWED_ORIGINS, with whitespace/empty entries removed and an empty default. Set this explicitly in production, for example https://giveabu.com,https://www.giveabu.com,capacitor://localhost. Add any other approved production/mobile origins explicitly. localhost HTTP, loopback and LAN origins belong only in local .env, which was not edited. Rebuild the configuration cache when deploying. There are no wildcard origin patterns.

RequireRole is the single source of admin role authorization. Admin, statistics, SMS and tier-write routes declare auth:sanctum plus role:admin. ApiSecurity now only handles request quotas, retired/debug endpoints and cache headers; it does not authorize roles. AuthenticateDonor, aliased donor.auth, explicitly protects donor updates/profile, messages, history, directory/search and device/session operations and enforces token expiry and owner IDs even after model binding. Admin uploads/statistics do not require a second donor credential. Existing Sanctum-protected donor resource management is admin-only.

All original security tests remain. Added tests verify explicit authentication middleware and anonymous denial across sensitive endpoints, allowed/denied CORS preflights, empty-origin configuration and donation-history isolation. Broader feature-suite baseline failures remain the old migration-order SQLite errors and missing homepage projects table. No new migration is needed for these review revisions; the existing Stage 2 token migration remains required.

Final review validation: ApiSecurityTest (20), SquadPaymentIntegrityTest (42) and KudiSmsTest (8) pass: 70 tests, 413 assertions. The broader Feature suite reports 70 passed, 9 pre-existing failures, 414 assertions. All changed PHP files against main pass syntax checks; focused middleware/CORS/security-test Pint checks and diff whitespace checks pass.
