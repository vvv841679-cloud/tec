<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageViews
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo registrar visitas para GET requests y excluir rutas de API
        if ($request->isMethod('GET') && !$request->is('api/*')) {
            $url = $request->path();

            // Registrar la visita
            PageView::addVisit($url);
        }

        return $next($request);
    }
}
