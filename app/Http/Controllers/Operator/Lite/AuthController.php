<?php

namespace App\Http\Controllers\Operator\Lite;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Operator\Lite\Concerns\ActivatesOperatorDevice;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    use ActivatesOperatorDevice;

    public function showLogin(): View
    {
        return view('operator-lite.login');
    }

    public function login(Request $request, LicenseService $licenses): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_uid' => ['required', 'string', 'max:64'],
        ]);

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], true)) {
            return back()->withErrors(['email' => 'Credenciais inválidas.'])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->active || ! $user->hasRole('operator')) {
            Auth::logout();

            return back()->withErrors(['email' => 'Acesso permitido apenas para operadores ativos.'])->onlyInput('email');
        }

        $company = $user->company;
        if (! $company || ! $licenses->companyCanOperate($company)) {
            Auth::logout();

            return back()->withErrors(['email' => 'Mensalidade em atraso. Conecte após a liberação no painel.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $deviceUid = $credentials['device_uid'];
        $createDevice = ! \App\Models\Device::withoutCompanyScope()->where('device_uid', $deviceUid)->exists();

        try {
            ['license' => $license] = $this->activateDevice($user, $deviceUid, null, $createDevice, $licenses);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Auth::logout();

            return back()->withErrors($e->errors())->onlyInput('email');
        }

        $this->storeOperatorSession($user, $license, $licenses);

        cookie()->queue(cookie('device_uid', $deviceUid, 60 * 24 * 365));

        return redirect()->route('operator-lite.home')
            ->with('lite_bootstrap', $this->bootstrapPayload($user));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('operator-lite.login');
    }
}
