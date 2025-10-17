<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Email Verification</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f7fa;
            line-height: 1.6;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333333;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .message {
            font-size: 15px;
            color: #555555;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .otp-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
        }
        .otp-label {
            color: #ffffff;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        .expiry-note {
            color: rgba(255, 255, 255, 0.9);
            font-size: 13px;
            margin-top: 15px;
        }
        .security-notice {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .security-notice p {
            margin: 0;
            font-size: 14px;
            color: #856404;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .email-footer p {
            margin: 5px 0;
            font-size: 13px;
            color: #6c757d;
        }
        .company-name {
            font-weight: 600;
            color: #495057;
        }
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }
            .otp-code {
                font-size: 28px;
                letter-spacing: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <h1>{{ $appName ?? config('app.name') }}</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <p class="greeting">Hello {{ $userName }},</p>

            <p class="message">
                Thank you for signing up! To complete your registration, please use the verification code below.
                This code is valid for <strong>{{ $expiryMinutes }} minutes</strong>.
            </p>

            <!-- OTP Box -->
            <div class="otp-container">
                <div class="otp-label">Your Verification Code</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="expiry-note">⏱ Expires in {{ $expiryMinutes }} minutes</div>
            </div>

            <!-- Security Notice -->
            <div class="security-notice">
                <p><strong>⚠️ Security Notice:</strong> Never share this code with anyone. Our team will never ask for your verification code.</p>
            </div>

            <p class="message">
                If you didn't request this code, please ignore this email or contact our support team if you have concerns about your account security.
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p class="company-name">{{ $appName ?? config('app.name') }}</p>
            <p>This is an automated message, please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} {{ $appName ?? config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
