<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding per le interfacce del dominio Sudoku
        $this->app->bind(
            \App\Domain\Sudoku\Contracts\ValidatorInterface::class,
            \App\Domain\Sudoku\Validator::class
        );
        
        $this->app->bind(
            \App\Domain\Sudoku\Contracts\GeneratorInterface::class,
            \App\Domain\Sudoku\Generator::class
        );
        
        $this->app->bind(
            \App\Domain\Sudoku\Contracts\DifficultyRaterInterface::class,
            \App\Domain\Sudoku\DifficultyRater::class
        );
        
        $this->app->bind(
            \App\Domain\Sudoku\Contracts\SolverInterface::class,
            \App\Domain\Sudoku\Solver::class
        );
        
        // Servizi applicativi
        $this->app->singleton(\App\Services\MoveValidationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registra observers per invalidazione cache automatica
        \App\Models\ChallengeAttempt::observe(\App\Observers\ChallengeAttemptObserver::class);
        
        // Registra policies per autorizzazione
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Challenge::class, \App\Policies\ChallengePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\ChallengeAttempt::class, \App\Policies\ChallengeAttemptPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
    }
}
