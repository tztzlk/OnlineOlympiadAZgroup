<?php

use App\Models\ChildProfile;
use App\Models\OlympiadRequest;
use App\Models\PaymentImportRow;
use App\Models\PaymentRecord;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createOlympiadFixture(User $parent): array
{
    $child = ChildProfile::create([
        'parent_id' => $parent->id,
        'first_name' => 'Максат',
        'last_name' => 'Амантаев',
        'grade' => 7,
        'language_preference' => 'ru',
    ]);

    $subject = Subject::create(['name' => 'Математика']);
    $quiz = Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'Олимпиада по математике',
        'time_limit' => 30,
        'is_published' => true,
    ]);

    $category = QuizCategory::create([
        'quiz_id' => $quiz->id,
        'label' => '7 класс',
        'grade_from' => 7,
        'grade_to' => 7,
        'sort_order' => 1,
    ]);

    $question = Question::create([
        'quiz_id' => $quiz->id,
        'quiz_category_id' => $category->id,
        'question' => '2 + 2 = ?',
        'position' => 1,
    ]);

    $correctAnswer = $question->answers()->create([
        'label' => 'A',
        'position' => 1,
        'answer' => '4',
        'is_correct' => true,
    ]);

    return compact('child', 'subject', 'quiz', 'category', 'question', 'correctAnswer');
}

function createOlympiadRequestForParent(User $parent): array
{
    ['child' => $child, 'subject' => $subject] = createOlympiadFixture($parent);

    Sanctum::actingAs($parent);

    $response = test()->postJson('/api/olympiad/request', [
        'subject_id' => $subject->public_id,
        'child_profile_id' => $child->public_id,
        'language' => 'ru',
        'parent_name' => 'Р РѕРґРёС‚РµР»СЊ',
        'parent_phone' => '+77000000000',
        'parent_email' => 'parent@example.com',
    ])->assertOk();

    $request = OlympiadRequest::query()->latest()->firstOrFail();
    $paymentRecord = PaymentRecord::query()->where('olympiad_request_id', $request->id)->firstOrFail();

    return compact('response', 'request', 'paymentRecord', 'subject', 'child');
}

function importKaspiCsv(User $admin, string $contents)
{
    Sanctum::actingAs($admin);

    return test()->post('/api/admin/payments/import', [
        'file' => UploadedFile::fake()->createWithContent('kaspi.csv', $contents),
    ], [
        'Accept' => 'application/json',
    ]);
}

it('creates a new olympiad participation as approved with pending payment', function () {
    $parent = User::factory()->create();
    ['child' => $child, 'subject' => $subject] = createOlympiadFixture($parent);

    Sanctum::actingAs($parent);

    $this->postJson('/api/olympiad/request', [
        'subject_id' => $subject->public_id,
        'child_profile_id' => $child->public_id,
        'language' => 'ru',
        'parent_name' => 'Родитель',
        'parent_phone' => '+77000000000',
        'parent_email' => 'parent@example.com',
    ])->assertOk()
        ->assertJsonPath('request.status', 'approved')
        ->assertJsonPath('request.payment_status', 'pending')
        ->assertJsonPath('request.reconciliation_status', 'awaiting_payment')
        ->assertJsonPath('request.child_profile_id', $child->public_id)
        ->assertJsonPath('payment_reference', fn ($value) => filled($value))
        ->assertJsonPath('payment_comment', fn ($value) => filled($value))
        ->assertJsonPath('redirect_to_quiz', false);

    $this->assertDatabaseHas('olympiad_requests', [
        'user_id' => $parent->id,
        'child_profile_id' => $child->id,
        'subject_id' => $subject->id,
        'status' => 'approved',
        'payment_status' => 'pending',
    ]);

    $this->assertDatabaseHas('payment_records', [
        'parent_id' => $parent->id,
        'child_profile_id' => $child->id,
        'subject_id' => $subject->id,
        'status' => 'pending',
        'provider' => 'kaspi',
        'reconciliation_status' => 'awaiting_payment',
    ]);
});

it('updates an existing participation without creating duplicates and resets unpaid entries to pending payment', function () {
    $parent = User::factory()->create();
    ['child' => $child, 'subject' => $subject] = createOlympiadFixture($parent);

    Sanctum::actingAs($parent);

    $this->postJson('/api/olympiad/request', [
        'subject_id' => $subject->public_id,
        'child_profile_id' => $child->public_id,
        'language' => 'ru',
        'parent_name' => 'Первый родитель',
        'parent_phone' => '+77000000000',
        'parent_email' => 'first@example.com',
    ])->assertOk();

    $request = OlympiadRequest::query()->firstOrFail();
    $request->update([
        'status' => 'approved',
        'payment_status' => 'failed',
    ]);

    $this->postJson('/api/olympiad/request', [
        'subject_id' => $subject->public_id,
        'child_profile_id' => $child->public_id,
        'language' => 'kk',
        'parent_name' => 'Обновлённый родитель',
        'parent_phone' => '+77011111111',
        'parent_email' => 'updated@example.com',
    ])->assertOk()
        ->assertJsonPath('request.status', 'approved')
        ->assertJsonPath('request.payment_status', 'pending');

    expect(OlympiadRequest::count())->toBe(1);

    $this->assertDatabaseHas('olympiad_requests', [
        'id' => $request->id,
        'language' => 'kk',
        'parent_name' => 'Обновлённый родитель',
        'payment_status' => 'pending',
        'status' => 'approved',
    ]);
});

it('does not allow quiz access until payment is confirmed', function () {
    $parent = User::factory()->create();
    ['child' => $child, 'subject' => $subject] = createOlympiadFixture($parent);

    Sanctum::actingAs($parent);

    $this->postJson('/api/olympiad/request', [
        'subject_id' => $subject->public_id,
        'child_profile_id' => $child->public_id,
        'language' => 'ru',
        'parent_name' => 'Родитель',
        'parent_phone' => '+77000000000',
        'parent_email' => 'parent@example.com',
    ])->assertOk();

    $request = OlympiadRequest::query()->firstOrFail();

    $this->getJson('/api/quiz/' . $subject->public_id . '?child_profile_id=' . $child->public_id)
        ->assertStatus(402)
        ->assertJsonPath('message', 'Оплата ещё не подтверждена. После проверки оплаты доступ к олимпиаде откроется.');

    $request->update(['status' => 'rejected', 'payment_status' => 'paid']);

    $this->getJson('/api/quiz/' . $subject->public_id . '?child_profile_id=' . $child->public_id)
        ->assertStatus(403)
        ->assertJsonPath('message', 'Заявка отклонена. Доступ к олимпиаде закрыт.');
});

it('allows quiz access and submission only for approved and paid requests', function () {
    $parent = User::factory()->create();
    ['child' => $child, 'subject' => $subject, 'quiz' => $quiz, 'question' => $question, 'correctAnswer' => $correctAnswer] = createOlympiadFixture($parent);

    Sanctum::actingAs($parent);

    $this->postJson('/api/olympiad/request', [
        'subject_id' => $subject->public_id,
        'child_profile_id' => $child->public_id,
        'language' => 'ru',
        'parent_name' => 'Родитель',
        'parent_phone' => '+77000000000',
        'parent_email' => 'parent@example.com',
    ])->assertOk();

    OlympiadRequest::query()->update([
        'status' => 'approved',
        'payment_status' => 'paid',
        'paid_at' => now(),
        'attempt_started_at' => now()->subMinutes(5),
    ]);

    $this->getJson('/api/quiz/' . $subject->public_id . '?child_profile_id=' . $child->public_id)
        ->assertOk()
        ->assertJsonPath('child.id', $child->public_id);

    $this->postJson('/api/quiz/' . $quiz->public_id . '/submit', [
        'child_profile_id' => $child->public_id,
        'answers' => [
            $question->id => $correctAnswer->id,
        ],
    ])->assertOk()
        ->assertJsonPath('score', 1);
});

it('lets an admin update request and payment statuses and exposes changes in the requests api', function () {
    $parent = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    ['child' => $child, 'subject' => $subject] = createOlympiadFixture($parent);

    Sanctum::actingAs($parent);
    $this->postJson('/api/olympiad/request', [
        'subject_id' => $subject->public_id,
        'child_profile_id' => $child->public_id,
        'language' => 'ru',
        'parent_name' => 'Родитель',
        'parent_phone' => '+77000000000',
        'parent_email' => 'parent@example.com',
    ])->assertOk();

    $request = OlympiadRequest::query()->firstOrFail();

    Sanctum::actingAs($admin);

    $this->patchJson('/api/admin/requests/' . $request->public_id . '/status', [
        'status' => 'approved',
    ])->assertOk()
        ->assertJsonPath('request.status', 'approved');

    $this->patchJson('/api/admin/requests/' . $request->public_id . '/payment', [
        'payment_status' => 'paid',
    ])->assertOk()
        ->assertJsonPath('request.payment_status', 'paid')
        ->assertJsonPath('request.reconciliation_status', 'matched');

    $this->getJson('/api/admin/requests')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $request->public_id,
            'status' => 'approved',
            'payment_status' => 'paid',
            'reconciliation_status' => 'matched',
        ]);
});

it('shows only the latest request per parent child and subject in the admin list', function () {
    $parent = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    ['child' => $child, 'subject' => $subject] = createOlympiadFixture($parent);

    OlympiadRequest::create([
        'user_id' => $parent->id,
        'child_profile_id' => $child->id,
        'subject_id' => $subject->id,
        'first_name' => $child->first_name,
        'last_name' => $child->last_name,
        'birth_date' => '2013-01-01',
        'grade' => 7,
        'language' => 'ru',
        'parent_name' => 'Старый родитель',
        'parent_phone' => '+77000000000',
        'parent_email' => 'old@example.com',
        'status' => 'rejected',
        'payment_status' => 'failed',
    ]);

    $latest = OlympiadRequest::create([
        'user_id' => $parent->id,
        'child_profile_id' => $child->id,
        'subject_id' => $subject->id,
        'first_name' => $child->first_name,
        'last_name' => $child->last_name,
        'birth_date' => '2013-01-01',
        'grade' => 7,
        'language' => 'ru',
        'parent_name' => 'Новый родитель',
        'parent_phone' => '+77011111111',
        'parent_email' => 'new@example.com',
        'status' => 'approved',
        'payment_status' => 'paid',
        'paid_at' => now(),
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/requests')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.id'))->toBe($latest->public_id);
    expect($response->json('data.0.parent_email'))->toBe('new@example.com');
});

it('lets the owner report payment without confirming it immediately', function () {
    $parent = User::factory()->create();
    ['request' => $request, 'paymentRecord' => $paymentRecord] = createOlympiadRequestForParent($parent);

    Sanctum::actingAs($parent);

    $this->postJson('/api/olympiad/request/' . $request->public_id . '/payment-report', [
        'paid_at' => '2026-04-16T10:00:00+05:00',
    ])->assertOk()
        ->assertJsonPath('payment_status', 'pending')
        ->assertJsonPath('reconciliation_status', 'reported');

    $paymentRecord->refresh();
    $request->refresh();

    expect($request->payment_status)->toBe('pending');
    expect($paymentRecord->status)->toBe('pending');
    expect($paymentRecord->reconciliation_status)->toBe('reported');
    expect($paymentRecord->customer_reported_at)->not->toBeNull();
    expect($paymentRecord->customer_paid_at)->not->toBeNull();
});

it('forbids reporting another users payment', function () {
    $parent = User::factory()->create();
    $stranger = User::factory()->create();
    ['request' => $request] = createOlympiadRequestForParent($parent);

    Sanctum::actingAs($stranger);

    $this->postJson('/api/olympiad/request/' . $request->public_id . '/payment-report', [
        'paid_at' => '2026-04-16T10:00:00+05:00',
    ])->assertForbidden();
});

it('matches imported kaspi csv rows by request id and stays idempotent on reimport', function () {
    $parent = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    ['request' => $request, 'paymentRecord' => $paymentRecord, 'subject' => $subject, 'child' => $child] = createOlympiadRequestForParent($parent);

    $csv = implode("\n", [
        'comment,amount,paid_at,reference',
        "\"Оплата заявки {$request->public_id}\",{$paymentRecord->amount},2026-04-16 10:15:00,TX-REQUEST-ID",
    ]);

    importKaspiCsv($admin, $csv)
        ->assertOk()
        ->assertJsonPath('summary.matched', 1);

    $request->refresh();
    $paymentRecord->refresh();

    expect($request->payment_status)->toBe('paid');
    expect($paymentRecord->status)->toBe('paid');
    expect($paymentRecord->reconciliation_status)->toBe('matched');
    expect($paymentRecord->external_reference)->toBe('TX-REQUEST-ID');

    Sanctum::actingAs($parent);
    $this->getJson('/api/quiz/' . $subject->public_id . '?child_profile_id=' . $child->public_id)
        ->assertOk()
        ->assertJsonPath('child.id', $child->public_id);

    importKaspiCsv($admin, $csv)->assertOk();

    expect(PaymentImportRow::count())->toBe(1);
});

it('matches imported kaspi csv rows by full payment comment', function () {
    $parent = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    ['response' => $response, 'request' => $request, 'paymentRecord' => $paymentRecord] = createOlympiadRequestForParent($parent);

    $fullComment = $response->json('payment_comment');

    $csv = implode("\n", [
        'comment,amount,paid_at,reference',
        '"' . str_replace('"', '""', $fullComment) . '",' . $paymentRecord->amount . ',2026-04-16 11:00:00,TX-FULL-COMMENT',
    ]);

    importKaspiCsv($admin, $csv)->assertOk();

    $request->refresh();
    $paymentRecord->refresh();

    expect($request->payment_status)->toBe('paid');
    expect($paymentRecord->status)->toBe('paid');
    expect($paymentRecord->external_reference)->toBe('TX-FULL-COMMENT');
});

it('marks ambiguous imported rows for review without opening quiz access', function () {
    $parentA = User::factory()->create();
    $parentB = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);

    ['request' => $requestA, 'paymentRecord' => $paymentA, 'subject' => $subjectA, 'child' => $childA] = createOlympiadRequestForParent($parentA);
    ['request' => $requestB, 'paymentRecord' => $paymentB] = createOlympiadRequestForParent($parentB);

    $paymentA->forceFill([
        'status' => 'pending',
        'reconciliation_status' => 'reported',
        'customer_reported_at' => now(),
        'customer_paid_at' => now()->setTime(10, 0),
    ])->save();

    $paymentB->forceFill([
        'amount' => $paymentA->amount,
        'status' => 'pending',
        'reconciliation_status' => 'reported',
        'customer_reported_at' => now(),
        'customer_paid_at' => now()->setTime(10, 5),
    ])->save();

    $csv = implode("\n", [
        'comment,amount,paid_at,reference',
        ','.$paymentA->amount.',2026-04-16 10:10:00,TX-AMBIGUOUS',
    ]);

    importKaspiCsv($admin, $csv)->assertOk();

    $requestA->refresh();
    $paymentA->refresh();
    $paymentB->refresh();

    expect($requestA->payment_status)->toBe('pending');
    expect($paymentA->reconciliation_status)->toBe('needs_review');
    expect($paymentB->reconciliation_status)->toBe('needs_review');
    expect(PaymentImportRow::query()->firstOrFail()->status)->toBe('needs_review');

    Sanctum::actingAs($parentA);
    $this->getJson('/api/quiz/' . $subjectA->public_id . '?child_profile_id=' . $childA->public_id)
        ->assertStatus(402);
});

it('stores explanation text when admin creates a quiz', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/quizzes', [
        'subject' => [
            'name' => 'Физика',
            'description' => 'Описание предмета',
            'start_date' => '2026-04-21',
        ],
        'title' => 'Олимпиада по физике',
        'description' => 'Проверка знаний',
        'price' => 2990,
        'time_limit' => 45,
        'is_published' => false,
        'categories' => [[
            'label' => '7-8',
            'grade_from' => 7,
            'grade_to' => 8,
            'sort_order' => 1,
            'questions' => [[
                'question' => 'Сколько будет 2 + 2?',
                'explanation' => 'Потому что при сложении двух и двух получаем четыре.',
                'position' => 1,
                'correct_answer' => 'A',
                'answers' => [
                    ['label' => 'A', 'answer' => '4', 'position' => 1],
                    ['label' => 'B', 'answer' => '5', 'position' => 2],
                ],
            ]],
        ]],
    ])->assertCreated();

    expect(Question::query()->firstOrFail()->explanation)->toBe('Потому что при сложении двух и двух получаем четыре.');
    expect($response->json('categories.0.questions.0.explanation'))->toBe('Потому что при сложении двух и двух получаем четыре.');
});

it('returns full review payload with correct answers and explanation text', function () {
    $parent = User::factory()->create();
    ['child' => $child, 'subject' => $subject, 'quiz' => $quiz, 'question' => $question, 'correctAnswer' => $correctAnswer] = createOlympiadFixture($parent);

    $question->update([
        'explanation' => 'Правильный ответ получается обычным сложением.',
    ]);

    $wrongAnswer = $question->answers()->create([
        'label' => 'B',
        'position' => 2,
        'answer' => '5',
        'is_correct' => false,
    ]);

    Sanctum::actingAs($parent);

    $this->postJson('/api/olympiad/request', [
        'subject_id' => $subject->public_id,
        'child_profile_id' => $child->public_id,
        'language' => 'ru',
        'parent_name' => 'Родитель',
        'parent_phone' => '+77000000000',
        'parent_email' => 'parent@example.com',
    ])->assertOk();

    OlympiadRequest::query()->update([
        'status' => 'approved',
        'payment_status' => 'paid',
        'paid_at' => now(),
        'attempt_started_at' => now()->subMinutes(5),
    ]);

    $submitResponse = $this->postJson('/api/quiz/' . $quiz->public_id . '/submit', [
        'child_profile_id' => $child->public_id,
        'answers' => [
            $question->id => $wrongAnswer->id,
        ],
    ])->assertOk();

    $resultId = $submitResponse->json('id');

    $this->getJson('/api/profile/results/' . $resultId . '/mistakes')
        ->assertOk()
        ->assertJsonPath('mistakes_count', 1)
        ->assertJsonPath('questions_count', 1)
        ->assertJsonPath('items.0.status', 'wrong')
        ->assertJsonPath('items.0.correct_answer.label', 'A')
        ->assertJsonPath('items.0.selected_answer.label', 'B')
        ->assertJsonPath('items.0.explanation', 'Правильный ответ получается обычным сложением.');
});
