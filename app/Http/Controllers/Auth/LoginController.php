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
            
            // Create a token for API access and store it (if Sanctum is available)
            $user = Auth::user();
            try {
                if (method_exists($user, 'createToken')) {
                    $token = $user->createToken('web_session')->plainTextToken;
                    session(['auth_token' => $token]);
                }
            } catch (\Exception $e) {
                // If Sanctum is not available, continue without token
            }
            
            // Store user in session
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
        // Revoke all user tokens (if Sanctum is available)
        if (Auth::check()) {
            $user = Auth::user();
            try {
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }
            } catch (\Exception $e) {
                // If Sanctum is not available, continue without revoking tokens
            }
        }
        
        // Destroy the session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
