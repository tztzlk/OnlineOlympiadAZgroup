<?php

use App\Models\ChildProfile;
use App\Models\OlympiadRequest;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

it('creates a new olympiad request as pending and pending payment', function () {
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
        ->assertJsonPath('request.status', 'pending')
        ->assertJsonPath('request.payment_status', 'pending')
        ->assertJsonPath('request.child_profile_id', $child->public_id)
        ->assertJsonPath('redirect_to_quiz', false);

    $this->assertDatabaseHas('olympiad_requests', [
        'user_id' => $parent->id,
        'child_profile_id' => $child->id,
        'subject_id' => $subject->id,
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);
});

it('updates an existing olympiad request without creating duplicates or auto approving it', function () {
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
        'status' => 'pending',
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
        ->assertJsonPath('request.status', 'pending')
        ->assertJsonPath('request.payment_status', 'failed');

    expect(OlympiadRequest::count())->toBe(1);

    $this->assertDatabaseHas('olympiad_requests', [
        'id' => $request->id,
        'language' => 'kk',
        'parent_name' => 'Обновлённый родитель',
        'payment_status' => 'failed',
        'status' => 'pending',
    ]);
});

it('does not allow quiz access until request is approved and payment is confirmed', function () {
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
        ->assertStatus(403)
        ->assertJsonPath('message', 'Заявка ещё не одобрена администратором.');

    $request->update(['status' => 'approved']);

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
        ->assertJsonPath('request.payment_status', 'paid');

    $this->getJson('/api/admin/requests')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $request->public_id,
            'status' => 'approved',
            'payment_status' => 'paid',
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
