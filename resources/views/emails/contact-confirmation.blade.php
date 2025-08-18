@php
    $locale = $contactData['locale'] ?? 'en';
    $isItalian = $locale === 'it';
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isItalian ? 'Conferma messaggio ricevuto - PlaySudoku' : 'Message received confirmation - PlaySudoku' }}</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        
        .content {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .content h2 {
            font-size: 24px;
            margin: 20px 0;
            color: #28a745;
        }
        
        .content p {
            font-size: 16px;
            margin: 15px 0;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .success-icon {
            font-size: 64px;
            margin-bottom: 20px;
            display: block;
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
            border-left: 4px solid #28a745;
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
        
        .message-preview {
            background-color: white;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-top: 12px;
            max-height: 120px;
            overflow: hidden;
            position: relative;
            line-height: 1.6;
        }
        
        .cta-section {
            background: linear-gradient(135deg, #e8f5e8 0%, #f0f9ff 100%);
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin: 40px 0;
            border: 1px solid #e0f2e0;
        }
        
        .cta-section h3 {
            margin: 0 0 15px 0;
            font-size: 20px;
            color: #1e7e34;
        }
        
        .cta-section p {
            margin: 0 0 25px 0;
            color: #495057;
        }
        
        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,123,255,0.3);
        }
        
        .cta-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,123,255,0.4);
            text-decoration: none;
            color: white;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        
        .footer p {
            margin: 10px 0;
        }
        
        .footer strong {
            color: #495057;
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
            
            .content h2 {
                font-size: 20px;
            }
            
            .content p {
                font-size: 15px;
            }
            
            .success-icon {
                font-size: 48px;
            }
            
            .cta-section {
                padding: 20px;
                margin: 25px 0;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .cta-button {
                width: 100%;
                max-width: 280px;
                text-align: center;
                padding: 16px 20px;
            }
            
            .field {
                padding: 15px;
            }
            
            .message-preview {
                padding: 15px;
                max-height: 100px;
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
            
            .success-icon {
                font-size: 40px;
            }
            
            .field-value,
            .content p {
                font-size: 14px;
            }
        }
        
        /* Dark mode support for email clients that support it */
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
            
            .message-preview {
                background-color: #2d2d2d;
                border-color: #404040;
                color: #e0e0e0;
            }
            
            .footer {
                color: #a0a0a0;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="container">
            <div class="header">
                <h1>{{ $isItalian ? '🎯 PlaySudoku' : '🎯 PlaySudoku' }}</h1>
            </div>
            
            <div class="content">
                <div class="success-icon">✅</div>
                <h2>{{ $isItalian ? 'Messaggio Ricevuto!' : 'Message Received!' }}</h2>
                <p>
                    {{ $isItalian 
                        ? 'Ciao ' . $contactData['name'] . ', grazie per averci contattato!' 
                        : 'Hello ' . $contactData['name'] . ', thank you for contacting us!' 
                    }}
                </p>
                <p>
                    {{ $isItalian 
                        ? 'Abbiamo ricevuto il tuo messaggio e ti risponderemo il prima possibile. Ecco un riepilogo di quello che ci hai inviato:' 
                        : 'We have received your message and will respond as soon as possible. Here\'s a summary of what you sent us:' 
                    }}
                </p>
            </div>
            
            <div class="fields-container">
                <div class="field">
                    <span class="field-label">{{ $isItalian ? '📋 Oggetto:' : '📋 Subject:' }}</span>
                    <div class="field-value">{{ $contactData['subject'] }}</div>
                </div>
                
                <div class="field">
                    <span class="field-label">{{ $isItalian ? '💬 Il tuo messaggio:' : '💬 Your message:' }}</span>
                    <div class="message-preview">{{ $contactData['message'] }}</div>
                </div>
                
                <div class="field">
                    <span class="field-label">{{ $isItalian ? '📅 Inviato il:' : '📅 Sent on:' }}</span>
                    <div class="field-value">{{ $contactData['submitted_at']->format('d/m/Y H:i:s') }}</div>
                </div>
            </div>
            
            <div class="cta-section">
                <h3>{{ $isItalian ? 'Nel frattempo, perché non giochi a Sudoku?' : 'In the meantime, why not play some Sudoku?' }}</h3>
                <p>
                    {{ $isItalian 
                        ? 'Scopri le nostre sfide quotidiane e migliora le tue abilità!' 
                        : 'Discover our daily challenges and improve your skills!' 
                    }}
                </p>
                
                <div class="cta-buttons">
                    @if($isItalian)
                        <a href="{{ url('/it/sudoku/training') }}" class="cta-button">🧩 Allenamento Gratuito</a>
                        <a href="{{ url('/it/challenges') }}" class="cta-button">🏆 Sfide Quotidiane</a>
                    @else
                        <a href="{{ url('/en/sudoku/training') }}" class="cta-button">🧩 Free Training</a>
                        <a href="{{ url('/en/challenges') }}" class="cta-button">🏆 Daily Challenges</a>
                    @endif
                </div>
            </div>
            
            <div class="footer">
                <p>
                    {{ $isItalian 
                        ? 'Questo è un messaggio automatico di conferma. Non rispondere a questa email.' 
                        : 'This is an automatic confirmation message. Please do not reply to this email.' 
                    }}
                </p>
                <p>
                    <strong>PlaySudoku Team</strong><br>
                    {{ $isItalian ? 'La piattaforma Sudoku più avanzata' : 'The most advanced Sudoku platform' }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
