<?php

namespace App\Support;

use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublicSeo
{
    public static function page(string $path = '/'): array
    {
        $normalizedPath = self::normalizePath($path);

        return match ($normalizedPath) {
            '/' => self::buildPage(
                path: '/',
                title: 'Онлайн-олимпиада для школьников в Казахстане',
                description: 'Online Olympiad — онлайн-олимпиада для школьников 3-11 классов в Казахстане. Выберите предмет, зарегистрируйтесь, оплатите участие и получите результат онлайн.',
                type: 'website',
                content: [
                    'eyebrow' => 'Онлайн-олимпиада',
                    'title' => 'Онлайн-олимпиада для школьников 3-11 классов',
                    'intro' => 'Платформа помогает родителям быстро зарегистрировать ребёнка на олимпиаду, выбрать предмет, оплатить участие и получить результат онлайн без лишней бюрократии.',
                    'cta' => ['href' => '/subject', 'label' => 'Выбрать олимпиаду'],
                    'sections' => [
                        [
                            'title' => 'Что получает родитель',
                            'body' => 'Один личный кабинет для регистрации, оплаты, просмотра результатов и проверки сертификатов. Участие подходит для школьников 3-11 классов и проходит полностью онлайн.',
                        ],
                        [
                            'title' => 'Как проходит участие',
                            'body' => 'Вы выбираете предмет, указываете данные участника, оплачиваете олимпиаду через Kaspi и после подтверждения оплаты ребёнок получает доступ к заданиям.',
                        ],
                        [
                            'title' => 'Почему это удобно',
                            'body' => 'Платформа объединяет регистрацию, оплату, рейтинг, сертификаты и поддержку. Это снижает путь до регистрации и помогает родителям быстрее принять решение.',
                        ],
                    ],
                    'faq' => [
                        [
                            'question' => 'Для каких классов подходит онлайн-олимпиада?',
                            'answer' => 'На платформе участвуют школьники 3-11 классов. Конкретные диапазоны классов зависят от опубликованных олимпиад по предметам.',
                        ],
                        [
                            'question' => 'Как зарегистрироваться на олимпиаду?',
                            'answer' => 'Перейдите в каталог олимпиад, выберите предмет, заполните данные участника, оплатите участие и дождитесь подтверждения платежа.',
                        ],
                        [
                            'question' => 'Когда виден результат?',
                            'answer' => 'Результат становится доступен сразу после завершения олимпиады, а сертификат можно проверить по ID на отдельной странице.',
                        ],
                    ],
                ],
            ),
            '/subject' => self::buildPage(
                path: '/subject',
                title: 'Каталог онлайн-олимпиад по предметам',
                description: 'Выберите онлайн-олимпиаду по предмету, посмотрите доступные направления для школьников и перейдите к регистрации и оплате участия.',
                content: [
                    'eyebrow' => 'Каталог олимпиад',
                    'title' => 'Онлайн-олимпиады по предметам для школьников',
                    'intro' => 'На этой странице родители выбирают предмет, знакомятся с форматом участия и переходят к регистрации ребёнка на онлайн-олимпиаду.',
                    'cta' => ['href' => '/help-desk', 'label' => 'Нужна помощь?'],
                    'sections' => [
                        [
                            'title' => 'Как выбрать предмет',
                            'body' => 'Сравните доступные олимпиады, посмотрите описание предмета и перейдите на отдельную страницу предмета, чтобы изучить классы, формат и условия участия.',
                        ],
                        [
                            'title' => 'Как проходит оплата',
                            'body' => 'После оформления участия система открывает ссылку на оплату Kaspi. После подтверждения платежа участник получает доступ к олимпиаде.',
                        ],
                    ],
                    'faq' => [
                        [
                            'question' => 'Можно ли выбрать предмет заранее и зарегистрироваться позже?',
                            'answer' => 'Да, сначала можно изучить доступные олимпиады по предметам, а затем вернуться к оформлению участия в удобный момент.',
                        ],
                        [
                            'question' => 'Где посмотреть классы для предмета?',
                            'answer' => 'Для каждого опубликованного предмета доступна отдельная страница с диапазонами классов и форматом олимпиады.',
                        ],
                    ],
                ],
            ),
            '/about' => self::buildPage(
                path: '/about',
                title: 'О платформе Online Olympiad',
                description: 'Узнайте, как работает платформа Online Olympiad для школьников, родителей и образовательных организаций в Казахстане.',
                content: [
                    'eyebrow' => 'О платформе',
                    'title' => 'Платформа Online Olympiad для школьников и родителей',
                    'intro' => 'Мы создаём удобную и понятную среду, в которой родители могут зарегистрировать ребёнка на олимпиаду, отслеживать оплату, результаты и сертификаты.',
                    'cta' => ['href' => '/subject', 'label' => 'Перейти к олимпиадам'],
                    'sections' => [
                        [
                            'title' => 'Для кого платформа',
                            'body' => 'Для родителей школьников, для самих учеников и для образовательных организаций, которым важны прозрачность, удобство и быстрый доступ к результатам.',
                        ],
                        [
                            'title' => 'Что уже доступно',
                            'body' => 'Онлайн-олимпиады по предметам, личный кабинет, рейтинг, проверка сертификатов, тренировки и служба поддержки.',
                        ],
                    ],
                ],
            ),
            '/rules' => self::buildPage(
                path: '/rules',
                title: 'Правила участия в онлайн-олимпиаде',
                description: 'Правила проведения онлайн-олимпиад: честное участие, время выполнения, технические требования и причины аннулирования результата.',
                content: [
                    'eyebrow' => 'Правила',
                    'title' => 'Правила участия в онлайн-олимпиадах',
                    'intro' => 'Перед стартом родителям и участникам важно понять условия прохождения, ограничения и требования к честному выполнению заданий.',
                    'cta' => ['href' => '/subject', 'label' => 'Выбрать олимпиаду'],
                    'sections' => [
                        [
                            'title' => 'Основные требования',
                            'body' => 'Каждая олимпиада проходит индивидуально, во временном лимите и без использования посторонней помощи.',
                        ],
                        [
                            'title' => 'Техническая сторона',
                            'body' => 'При технических вопросах участник или родитель могут обратиться в Help Desk. Нарушение правил может привести к аннулированию результата.',
                        ],
                    ],
                ],
            ),
            '/leaderboard' => self::buildPage(
                path: '/leaderboard',
                title: 'Рейтинг участников онлайн-олимпиад',
                description: 'Посмотрите рейтинг участников, лучшие результаты по предметам и достижения школьников на платформе Online Olympiad.',
                content: [
                    'eyebrow' => 'Рейтинг',
                    'title' => 'Рейтинг участников и результаты онлайн-олимпиад',
                    'intro' => 'Публичный рейтинг помогает родителям и школам видеть реальные результаты участников и активность платформы по предметам.',
                    'cta' => ['href' => '/certificate-check', 'label' => 'Проверить сертификат'],
                    'sections' => [
                        [
                            'title' => 'Зачем нужен рейтинг',
                            'body' => 'Рейтинг показывает лучшие результаты по предметам, помогает оценить активность участников и повышает доверие к платформе.',
                        ],
                    ],
                ],
            ),
            '/certificate-check' => self::buildPage(
                path: '/certificate-check',
                title: 'Проверка сертификата онлайн-олимпиады',
                description: 'Проверьте сертификат или результат участника по ID и подтвердите, что запись существует в системе Online Olympiad.',
                content: [
                    'eyebrow' => 'Проверка сертификата',
                    'title' => 'Проверка сертификата по ID результата',
                    'intro' => 'Эта страница помогает родителям, школам и партнёрам быстро подтвердить подлинность сертификата и результата участника.',
                    'sections' => [
                        [
                            'title' => 'Как работает проверка',
                            'body' => 'Введите ID результата или сертификата и система покажет основные сведения по участнику, предмету и итоговому результату.',
                        ],
                    ],
                ],
            ),
            '/help-desk' => self::buildPage(
                path: '/help-desk',
                title: 'Help Desk: помощь по регистрации, оплате и входу',
                description: 'Служба поддержки Online Olympiad: помощь по регистрации, оплате, входу, доступу к олимпиаде и техническим вопросам.',
                content: [
                    'eyebrow' => 'Поддержка',
                    'title' => 'Помощь по регистрации, оплате и участию',
                    'intro' => 'Если у родителя возникли вопросы по регистрации, подтверждению оплаты, входу в кабинет или прохождению теста, команда поддержки поможет разобраться.',
                    'sections' => [
                        [
                            'title' => 'С чем помогает Help Desk',
                            'body' => 'Поддержка отвечает по статусу участия, оплате через Kaspi, доступу к олимпиаде, сертификатам и техническим ошибкам.',
                        ],
                        [
                            'title' => 'Как быстрее получить помощь',
                            'body' => 'Опишите тему обращения, укажите контактные данные и кратко сформулируйте проблему. Чем точнее запрос, тем быстрее команда сможет помочь.',
                        ],
                    ],
                    'faq' => [
                        [
                            'question' => 'Куда писать, если оплата не подтвердилась?',
                            'answer' => 'Используйте форму Help Desk и укажите тему, связанную с оплатой. Команда проверит статус платежа и подскажет дальнейшие шаги.',
                        ],
                        [
                            'question' => 'Можно ли обратиться, если не получается войти?',
                            'answer' => 'Да, служба поддержки помогает при проблемах со входом, восстановлением пароля и доступом к личному кабинету.',
                        ],
                    ],
                ],
            ),
            default => self::buildPage(
                path: $normalizedPath,
                title: 'Online Olympiad',
                description: 'Online Olympiad — платформа онлайн-олимпиад для школьников, родителей и образовательных организаций.',
                content: [],
            ),
        };
    }

    public static function subjectPage(Subject $subject): array
    {
        $quiz = self::publishedQuiz($subject);
        $gradeRanges = self::gradeRanges($quiz);
        $gradeSummary = $gradeRanges !== [] ? implode(', ', $gradeRanges) : '3-11 классы';
        $timeLimit = $quiz?->time_limit;
        $durationText = $timeLimit ? "{$timeLimit} минут" : 'по таймеру олимпиады';
        $description = trim($subject->description ?: "Онлайн-олимпиада по предмету {$subject->name} для школьников в Казахстане.");
        $title = "{$subject->name}: онлайн-олимпиада для школьников";
        $metaDescription = "{$description} Классы: {$gradeSummary}. Формат участия — онлайн, оплата через Kaspi, результаты и сертификаты доступны на платформе.";

        $content = [
            'eyebrow' => 'Предмет',
            'title' => "Онлайн-олимпиада по предмету {$subject->name}",
            'intro' => $description,
            'cta' => ['href' => "/subject?subject={$subject->public_id}", 'label' => 'Оформить участие'],
            'sections' => [
                [
                    'title' => 'Для каких классов подходит',
                    'body' => "Опубликованные категории по предмету {$subject->name}: {$gradeSummary}. Если администратор расширит диапазон, страница автоматически появится в sitemap.",
                ],
                [
                    'title' => 'Формат участия',
                    'body' => "Олимпиада проходит онлайн. После регистрации родитель оплачивает участие через Kaspi, а после подтверждения платежа ребёнок получает доступ к заданиям. Ориентировочная длительность: {$durationText}.",
                ],
                [
                    'title' => 'Результаты и сертификаты',
                    'body' => 'После завершения олимпиады результат доступен в личном кабинете, а сертификат можно проверить на отдельной публичной странице по ID.',
                ],
            ],
            'faq' => [
                [
                    'question' => "Как зарегистрироваться на олимпиаду по предмету {$subject->name}?",
                    'answer' => 'Перейдите в каталог олимпиад, выберите предмет, заполните данные участника, оплатите участие и дождитесь подтверждения платежа.',
                ],
                [
                    'question' => 'Сколько длится олимпиада?',
                    'answer' => "Для опубликованной олимпиады ориентировочный таймер составляет {$durationText}.",
                ],
                [
                    'question' => 'Когда доступны результаты?',
                    'answer' => 'Результаты и сертификаты становятся доступны после завершения олимпиады и сохраняются в личном кабинете участника.',
                ],
            ],
        ];

        $page = self::buildPage(
            path: "/subjects/{$subject->public_id}",
            title: $title,
            description: $metaDescription,
            type: 'article',
            content: $content,
            image: $subject->image ?: self::assetUrl(config('seo.default_image')),
        );

        $page['subject'] = [
            'id' => $subject->public_id,
            'name' => $subject->name,
            'description' => $description,
            'image' => $subject->image,
            'start_date' => optional($subject->start_date)->toDateString(),
            'grade_ranges' => $gradeRanges,
            'time_limit' => $timeLimit,
            'payment_url' => config('services.kaspi.payment_url'),
            'registration_url' => "/subject?subject={$subject->public_id}",
            'faq' => $content['faq'],
        ];

        $page['schemas'][] = self::courseSchema($page);

        return $page;
    }

    public static function routeMap(): array
    {
        return collect([
            '/',
            '/subject',
            '/about',
            '/rules',
            '/leaderboard',
            '/certificate-check',
            '/help-desk',
        ])->mapWithKeys(fn (string $path) => [$path => self::page($path)])
            ->all();
    }

    public static function sitemapEntries(): array
    {
        $staticPages = collect(self::routeMap())
            ->filter(fn (array $page, string $path) => !Str::startsWith($path, ['/login', '/register', '/profile', '/quiz', '/training', '/admin']))
            ->map(fn (array $page) => [
                'loc' => $page['canonical'],
                'lastmod' => now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => $page['path'] === '/' ? '1.0' : '0.8',
            ]);

        $subjectPages = Subject::query()
            ->whereHas('quizzes', fn ($query) => $query->where('is_published', true))
            ->orderBy('name')
            ->get()
            ->map(fn (Subject $subject) => [
                'loc' => self::url("/subjects/{$subject->public_id}"),
                'lastmod' => optional($subject->updated_at ?: $subject->created_at)->toDateString() ?: now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ]);

        return $staticPages
            ->concat($subjectPages)
            ->values()
            ->all();
    }

    public static function fallbackViewData(array $seo): array
    {
        return [
            'seo' => $seo,
            'seoDefaults' => self::defaultClientSeo(),
            'seoRouteMap' => self::clientRouteMap(),
        ];
    }

    public static function defaultClientSeo(): array
    {
        $home = self::page('/');

        return [
            'title' => $home['title'],
            'description' => $home['description'],
            'canonical' => $home['canonical'],
            'image' => $home['image'],
            'robots' => $home['robots'],
            'type' => $home['type'],
            'siteName' => config('seo.site_name'),
            'locale' => config('seo.default_locale'),
        ];
    }

    public static function clientRouteMap(): array
    {
        return collect(self::routeMap())
            ->map(fn (array $page) => [
                'title' => $page['title'],
                'description' => $page['description'],
                'canonical' => $page['canonical'],
                'image' => $page['image'],
                'robots' => $page['robots'],
                'type' => $page['type'],
                'schemas' => $page['schemas'],
            ])
            ->all();
    }

    protected static function buildPage(
        string $path,
        string $title,
        string $description,
        string $type = 'website',
        array $content = [],
        ?string $image = null,
    ): array {
        $canonical = self::url($path);
        $imageUrl = self::resolveImage($image);

        $page = [
            'path' => self::normalizePath($path),
            'lang' => config('seo.default_lang', 'ru-KZ'),
            'locale' => config('seo.default_locale', 'ru_KZ'),
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'image' => $imageUrl,
            'robots' => 'index,follow,max-image-preview:large',
            'type' => $type,
            'content' => $content,
            'breadcrumbs' => self::breadcrumbs(self::normalizePath($path), $content['title'] ?? $title),
            'schemas' => [],
        ];

        $page['schemas'] = [
            self::organizationSchema(),
            self::websiteSchema(),
            self::breadcrumbSchema($page['breadcrumbs']),
        ];

        if (!empty($content['faq'])) {
            $page['schemas'][] = self::faqSchema($content['faq']);
        }

        return $page;
    }

    protected static function normalizePath(string $path): string
    {
        $trimmed = trim($path);

        if ($trimmed === '' || $trimmed === '/') {
            return '/';
        }

        return '/' . trim($trimmed, '/');
    }

    protected static function url(string $path): string
    {
        return config('seo.site_url') . ($path === '/' ? '/' : self::normalizePath($path));
    }

    protected static function assetUrl(string $path): string
    {
        return config('seo.site_url') . '/' . ltrim($path, '/');
    }

    protected static function resolveImage(?string $image): string
    {
        if (!$image) {
            return self::assetUrl(config('seo.default_image'));
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        return self::assetUrl($image);
    }

    protected static function organizationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('seo.site_name'),
            'url' => config('seo.site_url'),
            'email' => config('seo.support_email'),
            'telephone' => config('seo.support_phone'),
        ];
    }

    protected static function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('seo.site_name'),
            'url' => config('seo.site_url'),
            'inLanguage' => config('seo.default_lang', 'ru-KZ'),
        ];
    }

    protected static function breadcrumbSchema(array $breadcrumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($breadcrumbs)->values()->map(
                fn (array $crumb, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $crumb['name'],
                    'item' => $crumb['url'],
                ]
            )->all(),
        ];
    }

    protected static function faqSchema(array $faq): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faq)->map(
                fn (array $item) => [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ]
            )->all(),
        ];
    }

    protected static function courseSchema(array $page): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $page['title'],
            'description' => $page['description'],
            'provider' => [
                '@type' => 'Organization',
                'name' => config('seo.site_name'),
                'url' => config('seo.site_url'),
            ],
        ];
    }

    protected static function breadcrumbs(string $path, string $currentTitle): array
    {
        $breadcrumbs = [
            ['name' => 'Главная', 'url' => self::url('/')],
        ];

        return match ($path) {
            '/' => $breadcrumbs,
            '/subject' => array_merge($breadcrumbs, [
                ['name' => 'Олимпиады', 'url' => self::url('/subject')],
            ]),
            '/about' => array_merge($breadcrumbs, [
                ['name' => 'О платформе', 'url' => self::url('/about')],
            ]),
            '/rules' => array_merge($breadcrumbs, [
                ['name' => 'Правила', 'url' => self::url('/rules')],
            ]),
            '/leaderboard' => array_merge($breadcrumbs, [
                ['name' => 'Рейтинг', 'url' => self::url('/leaderboard')],
            ]),
            '/certificate-check' => array_merge($breadcrumbs, [
                ['name' => 'Проверка сертификата', 'url' => self::url('/certificate-check')],
            ]),
            '/help-desk' => array_merge($breadcrumbs, [
                ['name' => 'Help Desk', 'url' => self::url('/help-desk')],
            ]),
            default => Str::startsWith($path, '/subjects/')
                ? array_merge($breadcrumbs, [
                    ['name' => 'Олимпиады', 'url' => self::url('/subject')],
                    ['name' => $currentTitle, 'url' => self::url($path)],
                ])
                : array_merge($breadcrumbs, [
                    ['name' => $currentTitle, 'url' => self::url($path)],
                ]),
        };
    }

    protected static function publishedQuiz(Subject $subject): ?Quiz
    {
        return $subject->quizzes()
            ->where('is_published', true)
            ->with('categories')
            ->orderByDesc('id')
            ->first();
    }

    protected static function gradeRanges(?Quiz $quiz): array
    {
        if (!$quiz) {
            return [];
        }

        return $quiz->categories
            ->map(function (QuizCategory $category) {
                if ($category->grade_from === null || $category->grade_to === null) {
                    return null;
                }

                return $category->grade_from === $category->grade_to
                    ? (string) $category->grade_from
                    : "{$category->grade_from}-{$category->grade_to}";
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
