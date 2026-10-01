# Stage 4: High availability and production architecture

This branch depends on Stage 3. It adds `/ready` dependency checks and environment-configured trusted proxy addresses. No infrastructure is provisioned and no production configuration is changed by this PR.

## Health checks

- `/up`: Laravel boot/liveness. Do not restart all app nodes because a shared dependency fails.
- `/ready`: SELECT 1 against the configured database, cache put/get/delete, and optional storage disk write/read/delete. Returns only `ready` (200) or `unavailable` (503), with no cache and no session/auth middleware. Errors never expose hostnames, credentials, stack traces or provider payloads.
- Creating the node-local `storage/framework/node-draining` file returns 503 from `/ready` without stopping in-flight requests or `/up`. Remove it only after release checks succeed. Keep framework storage and this marker node-local; mount only the designated shared upload paths.
- `READINESS_STORAGE_DISKS=public` enables public-disk probing after shared storage is provisioned. Each probe uses a random short-lived path and deletes it. Grant create/read/delete permissions; deny directory listing to public clients at the storage edge.

Restrict `/ready` and `/up` to load balancer/monitor networks at the ingress. Configure probing every 10 seconds, a 5-second probe deadline, 3 failures before removal and 3 successes before returning traffic. Set DB/Redis/storage connect/read timeouts below the probe deadline at your client/provider configuration. This code cannot cancel a blocking driver call; do not treat load-balancer timeouts as application connection timeouts. Redis/session/queue health and worker health need separate monitoring. Database SELECT 1 alone cannot prove writable-primary health.

## Target topology

Use at least two PHP-FPM/Nginx app nodes across separate failure domains behind redundant managed HTTPS load balancing. Supply identical releases, APP_KEY, production URL, Redis/cache prefixes and authentication config. Do not introduce read replicas into payment reads: verification must read/write the authoritative primary and preserve row locks/transactions.

Use a managed primary database with automatic failover and point-in-time recovery. Use managed Redis failover with persistence and a noeviction queue/lock policy; keep independent cache capacity if caching requires eviction. Stage 3's periodic scan recovers pending payments after queue loss; completed SMS/email claims require manual reconciliation rather than blind delivery retries.

Set SESSION_DRIVER=redis, CACHE_STORE=redis and QUEUE_CONNECTION=redis only after validating the shared infrastructure. Sessions must survive requests alternating between nodes; sticky sessions are not an HA substitute. Redis failover may log users out if state is lost.

Uploads currently explicitly use the `public` disk and public/storage routes. Keep its path backed by replicated shared storage mounted at storage/app/public on every app node and worker; use storage:link per release. Provide shared Livewire temporary upload storage or validated cross-node upload routing. Do not merely set FILESYSTEM_DISK=s3: that does not redirect explicit public disk usage, and an S3 adapter has not been added here. Keep compiled views, logs and the draining marker node-local. Verify upload/download/delete across both nodes before enabling balancing. Shared filesystem outage is checked by READINESS_STORAGE_DISKS=public, but a filesystem probe cannot prove the mount is the intended shared volume; validate mount identity during provisioning.

## Proxy and application configuration

Set APP_ENV=production, APP_DEBUG=false, APP_URL=https://giveabu.com, SESSION_SECURE_COOKIE=true. Configure TRUSTED_PROXIES as exact load-balancer IPs/CIDRs. Forward protocol, port and client IP only from those proxies; forwarded host is intentionally not trusted. Restrict app-node ports to ingress networks. Keep CORS_ALLOWED_ORIGINS explicit. Preserve signed reset-link URLs through the proxy.

An example private app-node Nginx configuration is in deployment/giveabu-app.nginx.conf. Adjust paths, PHP-FPM socket, ingress network and request/upload limits. Test nginx -t before reload. It is not an internet-facing TLS configuration; TLS is terminated at the trusted managed load balancer. This PR does not change .env, DNS, certificates or live server configuration.

## Rolling releases and failover

1. Build a versioned release and install locked dependencies. Address the existing security advisories before production rollout.
2. Apply backward-compatible migrations once from a controlled deployment runner; do not migrate from every node. Stage 3 indexes require a production-sized lock/duration rehearsal.
3. Drain one node using its marker; wait until the load balancer removes it and active requests finish. At least one other ready node must remain.
4. Switch that node's release symlink, rebuild config/routes/views, reload PHP-FPM/opcache, validate health locally, and remove the marker. Wait for readiness successes before draining the next node.
5. Gracefully restart workers using queue:restart and Stage 3 Supervisor settings. Run scheduler on multiple nodes only with shared Redis and its distributed scheduler locks. Never kill an active worker solely to accelerate rollout.
6. Exercise a synthetic read-only probe through the ingress. Verify donor login/session continuity, HTTPS reset links, uploads and staging gateway callbacks.

For rollback, drain and return one node at a time to the former compatible release. Do not undo financial state or remove migration columns needed by another running version. Leave compatible indexes installed. Reconcile uncertain provider outcomes independently.

## Availability acceptance

In staging, stop one app node, one worker and the Redis/database primary separately. Confirm traffic reroutes, duplicate verification remains idempotent, completed records cannot revert, pending payments recover after failover, and external notification claims do not duplicate. Perform a backup restore drill and measure actual recovery time/data loss. Repeat upload/session checks after failover. Provider outages must not drain every app node: readiness does not call payment, SMS or SMTP providers.

The 99.99% goal remains an infrastructure/operations target, not a guarantee from this PR. Stages 5 and 6 must add monitoring and repeatable load/failover evidence before assigning a production SLO.

References: [Laravel deployment](https://laravel.com/framework/docs/12.x/deployment), [trusted proxies](https://laravel.com/docs/12.x/requests#configuring-trusted-proxies).
