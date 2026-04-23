<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Certificate</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #2c2416; background: #fffaf2; }
        .sheet { width: 100%; height: 100%; padding: 34px; box-sizing: border-box; background: radial-gradient(circle at top left, rgba(214, 181, 100, 0.24), transparent 28%), linear-gradient(135deg, #fffaf2 0%, #f6ebcb 100%); }
        .frame { height: 100%; border: 4px solid #b48d35; border-radius: 26px; padding: 26px; box-sizing: border-box; }
        .inner { height: 100%; border: 2px dashed #dfc27f; border-radius: 20px; padding: 54px 64px; box-sizing: border-box; text-align: center; background: rgba(255, 255, 255, 0.55); }
        .brand { margin: 0; font-size: 20px; letter-spacing: 0.4em; color: #8a6b25; }
        .title { margin: 26px 0 10px; font-size: 48px; font-weight: 700; color: #6f5220; }
        .accent { width: 260px; height: 6px; margin: 0 auto 26px; border-radius: 999px; background: linear-gradient(90deg, #b48d35 0%, #d6b564 100%); }
        .lead { margin: 0 0 34px; font-size: 19px; color: #6f5c3a; }
        .name { margin: 0 0 12px; font-size: 42px; font-weight: 700; color: #111827; }
        .location { margin: 0 0 30px; font-size: 18px; color: #4b5563; }
        .meta { width: 100%; border-collapse: separate; border-spacing: 14px; margin-bottom: 32px; }
        .meta td { width: 50%; padding: 14px 16px; border: 1px solid #ead9aa; border-radius: 14px; background: rgba(255, 255, 255, 0.72); vertical-align: top; }
        .meta-label { display: block; margin-bottom: 6px; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: #8a6b25; }
        .meta-value { font-size: 16px; color: #1f2937; font-weight: 700; }
        .footnote, .result-id { font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="frame">
            <div class="inner">
                <p class="brand">EURICA</p>
                <h1 class="title">СЕРТИФИКАТ</h1>
                <div class="accent"></div>
                <p class="lead">Подтверждает участие и получение результата</p>
                <h2 class="name">{{ $studentName }}</h2>
                <p class="location">{{ $school }}, {{ $city }}</p>
                <table class="meta">
                    <tr>
                        <td><span class="meta-label">Предмет</span><span class="meta-value">{{ $subject }}</span></td>
                        <td><span class="meta-label">Категория</span><span class="meta-value">{{ $category }}</span></td>
                    </tr>
                    <tr>
                        <td><span class="meta-label">Олимпиада</span><span class="meta-value">{{ $quizTitle }}</span></td>
                        <td><span class="meta-label">Результат</span><span class="meta-value">{{ $scoreLine }}</span></td>
                    </tr>
                </table>
                <p class="footnote">Сертификат сгенерирован автоматически системой Eurica.</p>
                <p class="result-id">ID результата #{{ $resultId }} · {{ $date }}</p>
            </div>
        </div>
    </div>
</body>
</html>
