<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #d0c5c5;
            margin: 0;
            padding: 0;
            color: #ffffff;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #1e1e1e;
            border-radius: 12px;
            overflow: hidden;
        }

        .logo-section {
            padding: 30px 0;
            text-align: center;
            background-color: #1e1e1e;
        }

        .logo {
            font-weight: bold;
            font-size: 24px;
            color: #ffffff;
        }

        .content {
            padding: 20px 30px;
            color: #e0e0e0;
        }

        h1 {
            font-size: 22px;
            color: #ffffff;
            text-align: center;
            margin-bottom: 25px;
        }

        p {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 15px;
            color: #cccccc;
        }

        .verification-box {
            background-color: #262626;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }

        .code-label {
            font-size: 14px;
            color: #999999;
            margin-bottom: 10px;
        }

        .verification-code {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 4px;
            color: #ffffff;
        }

        .verification-note {
            font-size: 12px;
            color: #777777;
            text-align: center;
            margin-top: 15px;
        }

        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .verify-button {
            display: inline-block;
            background-color: #FF6B00;
            color: #ffffff;
            padding: 14px 40px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
        }

        .social-links {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid #333333;
            margin-top: 30px;
        }

        .social-icon {
            display: inline-block;
            width: 32px;
            height: 32px;
            background-color: #333333;
            border-radius: 50%;
            margin: 0 5px;
            text-align: center;
            line-height: 32px;
        }

        .footer {
            background-color: #1e1e1e;
            padding: 20px;
            text-align: center;
            color: #777777;
            font-size: 12px;
            border-top: 1px solid #333333;
        }

        /* Responsive Design */
        @media screen and (max-width: 480px) {
            .container {
                width: 90%;
                margin: 10px auto;
            }

            .content {
                padding: 15px;
            }

            .verification-code {
                font-size: 20px;
            }

            .verify-button {
                padding: 12px 30px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo-section">
            <div class="logo">LOGO</div>
            <div style="font-size: 12px; color: #777777; margin-top: 5px;">{{ config('app.name') }}</div>
        </div>

        <div class="content">
            <h1>Verify your OTP</h1>

            <div class="verification-box">
                <p>Dear, {{ $fullName }}</p> </br>
                <div class="code-label">Your OTP code</div>
                <div class="verification-code">{{ $otp }}</div>
                <div class="verification-note">This code will expire in 5 minutes</div>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
