Pending Squad and Interswitch donations can now be recovered by bounded scheduled jobs, using shared Redis locks and the existing gateway integrity services. This avoids unbounded polling and keeps provider latency out of scheduler processes while preserving callback responses and SMS receipt claims.

Adds Predis, after-commit queue configuration, a rotating recovery scanner, retry/overlap controls, payment query indexes, supervised-worker configuration and deployment/rollback instructions. Recovery scheduling is disabled by default.

Validation: payment, security and KudiSMS regression suites plus new queued recovery/outage/index tests. See docs/stage-3-performance.md for runtime Redis testing and deployment prerequisites. Do not merge before review.

Verified locally: 85 targeted tests / 551 assertions; real Redis unique dispatch and worker consumption. Full suite: 86 pass and 9 existing fixture/migration failures. Composer audit reports 59 advisories in 17 existing packages (none in added Predis); dependency remediation must receive separate security review before production activation.


Review fixes: removes duplicate session cookie overrides; post-payment effects now use a durable notification outbox and after-commit `notifications` jobs for Squad, Interswitch and Paystack. Redis publication failures preserve success and pending outbox state. Existing delivery claims survive job retries; failed/uncertain delivery remains manual reconciliation and failed_jobs-visible. Adds outbox schema migration, republish command/scheduling, separate supervised notification workers and required secure Redis session settings. Financial verification is unchanged.

Revised validation: 95 targeted tests pass (601 assertions). Full suite: 96 pass and the same 9 historical migration/homepage fixture failures. A real Redis worker with simulated notification failure recorded failed_jobs and outbox failure while preserving completed donation state. New tests cover outer commit/rollback, rejected payments, broker outages, duplicate callbacks/webhooks, external delivery replay, failed delivery retries, Interswitch/Paystack receipt queuing and environment-driven session cookies. No live SMS/email was sent during validation.
