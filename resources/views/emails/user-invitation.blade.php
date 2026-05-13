<!DOCTYPE html>
<html>
<head>
    <style>
        .button {
            background-color: #2563eb;
            color: #ffffff !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }
    </style>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <h2>Hello!</h2>
    
    <p>{{ $invitation->invited_by_name ?? 'Administrator' }} has invited you to join the TDGCM Appointments system.</p>

    <div style="margin: 30px 0; text-align: center;">
        <a href="{{ $acceptUrl }}" class="button">Create Account & Set Up 2FA</a>
    </div>

    <p>This invitation link will expire on <strong>{{ $invitation->expires_at->format('F j, Y \a\t g:i A') }}</strong>.</p>
    
    <p>If you did not expect to receive an invitation, you may safely discard this email.</p>
    
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="font-size: 0.8em; color: #777;">Regards,<br>{{ config('app.name') }}</p>
</body>
</html>