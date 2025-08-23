# Cursor Rules Index - PlaySudoku Project

## Available Rules

### Core Architecture
- **[laravel-tall-stack.mdc](laravel-tall-stack.mdc)** - Laravel TALL stack best practices and conventions
- **[sudoku-solver-ai.mdc](sudoku-solver-ai.mdc)** - Sudoku Solver AI system architecture and components
- **[database-models.mdc](database-models.mdc)** - Database models and migration patterns

### Solver Implementation
- **[sudoku-solver-techniques.mdc](sudoku-solver-techniques.mdc)** - 30+ solving techniques implementation and debugging
  - Critical: Never use `$grid->getCandidates()` - use `$grid->getCell()->candidates`
  - Technique priority and error handling
  - Performance optimization and loop prevention

### Frontend Development
- **[blade-templates.mdc](blade-templates.mdc)** - Blade template syntax and error prevention
- **[blade-debugging-guide.mdc](blade-debugging-guide.mdc)** - Specific solutions for "unexpected end of file" errors
- **[javascript-integration.mdc](javascript-integration.mdc)** - JavaScript integration and social sharing

### Debugging & Testing
- **[api-debugging-patterns.mdc](api-debugging-patterns.mdc)** - API testing with cURL and common issues
- **[testing-debugging.mdc](testing-debugging.mdc)** - Testing workflows and development tools
- **[performance-optimization.mdc](performance-optimization.mdc)** - Performance issues and solutions

### SEO & Localization
- **[seo-localization.mdc](seo-localization.mdc)** - Multi-language support and SEO implementation

## Quick Reference

### Most Common Issues Solved

1. **Blade Syntax Errors** → `blade-debugging-guide.mdc`
2. **Solver Crashes** → `sudoku-solver-techniques.mdc`
3. **API 404/500 Errors** → `api-debugging-patterns.mdc`
4. **Performance Issues** → `performance-optimization.mdc`
5. **JavaScript Errors** → `javascript-integration.mdc`

### Key Commands
```bash
# Clear Blade cache
php artisan view:clear

# Test solver API
curl -X POST http://localhost:8002/api/public-solver/solve -H "Content-Type: application/json" -d '{"grid": [...], "step_by_step": false}'

# Monitor solver logs
tail -f storage/logs/laravel.log | grep "Applied technique"

# Check technique usage
curl ... | grep -o '"techniques_used":\[[^]]*\]' | sed 's/"techniques_used":\[//' | sed 's/\]//' | tr ',' '\n' | sed 's/"//g' | sort | uniq -c | sort -nr
```

### Critical Reminders
- ✅ Always use `$grid->getCell($row, $col)->candidates` not `getCandidates()`
- ✅ Wrap technique methods in try-catch blocks
- ✅ Extract Blade logic to PHP variables before injecting to JavaScript
- ✅ Use @json() for safe data injection
- ✅ Test API endpoints with cURL before debugging frontend
- ✅ Clear view cache when debugging Blade syntax errors

## Rule Usage

These rules are automatically available to the AI assistant when working on relevant files. The `globs` pattern in each rule determines which files trigger the rule, and the `description` allows manual fetching with the fetch_rules tool.

### Manual Rule Fetching
Use the fetch_rules tool to get specific guidance:
- `sudoku-solver-techniques` - For solver implementation issues
- `blade-debugging-guide` - For Blade syntax errors
- `api-debugging-patterns` - For API testing and debugging
- `performance-optimization` - For performance issues
