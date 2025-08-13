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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
