# Stage 3: Performance, Redis and queues

Stage 2 security changes are already present on main. This PR adds durable queued payment receipts and an opt-in scheduled recovery queue for Squad/Interswitch pending payments. Callbacks and verification responses retain their existing synchronous contract. SMS receipt formatting, receipt_phone, notification claims, financial verification and afterCommit notification semantics are unchanged.

`payments:queue-pending --limit=500` scans bounded batches (30-second minimum age), rotating a shared cache cursor across pending donations. Jobs contain only a donation ID, reload current state, require an unambiguous gateway binding, and call existing payment-integrity implementations. Completed/deleted payments are skipped. Provider outages throw a safe exception to retry without changing pending state. Paystack remains on its existing flow; this job does not invoke the legacy poller's Paystack implementation.

Jobs use the `payments` queue, dispatch after commit, unique cache locks and overlap locks. Financial idempotency remains database-enforced. Unique lock expiry and periodic scanning permit recovery after lost dispatches. Notification failures already claimed by the existing services remain manual reconciliation events; this PR does not retry external SMS delivery.

## Deployment

1. Back up the database. Run `composer install --no-dev --optimize-autoloader`. Predis is included; alternatively provision the native PHP Redis extension.
2. Apply migrations `2026_10_01_000004_add_payment_query_indexes` and `2026_10_01_000005_create_payment_notification_outbox`. The latter creates a receipt outbox keyed uniquely by donation/gateway. It adds donation status/creation/ID, donor/status/creation and transaction donation/creation indexes. It changes no columns or donation values. Index building may lock large production tables: measure on a production-sized copy and schedule an appropriate maintenance window.
3. Provision private, authenticated Redis with persistence and backups. Queue/lock keys must not be evicted; prefer dedicated queue Redis capacity and a `noeviction` policy. Redis is infrastructure, not supplied as a production cluster by this PR.
4. Configure these values in server `.env` (never commit credentials):

```dotenv
REDIS_CLIENT=predis
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_HOST=your-private-redis-host
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_QUEUE_RETRY_AFTER=180
MAIL_TIMEOUT=15
SESSION_SECURE_COOKIE=true
PAYMENT_RECOVERY_ENABLED=false
```

Set Redis credentials/TLS URL as required by your provider. All nodes must share APP_KEY and the same cache prefix. Switching sessions logs existing users out. Do not enable recovery until Redis and workers are healthy. Do not switch cache stores during an active recovery rollout because it changes the unique/overlap lock namespace.

5. Adjust `deployment/giveabu-worker.conf` paths/user and install in Supervisor. Start payments and notifications workers. Worker timeout (120 seconds) must remain below retry_after (180 seconds); shutdown grace is 180 seconds. Monitor memory and provider rate limits before increasing concurrency. Redis latency/timeouts must be bounded at the infrastructure/client level.
6. Run one `php artisan payments:queue-pending --limit=10` batch and verify processing with test/staging provider credentials. This command can verify real payments and invoke existing receipts: do not run against live data merely to test queue connectivity.
7. Enable `PAYMENT_RECOVERY_ENABLED=true`, rebuild config cache and install Laravel's scheduler cron (`* * * * * ... artisan schedule:run`). Scheduling uses shared Redis locks via `onOneServer` and `withoutOverlapping`. Retire the legacy polling daemon rather than running both recovery loops.
8. On each release run `php artisan queue:restart` and verify Supervisor replaced workers. Keep any existing workers for other queues separately; the two templates consume `payments` and `notifications` separately.

## Verification and rollback

Run ApiSecurityTest, SquadPaymentIntegrityTest and KudiSmsTest. New tests cover bounded rotating dispatch, replay/idempotency, provider outage retries, and index migration repeatability/reversal. Test Redis dispatch and worker processing in staging before activation. The full historical suite currently has unrelated fresh-schema migration failures; targeted payment/security suites must still pass.

To disable: set PAYMENT_RECOVERY_ENABLED=false, rebuild config cache, let in-flight workers drain, then stop the payments workers. Restore former session/cache settings only with an intentional logout/lock transition. Do not purge queued jobs before checking their state. Indexes may remain safely installed. No payment state is rolled back by disabling queue recovery.

Queue behavior follows [Laravel 12 queue documentation](https://laravel.com/framework/docs/12.x/queues).

## Review results (1 October 2026)

- Targeted suites: 85 passed, 551 assertions.
- Full suite: 86 passed, 9 failed. Eight failures hit the pre-existing early donor_sessions migration during fresh SQLite schema creation; the ninth homepage test has no projects fixture. This PR does not alter old production migrations to hide them.
- Predis/Redis 7 integration: two dispatches for the same nonexistent donation produced one queued job; an actual Redis worker consumed it against an isolated in-memory database. No payment, SMS or email providers were called.
- Composer audit: 59 advisories across 17 existing packages, including Laravel, Livewire, Symfony and PhpSpreadsheet. Predis has no reported advisory. Existing package versions were not upgraded by this PR. The dependency audit requires a separate security follow-up and prevents declaring the application fully security-hardened.
- Composer validation, changed PHP syntax checks and diff whitespace checks pass.


## Queued receipts (Stage 3 review fixes)

Financial verification remains synchronous. Squad/Interswitch completion inserts the notification outbox within the financial transaction; Paystack completion paths record payment updates and the same outbox in database transactions after provider verification. The outbox publisher executes only after the outermost commit and catches broker errors without undoing payment success. Paystack verification rules remain unchanged; only the persistence boundary and receipt delivery are changed.

`DeliverPaymentNotification` carries only an outbox ID and uses the `notifications` queue. Its worker sends existing Kudi SMS and the tier/fallback receipt email. Gateway-specific notification claims prevent duplicate emails; original Squad notification keys and SMS claims remain unchanged. Existing synchronous receipt call sites now enqueue, including Paystack's Livewire confirmation. In production a mistakenly configured sync connection is rejected by the publisher and leaves the outbox pending instead of sending inline.

Three tries, 120-second timeout, 30/120/300-second backoff and a 600-second unique lock are configured. Queue retry_after remains 180 seconds. Claims are written before external delivery: a provider timeout or crash after a claim is treated as uncertain, not retried externally. Retries preserve those claims and cannot resend. Failures mark the outbox failed and throw safe exceptions so exhausted worker attempts are recorded in failed_jobs. No claim is automatically removed.

`payments:dispatch-notifications --limit=500` republishes stale/unpublished pending outbox records; it runs with the enabled Stage 3 scheduler. This closes the DB-to-Redis publish gap for transactional completions. Monitor outbox pending age/status plus failed_jobs. Verify failed_jobs exists and QUEUE_FAILED_DRIVER=database-uuids. Use `queue:failed` to inspect failures, but redact payloads and credentials when exporting logs. An operator must inspect sms.claimed/accepted/failed and notification.claimed/sent/failed and provider delivery reports before any manual recovery. A blind queue:retry does not resend failed/uncertain receipts: their claims intentionally remain locked. Restore provider-confirmed accepted/delivered states only through a reviewed reconciliation operation; this PR provides no automatic claim-deletion tool.

Production settings must include SESSION_DRIVER=redis, CACHE_STORE=redis, QUEUE_CONNECTION=redis and SESSION_SECURE_COOKIE=true. Local HTTP development may set SESSION_SECURE_COOKIE=false only in local .env. Duplicate bottom session.php keys were removed; domain, secure and same_site now use the standard environment-driven settings.

Deploy the outbox migration before app/worker activation. Drain old workers before switching receipt call sites, then start notification workers and restart payments workers. For rollback do not restore inline delivery while notification jobs remain active: disable scans, drain/stop notification workers, reconcile pending/failed outbox rows, then restore the earlier compatible release. Keep financial records and claims intact.

Revised queue verification: 95 targeted tests pass (601 assertions); full suite 96 pass and 9 historical failures. A real Redis notification worker with an in-memory database and simulated mail service failure recorded a failed_jobs row and failed outbox record without reversing the donation. Production timeout/backoff was not shortened; only the isolated smoke-test job used one try to verify failure capture immediately. Duplicate-delivery tests assert one SMS call and one email transport call across job replay. New transaction-boundary tests cover Squad rollback/commit and Paystack outer commit; Interswitch duplicate verification queues one job.

## Gateway confirmation and admin reconciliation

The Squad return page now distinguishes a recoverable pending confirmation from a terminal failure. It polls a short-lived signed status URL up to 18 times with 10-second pauses. Polls re-use the Squad/Interswitch integrity services only when the canonical donation has an unambiguous binding. The endpoint exposes only status, accepts no provider success flag, and requires a valid signature plus rate limiting. Completed/failed states refresh the return page. Outages/mismatches do not send receipts or complete donations. Background payment recovery remains available after the browser closes.

Admin navigation includes `/admin/reconciliation`, guarded by the existing auth/verified/role:admin middleware. It displays pending payments, actual configured queue sizes, pending/failed receipt outbox records and failed_jobs identifiers/timestamps, never raw job payloads/exceptions. POST actions are CSRF-protected and rate-limited: check gateway status via the existing recovery job, or publish a pending receipt. Failed receipt claims are not cleared. Queue totals do not establish worker liveness; Supervisor/worker health monitoring remains an operations requirement. Apply the outbox migration and run payments/notifications workers before using this screen.

Verification: 98 targeted tests passed (624 assertions), including delayed provider recovery via signed polling, duplicate receipt suppression, unsigned endpoint rejection, administrator-only reconciliation actions and administrator page rendering.
