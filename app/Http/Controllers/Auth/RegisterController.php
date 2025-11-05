<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'department' => 'required|string|max:255',
            'address' => 'required|string|max:500',
        ]);

        // Create user directly
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'department' => $request->department,
            'address' => $request->address,
        ]);

        // Log in the user automatically
        Auth::login($user);
        
        // Create a token for API access
        $token = $user->createToken('web_session')->plainTextToken;
        
        // Store token and user in session
        session(['auth_token' => $token]);
        session(['user' => $user->only('id', 'name', 'email', 'phone', 'department', 'address')]);

        return redirect()->route('dashboard');
    }
}
