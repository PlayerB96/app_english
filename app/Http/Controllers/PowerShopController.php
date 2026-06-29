<?php

namespace App\Http\Controllers;

use App\Http\Requests\RedeemPowerCodeRequest;
use App\Services\PowerShopService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PowerShopController extends Controller
{
    public function __construct(
        private readonly PowerShopService $shop,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null || ! $user->isLearner()) {
            abort(403);
        }

        $validated = $request->validate([
            'power_amount' => ['required', 'integer'],
            'payment_method' => ['required', 'string', 'in:yape,plin'],
            'receipt' => ['required', 'image', 'max:5120'],
        ]);

        try {
            $this->shop->submitPurchase(
                $user,
                (int) $validated['power_amount'],
                (string) $validated['payment_method'],
                $request->file('receipt'),
            );
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['receipt' => $exception->getMessage()]);
        }

        return back()->with(
            'status',
            'Recibimos tu comprobante. Validaremos el pago y acreditaremos tu poder pronto.',
        );
    }

    public function redeem(RedeemPowerCodeRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null || ! $user->isLearner()) {
            abort(403);
        }

        try {
            $result = $this->shop->redeemCode($user, (string) $request->validated('code'));
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['redeem_code' => $exception->getMessage()]);
        }

        return back()->with(
            'status',
            "Código canjeado. Se acreditaron {$result['power']} de poder. Tu saldo actual es {$result['balance']}.",
        );
    }
}
