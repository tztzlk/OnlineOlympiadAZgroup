# Deployment Audit — Senior DevOps Review

**Project:** Online Olympiad (Laravel 12 + Vue 3 SPA)  
**Audit focus:** Hidden deployment bugs that could break production.

---

## Executive Summary

Several **critical** and **high**‑impact issues were found that would cause production outages or broken behavior. One critical bug was fixed in-code; the rest are documented with mitigations below.

---

## Critical Findings (Would Break Production)

### 1. ✅ FIXED: Admin middleware on every request (HTTP Kernel)

**Location:** `app/Http/kernel.php`

**Issue:** `AdminMiddleware` was listed in the **global** `$middleware` array (as `'admin' => AdminMiddleware::class`). In Laravel, global middleware runs on every request. Result: **all requests** (including public API and login) were subject to admin checks; unauthenticated or non-admin users would get 403 Forbidden on every route.

**Fix applied:** Removed `'admin' => \App\Http\Middleware\AdminMiddleware::class` from `$middleware`. The `admin` middleware remains correctly registered in `$routeMiddleware` and is used only on admin routes in `routes/api.php`.

**Verification:** After deploy, confirm `/api/news`, `/api/auth/login`, and `/api/subjects` work without auth; `/api/admin/*` still requires auth:sanctum + admin.

---

### 2. Default database connection is SQLite

**Location:** `config/database.php` line 19

**Issue:** `'default' => env('DB_CONNECTION', 'sqlite')`. If `.env` is misconfigured or `DB_CONNECTION` is missing/typo’d, the app uses **SQLite** and looks for `database/database.sqlite`. On a typical production server (MySQL only), this causes **DB connection failures** and 500s on first request.

**Mitigation:**

- In `.env.example`, keep `DB_CONNECTION=mysql` and document that it is required.
- In deployment docs/checklist, add: “Verify `DB_CONNECTION=mysql` in production `.env`.”
- Optional hardening: in `config/database.php`, change default to `env('DB_CONNECTION', 'mysql')` if this app will always use MySQL in production.

---

### 3. VITE_API_URL and env expansion at build time

**Location:** `.env.example` has `VITE_API_URL="${APP_URL}/api"`; frontend uses `import.meta.env.VITE_API_URL` in `resources/js/api.js`.

**Issue:** Vite reads `.env` and may expand `${APP_URL}` only when the same vars are loaded into the same parsed set. If production build runs in CI or a minimal env where only `VITE_*` is set and `APP_URL` is not in the env used by Node, the built assets can get the literal string `"${APP_URL}/api"` as the API base URL, so **all API calls from the SPA fail** in production.

**Mitigation:**

- In production, **set `VITE_API_URL` explicitly** in `.env` (e.g. `VITE_API_URL=https://your-domain.com/api`) before running `npm run build`.
- Document in DEPLOYMENT.md: “For production, set `VITE_API_URL` to the full API base URL; do not rely only on `APP_URL` expansion during `npm run build`.”
- Consider validating in CI that built JS contains the expected API host (e.g. grep or small script) when building for production.

---

### 4. SANCTUM_STATEFUL_DOMAINS must be host-only

**Location:** `.env.example` has `SANCTUM_STATEFUL_DOMAINS="${APP_URL}"`; `config/sanctum.php` uses this for stateful SPA auth.

**Issue:** `APP_URL` is a full URL (e.g. `https://your-domain.com`). Sanctum’s stateful domain check compares **hostnames**. Using a full URL can leave leading/trailing noise or scheme in the value, so the request host might not match and **cookie-based auth can fail** (e.g. 401 on protected routes after login).

**Mitigation:**

- In production `.env`, set `SANCTUM_STATEFUL_DOMAINS=your-domain.com` (host only, no scheme, no path). For multiple domains, comma-separate.
- In `.env.example`, add a comment: “Use host only, e.g. `example.com` or `localhost`. Do not use full APP_URL.”
- Update DEPLOYMENT.md to state that `SANCTUM_STATEFUL_DOMAINS` must be the SPA host(s) only.

---

## High Findings (Risk of Outages or Incorrect Behavior)

### 5. User model `isAdmin()` vs `is_admin` (fixed for consistency)

**Location:** `app/Models/User.php`

**Issue:** Middleware uses `$user->is_admin` (column from migration). The model had `isAdmin()` returning `$this->role === 'admin'`, but there is no `role` column—only `is_admin`. Any code calling `isAdmin()` would get wrong behavior.

**Fix applied:** `isAdmin()` now returns `(bool) $this->is_admin`. Added `is_admin` to `$fillable` and `$casts` for consistency.

---

### 6. No CI/CD or deploy pipeline

**Location:** Repository root (no `.github/workflows`, `.gitlab-ci.yml`, or similar).

**Issue:** Deployments are manual. Easy to forget steps (e.g. `npm run build`, `php artisan migrate --force`, cache clears, queue worker restart), leading to **stale assets**, **missing migrations**, or **stale config** in production.

**Mitigation:**

- Add a minimal CI pipeline (e.g. GitHub Actions) that runs: `composer install --no-dev`, `npm ci && npm run build`, `php artisan migrate --force` (or use a deploy key/secrets), and optionally `php artisan config:cache` in a dry run.
- Document a single “deploy script” or checklist (as in DEPLOYMENT.md) and always use it.
- Consider a deploy script that fails if `APP_DEBUG=true` or `APP_ENV=local` in production.

---

### 7. Queue worker and cron not auto-verified

**Location:** DEPLOYMENT.md describes Supervisor and cron but there is no health check or probe that verifies queue/cron.

**Issue:** If Supervisor or cron is not installed or stops, jobs never run and the scheduler never runs. Failures can go unnoticed until users report missing functionality.

**Mitigation:**

- Add a simple health/sidecar endpoint (e.g. `/up` or a custom route) that checks queue connectivity (e.g. push a test job and verify, or check that the `jobs` table is being processed).
- Document monitoring for queue worker process and cron execution (e.g. log or metric when `schedule:run` executes).

---

### 8. Session and cache drivers depend on DB

**Location:** `config/session.php`, `config/cache.php`; `.env.example` has `SESSION_DRIVER=database`, `CACHE_STORE=database`.

**Issue:** If the DB is down or migrations haven’t run, session and cache fail, causing **broad 500s or “session not found”** behavior.

**Mitigation:**

- Ensure migrations run before traffic hits new code (e.g. run migrations in deploy script before switching symlink or restarting PHP).
- Consider a readiness probe that checks DB connectivity and, if possible, session write (e.g. write and read a test value).

---

## Medium / Low Findings

### 9. Migration rollback incomplete

**Location:** `database/migrations/2026_02_17_050416_add_is_admin_to_users.php`

**Issue:** `down()` did not drop the `is_admin` column, so `php artisan migrate:rollback` would not fully revert the schema.

**Fix applied:** `down()` now drops the `is_admin` column.

---

### 10. .env and dotfile exposure

**Location:** `public/.htaccess`; Nginx example in DEPLOYMENT.md.

**Status:** `.env` lives in project root, not in `public/`, so it is not directly served. DEPLOYMENT.md already includes `location ~ /\.(?!well-known).* { deny all; }` for Nginx. Apache’s default and the current rules do not serve `.env` from `public/`. **No change required**; keep ensuring document root is `public/` and dotfiles are denied.

---

### 11. No explicit APP_KEY validation on boot

**Issue:** If `APP_KEY` is empty in production, encryption and sessions can be insecure or break.

**Mitigation:** In deploy script or a bootstrap check, fail fast if `APP_ENV=production` and `APP_KEY` is empty (e.g. a one-line script or an Artisan command run during deploy).

---

### 12. CDN dependencies in Blade

**Location:** `resources/views/welcome.blade.php` loads Bootstrap CSS/JS from jsDelivr CDN.

**Issue:** If the CDN is down or blocked, the UI may not load correctly. SRI hashes are present, which is good for integrity.

**Mitigation:** Optional: bundle Bootstrap via npm for full control; or document the CDN dependency and consider a fallback or monitoring.

---

## Checklist Before First Production Deploy

- [ ] `.env` has `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` set, `APP_URL` correct.
- [ ] `DB_CONNECTION=mysql` and all `DB_*` values correct; migrations run.
- [ ] `VITE_API_URL` set to full production API URL; `npm run build` run with that env.
- [ ] `SANCTUM_STATEFUL_DOMAINS` set to production host only (e.g. `your-domain.com`).
- [ ] `SESSION_SECURE_COOKIE=true` and `SESSION_DOMAIN` set appropriately for HTTPS.
- [ ] Nginx (or Apache) document root is `public/`; dotfiles denied.
- [ ] Supervisor running queue workers; cron runs `schedule:run` every minute.
- [ ] `config:cache`, `route:cache`, `view:cache` run after deploy.
- [ ] HTTP Kernel: admin middleware only on admin routes (fix applied in this audit).
- [ ] User model: `isAdmin()` and `is_admin` consistent (fix applied).

---

## Summary of Code Changes Made in This Audit

1. **app/Http/kernel.php** — Removed `AdminMiddleware` from global `$middleware` (it stays in `$routeMiddleware` and is used only on admin routes).
2. **app/Models/User.php** — `isAdmin()` now uses `is_admin` attribute; added `is_admin` to `$fillable` and `$casts`.
3. **database/migrations/2026_02_17_050416_add_is_admin_to_users.php** — `down()` now drops `is_admin` column for correct rollback.

All other items are documented mitigations or process improvements with no code change in this audit.
