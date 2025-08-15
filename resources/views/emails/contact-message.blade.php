<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuovo messaggio di contatto - PlaySudoku</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .field {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .field-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }
        .field-value {
            color: #333;
            font-size: 16px;
        }
        .message-content {
            background-color: white;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            margin-top: 10px;
            white-space: pre-wrap;
        }
        .metadata {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 30px;
            font-size: 14px;
            color: #666;
        }
        .metadata-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .reply-button {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 PlaySudoku - Nuovo Messaggio</h1>
        </div>
        
        <div class="field">
            <span class="field-label">👤 Nome:</span>
            <div class="field-value">{{ $contactData['name'] }}</div>
        </div>
        
        <div class="field">
            <span class="field-label">📧 Email:</span>
            <div class="field-value">
                <a href="mailto:{{ $contactData['email'] }}">{{ $contactData['email'] }}</a>
            </div>
        </div>
        
        <div class="field">
            <span class="field-label">📋 Oggetto:</span>
            <div class="field-value">{{ $contactData['subject'] }}</div>
        </div>
        
        <div class="field">
            <span class="field-label">💬 Messaggio:</span>
            <div class="message-content">{{ $contactData['message'] }}</div>
        </div>
        
        <div style="text-align: center;">
            <a href="mailto:{{ $contactData['email'] }}?subject=Re: {{ $contactData['subject'] }}" class="reply-button">
                📧 Rispondi Direttamente
            </a>
        </div>
        
        <div class="metadata">
            <div class="metadata-title">ℹ️ Informazioni Tecniche</div>
            <strong>Data ricezione:</strong> {{ $contactData['submitted_at']->format('d/m/Y H:i:s') }}<br>
            <strong>Lingua utente:</strong> {{ strtoupper($contactData['locale']) }}<br>
            <strong>Indirizzo IP:</strong> {{ $contactData['ip_address'] }}<br>
            <strong>User Agent:</strong> {{ $contactData['user_agent'] }}
        </div>
    </div>
</body>
</html>
