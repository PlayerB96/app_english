<?php

namespace App\Services;

use App\Models\PowerCodeRedemption;
use App\Models\PowerPurchaseRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class PowerShopService
{
    public function __construct(
        private readonly TokenService $tokens,
    ) {}

    /**
     * @return list<array{power: int, soles: int}>
     */
    public function packages(): array
    {
        /** @var list<array{power: int, soles: int}> $packages */
        $packages = config('power_shop.packages', []);

        return $packages;
    }

    /**
     * @return array{power: int, soles: int}
     */
    public function resolvePackage(int $powerAmount): array
    {
        foreach ($this->packages() as $package) {
            if ((int) $package['power'] === $powerAmount) {
                return $package;
            }
        }

        throw new InvalidArgumentException('El paquete de poder seleccionado no es válido.');
    }

    public function submitPurchase(
        User $user,
        int $powerAmount,
        string $paymentMethod,
        UploadedFile $receipt,
    ): PowerPurchaseRequest {
        if (! $user->isLearner()) {
            throw new InvalidArgumentException('Solo los aprendices pueden comprar poder.');
        }

        if (! in_array($paymentMethod, ['yape', 'plin'], true)) {
            throw new InvalidArgumentException('El método de pago no es válido.');
        }

        $package = $this->resolvePackage($powerAmount);
        $path = $receipt->store("power-receipts/{$user->id}", 'public');

        if ($path === false) {
            throw new InvalidArgumentException('No se pudo guardar el comprobante.');
        }

        return PowerPurchaseRequest::query()->create([
            'user_id' => $user->id,
            'power_amount' => (int) $package['power'],
            'soles_amount' => (int) $package['soles'],
            'payment_method' => $paymentMethod,
            'receipt_path' => $path,
            'status' => 'pending',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function clientConfig(): array
    {
        return [
            'packages' => $this->packages(),
            'yape' => config('power_shop.yape'),
            'plin' => config('power_shop.plin'),
        ];
    }

    public function receiptUrl(PowerPurchaseRequest $request): string
    {
        return Storage::disk('public')->url($request->receipt_path);
    }

    /**
     * @return array{power: int, balance: int}
     */
    public function redeemCode(User $user, string $publicCode): array
    {
        if (! $user->isLearner()) {
            throw new InvalidArgumentException('Solo los aprendices pueden canjear códigos.');
        }

        $publicCode = $this->normalizeRedeemCode($publicCode);

        if (! preg_match('/^[A-Z0-9]{3}$/', $publicCode)) {
            throw new InvalidArgumentException('El código debe tener 3 caracteres (letras o números).');
        }

        $definition = $this->resolveRedeemCode($publicCode);

        if ($definition === null) {
            throw new InvalidArgumentException('El código ingresado no es válido.');
        }

        $alreadyRedeemed = PowerCodeRedemption::query()
            ->where('user_id', $user->id)
            ->where('internal_code', $definition['internal'])
            ->exists();

        if ($alreadyRedeemed) {
            throw new InvalidArgumentException('Ya canjeaste este código con tu cuenta.');
        }

        return DB::transaction(function () use ($user, $publicCode, $definition): array {
            PowerCodeRedemption::query()->create([
                'user_id' => $user->id,
                'internal_code' => $definition['internal'],
                'public_code' => $publicCode,
                'power_amount' => $definition['power'],
            ]);

            $balance = $this->tokens->earn($user, $definition['power'], "redeem:{$definition['internal']}");

            return [
                'power' => $definition['power'],
                'balance' => $balance,
            ];
        });
    }

    /**
     * @return array{internal: string, power: int}|null
     */
    private function resolveRedeemCode(string $publicCode): ?array
    {
        /** @var array<string, array{public_code: string, power: int}> $codes */
        $codes = config('power_shop.redeem_codes', []);

        foreach ($codes as $internal => $definition) {
            if ($this->normalizeRedeemCode((string) $definition['public_code']) === $publicCode) {
                return [
                    'internal' => (string) $internal,
                    'power' => (int) $definition['power'],
                ];
            }
        }

        return null;
    }

    private function normalizeRedeemCode(string $code): string
    {
        return strtoupper(trim($code));
    }
}
