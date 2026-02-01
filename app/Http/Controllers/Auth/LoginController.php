<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        // Flow 2 Check: Jika sudah login via SSO session, redirect ke dashboard
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        // Flow 1: Belum login, redirect ke halaman login Q-Link
        // Kita kirim parameter 'redirect' agar Q-Link mengembalikan user ke sini setelah login
        // Target kembali adalah dashboard admin Q-Game
        $targetUrl = route('admin.dashboard'); 
        $qLinkLoginUrl = 'https://q-link.my.id/login?redirect=' . urlencode($targetUrl);
        
        return redirect()->away($qLinkLoginUrl);
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // FIX Flow 1: Logout diarahkan ke Halaman Depan Game (bukan login page Q-Link)
        return redirect()->route('game');
    }
}
