<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Authenticate using Laravel's built-in authentication
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            
            // Create a token for API access and store it
            $user = Auth::user();
            $token = $user->createToken('web_session')->plainTextToken;
            
            // Store token and user in session
            session(['auth_token' => $token]);
            session(['user' => $user->only('id', 'name', 'email')]);
            
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas son incorrectas.',
        ])->withInput($request->only('email'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        // Revoke all user tokens
        if (Auth::check()) {
            $user = Auth::user();
            $user->tokens()->delete();
        }
        
        // Destroy the session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
