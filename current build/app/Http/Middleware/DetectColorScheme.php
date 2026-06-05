<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DetectColorScheme
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        // Send hint to the browser to provide color scheme preference
        $response = $next($request);
        $response->headers->set('Accept-CH', 'Sec-CH-Prefers-Color-Scheme');

        // Check for the color scheme preference and store it in session
        $colorScheme = $request->header('Sec-CH-Prefers-Color-Scheme', 'light');
        if ($colorScheme === 'dark') {
            session(['theme' => 'dark']);
        } elseif ($colorScheme === 'light') {
            session(['theme' => 'light']);
        } else {
            session(['theme' => 'auto']);
        }

        return $response;
    }
}
