Pending Squad and Interswitch donations can now be recovered by bounded scheduled jobs, using shared Redis locks and the existing gateway integrity services. This avoids unbounded polling and keeps provider latency out of scheduler processes while preserving callback responses and SMS receipt claims.

Adds Predis, after-commit queue configuration, a rotating recovery scanner, retry/overlap controls, payment query indexes, supervised-worker configuration and deployment/rollback instructions. Recovery scheduling is disabled by default.

Validation: payment, security and KudiSMS regression suites plus new queued recovery/outage/index tests. See docs/stage-3-performance.md for runtime Redis testing and deployment prerequisites. Do not merge before review.

Verified locally: 85 targeted tests / 551 assertions; real Redis unique dispatch and worker consumption. Full suite: 86 pass and 9 existing fixture/migration failures. Composer audit reports 59 advisories in 17 existing packages (none in added Predis); dependency remediation must receive separate security review before production activation.
