<?php

use App\Models\ChildProfile;
use App\Models\OlympiadRequest;
use App\Models\PaymentRecord;
use App\Models\ProcessedWebhook;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('limits login attempts to five per minute per ip', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => bcrypt('StrongPass123!'),
    ]);

    RateLimiter::clear('login:127.0.0.1');

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }

    $this->postJson('/api/auth/login', [
        'email' => 'login@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(429);
});

it('limits registration to three accounts per hour per ip', function () {
    config()->set('security.pow.difficulty', 1);
    RateLimiter::clear('register:127.0.0.1');

    foreach (range(1, 3) as $index) {
        $pow = powPayload($this, 'register');

        $this->postJson('/api/auth/register', [
            'name' => 'User ' . $index,
            'email' => "user{$index}@example.com",
            'phone' => '+7700000000' . $index,
            'school' => 'School ' . $index,
            'city' => 'Almaty',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
            ...$pow,
        ])->assertCreated();
    }

    $pow = powPayload($this, 'register');

    $this->postJson('/api/auth/register', [
        'name' => 'User 4',
        'email' => 'user4@example.com',
        'phone' => '+77000000004',
        'school' => 'School 4',
        'city' => 'Almaty',
        'password' => 'StrongPass123!',
        'password_confirmation' => 'StrongPass123!',
        ...$pow,
    ])->assertStatus(429);
});

it('limits password reset requests per ip', function () {
    User::factory()->create(['email' => 'reset@example.com']);
    RateLimiter::clear('password-reset-request:127.0.0.1');

    foreach (range(1, 3) as $attempt) {
        $this->postJson('/api/auth/forgot-password', [
            'email' => 'reset@example.com',
        ])->assertOk();
    }

    $this->postJson('/api/auth/forgot-password', [
        'email' => 'reset@example.com',
    ])->assertStatus(429);
});

it('limits authenticated api endpoints to one hundred requests per minute per user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    RateLimiter::clear('api:' . $user->public_id);

    foreach (range(1, 100) as $attempt) {
        $this->getJson('/api/profile')->assertOk();
    }

    $this->getJson('/api/profile')->assertStatus(429);
});

it('returns a json 401 for unauthenticated api profile requests', function () {
    $this->getJson('/api/profile')
        ->assertStatus(401)
        ->assertJson([
            'message' => 'Требуется авторизация.',
        ]);
});

it('enforces ai generation quota for free plan users', function () {
    $user = User::factory()->create(['plan' => 'free']);
    Sanctum::actingAs($user);

    $key = sprintf('ai:%s:%s:%s', 'free', $user->public_id, now()->toDateString());
    RateLimiter::clear($key);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/ai/generate', [
            'prompt' => 'Write a short summary',
        ])->assertOk()
            ->assertHeader('X-AI-Limit', '5');
    }

    $this->postJson('/api/ai/generate', [
        'prompt' => 'One more prompt',
    ])->assertStatus(429);
});

it('enforces ai generation quota for pro plan users', function () {
    $user = User::factory()->create(['plan' => 'pro']);
    Sanctum::actingAs($user);

    $key = sprintf('ai:%s:%s:%s', 'pro', $user->public_id, now()->toDateString());
    RateLimiter::clear($key);

    foreach (range(1, 50) as $attempt) {
        $this->postJson('/api/ai/generate', [
            'prompt' => 'Generate protected content',
        ])->assertOk()
            ->assertHeader('X-AI-Limit', '50');
    }

    $this->postJson('/api/ai/generate', [
        'prompt' => 'Attempt fifty one',
    ])->assertStatus(429);
});

it('requires proof of work on public forms', function () {
    config()->set('security.pow.difficulty', 1);

    $this->postJson('/api/support/callback', [
        'name' => 'Parent',
        'phone' => '+77000000000',
    ])->assertStatus(422);

    $pow = powPayload($this, 'callback');

    $this->postJson('/api/support/callback', [
        'name' => 'Parent',
        'phone' => '+77000000000',
        ...$pow,
    ])->assertCreated();
});

it('verifies webhook signatures rejects stale stripe signatures and ignores duplicate deliveries', function () {
    config()->set('services.webhooks.yookassa_secret', 'yoo-secret');
    config()->set('services.webhooks.stripe_secret', 'stripe-secret');
    config()->set('services.webhooks.telegram_secret_token', 'telegram-secret');
    config()->set('security.webhooks.stripe_tolerance_seconds', 300);

    $user = User::factory()->create();
    $child = ChildProfile::create([
        'parent_id' => $user->id,
        'first_name' => 'Ayan',
        'last_name' => 'Test',
        'grade' => 5,
    ]);
    $subject = Subject::create(['name' => 'Physics']);
    $requestRecord = OlympiadRequest::create([
        'user_id' => $user->id,
        'child_profile_id' => $child->id,
        'subject_id' => $subject->id,
        'first_name' => 'Ayan',
        'last_name' => 'Test',
        'birth_date' => now()->subYears(10)->toDateString(),
        'grade' => 5,
        'language' => 'ru',
        'parent_name' => 'Parent',
        'parent_phone' => '+77000000000',
        'parent_email' => 'parent@example.com',
        'status' => 'approved',
        'payment_status' => 'pending',
    ]);
    $paymentRecord = PaymentRecord::create([
        'parent_id' => $user->id,
        'child_profile_id' => $child->id,
        'subject_id' => $subject->id,
        'olympiad_request_id' => $requestRecord->id,
        'status' => 'pending',
    ]);

    $stripePayload = json_encode([
        'id' => 'evt_test_1',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_test_1',
                'payment_intent' => 'pi_test_1',
                'metadata' => [
                    'olympiad_request_public_id' => $requestRecord->public_id,
                    'payment_record_public_id' => $paymentRecord->public_id,
                ],
            ],
        ],
    ], JSON_THROW_ON_ERROR);

    $timestamp = (string) now()->timestamp;
    $stripeSignature = hash_hmac('sha256', $timestamp . '.' . $stripePayload, 'stripe-secret');

    $this->withHeaders(['Stripe-Signature' => "t={$timestamp},v1={$stripeSignature}"])
        ->postJson('/api/webhooks/stripe', json_decode($stripePayload, true, 512, JSON_THROW_ON_ERROR))
        ->assertOk()
        ->assertJsonPath('provider', 'stripe')
        ->assertJsonPath('applied', true)
        ->assertJsonPath('duplicate', false);

    $requestRecord->refresh();
    $paymentRecord->refresh();

    expect($requestRecord->payment_status)->toBe('paid');
    expect($paymentRecord->status)->toBe('paid');
    expect(ProcessedWebhook::count())->toBe(1);

    $this->withHeaders(['Stripe-Signature' => "t={$timestamp},v1={$stripeSignature}"])
        ->postJson('/api/webhooks/stripe', json_decode($stripePayload, true, 512, JSON_THROW_ON_ERROR))
        ->assertOk()
        ->assertJsonPath('duplicate', true);

    expect(ProcessedWebhook::count())->toBe(1);

    $staleTimestamp = (string) now()->subMinutes(10)->timestamp;
    $staleSignature = hash_hmac('sha256', $staleTimestamp . '.' . $stripePayload, 'stripe-secret');

    $this->withHeaders(['Stripe-Signature' => "t={$staleTimestamp},v1={$staleSignature}"])
        ->postJson('/api/webhooks/stripe', json_decode($stripePayload, true, 512, JSON_THROW_ON_ERROR))
        ->assertStatus(401);

    $yooPayload = json_encode(['event' => 'payment.succeeded', 'object' => ['id' => 'yoo_1']], JSON_THROW_ON_ERROR);
    $yooSignature = hash_hmac('sha256', $yooPayload, 'yoo-secret');

    $this->withHeaders(['X-Yookassa-Signature' => $yooSignature])
        ->postJson('/api/webhooks/yookassa', json_decode($yooPayload, true, 512, JSON_THROW_ON_ERROR))
        ->assertOk()
        ->assertJsonPath('provider', 'yookassa');

    $this->withHeaders(['X-Telegram-Bot-Api-Secret-Token' => 'telegram-secret'])
        ->postJson('/api/webhooks/telegram', ['update_id' => 1])
        ->assertOk()
        ->assertJsonPath('provider', 'telegram');
});

it('enforces body size limits for api requests and uploads', function () {
    config()->set('security.pow.difficulty', 1);

    $pow = powPayload($this, 'feedback');

    $this->withServerVariables(['CONTENT_LENGTH' => (string) (1024 * 1024 + 1)])
        ->postJson('/api/support/feedback', [
            'name' => 'Parent',
            'email' => 'parent@example.com',
            'topic' => 'Need help',
            'message' => 'hello',
            ...$pow,
        ])->assertStatus(413);

    $admin = User::factory()->create(['is_admin' => true]);
    Sanctum::actingAs($admin);

    $this->withServerVariables(['CONTENT_LENGTH' => (string) (10 * 1024 * 1024 + 1)])
        ->post('/api/admin/quizzes/upload-image', [])
        ->assertStatus(413);
});

it('uses public uuids on exposed resources instead of sequential numeric ids', function () {
    $user = User::factory()->create();
    $child = ChildProfile::create([
        'parent_id' => $user->id,
        'first_name' => 'Ayan',
        'last_name' => 'Test',
        'grade' => 5,
    ]);
    $subject = Subject::create([
        'name' => 'Physics',
    ]);
    $quiz = \App\Models\Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'Physics Olympiad',
        'time_limit' => 30,
        'is_published' => true,
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/profile/children/' . $child->public_id)
        ->assertOk()
        ->assertJsonPath('id', $child->public_id);

    $this->getJson('/api/profile/children/' . $child->id)
        ->assertNotFound();

    $this->getJson('/api/quiz/' . $quiz->id)
        ->assertNotFound();

    $this->getJson('/api/subjects')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $subject->public_id,
            'name' => 'Physics',
        ]);
});
