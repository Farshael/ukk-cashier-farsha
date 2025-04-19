<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Gunakan Auth untuk cek login

class IsGuest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Cek role user
            $user = Auth::user();
            
            if ($user->role == 'admin') {
                return redirect()->route('dashboard')->with('message', 'You are already logged in!');
            } elseif ($user->role == 'employee') {
                return redirect()->route('dashboard')->with('message', 'You are already logged in!');
            } else {
                return redirect()->route('permission');
            }
        }
    
        return $next($request);
    }    
}