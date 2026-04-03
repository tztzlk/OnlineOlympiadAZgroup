<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#f7f2e7;font-family:'Segoe UI',Arial,sans-serif;color:#1a1712;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 12px;background:#f7f2e7;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#fff9ee;border:1px solid #eadcc0;border-radius:24px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 32px;background:linear-gradient(135deg,#fff9ee 0%,#f5e6bd 100%);border-bottom:1px solid #eadcc0;">
                            <div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;font-weight:700;color:#8f6f2a;">Online Olympiad</div>
                            <h1 style="margin:12px 0 0;font-size:30px;line-height:1.15;color:#1a1712;">{{ $title }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;">
                            <p style="margin:0 0 14px;font-size:16px;color:#5d5548;">Здравствуйте, {{ $recipientName }}.</p>
                            <p style="margin:0 0 18px;font-size:16px;line-height:1.65;color:#1a1712;">{{ $body }}</p>

                            @if(!empty($context))
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px;border-collapse:separate;border-spacing:0 10px;">
                                    @foreach($context as $label => $value)
                                        <tr>
                                            <td style="width:180px;font-size:13px;color:#8a7b62;vertical-align:top;">{{ $label }}</td>
                                            <td style="font-size:14px;color:#1a1712;font-weight:600;">{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            @if($actionUrl)
                                <div style="margin:0 0 20px;">
                                    <a href="{{ $actionUrl }}" style="display:inline-block;padding:14px 20px;background:#c9ab63;color:#1a1712;text-decoration:none;border-radius:14px;font-weight:700;">{{ $actionLabel }}</a>
                                </div>
                            @endif

                            <p style="margin:0;font-size:14px;line-height:1.6;color:#5d5548;">
                                Если у вас появились вопросы, ответьте на это письмо или свяжитесь с поддержкой через форму на сайте.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
