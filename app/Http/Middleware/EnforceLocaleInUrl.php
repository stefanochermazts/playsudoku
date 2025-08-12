<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceLocaleInUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = (array) config('app.supported_locales', ['en','it']);
        $first = $request->segment(1);

        if (! in_array($first, $supported, true)) {
            $preferred = $request->session()->get('locale');
            if (! is_string($preferred) || ! in_array($preferred, $supported, true)) {
                $preferred = 'en';
                $accept = (string) $request->header('Accept-Language', '');
                if (preg_match('/\bit\b/i', $accept)) {
                    $preferred = 'it';
                }
            }

            return redirect()->to(url('/'.$preferred.$request->getRequestUri()));
        }

        return $next($request);
    }
}


