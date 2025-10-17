<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Report Message</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 700px;
            margin: 30px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .email-header {
            text-align: center;
            background-color: #1e90ff;
            color: #fff;
            padding: 15px;
            border-radius: 10px 10px 0 0;
        }

        .email-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .section {
            margin-bottom: 20px;
        }

        .section h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }

        .report-details {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .report-details p {
            margin: 8px 0;
            font-size: 16px;
        }

        .report-details .label {
            font-weight: bold;
            color: #333;
            display: inline-block;
            width: 120px;
        }

        .report-details .value {
            color: #555;
        }

        .footer {
            text-align: center;
            font-size: 14px;
            color: #777;
            margin-top: 30px;
        }

        .footer a {
            color: #1e90ff;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="email-container">
        <div class="email-header">
            <h2>New Report Message</h2>
        </div>

        <div class="section">
            <h3>Report Details:</h3>
            <div class="report-details">
                <p><span class="label">First Name:</span><span class="value"> {{ $report->first_name }} </span></p>
                <p><span class="label">Last Name:</span><span class="value"> {{ $report->last_name ?? 'N/A' }} </span>
                </p>
                <p><span class="label">Email:</span><span class="value"> {{ $report->email }} </span></p>
                <p><span class="label">Phone Number:</span><span class="value"> {{ $report->number ?? 'N/A' }} </span>
                </p>
                <p><span class="label">Subject:</span><span class="value"> {{ $report->subject }} </span></p>
                <p><span class="label">Message:</span><span class="value"> {{ $report->message }} </span></p>
            </div>
        </div>

        <div class="footer">
            <p>For more details, please contact us at
                <a href="mailto:admin@example.com">

                    

                </a>
            </p>
        </div>
    </div>

</body>

</html>
