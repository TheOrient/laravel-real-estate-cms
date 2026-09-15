<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'E-posta adresi gereklidir.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'password.required' => 'Şifre alanı gereklidir.',
        ]);

        // Check if the user exists before trying to authenticate
        $user = \App\Models\User::where('email', $request->email)->first();

        // Check if user is active
        if ($user && !$user->is_active) {
            return back()->withErrors([
                'email' => __('auth.inactive_account'),
            ])->withInput($request->only('email', 'remember'));
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Store user version in session
            $request->session()->put('user_version', Auth::user()->version);

            // Single-agency site: the only account is the operator's
            // admin. Push them straight to the admin dashboard on
            // login instead of the public home — no reason to detour
            // through the storefront. Non-admin roles (if ever added)
            // still land wherever they were headed.
            $user = Auth::user();
            $adminRoles = ['admin', 'super_admin'];
            $isAdmin = $user
                && (in_array((string) $user->user_role, $adminRoles, true)
                    || ! empty($user->is_admin));

            if ($isAdmin && ! $request->session()->has('url.intended')) {
                try {
                    return redirect()->route('admin.dashboard');
                } catch (\Throwable $e) {
                    return redirect('/admin');
                }
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Girdiğiniz bilgiler hatalı.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}