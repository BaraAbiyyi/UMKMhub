<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\StoreApplication\StoreStoreApplicationRequest;
use App\Models\Category;
use App\Models\User;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function loginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->homeFor($request->user()));
    }

    public function registerForm(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated()['name'],
            'email' => $request->validated()['email'],
            'password' => Hash::make($request->validated()['password']),
            'role' => UserRole::Customer,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Registrasi berhasil. Selamat berbelanja!');
    }

    public function storeApplicationForm(): View
    {
        return view('auth.store-application', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function storeApplication(StoreStoreApplicationRequest $request, StoreService $service): RedirectResponse
    {
        $store = $service->register($request->validated());

        Auth::login($store->owner);
        $request->session()->regenerate();

        return redirect()->route('umkm.dashboard')->with('status', 'Pendaftaran UMKM terkirim. Mohon tunggu verifikasi Superadmin.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Anda telah keluar.');
    }

    private function homeFor(User $user): string
    {
        return match ($user->role) {
            UserRole::Superadmin => route('admin.dashboard'),
            UserRole::UmkmAdmin => route('umkm.dashboard'),
            default => route('home'),
        };
    }
}
