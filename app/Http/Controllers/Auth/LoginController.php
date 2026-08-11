<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Credenciais inválidas.'])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Usuário desativado.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        if ($user->isSuperAdmin()) {
            return redirect()->route('super.dashboard');
        }

        if ($user->isCompanyAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        Auth::logout();

        return back()->withErrors(['email' => 'Use o app operador para este usuário.'])->onlyInput('email');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
