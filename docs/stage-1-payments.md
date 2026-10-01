# Stage 1 payment integrity

## Confirmed failure and scope

The deployed migration history defines donations.status as pending/success/failed, while Squad, Paystack and Interswitch write completed. MySQL strict mode rejects that value. Donations has paid_at but no verified_at; the model previously discarded both timestamp fields. These defects can surface after a donor has already paid.

Squad previously overwrote expected amounts, recreated donations in the callback, trusted webhook JSON, marked provider outages failed, overwrote initialization audit records, and completed payments without locking. The admin verification actions and pending-payment poller contained separate completion implementations; the poller also referenced a missing transactions relationship. Cleanup deleted unresolved donations after 24 hours. The thank-you page incorrectly promised no charge had occurred when verification failed.

This change centralizes all Squad completion in SquadPaymentService. Paystack and Interswitch share the corrected schema and project reconciliation helper. Interswitch API, redirect and webhook now share server-side verification with recoverable outages and exact expected-amount checks; wider gateway/security reviews remain outstanding. No availability guarantee follows from these changes. Stages 2–6 remain separate.

## State and authority

Donation is the canonical payment object. State is pending, completed or failed; legacy success is migrated to completed. A verification outcome (pending, unavailable, rejected, wrong_gateway, not_found, failed or completed) is separate from the donation state. Provider HTTP errors, malformed responses, missing configuration and connection errors do not change financial state. A subsequent verified success may recover a previously failed donation. Completed Squad donations never reverse or trigger completion again.

Verification requires a transaction history linked to the existing donation that binds its reference to Squad with no conflicting gateway. References match case-sensitively in application checks. Unknown references never create donations. Existing Squad donations without transaction history require operator inspection and gateway-binding reconciliation; never automatically fabricate a paid donation from a callback.

The server verifies transaction_ref, transaction_status=Success, transaction_currency_id=NGN (currency is accepted as an alternative field), and transaction_amount in integer kobo. Expected amounts stay in decimal Naira. Conversion uses integer arithmetic, accepts at most two decimal places, and never infers payment amounts from metadata. The verification endpoint's envelope must have success=true and a successful HTTP response. Other/unknown status labels are pending rather than success.

Official references:
- https://docs.squadco.com/webhook-direct-url/signature-validation/
- https://docs.squadco.com/webhook-direct-url/webhook-and-direct-url/
- https://docs.squadco.com/Payments/squad-payment-modal/
- https://squadinc.gitbook.io/squad-api-documentation/payments/accept-payments-1 (deprecated official verification examples; current site could not be fetched reliably during implementation).

Before deployment validate the response contract and signed raw-body delivery in Squad sandbox using the current merchant integration. Automated tests never call a live provider. Card/payment webhooks require x-squad-encrypted-body: uppercase HMAC-SHA512 of the raw request body with SQUAD_SECRET_KEY, compared in constant time. Virtual-account webhook versions use different specifications and are outside this route's scope. Even signed webhook contents are only a trigger for independent verification; claimed amount/status/date never complete a donation. Unknown refs/rejected verification are acknowledged without financial effects; provider/database outages return 503 so delivery can retry. Unauthenticated payloads return 401.

Browser and API response keys remain available. API data.verification_status explains recoverable outcomes; unavailable returns 503, integrity rejection/wrong gateway 422, unknown reference 404. The thank-you page displays uncertainty without asserting the donor was not charged. POST /donations continues to record donation intent and return its existing record response, but now records pending instead of a fabricated paid donation. Clients must initiate a real gateway payment and complete via verification; this intentional financial-security behavior change must be communicated to mobile clients.

## Concurrency, history and notifications

Completion locks the project first, then the donation, rechecks state, records the success event and rebuilds raised in the same database transaction. All application project-total recalculations use ProjectFundingService: project row lock plus current locking reads of completed donations, exact kobo summation, and the existing project closing behavior. Laravel retries deadlocks up to five times. Use InnoDB in production. This serializes same-project updates and duplicate completion; it does not make the other gateways' separate donation transitions idempotent.

PaymentTransaction remains an event history (option B), minimizing reporting/client changes. New Squad events have unique SHA256 event_key identities based on gateway, canonical reference, event type and normalized discrepancy/status evidence. Initialization, success, failure, pending and rejected events do not overwrite each other. Duplicate outcomes are coalesced, distinct mismatches retained. Sanitized reconciliation fields retain provider amount/currency/reference/status, while amount on each event is the expected donation amount. No raw customer/provider payload or exception message is logged by the new Squad paths. Historical events cannot be reconstructed if an earlier upsert already erased them. Legacy event_key values remain NULL, leaving other gateways' existing upsert behavior intact.

Notifications run after the financial transaction commits. Only the first completion registers this work. A unique notification.claimed event prevents repeated sends. A tier email is the receipt when applicable; otherwise the generic receipt is used. Failure is logged and recorded separately and cannot reverse payment. Delivery is synchronous after commit for Stage 1; no queue infrastructure is required. Claims deliberately provide at-most-once attempts: a crash between claim and delivery can lose an email. Do not blindly replay claimed receipts, since transport success followed by process failure is ambiguous. Check email/provider logs and arrange manual delivery as needed. Durable queue/outbox retry architecture belongs to Stage 3.

## Deployment and preflight

1. Back up the database and rehearse on an anonymized production clone with the production MySQL version, strict SQL mode, InnoDB and collation. SQLite tests cannot prove MySQL row-lock or DDL behavior.
2. Configure environment-only SMS credentials: OZEKI_USERNAME, OZEKI_PASSWORD and KUDI_SMS_KEY. Rotate all formerly hardcoded credentials, including the SMS token/password, because removing defaults does not remove Git history. Never place credentials in the PR or deployment document. Clear/rebuild configuration cache using the normal deployment procedure.
3. Configure SQUAD_SECRET_KEY and SQUAD_API_URL for the correct environment. If clients use custom redirects, set SQUAD_CALLBACK_URLS to comma-separated exact URL bases (e.g. an approved application deep link). Default /donation/thank-you works without this setting. Query strings are allowed; fragments, control characters, backslashes, unlisted origins/paths are rejected. No wildcard host matching. The mobile deep-link inventory is not present in this repository: operators must populate it before enabling updated initiation for those clients.
4. Drain/pause payment traffic, workers and the existing verification poller during migration. Do not run old app nodes that still write success alongside this code. Disable pending-donation deletion from any independently deployed scripts. The existing donations:cleanup-pending command now only reports unresolved records.
5. Run `php artisan payments:preflight` against the deployed database before `php artisan migrate --force`. Exit failure requires manual reconciliation. To inspect affected references privately:

   ```sql
   SELECT payment_reference, COUNT(*) AS copies
   FROM donations WHERE payment_reference IS NOT NULL
   GROUP BY payment_reference HAVING COUNT(*) > 1;
   SELECT status, COUNT(*) AS records FROM donations GROUP BY status;
   ```

   Reconcile duplicate rows, amounts, project assignments and related transactions against Squad/provider settlements. Do not delete paid rows or rename references merely to satisfy the index. Empty-string references also count as duplicates; NULL references remain allowed. Migration checks run before DDL and abort on duplicates/unexpected states. MySQL table changes can lock large tables; rehearse timing and schedule a maintenance window. Any existing blank/invalid status needs explicit investigation, not automatic conversion. Historical FLUTTERWAVE-prefixed dummy completions also require operator audit; this change does not delete or retroactively classify them.
6. The forward migration widens the enum, converts success to completed, constrains it to the canonical states, adds nullable verified_at, a unique donation reference index and nullable unique transaction event_key. Existing paid_at remains intact. It detects previously applied new columns/indexes so interrupted MySQL DDL can be retried. It does not backdate verified_at or claim historical payments were reverified.
7. Rebuild project totals after legacy-state conversion. A repair command is provided: `php artisan payments:reconcile-projects`. Run with payment processing paused for the initial historical repair, then resume traffic and provider webhook delivery. It uses the same locks/current-read logic as normal payments.
8. Reconcile unresolved payments against provider settlements. `donations:verify-pending` now includes records older than 24 hours and delegates Squad to the shared service. Its existing six-iteration/daemon interface remains unchanged. This command still has older Paystack/Interswitch logic; audit those integrations before enabling broad automatic recovery. Ensure gateway binding exists for pending Squad references. Verification rejection events must be reviewed, not force-completed.

## Validation and limits

Run `php artisan test --filter=SquadPaymentIntegrityTest`, PHP syntax checks and Pint on changed PHP files. Tests use Http::fake with stray requests prohibited, an in-memory SQLite schema using the actual donation/transaction migrations, and isolated donor/project fixtures. They cover conversion, mismatches, missing fields, callback/webhook/API duplication, an interleaved stale callback response, failures/recovery, notification failure, timestamps, unique references, immutable event outcomes, legacy-state migration/preflight, old-pending preservation and admin verification.

The complete baseline suite already fails: migration 2025_01_15_120000_make_donor_id_nullable_in_donor_sessions_table runs before creation of donor_sessions and generates an invalid SQLite INSERT with no columns. The existing homepage test also lacks database setup and fails on missing projects. This stage does not rewrite old production migrations to fix unrelated bootstrap failures. True concurrent MySQL integration tests and production-clone migration rehearsal are still required before deployment; no local MySQL server was available for validation.

## Rollback

The migration is forward-only: rollback throws rather than shrink financial enums or destroy timestamps, uniqueness and event history. Roll back application deployment only to a compatible build that understands completed and preserves the new audit schema. The pre-hardening code is not safe to restore because it recreates the original enum/status, amount and webhook defects. Prefer a corrective migration/patch. Preserve canonical donations and all new events for reconciliation. Keep payment traffic paused if an incident prevents trustworthy verification; retry/reconcile instead of declaring collected payments failed.

## Stage 2 follow-up

Review authorization, throttling and data exposure for donor-tier writes, donor edits, send-sms, test-google-token, debug device registration, messaging, alumni/donor endpoints and payment endpoints. Audit Paystack/Interswitch success verification, webhook signatures, cross-gateway reference binding, transient errors, duplicate financial/notification effects and admin backfill/reporting semantics. Review broad reporting aliases and receipt exposure. Infrastructure, Redis, queues, storage boot-time directory creation and monitoring remain later stages.

## Interswitch PR review revisions

Verification exceptions, connection failures, unsuccessful HTTP responses and unknown/processing response codes preserve the existing Donation state. Only an explicit allowlist of verified bank declines/cancellation transitions to failed; missing or unfamiliar codes stay recoverable. Interswitch response-code reference: https://docs.interswitchgroup.com/docs/payment-response-codes and https://docs.interswitchgroup.com/docs/response-codes.

API verification, browser redirect and authenticated webhook use the same independent server query. The query amount comes from the existing Donation, never redirect/webhook input. Successful verification requires a valid integer provider amount exactly equal to expected integer kobo. Missing, fractional or mismatched amounts return 409, leave the donation uncompleted, and record sanitized expected/received minor units in distinct reconciliation events. Donation.amount is never overwritten. Completed donations are protected from reversal; project and donation locks follow the existing project-first order. Receipt/tier delivery happens after commit and cannot undo completion. Existing webhook signature mechanics are preserved; this change does not claim a full Interswitch integration/signature audit.

`payments:preflight` now lists pending literal `ABU_ZARIA_SQUAD_` references without any Squad PaymentTransaction reference binding and exits unsuccessfully when present. An Interswitch binding does not satisfy this check. The command is read-only: investigate historical provider records and reconcile gateway binding before deployment; it never creates a binding automatically. No additional migration or mobile settings change is required by these review revisions. Clients must handle retryable verification 503 and amount rejection 409; redirects show pending when verification is unavailable.

Six additional regression tests cover network/provider outages, uncertain versus confirmed failure, invalid/mismatched amounts and reconciliation evidence, exact kobo and duplicate success, redirect/webhook verification, and read-only historical preflight reporting.
