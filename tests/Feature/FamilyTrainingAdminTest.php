<?php

use App\Models\CallbackRequest;
use App\Models\ChildProfile;
use App\Models\OlympiadRequest;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('lets a parent create and update child profiles', function () {
    $parent = User::factory()->create();
    Sanctum::actingAs($parent);

    $create = $this->postJson('/api/profile/children', [
        'first_name' => 'Aruzhan',
        'last_name' => 'Test',
        'grade' => 5,
        'language_preference' => 'ru',
    ])->assertCreated();

    $childId = $create->json('child.id');

    $this->patchJson('/api/profile/children/' . $childId, [
        'first_name' => 'Aruzhan',
        'last_name' => 'Test',
        'grade' => 6,
        'language_preference' => 'kk',
    ])->assertOk()
        ->assertJsonPath('child.grade', 6)
        ->assertJsonPath('child.language_preference', 'kk');
});

it('prevents a parent from reading another parents child profile', function () {
    $parent = User::factory()->create();
    $otherParent = User::factory()->create();
    $child = ChildProfile::create([
        'parent_id' => $otherParent->id,
        'first_name' => 'Ali',
        'last_name' => 'Hidden',
    ]);

    Sanctum::actingAs($parent);

    $this->getJson('/api/profile/children/' . $child->public_id)
        ->assertForbidden();
});

it('stores olympiad request and result for a selected child', function () {
    $parent = User::factory()->create();
    $child = ChildProfile::create([
        'parent_id' => $parent->id,
        'first_name' => 'Dana',
        'last_name' => 'Student',
        'grade' => 7,
        'language_preference' => 'ru',
    ]);
    $subject = Subject::create(['name' => 'Math']);
    $quiz = Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'Math Quiz',
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
    $correct = $question->answers()->create([
        'label' => 'A',
        'position' => 1,
        'answer' => '4',
        'is_correct' => true,
    ]);

    Sanctum::actingAs($parent);

    $this->postJson('/api/olympiad/request', [
        'subject_id' => $subject->public_id,
        'child_profile_id' => $child->public_id,
        'language' => 'ru',
        'parent_name' => $parent->name,
        'parent_phone' => $parent->phone,
        'parent_email' => $parent->email,
    ])->assertOk();

    OlympiadRequest::query()->update([
        'payment_status' => 'paid',
        'status' => 'approved',
    ]);

    $this->postJson('/api/quiz/' . $quiz->public_id . '/submit', [
        'child_profile_id' => $child->public_id,
        'answers' => [
            $question->id => $correct->id,
        ],
    ])->assertOk();

    $this->assertDatabaseHas('quiz_results', [
        'user_id' => $parent->id,
        'child_profile_id' => $child->id,
        'quiz_id' => $quiz->id,
        'score' => 1,
    ]);
});

it('creates training attempts without creating quiz results', function () {
    $parent = User::factory()->create();
    $child = ChildProfile::create([
        'parent_id' => $parent->id,
        'first_name' => 'Mira',
        'last_name' => 'Train',
        'grade' => 8,
    ]);
    $subject = Subject::create(['name' => 'Physics']);
    $quiz = Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'Physics Quiz',
        'time_limit' => 20,
        'is_published' => true,
    ]);
    $category = QuizCategory::create([
        'quiz_id' => $quiz->id,
        'label' => '8 класс',
        'grade_from' => 8,
        'grade_to' => 8,
        'sort_order' => 1,
    ]);
    $question = Question::create([
        'quiz_id' => $quiz->id,
        'quiz_category_id' => $category->id,
        'question' => 'g = ?',
        'position' => 1,
    ]);
    $correct = $question->answers()->create([
        'label' => 'A',
        'position' => 1,
        'answer' => '9.8',
        'is_correct' => true,
    ]);

    Sanctum::actingAs($parent);

    $this->postJson('/api/training/' . $quiz->public_id . '/submit', [
        'child_profile_id' => $child->public_id,
        'answers' => [
            $question->id => $correct->id,
        ],
    ])->assertOk()
        ->assertJsonPath('score', 1)
        ->assertJsonPath('items.0.correct_answer', '9.8');

    $this->assertDatabaseHas('training_attempts', [
        'child_profile_id' => $child->id,
        'quiz_id' => $quiz->id,
        'score' => 1,
    ]);
    $this->assertDatabaseMissing('quiz_results', [
        'child_profile_id' => $child->id,
        'quiz_id' => $quiz->id,
    ]);
});

it('stores callback requests when proof of work is valid', function () {
    config()->set('security.pow.difficulty', 1);
    $payload = powPayload($this, 'callback');

    $this->postJson('/api/support/callback', [
        'name' => 'Parent',
        'phone' => '+77000000000',
        'email' => 'parent@example.com',
        'message' => 'Call me back',
        ...$payload,
    ])->assertCreated();

    $this->assertDatabaseHas('callback_requests', [
        'name' => 'Parent',
        'phone' => '+77000000000',
        'type' => 'callback',
    ]);
});

it('stores help desk requests and exposes them in admin callbacks list', function () {
    config()->set('security.pow.difficulty', 1);
    $payload = powPayload($this, 'feedback');

    $this->postJson('/api/support/feedback', [
        'name' => 'Help User',
        'email' => 'help@example.com',
        'phone' => '+77044444444',
        'topic' => 'Login issue',
        'message' => 'Cannot access account',
        ...$payload,
    ])->assertOk();

    $this->assertDatabaseHas('callback_requests', [
        'name' => 'Help User',
        'email' => 'help@example.com',
        'phone' => '+77044444444',
        'topic' => 'Login issue',
        'type' => 'helpdesk',
    ]);

    $admin = User::factory()->create(['is_admin' => true]);
    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/callbacks')
        ->assertOk()
        ->assertJsonFragment([
            'name' => 'Help User',
            'email' => 'help@example.com',
            'topic' => 'Login issue',
            'type' => 'helpdesk',
        ]);
});

it('lets admin import participants from csv', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Sanctum::actingAs($admin);

    $content = implode("\n", [
        'parent_email;parent_name;parent_phone;child_first_name;child_last_name;grade;birth_date;school;city;language_preference',
        'mom@example.com;Mom User;+77011111111;Ayan;Import;5;2014-01-01;School 1;Almaty;ru',
    ]);

    $file = UploadedFile::fake()->createWithContent('participants.csv', $content);

    $this->post('/api/admin/participants/import', [
        'file' => $file,
    ])->assertOk()
        ->assertJsonPath('imported', 1);

    $this->assertDatabaseHas('users', [
        'email' => 'mom@example.com',
    ]);
    $this->assertDatabaseHas('child_profiles', [
        'first_name' => 'Ayan',
        'last_name' => 'Import',
    ]);

    $parent = User::query()->where('email', 'mom@example.com')->firstOrFail();

    expect(Hash::check('TempPass123!', $parent->password))->toBeFalse();
});

it('lets admin export callback requests', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    CallbackRequest::create([
        'name' => 'Callback Lead',
        'phone' => '+77022222222',
        'email' => 'lead@example.com',
        'message' => 'Need details',
    ]);

    Sanctum::actingAs($admin);

    $this->get('/api/admin/callbacks/export')
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
});

it('lets admin export participants', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $parent = User::factory()->create([
        'name' => 'Parent Export',
        'email' => 'parent-export@example.com',
        'phone' => '+77033333333',
    ]);
    ChildProfile::create([
        'parent_id' => $parent->id,
        'first_name' => 'Amina',
        'last_name' => 'Export',
        'grade' => 6,
        'school' => 'School 12',
        'city' => 'Almaty',
        'language_preference' => 'ru',
    ]);

    Sanctum::actingAs($admin);

    $this->get('/api/admin/participants/export')
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
});

it('lets admin create quiz questions with a custom number of answers', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/quizzes', [
        'title' => 'Flexible Answers Quiz',
        'description' => 'Quiz with three answers',
        'time_limit' => 45,
        'is_published' => false,
        'subject' => [
            'name' => 'Biology',
            'description' => 'Biology subject',
            'start_date' => now()->toDateString(),
        ],
        'categories' => [
            [
                'label' => '5-6',
                'grade_from' => 5,
                'grade_to' => 6,
                'sort_order' => 1,
                'questions' => [
                    [
                        'question' => 'How many hearts does an octopus have?',
                        'position' => 1,
                        'correct_answer' => 'C',
                        'answers' => [
                            ['label' => 'A', 'position' => 1, 'answer' => 'One'],
                            ['label' => 'B', 'position' => 2, 'answer' => 'Two'],
                            ['label' => 'C', 'position' => 3, 'answer' => 'Three'],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('categories.0.questions.0.correct_answer', 'C')
        ->assertJsonCount(3, 'categories.0.questions.0.answers');

    $quizId = \App\Models\Quiz::query()->where('title', 'Flexible Answers Quiz')->value('id');

    expect($quizId)->not->toBeNull();
    expect(\App\Models\Answer::query()->whereHas('question', fn ($query) => $query->where('quiz_id', $quizId))->count())->toBe(3);
});
