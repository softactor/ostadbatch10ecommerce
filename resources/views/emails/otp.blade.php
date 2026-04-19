<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .header h1 {
            color: #4a5568;
            margin: 0;
        }
        .content {
            padding: 30px 20px;
            text-align: center;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #3182ce;
            background-color: #ebf8ff;
            display: inline-block;
            padding: 15px 30px;
            border-radius: 8px;
            letter-spacing: 5px;
            margin: 20px 0;
            font-family: monospace;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
            font-size: 12px;
            color: #718096;
        }
        .warning {
            color: #e53e3e;
            font-size: 12px;
            margin-top: 15px;
        }
        .button {
            background-color: #3182ce;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>
        
        <div class="content">
            <h2>Email Verification</h2>
            
            @if($userName)
                <p>Hello {{ $userName }},</p>
            @else
                <p>Hello,</p>
            @endif
            
            <p>You have requested an OTP for authentication. Please use the following code to complete your verification:</p>
            
            <div class="otp-code">
                {{ $otp }}
            </div>
            
            <p>This OTP is valid for <strong>5 minutes</strong>.</p>
            
            <p>If you didn't request this code, please ignore this email or contact support.</p>
        </div>
        
        <div class="footer">
            <p>Thank you for using {{ config('app.name') }}</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p class="warning">This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>