<?php
declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SiteLayout extends Component
{
    public function __construct(
        public ?string $seoTitle = null,
        public ?string $seoDescription = null
    ) {}

    public function render(): View|Closure|string
    {
        return view('layouts.site');
    }
}


