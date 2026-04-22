# Online Olympiad - Project Documentation

## Project Overview

**Project Name:** Online Olympiad  
**Framework:** Laravel 12 + Vue 3 + Tailwind CSS  
**Type:** Educational Quiz/Olympiad Platform  
**Language:** PHP 8.2+, JavaScript (ES6+)  
**Database:** MySQL/SQLite  

---

## Project Structure

### Root Directory Files

| File | Purpose |
|------|---------|
| `.editorconfig` | Editor configuration for consistent coding styles |
| `.env` | Environment configuration (DB, API keys, secrets) |
| `.env.example` | Example environment file template |
| `.gitattributes` | Git attributes configuration |
| `.gitignore` | Git ignore rules |
| `artisan` | Laravel CLI command runner |
| `composer.json` | PHP dependencies and project metadata |
| `composer.lock` | Locked PHP dependency versions |
| `DEPLOYMENT.md` | Deployment instructions and guide |
| `DEPLOYMENT_AUDIT.md` | Deployment audit checklist |
| `DEPLOYMENT_CHECKLIST.md` | Pre-deployment checklist |
| `PRODUCTION_READINESS_REPORT.md` | Production readiness assessment |
| `package.json` | Node.js dependencies and scripts |
| `package-lock.json` | Locked Node.js dependency versions |
| `phpunit.xml` | PHPUnit testing configuration |
| `README.md` | Standard Laravel README |
| `vite.config.js` | Vite build tool configuration |

---

## Backend Structure

### `app/` Directory

#### `app/Console/`
- Console command classes for scheduled tasks and CLI operations

#### `app/Http/Controllers/`

**Base Controllers:**
| File | Purpose |
|------|---------|
| `Controller.php` | Base controller class |
| `PublicPageController.php` | Handles public pages, SEO, sitemap, robots.txt |

**API Controllers (`app/Http/Controllers/Api/`):**
| File | Purpose |
|------|---------|
| `AdminController.php` | Admin dashboard, user management, exports, imports |
| `AdminQuizController.php` | Quiz CRUD operations for admin |
| `AiController.php` | AI generation endpoints |
| `AuthController.php` | Authentication (login, register, password reset) |
| `ChildProfileController.php` | Child profile management |
| `LeaderboardController.php` | Leaderboard data |
| `MathQuizController.php` | Math quiz specific operations |
| `NewsController.php` | News/announcements API |
| `NotificationController.php` | User notifications, onboarding |
| `OlympiadRequestController.php` | Olympiad request handling, status, payments |
| `ProfileController.php` | User profile, results, certificates, payments |
| `QuizController.php` | Quiz attempts, submissions, violations |
| `SecurityController.php` | POW (Proof of Work) security challenges |
| `SubjectController.php` | Subject/category management |
| `SupportController.php` | Feedback and callback requests |
| `TrainingController.php` | Training mode quiz operations |
| `WebhookController.php` | Payment webhooks (YooKassa, Stripe, Telegram) |

#### `app/Http/Middleware/`
- Custom middleware classes for request filtering
- Throttling, authentication, admin checks, POW verification

#### `app/Http/Resources/`
- API resource transformers for consistent JSON responses

#### `app/Http/kernel.php`
- HTTP kernel configuration

#### `app/Mail/`
- Mailable classes for email notifications

#### `app/Models/`

| Model | Purpose | Key Relations |
|-------|---------|---------------|
| `Answer.php` | Quiz answer options | belongsTo Question |
| `CallbackRequest.php` | Support callback requests | belongsTo User |
| `ChildProfile.php` | Child/student profiles | belongsTo User (parent) |
| `MathQuestion.php` | Math-specific questions | - |
| `News.php` | News/announcements | - |
| `OlympiadRequest.php` | Olympiad participation requests | belongsTo User, ChildProfile |
| `PaymentImportRow.php` | Payment import tracking | - |
| `PaymentRecord.php` | Payment records | belongsTo User, OlympiadRequest |
| `PlatformNotification.php` | In-app notifications | belongsTo User |
| `ProcessedWebhook.php` | Webhook processing log | - |
| `Question.php` | Quiz questions | hasMany Answers, belongsTo Quiz |
| `Quiz.php` | Quiz definitions | hasMany Questions, belongsTo Subject |
| `QuizCategory.php` | Quiz categories | hasMany Quizzes |
| `QuizQuestion.php` | Quiz-question pivot | - |
| `QuizResult.php` | Quiz attempt results | belongsTo User, Quiz |
| `QuizSubject.php` | Quiz subjects | - |
| `Subject.php` | Olympiad subjects | - |
| `TrainingAttempt.php` | Training mode attempts | belongsTo User |
| `User.php` | User accounts | hasMany OlympiadRequests, ChildProfiles, QuizResults |

**User Model Features:**
- Role-based admin access (admin, operator, content, analyst)
- Sanctum API tokens
- Public ID system for privacy
- Plan/subscription support
- Settings JSON storage

#### `app/Providers/`
- Service providers for application bootstrapping

#### `app/Support/`
- Helper classes and utility functions

---

## Frontend Structure

### `resources/js/`

| File | Purpose |
|------|---------|
| `App.vue` | Root Vue application component |
| `adminAccess.js` | Admin access control utilities |
| `api.js` | Axios API client configuration |
| `app.js` | Vue app initialization (Pinia, Router, i18n, SEO) |
| `bootstrap.js` | Bootstrap JavaScript |
| `pow.js` | Proof of Work security implementation |
| `profileSections.js` | Profile section definitions |

**`resources/js/composables/`:**
- Reusable Vue composition functions
- `useSeo.js` - SEO management

### `resources/router/`
- Vue Router configuration

### `resources/stores/`
- Pinia state management stores

### `resources/i18n.js`
- Vue i18n internationalization configuration

### `resources/css/`
- Tailwind CSS and custom stylesheets

### `resources/views/`

| File | Purpose |
|------|---------|
| `welcome.blade.php` | Main SPA entry point with Vue mount |
| `sitemap.blade.php` | XML sitemap template |
| `certificates/` | Certificate PDF templates |
| `emails/` | Email blade templates |
| `partials/` | Reusable view partials |

### `resources/page/` - Vue Page Components

**Public Pages:**
| File | Purpose |
|------|---------|
| `About.vue` | About platform page |
| `CertificateCheck.vue` | Public certificate verification |
| `CertificatePreview.vue` | Certificate preview |
| `EditProfile.vue` | Profile editing |
| `ForgotPassword.vue` | Password recovery |
| `HelpDesk.vue` | Support/help desk |
| `Home.vue` | Homepage |
| `Leaderboard.vue` | Top participants display |
| `Login.vue` | User login |
| `Profile.vue` | Main profile dashboard |
| `Quiz.vue` | Active quiz interface |
| `Register.vue` | User registration |
| `RequestSuccess.vue` | Success confirmation pages |
| `ResetPassword.vue` | Password reset form |
| `ResultMistakes.vue` | Quiz mistake review |
| `Results.vue` | Quiz results display |
| `Rules.vue` | Platform rules |
| `Subject.vue` | Subject listing |
| `SubjectDetail.vue` | Subject details |
| `Training.vue` | Training mode |
| `Waiting.vue` | Waiting/loading states |

**Admin Pages (`resources/page/admin/`):**
| File | Purpose |
|------|---------|
| `AdminCallbacks.vue` | Callback request management |
| `AdminDashboard.vue` | Admin dashboard |
| `AdminLayout.vue` | Admin layout wrapper |
| `AdminLogin.vue` | Admin login |
| `AdminPayments.vue` | Payment management |
| `AdminQuizzes.vue` | Quiz management |
| `AdminRequests.vue` | Olympiad request management |
| `AdminResults.vue` | Results management |

**Profile Pages (`resources/page/profile/`):**
| File | Purpose |
|------|---------|
| `ProfileChildren.vue` | Child profiles |
| `ProfileLayout.vue` | Profile layout wrapper |
| `ProfileNotifications.vue` | User notifications |
| `ProfileOlympiads.vue` | Olympiad history |
| `ProfileOverview.vue` | Profile overview |
| `ProfilePayments.vue` | Payment history |
| `ProfileTraining.vue` | Training history |

### `resources/components/` - Reusable Vue Components

| Component | Purpose |
|-----------|---------|
| `CountdownBadge.vue` | Timer/countdown display |
| `Footer.vue` | Site footer |
| `Header.vue` | Navigation header |
| `HowItWorks.vue` | Platform explanation |
| `KaspiPaymentAssist.vue` | Kaspi payment helper |
| `Lang.vue` | Language switcher |
| `News.vue` | News display |
| `NotificationCenter.vue` | Notification UI |
| `PublicOffer.vue` | Terms/Public offer |
| `Reviews.vue` | User reviews display |
| `StatePanel.vue` | State indicator panel |
| `StatusBadge.vue` | Status badges |
| `Welcome.vue` | Welcome section |
| `Welcome.fixed.vue` | Fixed welcome variant |

---

## Database Structure

### Migrations (`database/migrations/`)

**Core Tables:**
| Migration | Creates |
|-----------|---------|
| `0001_01_01_000000_create_users_table.php` | users, password_resets, sessions |
| `0001_01_01_000001_create_cache_table.php` | cache, cache_locks |
| `0001_01_01_000002_create_jobs_table.php` | jobs, failed_jobs |
| `2026_02_05_144353_create_personal_access_tokens_table.php` | personal_access_tokens |

**Feature Migrations:**
| Migration | Purpose |
|-----------|---------|
| `2026_02_12_054847_add_role_to_users_table.php` | User roles |
| `2026_02_16_092503_create_news_table.php` | News table |
| `2026_02_17_050416_add_is_admin_to_users.php` | Admin flag |
| `2026_02_17_060800_create_subjects_table.php` | Subjects table |
| `2026_02_17_060803_create_olympiad_requests_table.php` | Olympiad requests |
| `2026_02_17_093510_create_quiz_subjects_table.php` | Quiz subjects |
| `2026_02_17_093547_create_quiz_questions_table.php` | Quiz questions |
| `2026_02_19_053649_create_quizzes_table.php` | Quizzes |
| `2026_02_19_053714_create_questions_table.php` | Questions |
| `2026_02_19_053727_create_answers_table.php` | Answers |
| `2026_02_19_053739_create_quiz_results_table.php` | Quiz results |
| `2026_03_14_000001_add_admin_fields_to_quiz_tables.php` | Admin quiz fields |
| `2026_03_17_000002_add_school_and_violation_fields.php` | School/violation tracking |
| `2026_03_17_000003_add_city_and_quiz_categories.php` | Cities and categories |
| `2026_03_18_000004_add_question_images_and_payment_fields.php` | Images, payments |
| `2026_03_24_000005_add_production_indexes.php` | Performance indexes |
| `2026_03_30_000006_create_child_profiles_table.php` | Child profiles |
| `2026_03_30_000007_add_child_profile_relationships.php` | Child relationships |
| `2026_03_30_000008_create_training_attempts_payment_records_callback_requests.php` | Training, payments, callbacks |
| `2026_03_30_000009_add_public_ids_and_plan.php` | Public IDs, plans |
| `2026_03_31_000010_create_processed_webhooks_table.php` | Webhook log |
| `2026_04_03_000011_add_settings_to_users_and_create_platform_notifications_table.php` | Settings, notifications |
| `2026_04_09_000012_add_type_and_topic_to_callback_requests.php` | Callback types |
| `2026_04_13_000010_add_admin_role_to_users.php` | Admin roles |
| `2026_04_15_000013_add_price_to_quizzes_table.php` | Quiz pricing |
| `2026_04_16_000014_add_answers_to_quiz_results_table.php` | Answer storage |
| `2026_04_16_000015_add_certificate_payment_fields.php` | Certificate payments |
| `2026_04_16_000016_add_kaspi_reconciliation_fields.php` | Kaspi integration |
| `2026_04_17_000016_add_attempt_timing_fields_to_olympiad_and_results.php` | Timing tracking |
| `2026_04_21_000017_add_explanation_to_questions_table.php` | Question explanations |

### Seeders (`database/seeders/`)

| File | Purpose |
|------|---------|
| `DatabaseSeeder.php` | Main seeder orchestrator |
| `QuizQuestionsSeeder.php` | Sample quiz questions |
| `QuizSeeder.php` | Sample quizzes |
| `SubjectSeeder.php` | Sample subjects |

---

## Routing

### `routes/api.php`

**Public API Endpoints:**
- `POST /auth/register` - User registration (with POW)
- `POST /auth/login` - User login
- `POST /auth/forgot-password` - Password reset request
- `POST /auth/reset-password` - Password reset confirm
- `GET /news` - List news
- `GET /leaderboard` - Get leaderboard
- `GET /certificate-check/{result}` - Public certificate verification
- `GET /subjects` - List subjects
- `GET /subjects/{id}` - Subject details
- `GET /security/pow-challenge` - Get POW challenge
- `POST /support/feedback` - Submit feedback
- `POST /support/callback` - Request callback
- `POST /webhooks/yookassa` - YooKassa webhook
- `POST /webhooks/stripe` - Stripe webhook
- `POST /webhooks/telegram` - Telegram webhook

**Authenticated API Endpoints:**
- Profile management (children, results, payments, trainings)
- Olympiad requests (create, check status, payment reports)
- Quiz operations (start, submit, violations)
- Training mode
- AI generation

**Admin API Endpoints:**
- Dashboard, users, participants
- Payments (export, import)
- Results (export)
- Callbacks (export)
- Requests (view, update status, update payment, delete)
- Quizzes (CRUD, publish/unpublish, image upload)

### `routes/web.php`

- `GET /robots.txt` - Robots.txt
- `GET /sitemap.xml` - XML sitemap
- `GET /` - Home page
- `GET /subject` - Subjects page
- `GET /subjects/{subject}` - Subject detail
- `GET /about` - About page
- `GET /rules` - Rules page
- `GET /leaderboard` - Leaderboard
- `GET /certificate-check` - Certificate check
- `GET /help-desk` - Help desk
- `GET /{any}` - SPA fallback (catches all for Vue Router)

---

## Configuration (`config/`)

| File | Purpose |
|------|---------|
| `app.php` | Application settings |
| `auth.php` | Authentication configuration |
| `cache.php` | Cache stores |
| `cors.php` | CORS policy |
| `database.php` | Database connections |
| `filesystems.php` | Storage disks |
| `logging.php` | Logging channels |
| `mail.php` | Mail drivers |
| `queue.php` | Queue drivers |
| `sanctum.php` | API token configuration |
| `security.php` | Security settings |
| `seo.php` | SEO defaults |
| `services.php` | Third-party services |
| `session.php` | Session configuration |

---

## Public Assets (`public/`)

| File | Purpose |
|------|---------|
| `.htaccess` | Apache rewrite rules |
| `index.php` | Application entry point |
| `robots.txt` | Search crawler rules |
| `favicon.ico` | Site favicon |
| `boy.png` | Static image asset |
| `welcome.png` | Welcome page image |
| `kaspi.png` | Kaspi payment logo |
| `theme-init.js` | Theme initialization |
| `build/` | Compiled assets (Vite) |

---

## Testing (`tests/`)

| Directory/File | Purpose |
|----------------|---------|
| `Feature/` | Feature/integration tests |
| `Unit/` | Unit tests |
| `Pest.php` | Pest testing configuration |
| `TestCase.php` | Base test case |

---

## Key Dependencies

### PHP (composer.json)
- `laravel/framework: ^12.0` - Laravel framework
- `laravel/sanctum: *` - API authentication
- `laravel/tinker: ^2.10.1` - Interactive shell
- `pestphp/pest: ^4.3` - Testing framework

### JavaScript (package.json)
- `vue: ^3.5.27` - Vue.js framework
- `vue-router: ^4.6.4` - Vue routing
- `pinia: ^3.0.4` - State management
- `vue-i18n: ^9.14.5` - Internationalization
- `tailwindcss: ^4.0.0` - CSS framework
- `vite: ^7.0.7` - Build tool
- `axios: ^1.13.4` - HTTP client
- `qrcode.vue: ^3.8.0` - QR code generation
- `imask: ^7.6.1` - Input masking

---

## Features Summary

1. **User Management**
   - Registration/Login with POW protection
   - Password reset
   - Role-based admin access
   - Child profile management

2. **Quiz/Olympiad System**
   - Multiple subjects and categories
   - Question with answers and explanations
   - Quiz attempts with timing
   - Violation detection
   - Training mode

3. **Payment Integration**
   - YooKassa (Russian payment system)
   - Stripe support
   - Kaspi integration
   - Webhook handling

4. **Admin Dashboard**
   - Role-based access (admin, operator, content, analyst)
   - User/participant management
   - Quiz CRUD with image upload
   - Payment tracking and import/export
   - Results management
   - Callback request handling

5. **Security Features**
   - Proof of Work (POW) anti-spam
   - Rate limiting/throttling
   - Sanctum API tokens
   - Admin capability checks

6. **Additional Features**
   - Certificate generation and verification
   - Leaderboard
   - News/announcements
   - Notifications
   - Multi-language support (i18n)
   - SEO optimization
   - AI integration

---

## Database Schema Summary

**Core Entities:**
- Users (with admin roles, public IDs)
- ChildProfiles (student profiles linked to parents)
- Subjects (olympiad subjects)
- Quizzes (quiz definitions with pricing)
- Questions (with images, explanations)
- Answers (correct/incorrect options)
- QuizResults (attempt results with answers)
- OlympiadRequests (participation requests)
- PaymentRecords (payment tracking)
- TrainingAttempts (training mode history)
- PlatformNotifications (in-app notifications)
- CallbackRequests (support requests)
- News (announcements)

---

## Build & Development

**Scripts (from package.json):**
- `npm run build` - Production build with Vite
- `npm run dev` - Development server

**Composer Scripts:**
- `composer run setup` - Full project setup
- `composer run dev` - Start development servers
- `composer run test` - Run tests

---

*Generated: April 21, 2026*
