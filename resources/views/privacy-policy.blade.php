<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Privacy Policy - Reacti</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f7f8fa;
            font-family: "Inter", sans-serif;
        }

        .policy-wrapper {
            max-width: 850px;
            margin: 40px auto;
            padding: 30px 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }

        .policy-content {
            font-size: 16px;
            line-height: 1.75;
            color: #333;
        }

        h1,
        h2,
        h3,
        h4 {
            margin-top: 25px;
            font-weight: 600;
        }

        p {
            margin-bottom: 14px;
        }

        @media print {
            body {
                background: white;
            }

            .policy-wrapper {
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="policy-wrapper">

        <div class="text-center mb-4">
            <h2 class="fw-bold">Privacy Policy</h2>
            <p class="text-muted">Last Updated: <strong>November 28, 2024</strong></p>
        </div>

        <hr>

        <div class="policy-content">
            {!! $content !!}
        </div>

        <hr>

        <div class="text-center mt-4">
            <p class="text-muted small mb-1">Questions or concerns?</p>
            <p class="mb-0">
                <a href="mailto:support@reacti-app.com">support@reacti-app.com</a>
            </p>
        </div>

    </div>

</body>

</html>
