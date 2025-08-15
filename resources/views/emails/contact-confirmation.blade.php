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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            text-align: center;
            margin-bottom: 30px;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .field {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #28a745;
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
        .message-preview {
            background-color: white;
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            margin-top: 10px;
            max-height: 100px;
            overflow: hidden;
            position: relative;
        }
        .cta-section {
            background-color: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 30px 0;
        }
        .cta-button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
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
        
        <div class="cta-section">
            <h3>{{ $isItalian ? 'Nel frattempo, perché non giochi a Sudoku?' : 'In the meantime, why not play some Sudoku?' }}</h3>
            <p>
                {{ $isItalian 
                    ? 'Scopri le nostre sfide quotidiane e migliora le tue abilità!' 
                    : 'Discover our daily challenges and improve your skills!' 
                }}
            </p>
            
            @if($isItalian)
                <a href="{{ url('/it/sudoku/training') }}" class="cta-button">🧩 Allenamento Gratuito</a>
                <a href="{{ url('/it/challenges') }}" class="cta-button">🏆 Sfide Quotidiane</a>
            @else
                <a href="{{ url('/en/sudoku/training') }}" class="cta-button">🧩 Free Training</a>
                <a href="{{ url('/en/challenges') }}" class="cta-button">🏆 Daily Challenges</a>
            @endif
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
</body>
</html>
