# Production Readiness Report — Online Olympiad

## Summary

This report documents the production readiness audit and refactoring applied to the Online Olympiad platform (Laravel 12 + Vue 3 SPA).

---

## Problems Found

### 1. Hardcoded localhost URLs (Critical)

**8 frontend locations** used `http://localhost:8000/api` as the API base URL, making the application non-functional outside of local development.

| File | Issue |
|---|---|
| `resources/js/api.js` | `baseURL: 'http://localhost:8000/api'` |
| `resources/router/index.js` | `fetch("http://localhost:8000/api/profile", ...)` |
| `resources/page/admin/AdminLogin.vue` | `axios.post("http://localhost:8000/api/auth/admin/login", ...)` |
| `resources/page/admin/AdminRequests.vue` | Two calls with full localhost URLs |
| `resources/page/admin/AdminDashboard.vue` | `axios.get("http://localhost:8000/api/admin/dashboard", ...)` |
| `resources/page/admin/AdminQuizzes.vue` | `axios.get("http://localhost:8000/api/admin/quizzes", ...)` |
| `resources/components/News.vue` | `axios.get('http://localhost:8000/api/news')` |

### 2. Inconsistent API Client Usage (Medium)

- A centralized `api.js` client existed but was not used consistently.
- Admin pages and `News.vue` imported raw `axios` and used full hardcoded URLs.
- The router guard used native `fetch()` instead of the axios client.
- `EditProfile.vue` used `import api from "@/api"` with a Vite alias (`@/`) that was never defined in `vite.config.js`.

### 3. No Vite Environment Variables for API (Medium)

- `.env.example` had no `VITE_API_URL` variable.
- The frontend never used `import.meta.env.VITE_*` for API configuration.
- The API base URL was only configurable by editing source code.

### 4. Sanctum / Session Not Documented for Production (Medium)

- `config/sanctum.php` stateful domains defaulted to `localhost,localhost:3000,127.0.0.1`.
- No documentation on what to set for `SANCTUM_STATEFUL_DOMAINS` or `SESSION_DOMAIN` in production.
- The `.env` file had a duplicate `SESSION_DOMAIN` line (first set to `127.0.0.1`, then overridden to `null`).

### 5. Queue Configuration Defaults (Low)

- `config/queue.php` batching and failed job tables defaulted to `sqlite` as the database connection, but the application uses MySQL.

### 6. No Deployment Documentation (Medium)

- No `DEPLOYMENT.md` or production setup instructions existed.
- No Supervisor configuration example for queue workers.
- No Nginx configuration reference.
- Laravel optimization commands were not documented.

---

## Fixes Applied

### 1. Centralized API Client (`resources/js/api.js`)

- Changed `baseURL` from hardcoded `http://localhost:8000/api` to `import.meta.env.VITE_API_URL || '/api'`.
- The client now reads the API URL from the Vite environment at build time, with a sensible fallback for same-origin deployments.
- Existing interceptors (Bearer token injection, 401 redirect) preserved.

### 2. All Frontend Components Migrated to Centralized Client

| File | Change |
|---|---|
| `resources/router/index.js` | Replaced `fetch()` with `api.get('/profile')` |
| `resources/page/admin/AdminLogin.vue` | Replaced raw `axios` with `api.post('/auth/admin/login')` |
| `resources/page/admin/AdminRequests.vue` | Replaced raw `axios` with `api.patch()` and `api.get()` |
| `resources/page/admin/AdminDashboard.vue` | Replaced raw `axios` with `api.get('/admin/dashboard')` |
| `resources/page/admin/AdminQuizzes.vue` | Replaced raw `axios` with `api.get('/admin/quizzes')` |
| `resources/components/News.vue` | Replaced raw `axios` with `api.get('/news')` |
| `resources/page/EditProfile.vue` | Fixed broken `@/api` alias to `../js/api` |

All admin components no longer manually read `localStorage.getItem("token")` and pass headers — the centralized client interceptor handles token injection automatically.

### 3. Environment Configuration

- **`.env.example`**: Added `VITE_API_URL`, `SANCTUM_STATEFUL_DOMAINS`, and production guidance comments.
- **`config/queue.php`**: Changed batching and failed job database defaults from `sqlite` to `mysql`.

### 4. Documentation Created

- **`DEPLOYMENT.md`**: Full deployment guide covering server requirements, environment setup, Nginx config, Supervisor queue workers, cron, and update procedures.
- **`PRODUCTION_READINESS_REPORT.md`**: This file.

---

## Remaining Risks and Recommendations

### Security

- **HTTPS required**: Ensure SSL is configured. Set `SESSION_SECURE_COOKIE=true` in production `.env`.
- **`.env` protection**: Ensure the `.env` file is not accessible from the web (Nginx config should deny `/.` paths).
- **`APP_DEBUG=false`**: Must be set in production to prevent stack traces from leaking.
- **`APP_KEY`**: Generate a strong key with `php artisan key:generate` and keep it secret.

### Operational

- **Queue worker monitoring**: Supervisor must be configured and running for background jobs. See `DEPLOYMENT.md` for the config.
- **Database migrations**: Run `php artisan migrate --force` on every deployment.
- **Cache invalidation**: Run `config:cache`, `route:cache`, `view:cache` after every deployment.
- **Frontend rebuild**: `npm run build` must be run whenever environment variables or frontend code changes, since `VITE_*` values are embedded at build time.

### Known Limitations

- **Admin panel token storage**: Admin login stores the token in `localStorage` and the centralized client reads it from the Pinia store. Ensure the Pinia `user` store is hydrated from `localStorage` on app initialization so admin sessions persist across page reloads.
- **CDN dependencies in Blade**: `welcome.blade.php` loads Bootstrap CSS/JS from a CDN. Consider bundling via npm for reliability, or pin CDN versions with SRI hashes (already present).
- **No CSRF cookie fetch**: Sanctum cookie-based auth typically requires a `GET /sanctum/csrf-cookie` call before login. Verify this is handled if switching from token to cookie auth.

---

## Deployment Steps (Quick Reference)

1. Clone repository to server
2. `composer install --no-dev --optimize-autoloader`
3. Copy `.env.example` to `.env`, configure production values
4. `php artisan key:generate`
5. `npm ci && npm run build`
6. `php artisan migrate --force`
7. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
8. Configure Nginx (see `DEPLOYMENT.md`)
9. Configure Supervisor for queue workers
10. Set up cron for task scheduler
11. Verify: visit the production URL, test login, test admin panel
