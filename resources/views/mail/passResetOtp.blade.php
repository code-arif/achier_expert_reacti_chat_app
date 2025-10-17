<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - Ownhustle</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #f5f5f5;
        }

        .header {
            background-color: #010c0f;
            padding: 15px;
            text-align: center;
            color: white;
            border-radius: 8px 8px 0 0;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
        }

        .content {
            padding: 20px;
            font-size: 16px;
            color: #141414;
            text-align: center;
        }

        .verification-code {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
            display: inline-block;
        }

        .button {
            display: inline-block;
            background-color: #010c0f;
            color: #ffffff;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #f3ac4e;
        }

        .footer {
            background-color: #f5f5f5;
            padding: 15px;
            text-align: center;
            font-size: 14px;
            border-radius: 0 0 8px 8px;
        }

        /* Responsive Design */
        @media screen and (max-width: 480px) {
            .container {
                width: 90%;
                padding: 15px;
            }

            .header h1 {
                font-size: 20px;
            }

            .content {
                font-size: 14px;
            }

            .verification-code {
                font-size: 16px;
                padding: 12px;
            }

            .button {
                font-size: 14px;
                padding: 10px 18px;
            }

            .footer {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reset Your Password</h1>
        </div>
        <div class="content">
            <p>Hi, {{ $user->name ?? 'User' }}</p>
            <p>You recently requested to reset your password for {{ config('app.name') }}. Use the code below to reset your password:</p>
            <div class="verification-code">
                {{ $otp }}
            </div>
            <p>This code is valid for only 5 minutes.</p>
            <p>Regards,<br>{{ config('app.name') }} Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
