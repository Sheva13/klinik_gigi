<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 480px; margin: auto; background: white; border-radius: 8px; padding: 20px; box-shadow: 0 0 12px rgba(0,0,0,0.05);">
        <tr>
            <td align="center">
                <h2 style="margin-bottom: 10px; color: #333;">Verification Code</h2>
                <p style="color: #666; margin-top: 0;">
                    Use the code below to verify your account.
                </p>

                <div style="font-size: 32px; font-weight: bold; margin: 20px 0; letter-spacing: 4px; color: #1a73e8;">
                    {{ $pin }}
                </div>

                <p style="color: #444; font-size: 14px;">
                    This code will expire in <strong>{{ $minutes }} minutes</strong>.
                </p>

                <p style="color: #999; font-size: 12px; margin-top: 30px;">
                    If you did not request this, please ignore this email.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
