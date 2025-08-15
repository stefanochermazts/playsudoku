# 🧩 PlaySudoku - Piattaforma Sudoku Competitiva

> Una piattaforma Sudoku moderna basata su **TALL Stack** (Tailwind CSS, Alpine.js, Laravel, Livewire) con sfide asincrone, classifiche a tempo e accessibilità completa.

[![CI](https://github.com/username/playsudoku/workflows/CI/badge.svg)](https://github.com/username/playsudoku/actions)
[![codecov](https://codecov.io/gh/username/playsudoku/branch/main/graph/badge.svg)](https://codecov.io/gh/username/playsudoku)
[![PHPStan Level 6](https://img.shields.io/badge/PHPStan-level%206-brightgreen.svg)](https://phpstan.org/)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20.svg)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6.svg)](https://laravel-livewire.com)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## ✨ Caratteristiche Principali

### 🎮 Engine Sudoku Avanzato
- **Generazione deterministica** con seed personalizzabili
- **Solver logico** con 15+ tecniche di risoluzione
- **Validator** per unicità soluzione e correttezza
- **5 livelli di difficoltà**: Easy → Crazy
- **Candidati (pencil marks)** per strategie avanzate

### 🏆 Modalità Competitiva
- **Sfide asincrone** su griglia condivisa (Daily/Weekly)
- **Classifiche a tempo** con tie-break intelligenti
- **Anti-cheat** con validazione server-side
- **Replay system** per analisi mosse
- **Leaderboard globali** e filtrabili

### ♿ Accessibilità & UX
- **WCAG 2.2 AA compliant** (11 test automatizzati)
- **Navigazione tastiera completa** con screen reader
- **Tema scuro/chiaro** con contrasti verificati
- **Mobile responsive** e touch-friendly
- **Skip links** e landmark semantici

### 🔧 Qualità del Codice
- **95+ test automatizzati** (Unit/Feature/Browser)
- **Code coverage 80%+** con gate CI
- **PHPStan analisi statica** level 6
- **Laravel Pint** per code style
- **Browser testing** con Laravel Dusk

## 🚀 Quick Start

### Requisiti
- **PHP 8.3+** con estensioni: mbstring, dom, curl, fileinfo, openssl, pdo
- **Composer 2.0+**
- **Node.js 18+** & npm
- **PostgreSQL 14+** (o SQLite per sviluppo)

### Installazione

```bash
# 1. Clona il repository
git clone https://github.com/username/playsudoku.git
cd playsudoku

# 2. Installa dipendenze
composer install
npm install

# 3. Configurazione ambiente
cp .env.example .env
php artisan key:generate

# 4. Database
php artisan migrate --seed

# 5. Asset building
npm run build

# 6. Avvia il server
php artisan serve
```

### Sviluppo
```bash
# Ambiente completo (server + queue + logs + vite)
composer dev

# Test & Quality
composer test           # Run all tests
composer test:coverage  # Test con coverage report
composer analyse        # PHPStan static analysis
composer lint           # Code style check
composer quality        # Lint + analyse + coverage
```

## 🧪 Testing

### Test Suite Completa
```bash
# Unit Tests (Sudoku Engine)
composer test:unit

# Feature Tests (Laravel)
composer test:feature

# Browser Tests (Dusk)
composer test:dusk

# Accessibility Tests
./vendor/bin/pest tests/Feature/AccessibilityTest.php
```

### Coverage Report
```bash
# Coverage HTML dettagliato
composer test:coverage-html
open coverage-html/index.html
```

## 📊 Architettura

### TALL Stack
- **🎨 Tailwind CSS**: Utility-first CSS framework
- **⚡ Alpine.js**: Lightweight JS per interazioni
- **🚀 Laravel 12**: Framework PHP moderno
- **🔄 Livewire**: Reactive components server-side

### Domain-Driven Design
```
app/
├── Domain/Sudoku/          # Core business logic
│   ├── Generator.php       # Puzzle generation
│   ├── Solver.php         # Logical solving
│   ├── Validator.php      # Solution validation
│   └── Grid.php           # Grid operations
├── Services/              # Application services
├── Http/Livewire/         # UI components
└── Models/                # Eloquent models
```

### Database Schema
- **Users**: Autenticazione e profili
- **Puzzles**: Griglie generate con metadati
- **Challenges**: Sfide temporizzate (daily/weekly/custom)
- **ChallengeAttempts**: Tentativi utenti con stato
- **AttemptMoves**: Log mosse per replay/anti-cheat

## 🎯 Funzionalità Core

### Generazione Puzzle
```php
// Genera puzzle deterministic
$puzzle = Generator::withSeed(12345)
    ->difficulty('hard')
    ->minGivens(25)
    ->generate();

// Verifica unicità soluzione
$isValid = Validator::hasUniqueSolution($puzzle);
```

### Sfide Competitive
```php
// Crea sfida daily
$challenge = ChallengeService::createDailyChallenge([
    'difficulty' => 'medium',
    'starts_at' => today(),
    'duration_hours' => 24
]);

// Partecipa a sfida
$attempt = $challenge->participate($user);
```

## 🔐 Sicurezza & Performance

### Anti-Cheat
- ✅ **Validazione server-side** di ogni mossa
- ✅ **Tempo minimo** di completamento (10s)
- ✅ **Analisi anomalie** tempi e pause
- ✅ **Rate limiting** su endpoint critici
- ✅ **CSRF protection** su tutte le form

### Ottimizzazioni
- ✅ **Caching intelligente** (Redis)
- ✅ **Query optimization** con eager loading
- ✅ **Asset bundling** e minificazione
- ✅ **Database indexing** ottimizzato
- ✅ **Lazy loading** componenti Livewire

## 🌐 Multilingua

Supporto completo per:
- 🇮🇹 **Italiano** (predefinito)
- 🇬🇧 **Inglese**
- 🔄 Sistema estendibile per altre lingue

## 📝 CI/CD & Quality Gates

### GitHub Actions
- ✅ **Automated testing** su PR
- ✅ **Code coverage** threshold 80%
- ✅ **Static analysis** PHPStan level 6
- ✅ **Code style** Laravel Pint
- ✅ **Security scanning**

### Pull Request Gates
```yaml
# .github/workflows/ci.yml
- Code coverage ≥ 80%
- All tests pass
- PHPStan level 6 ✓
- Code style check ✓
```

## 🤝 Contribuire

1. **Fork** il repository
2. **Crea** un branch feature (`git checkout -b feature/amazing-feature`)
3. **Esegui** i test (`composer quality`)
4. **Commit** le modifiche (`git commit -m 'Add amazing feature'`)
5. **Push** il branch (`git push origin feature/amazing-feature`)
6. **Apri** una Pull Request

### Guidelines
- ✅ Mantieni **coverage ≥ 80%**
- ✅ Segui **PSR-12** code style
- ✅ Aggiungi **test** per nuove feature
- ✅ Rispetta **accessibilità WCAG 2.2 AA**
- ✅ Documenta **API changes**

## 📜 Licenza

Questo progetto è rilasciato sotto licenza **MIT**. Vedi il file [LICENSE](LICENSE) per dettagli.

## 🙏 Ringraziamenti

- **Laravel Team** per il framework eccezionale
- **Livewire** per la reattività server-side
- **Tailwind CSS** per il design system
- **Community** PHP/Laravel per supporto e feedback

---

<div align="center">

**[🏠 Homepage](/)** • **[🎮 Demo](/)** • **[📚 Docs](/docs)** • **[🐛 Issues](https://github.com/username/playsudoku/issues)**

Fatto con ❤️ e ☕ da sviluppatori che amano i puzzle

</div>