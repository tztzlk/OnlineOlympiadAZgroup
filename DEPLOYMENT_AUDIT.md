# Deployment Audit - Current State

Project: Online Olympiad (Laravel 12 + Vue 3 SPA)
Audit date: 2026-04-27
Scope: current repository state, not historical assumptions

## Executive Summary

The deployment story is materially better than the older audit suggested:

- the default database connection is now `mysql`
- Sanctum stateful hosts are normalized from `APP_URL` / `FRONTEND_URL`
- a production deploy script exists
- a predeploy database validation command exists
- API authentication failures are explicitly rendered as JSON

The project is deployable, but it still has several operational and release-management gaps that make a broad public launch risky without careful manual monitoring.

## What Was Verified In The Current Codebase

### Confirmed Good

1. `config/database.php`
   - Default connection is `env('DB_CONNECTION', 'mysql')`.
   - This removes the older "fallback to sqlite in production" risk.

2. `config/sanctum.php`
   - Stateful domains are built from parsed hosts, not raw full URLs.
   - This removes the older "full APP_URL breaks Sanctum host matching" claim as a default code issue.

3. `config/queue.php`
   - Queue default is `database`.
   - Queue batching and failed-job storage also default to MySQL-backed settings.

4. `deploy/forge-deploy.sh`
   - A real deploy script exists and includes:
     - environment sanity checks
     - `composer install`
     - `npm ci && npm run build`
     - `php artisan deploy:check-db`
     - migrations
     - cache rebuild
     - queue restart

5. `routes/console.php`
   - `deploy:check-db` exists and checks:
     - DB connectivity
     - pending migrations
     - critical tables
     - duplicate critical `.env` keys
     - preview deployment safety

6. `bootstrap/app.php`
   - API `AuthenticationException` is rendered as JSON `401` instead of redirecting to a missing `login` route.
   - During this review, the response message encoding was also corrected.

7. Frontend build
   - `npm run build` completed successfully during this review.

## Current Findings

### High

1. No CI/CD pipeline

Status:
- No `.github/workflows`, GitLab CI, or equivalent pipeline config is present.

Risk:
- Deploy quality depends on manual discipline.
- Easy to miss build, migration, cache, or queue restart steps.
- No automatic regression gate before production deploy.

Recommendation:
- Add at least one pipeline that runs:
  - PHP install
  - JS install
  - frontend build
  - test suite
  - optional lint/static checks

2. Queue worker and scheduler are documented but not verified automatically

Status:
- Docs and deploy flow assume Supervisor / Forge scheduler are configured.
- The repo does not contain an application-level health check for queue processing or scheduler execution.

Risk:
- Background jobs may silently stop processing.
- Scheduled tasks may fail without immediate visibility.

Recommendation:
- Add operational checks for:
  - queue worker liveness
  - scheduler heartbeat
  - failed job alerting

3. Production environment bootstrap is under-documented because `.env.example` is missing

Status:
- The repository does not contain `.env.example`.
- Deployment docs describe required variables, but there is no canonical template file in the repo.

Risk:
- New environments are easier to misconfigure.
- Missing or duplicated keys become more likely.
- Reviewers and operators cannot diff the intended env shape cleanly.

Recommendation:
- Add a committed `.env.example` that matches the real production expectations in `DEPLOYMENT.md`.

### Medium

4. Frontend auth still depends on bearer tokens in `localStorage`

Status:
- `resources/js/api.js` injects `Authorization: Bearer ...` from the Pinia store.
- `resources/stores/user.js` hydrates token and session info from `localStorage`.

Risk:
- This is operationally workable, but less resilient than a stricter cookie-first SPA auth model.
- Any XSS would have access to the token.

Recommendation:
- Keep current model for launch if needed, but treat CSP and XSS prevention as critical.
- Consider migrating to a stricter first-party SPA auth model later if the product scales.

5. Frontend still hard-depends on build-time `VITE_API_URL` discipline

Status:
- `resources/js/api.js` uses `import.meta.env.VITE_API_URL || '/api'`.
- Same-origin deploys are safe because of the `/api` fallback.
- Cross-origin or unusual build environments still require explicit `VITE_API_URL`.

Risk:
- A bad production build can point to the wrong API host or rely on assumptions that only hold locally.

Recommendation:
- In production, always set `VITE_API_URL` explicitly before `npm run build`, even for same-origin setups.

6. Health endpoint exists, but it is shallow

Status:
- `/up` is configured through Laravel's built-in health route.
- It does not prove queue throughput, mail delivery, file storage health, or application-specific readiness.

Risk:
- Infra can look "up" while the platform is partially broken.

Recommendation:
- Add deeper readiness checks or a private ops endpoint for:
  - DB write/read sanity
  - queue backlog visibility
  - storage disk availability
  - mail transport smoke verification

7. Release verification is still largely manual

Status:
- There is a good deployment checklist, but production readiness still depends on human smoke testing.

Risk:
- Regressions in auth, quiz flow, payments, or certificate generation can slip through.

Recommendation:
- Add a post-deploy smoke suite or at least scripted API checks for the highest-value flows.

### Low

8. `app/Http/Kernel.php` remains as a legacy-looking file and can confuse maintainers

Status:
- Middleware configuration is primarily driven from `bootstrap/app.php`.
- `app/Http/Kernel.php` still exists and includes comments / structure that can mislead future edits.

Risk:
- Future changes may be applied in the wrong place.

Recommendation:
- Keep it aligned with the actual bootstrap strategy or document clearly that `bootstrap/app.php` is authoritative.

9. Client-side 401 handling is aggressive

Status:
- `resources/js/api.js` redirects immediately on any `401`.

Risk:
- Expired sessions are handled cleanly, but background/profile refreshes can force hard redirects that feel abrupt.

Recommendation:
- Acceptable for launch, but revisit if UX complaints appear around session expiry.

## Items From The Older Audit That Are No Longer Accurate

These older findings should no longer be treated as current code defects:

1. "Default database connection is SQLite"
   - Not true anymore. Default is now MySQL.

2. "SANCTUM_STATEFUL_DOMAINS must be fixed because APP_URL is a full URL"
   - The current code parses hosts from URLs before building the stateful list.

3. "Queue batching / failed jobs default to sqlite"
   - Not true anymore. Current queue config uses MySQL defaults.

4. "No deployment script or deployment documentation"
   - Not true anymore. Both now exist.

## Practical Launch Position

If you launch now, do it as a controlled production release:

- same-origin app + API
- explicit production env values
- manual deploy owner on standby
- queue and logs actively monitored
- small initial user group

I would not describe the current repo as "hands-off production-ready" yet, but it is substantially closer than the stale audit implied.

## Immediate Priorities Before Wider Public Use

1. Add `.env.example`
2. Add CI/CD pipeline
3. Add queue / scheduler health verification
4. Add scripted smoke checks for critical flows
5. Keep deployment bound to one documented script only
