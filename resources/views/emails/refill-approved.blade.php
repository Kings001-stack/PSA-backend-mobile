<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refill Request Approved</title>
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
            color: #16a34a;
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
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            padding: 16px;
            margin: 16px 0;
            border-radius: 8px;
        }
        .info-label {
            font-weight: 600;
            color: #166534;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 16px;
            color: #15803d;
            margin-top: 4px;
        }
        .footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 16px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">✅</div>
            <h1>Refill Request Approved</h1>
            <p class="subtitle">Your medication refill has been approved by our pharmacist</p>
        </div>

        <div class="content">
            <p>Hello {{ $userName }},</p>
            
            <p>Great news! Your refill request has been approved by our licensed pharmacist.</p>

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

            <p><strong>What happens next?</strong></p>
            <ul>
                <li>Our pharmacy team will prepare your medication</li>
                <li>You'll receive another notification when it's ready for pickup</li>
                <li>Please bring a valid ID when collecting your medication</li>
            </ul>

            <p>If you have any questions, please don't hesitate to contact us.</p>
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
