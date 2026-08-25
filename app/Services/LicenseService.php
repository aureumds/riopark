<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Device;
use App\Models\License;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;

class LicenseService
{
    public function companyCanIssue(Company $company): bool
    {
        if (! $company->active) {
            return false;
        }

        if ($company->subscription_status === 'blocked') {
            return false;
        }

        return $company->paid_until !== null && $company->paid_until->gte(now()->startOfDay());
    }

    public function companyCanOperate(Company $company): bool
    {
        return $this->companyCanIssue($company);
    }

    public function markPaid(Company $company, ?int $days = null): Company
    {
        $days ??= (int) config('license.period_days', 30);
        $base = $company->paid_until && $company->paid_until->gte(now()->startOfDay())
            ? $company->paid_until->copy()
            : now()->startOfDay();

        $company->update([
            'subscription_status' => 'paid',
            'paid_until' => $base->addDays($days)->toDateString(),
        ]);

        return $company->refresh();
    }

    public function issueForDevice(Device $device, ?User $issuedBy = null): License
    {
        $company = $device->company;

        if (! $this->companyCanIssue($company)) {
            throw new InvalidArgumentException('Empresa sem mensalidade em dia. Marque o pagamento antes de liberar a licença.');
        }

        if (! $device->active) {
            throw new InvalidArgumentException('Máquina desativada.');
        }

        $device->licenses()->whereNull('revoked_at')->update(['revoked_at' => now()]);

        $expiresAt = Carbon::parse($company->paid_until)->endOfDay();
        $jti = (string) Str::uuid();
        $payload = [
            'jti' => $jti,
            'company_id' => $device->company_id,
            'parking_lot_id' => $device->parking_lot_id,
            'device_uid' => $device->device_uid,
            'issued_at' => now()->toIso8601String(),
            'expires_at' => $expiresAt->toIso8601String(),
        ];

        $token = $this->encode($payload);

        return License::create([
            'company_id' => $device->company_id,
            'device_id' => $device->id,
            'jti' => $jti,
            'token' => $token,
            'issued_at' => now(),
            'expires_at' => $expiresAt,
            'issued_by' => $issuedBy?->id,
        ]);
    }

    public function decode(string $token): array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Token de licença inválido.');
        }

        [$body, $signature] = $parts;
        $expected = hash_hmac('sha256', $body, (string) config('license.secret'));

        if (! hash_equals($expected, $signature)) {
            throw new InvalidArgumentException('Assinatura da licença inválida.');
        }

        $padded = strtr($body, '-_', '+/');
        $pad = strlen($padded) % 4;
        if ($pad) {
            $padded .= str_repeat('=', 4 - $pad);
        }
        $json = base64_decode($padded, true);
        $payload = json_decode($json ?: '', true);

        if (! is_array($payload)) {
            throw new InvalidArgumentException('Payload da licença inválido.');
        }

        return $payload;
    }

    public function findValidLicense(string $token): License
    {
        $payload = $this->decode($token);

        $license = License::where('jti', $payload['jti'] ?? '')->first();

        if (! $license || $license->isRevoked()) {
            throw new InvalidArgumentException('Licença revogada ou inexistente.');
        }

        if ($license->expires_at->lt(now())) {
            throw new InvalidArgumentException('Licença vencida.');
        }

        return $license;
    }

    public function toClientPayload(License $license): array
    {
        $license->loadMissing('device');

        return [
            'token' => $license->token,
            'jti' => $license->jti,
            'issued_at' => $license->issued_at->toIso8601String(),
            'expires_at' => $license->expires_at->toIso8601String(),
            'grace_days' => (int) config('license.grace_days', 3),
            'device_uid' => $license->device->device_uid,
        ];
    }

    private function encode(array $payload): string
    {
        $body = rtrim(strtr(base64_encode((string) json_encode($payload)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $body, (string) config('license.secret'));

        return $body.'.'.$signature;
    }
}
