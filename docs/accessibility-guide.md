# Guida Accessibilità PlaySudoku

## 🎯 Conformità WCAG 2.2 AA

PlaySudoku è progettato per essere completamente accessibile e conforme agli standard WCAG 2.2 AA.

## 🎹 Navigazione da Tastiera

### Controlli Principali
- **Frecce direzionali**: Navigazione tra le celle della griglia Sudoku
- **Tab/Shift+Tab**: Navigazione tra controlli e sezioni
- **1-9**: Inserimento numeri nella cella selezionata
- **Backspace/Delete**: Cancellazione numero dalla cella
- **Spazio/Enter**: Attivazione pulsanti
- **C**: Toggle modalità candidati (se abilitata)
- **U**: Annulla ultima mossa
- **R**: Ripeti mossa annullata

### Skip Links
- **Skip to main grid**: Link rapido per saltare alla griglia principale

## 🔊 Screen Reader Support

### Struttura Semantica
- **Role="grid"**: Griglia Sudoku marcata correttamente
- **Role="gridcell"**: Ogni cella con attributi ARIA completi
- **Role="region"**: Sezioni di controlli e statistiche
- **Role="main"**: Area principale del gioco

### Attributi ARIA
- `aria-label`: Descrizioni dettagliate per ogni elemento
- `aria-rowindex/aria-colindex`: Posizione celle nella griglia
- `aria-selected`: Stato di selezione celle
- `aria-live="polite"`: Annunci di stato e azioni

### Annunci Dinamici
- Selezione celle con posizione e stato
- Inserimento/cancellazione numeri
- Errori e conflitti
- Completamento puzzle
- Cambio modalità candidati
- Azioni annulla/ripeti

## 🎨 Design Accessibile

### Contrasto Colori
- **Light Mode**: Ratio minimo 4.5:1 (WCAG AA)
- **Dark Mode**: Ratio minimo 4.5:1 (WCAG AA)
- **High Contrast**: Supporto media query `prefers-contrast: high`

### Focus Management
- **Focus rings**: Outline 3px con colori ad alto contrasto
- **Focus trapping**: Gestione corretta del focus nelle celle
- **Focus sincronizzato**: DOM e stato Livewire allineati

### Indicatori Visivi
- **Conflitti**: Icona ⚠️ + colore rosso
- **Numeri fissi**: Icona 📌 + colore distintivo
- **Evidenziazioni**: Pattern non basati solo su colore

## 🎛️ Preferenze Utente

### Tema
- **Auto**: Segue preferenze sistema
- **Light**: Tema chiaro
- **Dark**: Tema scuro
- **Sync**: Sincronizzazione con database per utenti autenticati

### Accessibilità
- **Reduced Motion**: Rispetto `prefers-reduced-motion`
- **High Contrast**: Supporto `prefers-contrast: high`
- **Screen Reader**: Ottimizzato per NVDA/JAWS/VoiceOver

## 🧪 Testing con Screen Reader

### NVDA (Windows)
```bash
# Download: https://www.nvaccess.org/download/
# Controlli principali:
Insert + Space = Toggle speech mode
Insert + B = Browse mode
Insert + F = Find
Tab = Navigate elements
Arrow keys = Read content
```

### JAWS (Windows)
```bash
# Controlli principali:
Insert + F12 = Toggle speech mode
Tab = Navigate elements
Arrow keys = Read content
Insert + F = Find
Insert + F5 = Refresh forms list
```

### VoiceOver (macOS)
```bash
# Attivazione: Cmd + F5
# Controlli principali:
Control + Option + Arrow = Navigate
Control + Option + Space = Activate
Control + Option + Shift + Down = Enter group
Control + Option + H = Next heading
Control + Option + T = Next table
```

### Testing Checklist

#### ✅ Navigazione
- [ ] Skip link funziona correttamente
- [ ] Tab order logico e intuitive
- [ ] Frecce direzionali nella griglia
- [ ] Tutti i controlli raggiungibili da tastiera

#### ✅ Screen Reader
- [ ] Griglia annunciata come "Griglia Sudoku 9x9"
- [ ] Posizione celle annunciata (es. "riga 3 colonna 5")
- [ ] Stato celle annunciato (vuota/numero/conflitto)
- [ ] Azioni annunciate (inserimento/cancellazione)
- [ ] Completamento puzzle annunciato

#### ✅ Visual
- [ ] Focus rings visibili su tutti gli elementi
- [ ] Contrasto colori sufficiente (4.5:1)
- [ ] Indicatori non basati solo su colore
- [ ] Testo leggibile in entrambi i temi

#### ✅ Funzionalità
- [ ] Tutti i controlli accessibili da tastiera
- [ ] Preferenze tema salvate correttamente
- [ ] Annunci live region funzionanti
- [ ] Gestione errori accessibile

## 📱 Mobile Accessibility

### Touch
- Target size minimo 44px × 44px
- Gesture alternative per navigazione
- Zoom supportato fino a 200%

### Voice Control
- Labels appropriati per comandi vocali
- Struttura semantica per navigazione

## 🔧 Sviluppo Accessibile

### Principi Implementati
1. **Percettibile**: Contenuti presentabili in modi diversi
2. **Operabile**: Interfaccia utilizzabile da tutti
3. **Comprensibile**: Informazioni e UI comprensibili
4. **Robusto**: Compatibile con tecnologie assistive

### Tools di Testing
- **axe DevTools**: Audit automatico accessibilità
- **WAVE**: Web Accessibility Evaluation Tool
- **Lighthouse**: Audit accessibilità Chrome
- **Color Oracle**: Simulazione daltonismo

## 📚 Risorse

- [WCAG 2.2 Guidelines](https://www.w3.org/WAI/WCAG22/quickref/)
- [ARIA Authoring Practices](https://www.w3.org/WAI/ARIA/apg/)
- [WebAIM Screen Reader Testing](https://webaim.org/articles/screenreader_testing/)
- [MDN Accessibility](https://developer.mozilla.org/en-US/docs/Web/Accessibility)
