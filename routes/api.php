<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicSolverController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public Solver AI API Routes
Route::prefix('public-solver')->group(function () {
    Route::post('/submit', [PublicSolverController::class, 'submit'])
         ->name('api.public-solver.submit');
    
    Route::post('/solve', [PublicSolverController::class, 'solve'])
         ->name('api.public-solver.solve');
    
    // Generate a random puzzle by difficulty
    Route::post('/generate', [PublicSolverController::class, 'generate'])
         ->name('api.public-solver.generate');
    
    Route::post('/share/{hash}', [PublicSolverController::class, 'share'])
         ->where('hash', '[a-f0-9]{64}')
         ->name('api.public-solver.share');
    
    Route::get('/stats', [PublicSolverController::class, 'stats'])
         ->name('api.public-solver.stats');
});

