<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        if (! $user->active) {
            throw ValidationException::withMessages([
                'email' => ['Usuário desativado.'],
            ]);
        }

        if (! $user->hasRole('operator')) {
            throw ValidationException::withMessages([
                'email' => ['Acesso permitido apenas para operadores.'],
            ]);
        }

        $company = $user->company;
        if (! $company || ! app(\App\Services\LicenseService::class)->companyCanOperate($company)) {
            throw ValidationException::withMessages([
                'email' => ['Mensalidade em atraso. Conecte após a liberação no painel.'],
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('operator-pos', ['operator'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load(['company', 'parkingLot']),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logout realizado']);
    }
}
