<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    use HttpResponses;

    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            if ($request->expectsJson()) {
                $user = Auth::user()->load('role');
                $rolePermissions = $user->role ? $user->role->permissions : [];
                if (is_string($rolePermissions)) {
                    $rolePermissions = json_decode($rolePermissions, true) ?? [];
                }
                $userData = $user->toArray();
                $userData['permissions'] = $rolePermissions;

                return $this->success([
                    'user' => $userData,
                    'redirect' => '/admin/dashboard'
                ], 'Login successful');
            }

            return redirect()->intended('/admin/dashboard');
        }

        if ($request->expectsJson()) {
            return $this->error(null, 'Invalid credentials', 401);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return $this->success(null, 'Logged out successfully');
        }

        return redirect()->route('login');
    }
}
