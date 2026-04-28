<?php

use App\Models\OlympiadRequest;
use App\Models\PaymentRecord;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\Subject;
use App\Models\User;
use App\Models\ChildProfile;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('requires school during registration', function () {
    config()->set('security.pow.difficulty', 1);
    $pow = powPayload($this, 'register');

    $response = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+7 (777) 777-77-77',
        'password' => 'Secret123!',
        'password_confirmation' => 'Secret123!',
        ...$pow,
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['school']);
});

it('allows a participant to download only their own certificate', function () {
    $user = User::factory()->create([
        'school' => 'Школа №1',
    ]);

    $otherUser = User::factory()->create([
        'school' => 'Школа №2',
    ]);

    $subject = Subject::create([
        'name' => 'Математика',
    ]);

    $quiz = Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'Итоговый тест',
        'time_limit' => 60,
        'is_published' => true,
    ]);

    $result = QuizResult::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'score' => 8,
        'total' => 10,
    ]);

    Sanctum::actingAs($user);

    $this->get('/api/profile/results/' . $result->public_id . '/certificate')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    Sanctum::actingAs($otherUser);

    $this->get('/api/profile/results/' . $result->public_id . '/certificate')
        ->assertForbidden();
});

it('disqualifies a quiz attempt after focus-loss violation', function () {
    $user = User::factory()->create([
        'school' => 'Школа №12',
    ]);

    $subject = Subject::create([
        'name' => 'Информатика',
    ]);

    $quiz = Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'Основной тур',
        'time_limit' => 45,
        'is_published' => true,
    ]);

    OlympiadRequest::create([
        'user_id' => $user->id,
        'subject_id' => $subject->id,
        'first_name' => 'Test',
        'last_name' => 'Student',
        'birth_date' => '2010-01-01',
        'grade' => '8',
        'language' => 'ru',
        'parent_name' => 'Parent',
        'parent_phone' => '+77777777777',
        'parent_email' => 'parent@example.com',
        'status' => 'approved',
        'payment_status' => 'paid',
        'completed' => false,
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/quiz/' . $quiz->public_id . '/violate')
        ->assertOk();

    $this->assertDatabaseHas('olympiad_requests', [
        'user_id' => $user->id,
        'subject_id' => $subject->id,
        'disqualification_reason' => 'window_focus_lost',
    ]);

    $this->getJson('/api/quiz/' . $subject->public_id)
        ->assertStatus(403);
});

it('starts an attempt on the server and stores timing telemetry on submit', function () {
    Carbon::setTestNow('2026-04-17 10:00:00');

    $user = User::factory()->create([
        'school' => 'Timing School',
    ]);

    $subject = Subject::create([
        'name' => 'Physics',
    ]);

    $quiz = Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'Final Round',
        'time_limit' => 30,
        'is_published' => true,
    ]);

    $category = $quiz->categories()->create([
        'label' => '8',
        'grade_from' => 8,
        'grade_to' => 8,
        'sort_order' => 1,
    ]);

    $question = $category->questions()->create([
        'quiz_id' => $quiz->id,
        'question' => '2 + 2 = ?',
        'position' => 1,
    ]);

    $correctAnswer = $question->answers()->create([
        'label' => 'A',
        'position' => 1,
        'answer' => '4',
        'is_correct' => true,
    ]);

    $question->answers()->create([
        'label' => 'B',
        'position' => 2,
        'answer' => '5',
        'is_correct' => false,
    ]);

    OlympiadRequest::create([
        'user_id' => $user->id,
        'subject_id' => $subject->id,
        'first_name' => 'Test',
        'last_name' => 'Student',
        'birth_date' => '2010-01-01',
        'grade' => '8',
        'language' => 'ru',
        'parent_name' => 'Parent',
        'parent_phone' => '+77777777777',
        'parent_email' => 'parent@example.com',
        'status' => 'approved',
        'payment_status' => 'paid',
        'completed' => false,
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/quiz/' . $quiz->public_id . '/start')
        ->assertOk()
        ->assertJsonPath('remaining_seconds', 1800);

    $this->assertDatabaseHas('olympiad_requests', [
        'user_id' => $user->id,
        'subject_id' => $subject->id,
    ]);

    Carbon::setTestNow('2026-04-17 10:03:00');

    $this->postJson('/api/quiz/' . $quiz->public_id . '/submit', [
        'answers' => [
            $question->id => $correctAnswer->id,
        ],
    ])
        ->assertOk()
        ->assertJsonPath('elapsed_seconds', 180)
        ->assertJsonPath('requires_review', false);

    $this->assertDatabaseHas('quiz_results', [
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'elapsed_seconds' => 180,
        'requires_review' => false,
    ]);

    Carbon::setTestNow();
});

it('rejects submissions that exceed the server-side time limit', function () {
    Carbon::setTestNow('2026-04-17 12:00:00');

    $user = User::factory()->create([
        'school' => 'Limit School',
    ]);

    $subject = Subject::create([
        'name' => 'Chemistry',
    ]);

    $quiz = Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'Short Quiz',
        'time_limit' => 1,
        'is_published' => true,
    ]);

    $category = $quiz->categories()->create([
        'label' => '8',
        'grade_from' => 8,
        'grade_to' => 8,
        'sort_order' => 1,
    ]);

    $question = $category->questions()->create([
        'quiz_id' => $quiz->id,
        'question' => 'H2O is?',
        'position' => 1,
    ]);

    $answer = $question->answers()->create([
        'label' => 'A',
        'position' => 1,
        'answer' => 'Water',
        'is_correct' => true,
    ]);

    OlympiadRequest::create([
        'user_id' => $user->id,
        'subject_id' => $subject->id,
        'first_name' => 'Test',
        'last_name' => 'Student',
        'birth_date' => '2010-01-01',
        'grade' => '8',
        'language' => 'ru',
        'parent_name' => 'Parent',
        'parent_phone' => '+77777777777',
        'parent_email' => 'parent@example.com',
        'status' => 'approved',
        'payment_status' => 'paid',
        'completed' => false,
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/quiz/' . $quiz->public_id . '/start')->assertOk();

    Carbon::setTestNow('2026-04-17 12:01:05');

    $this->postJson('/api/quiz/' . $quiz->public_id . '/submit', [
        'answers' => [
            $question->id => $answer->id,
        ],
    ])
        ->assertStatus(403);

    $this->assertDatabaseHas('olympiad_requests', [
        'user_id' => $user->id,
        'subject_id' => $subject->id,
        'disqualification_reason' => 'time_limit_exceeded',
    ]);

    Carbon::setTestNow();
});

it('requires a specific paid participant when access could be ambiguous', function () {
    $user = User::factory()->create(['school' => 'Family School']);

    $subject = Subject::create([
        'name' => 'Biology',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
    ]);

    $quiz = Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'Family Quiz',
        'time_limit' => 30,
        'is_published' => true,
    ]);

    $childOne = ChildProfile::create([
        'parent_id' => $user->id,
        'first_name' => 'One',
        'last_name' => 'Student',
        'grade' => 8,
    ]);

    $childTwo = ChildProfile::create([
        'parent_id' => $user->id,
        'first_name' => 'Two',
        'last_name' => 'Student',
        'grade' => 8,
    ]);

    $paidRequest = OlympiadRequest::create([
        'user_id' => $user->id,
        'child_profile_id' => $childOne->id,
        'subject_id' => $subject->id,
        'first_name' => 'One',
        'last_name' => 'Student',
        'birth_date' => '2010-01-01',
        'grade' => '8',
        'language' => 'ru',
        'parent_name' => 'Parent',
        'parent_phone' => '+77777777777',
        'parent_email' => 'parent@example.com',
        'status' => 'approved',
        'payment_status' => 'paid',
        'completed' => false,
    ]);

    PaymentRecord::create([
        'parent_id' => $user->id,
        'child_profile_id' => $childOne->id,
        'subject_id' => $subject->id,
        'olympiad_request_id' => $paidRequest->id,
        'amount' => 3000,
        'currency' => 'KZT',
        'provider' => 'kaspi',
        'status' => 'paid',
        'reconciliation_status' => 'matched',
        'paid_at' => now(),
    ]);

    OlympiadRequest::create([
        'user_id' => $user->id,
        'child_profile_id' => $childTwo->id,
        'subject_id' => $subject->id,
        'first_name' => 'Two',
        'last_name' => 'Student',
        'birth_date' => '2011-01-01',
        'grade' => '8',
        'language' => 'ru',
        'parent_name' => 'Parent',
        'parent_phone' => '+77777777777',
        'parent_email' => 'parent@example.com',
        'status' => 'approved',
        'payment_status' => 'pending',
        'completed' => false,
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/quiz/' . $subject->public_id)
        ->assertNotFound();

    $this->getJson('/api/quiz/' . $subject->public_id . '?child_profile_id=' . $childOne->public_id)
        ->assertOk();
});

it('blocks quiz access outside the configured subject period', function () {
    $user = User::factory()->create(['school' => 'Calendar School']);

    $subject = Subject::create([
        'name' => 'History',
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
    ]);

    $quiz = Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'History Quiz',
        'time_limit' => 30,
        'is_published' => true,
    ]);

    $category = $quiz->categories()->create([
        'label' => '8',
        'grade_from' => 8,
        'grade_to' => 8,
        'sort_order' => 1,
    ]);

    $question = $category->questions()->create([
        'quiz_id' => $quiz->id,
        'question' => 'Question?',
        'position' => 1,
    ]);

    $question->answers()->create([
        'label' => 'A',
        'position' => 1,
        'answer' => 'Answer',
        'is_correct' => true,
    ]);

    $child = ChildProfile::create([
        'parent_id' => $user->id,
        'first_name' => 'Timed',
        'last_name' => 'Student',
        'grade' => 8,
    ]);

    $request = OlympiadRequest::create([
        'user_id' => $user->id,
        'child_profile_id' => $child->id,
        'subject_id' => $subject->id,
        'first_name' => 'Timed',
        'last_name' => 'Student',
        'birth_date' => '2010-01-01',
        'grade' => '8',
        'language' => 'ru',
        'parent_name' => 'Parent',
        'parent_phone' => '+77777777777',
        'parent_email' => 'parent@example.com',
        'status' => 'approved',
        'payment_status' => 'paid',
        'completed' => false,
    ]);

    PaymentRecord::create([
        'parent_id' => $user->id,
        'child_profile_id' => $child->id,
        'subject_id' => $subject->id,
        'olympiad_request_id' => $request->id,
        'amount' => 3000,
        'currency' => 'KZT',
        'provider' => 'kaspi',
        'status' => 'paid',
        'reconciliation_status' => 'matched',
        'paid_at' => now(),
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/quiz/' . $subject->public_id . '?child_profile_id=' . $child->public_id)
        ->assertStatus(403)
        ->assertJsonPath('message', 'Период прохождения ещё не начался.');
});
