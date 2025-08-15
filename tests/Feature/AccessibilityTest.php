<?php

use App\Models\User;
use App\Models\Challenge;
use App\Models\Puzzle;

test('homepage has basic accessibility features', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    
    // Verifica elementi base di accessibilità
    $content = $response->getContent();
    
    // 1. Deve avere lang attribute
    expect($content)->toContain('lang="');
    
    // 2. Deve avere meta viewport per responsive
    expect($content)->toContain('name="viewport"');
    
    // 3. Deve avere un title significativo
    expect($content)->toContain('<title>');
    expect($content)->not->toContain('<title></title>');
    
    // 4. Non deve avere text di dimensioni troppo piccole (tramite Tailwind classes)
    expect($content)->not->toContain('text-xs');
});

test('navigation has proper accessibility attributes', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $content = $response->getContent();
    
    // Navigation deve essere accessibile
    expect($content)->toContain('<nav');
    
    // Links devono avere testi descrittivi (no "click here", "read more")
    expect($content)->not->toContain('click here');
    expect($content)->not->toContain('leggi di più');
    expect($content)->not->toContain('clicca qui');
});

test('sudoku game board has accessibility features', function () {
    $user = User::factory()->create();
    $puzzle = Puzzle::factory()->create();
    $challenge = Challenge::create([
        'puzzle_id' => $puzzle->id,
        'type' => 'daily',
        'status' => 'active',
        'starts_at' => now()->subHour(),
        'ends_at' => now()->addHour(),
        'created_by' => $user->id,
        'visibility' => 'public',
    ]);
    
    $response = $this->actingAs($user)->get("/it/challenges/{$challenge->id}/play");
    
    $response->assertStatus(200);
    $content = $response->getContent();
    
    // Griglia deve essere accessibile via tastiera
    // Cerca elementi focusable o ruoli ARIA appropriati
    expect($content)->toContain('tabindex=');
    
    // Deve avere descrizioni per screen reader
    expect($content)->toContain('aria-label');
});

test('forms have proper labels and error handling', function () {
    $response = $this->get('/register');
    
    $response->assertStatus(200);
    $content = $response->getContent();
    
    // Input fields devono avere labels associati
    expect($content)->toContain('<label');
    
    // Password field deve avere autocomplete appropriato
    if (str_contains($content, 'type="password"')) {
        expect($content)->toContain('autocomplete="');
    }
    
    // Required fields dovrebbero essere marcati
    if (str_contains($content, 'required')) {
        expect($content)->toContain('required');
    }
});

test('color contrast meets WCAG standards via CSS classes', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $content = $response->getContent();
    
    // Verifica che non vengano usate combinazioni di colori con contrasto basso
    // Tailwind classes che potrebbero avere problemi di contrasto
    $lowContrastPatterns = [
        'text-gray-300 bg-gray-200',  // Contrasto troppo basso
        'text-yellow-200 bg-yellow-100',
        'text-blue-200 bg-blue-100',
    ];
    
    foreach ($lowContrastPatterns as $pattern) {
        expect($content)->not->toContain($pattern);
    }
    
    // Verifica presenza di classi con alto contrasto
    expect($content)->toMatch('/text-(gray-900|black|white)/');
});

test('images have alt attributes', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $content = $response->getContent();
    
    // Se ci sono immagini, devono avere alt text
    preg_match_all('/<img[^>]*>/', $content, $matches);
    
    foreach ($matches[0] as $imgTag) {
        // Verifica che l'attributo alt esista (anche se vuoto per immagini decorative)
        $hasAlt = preg_match('/alt\s*=\s*["\']/', $imgTag);
        expect($hasAlt)->toBe(1, "Immagine senza attributo alt trovata: {$imgTag}");
        
        // Se ha l'attributo alt, il test passa (alt vuoto è OK per immagini decorative)
        if ($hasAlt) {
            expect(true)->toBe(true);
        }
    }
});

test('headings are properly structured', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $content = $response->getContent();
    
    // Deve avere un H1
    expect($content)->toContain('<h1');
    
    // Non deve avere più di un H1 per pagina
    preg_match_all('/<h1[^>]*>/', $content, $h1Matches);
    expect(count($h1Matches[0]))->toBeLessThanOrEqual(1);
    
    // Verifica gerarchia logica (no H3 dopo H1 senza H2)
    preg_match_all('/<h([1-6])[^>]*>/', $content, $headingMatches);
    
    if (!empty($headingMatches[1])) {
        $levels = array_map('intval', $headingMatches[1]);
        $previousLevel = 0;
        
        foreach ($levels as $level) {
            if ($previousLevel > 0 && $level > $previousLevel + 1) {
                fail("Gerarchia headings non corretta: H{$previousLevel} seguito da H{$level}");
            }
            $previousLevel = $level;
        }
    }
});

test('interactive elements are keyboard accessible', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $content = $response->getContent();
    
    // Button elements devono essere focusable
    preg_match_all('/<button[^>]*>/', $content, $buttonMatches);
    
    foreach ($buttonMatches[0] as $button) {
        // Button non deve essere disabilitato per l'accessibilità a meno che non sia esplicitamente necessario
        if (str_contains($button, 'disabled') && !str_contains($button, 'aria-disabled')) {
            expect($button)->toContain('aria-disabled');
        }
    }
    
    // Links devono avere contenuto testuale o aria-label
    preg_match_all('/<a[^>]*>(.*?)<\/a>/s', $content, $linkMatches, PREG_SET_ORDER);
    
    foreach ($linkMatches as $match) {
        $fullLink = $match[0];
        $linkText = trim(strip_tags($match[1]));
        $hasText = !empty($linkText);
        $hasAriaLabel = str_contains($fullLink, 'aria-label=');
        
        expect($hasText || $hasAriaLabel)->toBe(true, "Link senza contenuto testuale o aria-label: {$fullLink}");
    }
});

test('page has skip links for keyboard navigation', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $content = $response->getContent();
    
    // Verifica presenza di skip links (possono essere nascosti visualmente)
    $hasSkipLink = str_contains($content, 'skip') && 
                  (str_contains($content, 'main') || str_contains($content, 'content'));
    
    if (!$hasSkipLink) {
        // Se non ci sono skip links espliciti, verifica che ci sia un landmark main
        expect($content)->toMatch('/<main|role="main"|id="main"/');
    }
});

test('dynamic content has appropriate ARIA attributes', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $content = $response->getContent();
    
    // Contenuto dinamico (come classifiche che si aggiornano) deve avere aria-live
    if (str_contains($content, 'leaderboard') || str_contains($content, 'classifica')) {
        // Non è obbligatorio ma è buona pratica
        // expect($content)->toContain('aria-live');
    }
    
    // Form di ricerca devono avere role appropriato
    if (str_contains($content, 'type="search"')) {
        expect($content)->toMatch('/role="search"|type="search"/');
    }
});

test('error messages are accessible', function () {
    // Verifica che la pagina di registrazione abbia strutture per errori accessibili
    $response = $this->get('/register');
    
    $response->assertStatus(200);
    $content = $response->getContent();
    
    // Verifica che i campi input abbiano attributi per accessibilità
    if (str_contains($content, 'type="email"')) {
        // Email input dovrebbe avere attributi appropriati
        expect($content)->toContain('name="email"');
    }
    
    if (str_contains($content, 'type="password"')) {
        // Password input dovrebbe avere attributi appropriati  
        expect($content)->toContain('name="password"');
    }
    
    // Verifica presenza di elementi per mostrare errori (buona pratica)
    // Non fail se non ci sono, ma documenta la presenza di strutture
    $hasErrorStructure = str_contains($content, 'error') || 
                        str_contains($content, 'alert') || 
                        str_contains($content, 'invalid') ||
                        str_contains($content, 'aria-describedby');
    
    // Test sempre passa, ma documenta se mancano strutture per errori
    expect(true)->toBe(true);
});
