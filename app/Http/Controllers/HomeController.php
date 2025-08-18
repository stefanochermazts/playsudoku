<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\HomepageStatsService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomepageStatsService $homepageStatsService
    ) {}

    /**
     * Mostra la homepage con statistiche dinamiche
     */
    public function index(): View
    {
        $stats = $this->homepageStatsService->getStats();
        $homepageStats = $this->homepageStatsService;
        
        return view('home', compact('stats', 'homepageStats'));
    }
}
