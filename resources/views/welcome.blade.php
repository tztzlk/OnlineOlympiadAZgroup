@php($seo = $seo ?? \App\Support\PublicSeo::page(request()->path()))
@php($seoDefaults = $seoDefaults ?? \App\Support\PublicSeo::defaultClientSeo())
@php($seoRouteMap = $seoRouteMap ?? \App\Support\PublicSeo::clientRouteMap())
<!DOCTYPE html>
<html lang="{{ $seo['lang'] ?? 'ru-KZ' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="robots" content="{{ $seo['robots'] }}">
    <meta property="og:type" content="{{ $seo['type'] }}">
    <meta property="og:site_name" content="{{ config('seo.site_name') }}">
    <meta property="og:locale" content="{{ $seo['locale'] }}">
    <meta property="og:title" content="{{ $seo['title'] }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:url" content="{{ $seo['canonical'] }}">
    <meta property="og:image" content="{{ $seo['image'] }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] }}">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    <meta name="twitter:image" content="{{ $seo['image'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="/theme-init.js"></script>
    <script nonce="{{ $cspNonce ?? '' }}">
        window.__SEO_DEFAULTS__ = @json($seoDefaults);
        window.__SEO_ROUTE_MAP__ = @json($seoRouteMap);
        window.__SEO_SERVER__ = @json($seo);
    </script>
    <style>
        html.app-mounted #seo-fallback { display: none; }
        .seo-fallback {
            display: block;
            background: #fcf8ef;
            color: #261b0f;
            padding: 120px 20px 48px;
        }
        .seo-fallback__shell {
            width: min(1100px, 100%);
            margin: 0 auto;
            display: grid;
            gap: 28px;
        }
        .seo-fallback__nav {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }
        .seo-fallback__nav a,
        .seo-fallback__cta,
        .seo-fallback__subject-item a {
            color: #1f5f38;
            text-decoration: none;
            font-weight: 700;
        }
        .seo-fallback__hero,
        .seo-fallback__section,
        .seo-fallback__faq-item,
        .seo-fallback__card,
        .seo-fallback__subject-item {
            background: #fffaf1;
            border: 1px solid rgba(201, 171, 99, 0.25);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 12px 28px rgba(84, 65, 26, 0.08);
        }
        .seo-fallback__eyebrow {
            margin: 0 0 12px;
            text-transform: uppercase;
            letter-spacing: .1em;
            font-size: 12px;
            font-weight: 800;
            color: #6c5a2f;
        }
        .seo-fallback__hero h1,
        .seo-fallback__section h2,
        .seo-fallback__faq h2,
        .seo-fallback__subjects h2 {
            margin: 0;
        }
        .seo-fallback__intro,
        .seo-fallback__section p,
        .seo-fallback__faq-item p,
        .seo-fallback__subject-item p,
        .seo-fallback__card p {
            margin: 12px 0 0;
            line-height: 1.65;
            color: #5b4a30;
        }
        .seo-fallback__cta {
            display: inline-flex;
            margin-top: 18px;
        }
        .seo-fallback__card-grid,
        .seo-fallback__faq-list,
        .seo-fallback__subject-list,
        .seo-fallback__sections {
            display: grid;
            gap: 16px;
        }
        .seo-fallback__card-grid,
        .seo-fallback__subject-list {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }
        .seo-fallback__faq-item h3,
        .seo-fallback__subject-item h3 {
            margin: 0;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @unless(app()->environment('testing'))
        @vite('resources/js/app.js')
    @endunless
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    @foreach(($seo['schemas'] ?? []) as $schema)
        <script type="application/ld+json" data-dynamic-seo-schema="true">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endforeach
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <div id="app"></div>
    @include('partials.seo-fallback', ['seo' => $seo])
</body>
</html>
