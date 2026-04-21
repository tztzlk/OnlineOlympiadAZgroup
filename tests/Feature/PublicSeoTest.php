<?php

use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createPublishedSubjectFixture(): Subject
{
    $subject = Subject::create([
        'name' => 'Математика',
        'description' => 'Онлайн-олимпиада по математике для школьников.',
        'image' => '/boy.png',
        'start_date' => now()->addWeek(),
    ]);

    $quiz = Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'Олимпиада по математике',
        'description' => 'Проверка знаний по математике',
        'time_limit' => 45,
        'is_published' => true,
    ]);

    QuizCategory::create([
        'quiz_id' => $quiz->id,
        'label' => '3-4',
        'grade_from' => 3,
        'grade_to' => 4,
        'sort_order' => 1,
    ]);

    QuizCategory::create([
        'quiz_id' => $quiz->id,
        'label' => '5-6',
        'grade_from' => 5,
        'grade_to' => 6,
        'sort_order' => 2,
    ]);

    return $subject;
}

it('serves robots.txt with sitemap', function () {
    $response = $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('content-type', 'text/plain; charset=UTF-8');

    $content = $response->getContent();

    expect($content)->toContain('User-agent: *');
    expect($content)->toContain('Sitemap: https://onlineolympiadazgroup-3fw5c0hn.on-forge.com/sitemap.xml');
});

it('renders page specific seo tags and fallback html on the catalog page', function () {
    $siteUrl = rtrim(config('seo.site_url'), '/');

    $response = $this->get('/subject')
        ->assertOk();

    $response->assertHeader('content-type', 'text/html; charset=UTF-8');
    $response->assertSee('<html lang="ru-KZ">', false);
    $response->assertSee('<title>Каталог онлайн-олимпиад по предметам</title>', false);
    $response->assertSee('meta name="description" content="Выберите онлайн-олимпиаду по предмету, посмотрите доступные направления для школьников и перейдите к регистрации и оплате участия."', false);
    $response->assertSee("rel=\"canonical\" href=\"{$siteUrl}/subject\"", false);
    $response->assertSee('Онлайн-олимпиады по предметам для школьников', false);
});

it('includes subject pages in sitemap and renders subject seo landing', function () {
    $subject = createPublishedSubjectFixture();
    $siteUrl = rtrim(config('seo.site_url'), '/');

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee("<loc>{$siteUrl}/subject</loc>", false)
        ->assertSee("<loc>{$siteUrl}/subjects/{$subject->public_id}</loc>", false);

    $this->get("/subjects/{$subject->public_id}")
        ->assertOk()
        ->assertSee("<title>Математика: онлайн-олимпиада для школьников</title>", false)
        ->assertSee('Онлайн-олимпиада по предмету Математика', false)
        ->assertSee('Классы', false)
        ->assertSee('3-4, 5-6', false);
});

it('returns seo-ready subject details through the public api', function () {
    $subject = createPublishedSubjectFixture();

    $this->getJson("/api/subjects/{$subject->public_id}")
        ->assertOk()
        ->assertJsonPath('name', 'Математика')
        ->assertJsonPath('time_limit', 45)
        ->assertJsonPath('grade_ranges.0', '3-4')
        ->assertJsonPath('grade_ranges.1', '5-6')
        ->assertJsonPath('registration_url', "/subject?subject={$subject->public_id}");
});
