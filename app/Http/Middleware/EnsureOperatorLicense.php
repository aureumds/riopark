<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOperatorLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        $expiresAt = $request->session()->get('operator_license_expires_at');

        if (! $expiresAt) {
            return redirect()->route('operator-lite.license');
        }

        $graceDays = (int) config('license.grace_days', 3);
        $graceEnd = Carbon::parse($expiresAt)->addDays($graceDays);

        if (now()->gt($graceEnd)) {
            return redirect()->route('operator-lite.license')
                ->with('warning', 'Licença vencida. Renove com conexão à internet.');
        }

        return $next($request);
    }
}
