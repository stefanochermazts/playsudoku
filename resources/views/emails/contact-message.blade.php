<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuovo messaggio di contatto - PlaySudoku</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        
        .email-wrapper {
            width: 100%;
            max-width: 1024px;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            width: 100%;
            box-sizing: border-box;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px 12px 0 0;
            margin: -40px -40px 40px -40px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .fields-container {
            display: grid;
            gap: 20px;
            margin: 30px 0;
        }
        
        .field {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            transition: box-shadow 0.2s ease;
        }
        
        .field:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .field-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .field-value {
            color: #333;
            font-size: 16px;
            line-height: 1.5;
        }
        
        .field-value a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .field-value a:hover {
            text-decoration: underline;
        }
        
        .message-content {
            background-color: white;
            padding: 25px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-top: 12px;
            white-space: pre-wrap;
            line-height: 1.6;
            font-size: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .reply-section {
            text-align: center;
            margin: 40px 0;
        }
        
        .reply-button {
            display: inline-block;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(40,167,69,0.3);
        }
        
        .reply-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(40,167,69,0.4);
            text-decoration: none;
            color: white;
        }
        
        .metadata {
            background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
            padding: 25px;
            border-radius: 10px;
            margin-top: 40px;
            font-size: 14px;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }
        
        .metadata-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: #495057;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .metadata-grid {
            display: grid;
            gap: 10px;
        }
        
        .metadata-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .metadata-item:last-child {
            border-bottom: none;
        }
        
        .metadata-label {
            font-weight: 500;
            color: #6c757d;
            min-width: 120px;
            flex-shrink: 0;
        }
        
        .metadata-value {
            color: #495057;
            text-align: right;
            word-break: break-all;
        }
        
        /* Responsive Design */
        @media only screen and (max-width: 768px) {
            .email-wrapper {
                padding: 15px;
            }
            
            .container {
                padding: 25px;
                border-radius: 8px;
            }
            
            .header {
                padding: 20px;
                margin: -25px -25px 25px -25px;
                border-radius: 8px 8px 0 0;
            }
            
            .header h1 {
                font-size: 22px;
            }
            
            .field {
                padding: 15px;
            }
            
            .message-content {
                padding: 20px;
                font-size: 14px;
            }
            
            .reply-button {
                width: 100%;
                max-width: 280px;
                padding: 18px 20px;
            }
            
            .metadata {
                padding: 20px;
            }
            
            .metadata-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .metadata-label {
                min-width: auto;
            }
            
            .metadata-value {
                text-align: left;
            }
        }
        
        @media only screen and (max-width: 480px) {
            .email-wrapper {
                padding: 10px;
            }
            
            .container {
                padding: 20px;
            }
            
            .header {
                padding: 15px;
                margin: -20px -20px 20px -20px;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .field-value,
            .message-content {
                font-size: 14px;
            }
            
            .metadata {
                padding: 15px;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .container {
                background-color: #1e1e1e;
                color: #e0e0e0;
            }
            
            .field {
                background-color: #2d2d2d;
                color: #e0e0e0;
            }
            
            .field-value {
                color: #e0e0e0;
            }
            
            .field-value a {
                color: #9bb5ff;
            }
            
            .message-content {
                background-color: #2d2d2d;
                border-color: #404040;
                color: #e0e0e0;
            }
            
            .metadata {
                background-color: #2d2d2d;
                border-color: #404040;
                color: #a0a0a0;
            }
            
            .metadata-title {
                color: #e0e0e0;
            }
            
            .metadata-item {
                border-bottom-color: #404040;
            }
            
            .metadata-label {
                color: #a0a0a0;
            }
            
            .metadata-value {
                color: #e0e0e0;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="container">
            <div class="header">
                <h1>🎯 PlaySudoku - Nuovo Messaggio</h1>
            </div>
            
            <div class="fields-container">
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
            </div>
            
            <div class="reply-section">
                <a href="mailto:{{ $contactData['email'] }}?subject=Re: {{ $contactData['subject'] }}" class="reply-button">
                    📧 Rispondi Direttamente
                </a>
            </div>
            
            <div class="metadata">
                <div class="metadata-title">ℹ️ Informazioni Tecniche</div>
                <div class="metadata-grid">
                    <div class="metadata-item">
                        <span class="metadata-label">Data ricezione:</span>
                        <span class="metadata-value">{{ $contactData['submitted_at']->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Lingua utente:</span>
                        <span class="metadata-value">{{ strtoupper($contactData['locale']) }}</span>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Indirizzo IP:</span>
                        <span class="metadata-value">{{ $contactData['ip_address'] }}</span>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">User Agent:</span>
                        <span class="metadata-value">{{ $contactData['user_agent'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
