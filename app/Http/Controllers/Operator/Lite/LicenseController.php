<?php

namespace App\Http\Controllers\Operator\Lite;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Operator\Lite\Concerns\ActivatesOperatorDevice;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseController extends Controller
{
    use ActivatesOperatorDevice;

    public function show(Request $request): View
    {
        return view('operator-lite.license', [
            'expiresAt' => session('operator_license_expires_at'),
            'bootstrap' => $this->bootstrapPayload($request->user()),
        ]);
    }

    public function renew(Request $request, LicenseService $licenses): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
            'device_uid' => ['required', 'string', 'max:64'],
        ]);

        $user = $request->user();

        if (! \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Senha incorreta.']);
        }

        try {
            ['license' => $license] = $this->activateDevice(
                $user,
                $request->device_uid,
                null,
                false,
                $licenses
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $this->storeOperatorSession($user, $license, $licenses);

        return redirect()->route('operator-lite.home')
            ->with('success', 'Licença renovada com sucesso.')
            ->with('lite_bootstrap', $this->bootstrapPayload($user));
    }
}
