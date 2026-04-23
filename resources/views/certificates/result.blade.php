<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Certificate</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #1a1a1a; background: #fafafa; }
        .sheet { width: 100%; height: 100%; padding: 34px; box-sizing: border-box; background: #fafafa; }
        .frame { height: 100%; border: 4px solid #F2C94C; border-radius: 26px; padding: 26px; box-sizing: border-box; }
        .inner { height: 100%; border: 1px solid rgba(242, 201, 76, 0.55); border-radius: 20px; padding: 54px 64px; box-sizing: border-box; text-align: center; background: #ffffff; }
        .brand { margin: 0; font-size: 20px; letter-spacing: 0.4em; color: #9b7a10; }
        .title { margin: 26px 0 10px; font-size: 48px; font-weight: 700; color: #1a1a1a; }
        .accent { width: 260px; height: 6px; margin: 0 auto 26px; border-radius: 999px; background: #F2C94C; }
        .lead { margin: 0 0 34px; font-size: 19px; color: #6f5c3a; }
        .name { margin: 0 0 12px; font-size: 42px; font-weight: 700; color: #111827; }
        .location { margin: 0 0 30px; font-size: 18px; color: #4b5563; }
        .meta { width: 100%; border-collapse: separate; border-spacing: 14px; margin-bottom: 32px; }
        .meta td { width: 50%; padding: 14px 16px; border: 1px solid rgba(242, 201, 76, 0.32); border-radius: 14px; background: #fafafa; vertical-align: top; }
        .meta-label { display: block; margin-bottom: 6px; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: #9b7a10; }
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
