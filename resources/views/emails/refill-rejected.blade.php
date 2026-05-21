<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refill Request Update</title>
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
            color: #dc2626;
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
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 16px;
            margin: 16px 0;
            border-radius: 8px;
        }
        .info-label {
            font-weight: 600;
            color: #991b1b;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 16px;
            color: #b91c1c;
            margin-top: 4px;
        }
        .reason-box {
            background-color: #fff7ed;
            border: 2px solid #fb923c;
            padding: 20px;
            border-radius: 12px;
            margin: 24px 0;
        }
        .reason-box h2 {
            color: #9a3412;
            font-size: 16px;
            margin: 0 0 12px 0;
        }
        .reason-box p {
            color: #7c2d12;
            margin: 0;
            font-size: 15px;
            line-height: 1.6;
        }
        .footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }
        .contact-box {
            background-color: #eff6ff;
            padding: 20px;
            border-radius: 12px;
            margin: 24px 0;
            text-align: center;
        }
        .contact-box h3 {
            color: #1e40af;
            font-size: 16px;
            margin: 0 0 12px 0;
        }
        .contact-box p {
            color: #1e3a8a;
            margin: 4px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">⚠️</div>
            <h1>Refill Request Update</h1>
            <p class="subtitle">Your refill request requires attention</p>
        </div>

        <div class="content">
            <p>Hello {{ $userName }},</p>
            
            <p>We've reviewed your refill request and unfortunately cannot process it at this time.</p>

            <div class="info-box">
                <div class="info-label">Medication</div>
                <div class="info-value">{{ $medicationName }}</div>
            </div>

            <div class="info-box">
                <div class="info-label">Reference Number</div>
                <div class="info-value">#{{ $refillId }}</div>
            </div>

            <div class="reason-box">
                <h2>📋 Reason for Update</h2>
                <p>{{ $reason }}</p>
            </div>

            <div class="contact-box">
                <h3>Need Assistance?</h3>
                <p>Please contact our pharmacy team for more information</p>
                <p><strong>Phone:</strong> [Your Phone Number]</p>
                <p><strong>Email:</strong> [Your Email]</p>
            </div>

            <p><strong>What you can do:</strong></p>
            <ul>
                <li>Contact us to discuss the issue</li>
                <li>Provide additional documentation if required</li>
                <li>Schedule an appointment with your doctor if needed</li>
                <li>Submit a new refill request once the issue is resolved</li>
            </ul>

            <p>We're here to help ensure you receive the care you need safely and efficiently.</p>
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
