<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Budlist code</title>
</head>
<body style="margin:0;padding:0;background:#0f1115;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0f1115;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:460px;background:#191c24;border:1px solid #2a2f3a;border-radius:18px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 32px 8px;">
                            <span style="display:inline-block;width:34px;height:34px;line-height:34px;text-align:center;border-radius:10px;background:linear-gradient(135deg,#7c5cff,#3b82f6);color:#fff;font-weight:700;font-size:18px;">&#8369;</span>
                            <span style="color:#f4f5f7;font-size:20px;font-weight:700;vertical-align:middle;margin-left:10px;">Budlist</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 32px 0;color:#c8ccd6;font-size:15px;line-height:1.6;">
                            @if ($name)
                                Hi {{ $name }},
                            @else
                                Hi,
                            @endif
                            <p style="margin:12px 0 0;">Use this code to verify your email and finish signing in:</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;">
                            <div style="background:#0f1115;border:1px solid #2a2f3a;border-radius:14px;padding:18px;text-align:center;">
                                <span style="color:#f4f5f7;font-size:34px;font-weight:700;letter-spacing:10px;">{{ $code }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 28px;color:#8b909c;font-size:13px;line-height:1.6;">
                            This code expires in 10 minutes. If you didn&rsquo;t request it, you can safely ignore this email.
                        </td>
                    </tr>
                </table>
                <p style="color:#5b606b;font-size:12px;margin:18px 0 0;">Budlist &middot; personal budget tracker</p>
            </td>
        </tr>
    </table>
</body>
</html>
