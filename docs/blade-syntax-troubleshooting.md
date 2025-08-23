# 🚨 **Guida Blade Syntax Troubleshooting - PlaySudoku**

## **Errore Comune: "unexpected end of file, expecting elseif or else or endif"**

Questo è l'errore più frequente nelle view Blade. **CAUSA**: `@if` non bilanciati con `@endif`.

---

## 🔧 **SOLUZIONE RAPIDA - Metodo Debug**

### **Step 1: Isola il problema**
```bash
# Crea view minima di test
cp resources/views/public-solver/show.blade.php resources/views/public-solver/show.blade.php.broken

# Sostituisci con view semplice per confermare che il controller funziona
```

### **Step 2: Trova @if non bilanciati**
```bash
# Conta @if vs @endif
grep -n "@if" resources/views/public-solver/show.blade.php | wc -l
grep -n "@endif" resources/views/public-solver/show.blade.php | wc -l

# Lista completa per controllo visivo
grep -n -E "@if|@endif|@else|@elseif" resources/views/public-solver/show.blade.php
```

### **Step 3: Pattern problematici identificati**

#### ❌ **ERRORE**: @if inline senza @endif
```blade
@if($puzzle->difficulty)Difficulty: {{ ucfirst($puzzle->difficulty) }}<br>@endif
```

#### ✅ **CORRETTO**: @if su righe separate
```blade
@if($puzzle->difficulty)
    Difficulty: {{ ucfirst($puzzle->difficulty) }}<br>
@endif
```

#### ❌ **ERRORE**: @switch in JSON Schema.org
```blade
"name": "@switch(app()->getLocale())@case('it')Solver AI@break@default AI Solver@endswitch"
```

#### ✅ **CORRETTO**: Ternary operator nel JSON
```blade
"name": "{{ app()->getLocale() === 'it' ? 'Solver AI' : 'AI Solver' }}"
```

#### ❌ **ERRORE**: @push('head') con @switch complessi
```blade
@push('head')
<script>
{
    "title": "@switch(app()->getLocale())@case('it')Titolo@endswitch"
}
</script>
@endpush
```

#### ✅ **CORRETTO**: Semplifica la logica
```blade
@push('head')
<script>
{
    "title": "{{ app()->getLocale() === 'it' ? 'Titolo' : 'Title' }}"
}
</script>
@endpush
```

---

## 🛠️ **CHECKLIST DEBUGGING**

### **1. Bilanciamento @if/@endif**
- [ ] Conta `@if` === conta `@endif`
- [ ] Ogni `@if` ha il suo `@endif`
- [ ] `@elseif` e `@else` sono dentro strutture `@if...@endif`

### **2. Sintassi JSON Schema.org**
- [ ] **NO** `@switch` dentro JSON
- [ ] **SI** ternary operator `{{ condition ? 'A' : 'B' }}`
- [ ] Virgole finali valide in JSON
- [ ] Stringhe quotate correttamente

### **3. Problemi @push('head')**
- [ ] @push e @endpush bilanciati
- [ ] Contenuto @push valido (HTML/JSON)
- [ ] No @switch complessi in @push

### **4. Strutture Blade**
- [ ] `@foreach` ha `@endforeach`
- [ ] `@for` ha `@endfor`
- [ ] Parentesi `()` bilanciate nelle espressioni

---

## ⚡ **TEMPLATE VIEW MINIMA SEMPRE FUNZIONANTE**

Usa questo template per test rapidi:

```blade
<x-site-layout>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold">Test View</h1>
    
    <div class="mt-4">
        <p>Hash: {{ $puzzle->hash }}</p>
        <p>Status: {{ $puzzle->status }}</p>
        
        @if(isset($puzzle->difficulty))
            <p>Difficulty: {{ $puzzle->difficulty }}</p>
        @endif
        
        @if($puzzle->techniques_used && is_array($puzzle->techniques_used))
            <div class="mt-2">
                <strong>Techniques:</strong>
                @foreach($puzzle->techniques_used as $technique)
                    <span class="text-blue-600">{{ $technique }}</span>{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-6">
        <a href="{{ route('localized.public-solver.index', app()->getLocale()) }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded">
            Back to Solver
        </a>
    </div>
</div>
</x-site-layout>
```

---

## 🔍 **COMANDI DEBUG RAPIDI**

```bash
# Test HTTP status
curl -s -I "http://localhost:8002/it/solve/this-sudoku-puzzle/HASH" | head -1

# Controlla bilanciamento @if/@endif
grep -c "@if" resources/views/public-solver/show.blade.php
grep -c "@endif" resources/views/public-solver/show.blade.php

# Lista tutti i pattern Blade
grep -n -E "@(if|endif|else|elseif|switch|endswitch|foreach|endforeach)" resources/views/public-solver/show.blade.php

# Controlla sintassi PHP
php -l resources/views/public-solver/show.blade.php

# Test view alternativa
# Nel controller: return response()->view('public-solver.show-test', [...]);
```

---

## 📋 **PROCEDURA STANDARD RISOLUZIONE**

1. **BACKUP**: `cp show.blade.php show.blade.php.broken`
2. **TEST MINIMO**: Usa template sopra per confermare controller OK
3. **DEBUG BILANCIAMENTO**: Controlla @if/@endif con grep
4. **ISOLA SEZIONI**: Commenta blocchi per identificare il problema
5. **CORREGGI PATTERN**: Applica pattern corretti dalla guida
6. **TEST PROGRESSIVO**: Aggiungi sezioni una alla volta
7. **RIPRISTINA**: Una volta funzionante, sostituisci definitivamente

---

## 🎯 **REGOLE AUREE BLADE**

1. **MAI** `@switch` nei JSON Schema.org
2. **SEMPRE** righe separate per `@if...@endif`  
3. **SEMPRE** bilanciare `@if` con `@endif`
4. **SEMPRE** testare con view minima prima
5. **SEMPRE** backup prima di modifiche complesse

**Questo documento ti farà risparmiare ORE di debugging! 🚀**

---

*Ultimo aggiornamento: $(date) - PlaySudoku Troubleshooting Guide*
