<?php

use App\Models\OlympiadRequest;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('requires school during registration', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+7 (777) 777-77-77',
        'password' => 'Secret123!',
        'password_confirmation' => 'Secret123!',
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

    $this->get('/api/profile/results/' . $result->id . '/certificate')
        ->assertOk()
        ->assertHeader('content-type', 'image/svg+xml; charset=UTF-8')
        ->assertSee('ONLINE OLYMPIAD', false);

    Sanctum::actingAs($otherUser);

    $this->get('/api/profile/results/' . $result->id . '/certificate')
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
        'completed' => false,
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/quiz/' . $quiz->id . '/violate')
        ->assertOk();

    $this->assertDatabaseHas('olympiad_requests', [
        'user_id' => $user->id,
        'subject_id' => $subject->id,
        'disqualification_reason' => 'window_focus_lost',
    ]);

    $this->getJson('/api/quiz/' . $subject->id)
        ->assertStatus(403);
});
