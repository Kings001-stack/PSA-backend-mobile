<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medication Ready for Pickup</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .container {
            background-color: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        h1 {
            color: #2563eb;
            font-size: 24px;
            margin: 0 0 8px 0;
        }
        .subtitle {
            color: #64748b;
            font-size: 14px;
        }
        .content {
            margin: 24px 0;
        }
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 16px;
            margin: 16px 0;
            border-radius: 8px;
        }
        .info-label {
            font-weight: 600;
            color: #1e40af;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 16px;
            color: #1e3a8a;
            margin-top: 4px;
        }
        .highlight-box {
            background-color: #fef3c7;
            border: 2px solid #fbbf24;
            padding: 20px;
            border-radius: 12px;
            margin: 24px 0;
            text-align: center;
        }
        .highlight-box h2 {
            color: #92400e;
            font-size: 18px;
            margin: 0 0 8px 0;
        }
        .highlight-box p {
            color: #78350f;
            margin: 0;
            font-size: 14px;
        }
        .footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">📦</div>
            <h1>Your Medication is Ready!</h1>
            <p class="subtitle">You can now collect your prescription</p>
        </div>

        <div class="content">
            <p>Hello {{ $userName }},</p>
            
            <p>Your medication is now ready for pickup at our pharmacy.</p>

            <div class="info-box">
                <div class="info-label">Medication</div>
                <div class="info-value">{{ $medicationName }}</div>
            </div>

            <div class="info-box">
                <div class="info-label">Quantity</div>
                <div class="info-value">{{ $quantity }} units</div>
            </div>

            <div class="info-box">
                <div class="info-label">Reference Number</div>
                <div class="info-value">#{{ $refillId }}</div>
            </div>

            <div class="highlight-box">
                <h2>⏰ Pickup Hours</h2>
                <p>Monday - Friday: 8:00 AM - 8:00 PM</p>
                <p>Saturday: 9:00 AM - 6:00 PM</p>
                <p>Sunday: 10:00 AM - 4:00 PM</p>
            </div>

            <p><strong>What to bring:</strong></p>
            <ul>
                <li>Valid government-issued ID</li>
                <li>Reference number: #{{ $refillId }}</li>
                <li>Payment method (if applicable)</li>
            </ul>

            <p><strong>Important:</strong> Please collect your medication within 7 days. After this period, we may need to return it to stock.</p>

            <p>Thank you for choosing PrimeChem Pharmacy!</p>
        </div>

        <div class="footer">
            <p><strong>PrimeChem Pharmacy</strong></p>
            <p>Your trusted healthcare partner</p>
            <p style="font-size: 12px; color: #94a3b8; margin-top: 16px;">
                This is an automated notification. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
