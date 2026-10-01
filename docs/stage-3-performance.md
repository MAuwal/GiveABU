# Stage 3: Performance, Redis and queues

Stage 2 security changes are already present on main. This PR adds an opt-in scheduled recovery queue for Squad/Interswitch pending payments. Callbacks and verification responses retain their existing synchronous contract. SMS receipt formatting, receipt_phone, notification claims, financial verification and afterCommit notification semantics are unchanged.

`payments:queue-pending --limit=500` scans bounded batches (30-second minimum age), rotating a shared cache cursor across pending donations. Jobs contain only a donation ID, reload current state, require an unambiguous gateway binding, and call existing payment-integrity implementations. Completed/deleted payments are skipped. Provider outages throw a safe exception to retry without changing pending state. Paystack remains on its existing flow; this job does not invoke the legacy poller's Paystack implementation.

Jobs use the `payments` queue, dispatch after commit, unique cache locks and overlap locks. Financial idempotency remains database-enforced. Unique lock expiry and periodic scanning permit recovery after lost dispatches. Notification failures already claimed by the existing services remain manual reconciliation events; this PR does not retry external SMS delivery.

## Deployment

1. Back up the database. Run `composer install --no-dev --optimize-autoloader`. Predis is included; alternatively provision the native PHP Redis extension.
2. Apply migration `2026_10_01_000004_add_payment_query_indexes`. It adds donation status/creation/ID, donor/status/creation and transaction donation/creation indexes. It changes no columns or donation values. Index building may lock large production tables: measure on a production-sized copy and schedule an appropriate maintenance window.
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
PAYMENT_RECOVERY_ENABLED=false
```

Set Redis credentials/TLS URL as required by your provider. All nodes must share APP_KEY and the same cache prefix. Switching sessions logs existing users out. Do not enable recovery until Redis and workers are healthy. Do not switch cache stores during an active recovery rollout because it changes the unique/overlap lock namespace.

5. Adjust `deployment/giveabu-worker.conf` paths/user and install in Supervisor. Start two workers. Worker timeout (120 seconds) must remain below retry_after (180 seconds); shutdown grace is 180 seconds. Monitor memory and provider rate limits before increasing concurrency. Redis latency/timeouts must be bounded at the infrastructure/client level.
6. Run one `php artisan payments:queue-pending --limit=10` batch and verify processing with test/staging provider credentials. This command can verify real payments and invoke existing receipts: do not run against live data merely to test queue connectivity.
7. Enable `PAYMENT_RECOVERY_ENABLED=true`, rebuild config cache and install Laravel's scheduler cron (`* * * * * ... artisan schedule:run`). Scheduling uses shared Redis locks via `onOneServer` and `withoutOverlapping`. Retire the legacy polling daemon rather than running both recovery loops.
8. On each release run `php artisan queue:restart` and verify Supervisor replaced workers. Keep any existing workers for other queues separately; this template consumes only `payments`.

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
