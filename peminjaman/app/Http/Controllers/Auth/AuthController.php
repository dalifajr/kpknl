<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectForUser(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle login authentication.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $loginInput = $request->input('login');
        $password = $request->input('password');

        // Determine if login input is email or username
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Check credentials
        $user = User::where($fieldType, $loginInput)->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return $this->redirectForUser($user)
                ->with('success', 'Selamat datang kembali, ' . $user->username . '!');
        }

        return back()
            ->withInput($request->only('login', 'remember'))
            ->withErrors([
                'login' => 'Username/email atau password yang Anda masukkan salah.',
            ]);
    }

    /**
     * Show registration form.
     */
    public function showRegister(Request $request): View
    {
        $defaultRole = $request->query('role', 'peminjam');
        if (!in_array($defaultRole, ['admin', 'pelelang', 'peminjam'])) {
            $defaultRole = 'peminjam';
        }

        return view('auth.register', [
            'defaultRole' => $defaultRole,
        ]);
    }

    /**
     * Handle user registration.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'created_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectForUser($user)
            ->with('success', 'Akun berhasil dibuat dan Anda telah masuk sebagai ' . ucfirst($user->role) . '.');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Helper redirect based on role.
     */
    protected function redirectForUser(User $user): RedirectResponse
    {
        return match ($user->role) {
            User::ROLE_ADMIN => redirect()->intended(route('admin.dashboard')),
            User::ROLE_PELELANG => redirect()->intended(route('pelelang.dashboard')),
            User::ROLE_PEMINJAM => redirect()->intended(route('peminjam.dashboard')),
            default => redirect()->route('login'),
        };
    }
}
