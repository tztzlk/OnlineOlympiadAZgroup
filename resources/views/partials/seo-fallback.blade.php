@php($content = $seo['content'] ?? [])

@if(!empty($content))
    <main id="seo-fallback" class="seo-fallback" data-path="{{ $seo['path'] }}">
        <div class="seo-fallback__shell">
            <nav class="seo-fallback__nav" aria-label="Основная навигация">
                <a href="/">Главная</a>
                <a href="/subject">Олимпиады</a>
                <a href="/about">О платформе</a>
                <a href="/rules">Правила</a>
                <a href="/leaderboard">Рейтинг</a>
                <a href="/help-desk">Help Desk</a>
            </nav>

            <header class="seo-fallback__hero">
                @if(!empty($content['eyebrow']))
                    <p class="seo-fallback__eyebrow">{{ $content['eyebrow'] }}</p>
                @endif

                @if(!empty($content['title']))
                    <h1>{{ $content['title'] }}</h1>
                @endif

                @if(!empty($content['intro']))
                    <p class="seo-fallback__intro">{{ $content['intro'] }}</p>
                @endif

                @if(!empty($content['cta']))
                    <a class="seo-fallback__cta" href="{{ $content['cta']['href'] }}">{{ $content['cta']['label'] }}</a>
                @endif
            </header>

            @if(!empty($seo['subject']))
                <section class="seo-fallback__card-grid" aria-label="Информация по предмету">
                    <article class="seo-fallback__card">
                        <h2>Классы</h2>
                        <p>{{ !empty($seo['subject']['grade_ranges']) ? implode(', ', $seo['subject']['grade_ranges']) : '3-11 классы' }}</p>
                    </article>
                    <article class="seo-fallback__card">
                        <h2>Формат</h2>
                        <p>Онлайн-олимпиада с регистрацией и оплатой через Kaspi.</p>
                    </article>
                    <article class="seo-fallback__card">
                        <h2>Длительность</h2>
                        <p>{{ $seo['subject']['time_limit'] ? $seo['subject']['time_limit'] . ' минут' : 'Указана в опубликованной олимпиаде' }}</p>
                    </article>
                </section>
            @endif

            @if(!empty($content['sections']))
                <section class="seo-fallback__sections" aria-label="Основная информация">
                    @foreach($content['sections'] as $section)
                        <article class="seo-fallback__section">
                            <h2>{{ $section['title'] }}</h2>
                            <p>{{ $section['body'] }}</p>
                        </article>
                    @endforeach
                </section>
            @endif

            @if(!empty($content['faq']))
                <section class="seo-fallback__faq" aria-label="Частые вопросы">
                    <h2>Частые вопросы</h2>
                    <div class="seo-fallback__faq-list">
                        @foreach($content['faq'] as $item)
                            <article class="seo-fallback__faq-item">
                                <h3>{{ $item['question'] }}</h3>
                                <p>{{ $item['answer'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($seo['path'] === '/subject')
                @php($subjects = \App\Models\Subject::query()->whereHas('quizzes', fn ($query) => $query->where('is_published', true))->orderBy('name')->get())
                @if($subjects->isNotEmpty())
                    <section class="seo-fallback__subjects" aria-label="Каталог предметов">
                        <h2>Доступные предметы</h2>
                        <div class="seo-fallback__subject-list">
                            @foreach($subjects as $subject)
                                <article class="seo-fallback__subject-item">
                                    <h3><a href="/subjects/{{ $subject->public_id }}">{{ $subject->name }}</a></h3>
                                    <p>{{ $subject->description ?: 'Онлайн-олимпиада для школьников по предмету ' . $subject->name }}</p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endif
        </div>
    </main>
@endif
