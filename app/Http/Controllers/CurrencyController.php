<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct(
        public CurrencyService $currencyService
    ) {}

    /**
     * Devuelve el estado actual de la tasa de cambio y la divisa activa.
     */
    public function getRate(): JsonResponse
    {
        $rate = $this->currencyService->getRateUsdToPen();
        $active = $this->currencyService->getActiveCurrency();

        return response()->json([
            'status' => 'success',
            'rate_usd_to_pen' => $rate,
            'active_currency' => $active,
            'reference' => '1 USD = '.number_format($rate, 2).' PEN',
        ]);
    }

    /**
     * Cambia la divisa activa en la sesión del usuario ('PEN' o 'USD').
     */
    public function setCurrency(Request $request): JsonResponse|RedirectResponse
    {
        $currency = $request->input('moneda', $request->input('currency', 'PEN'));
        $active = $this->currencyService->setActiveCurrency($currency);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'active_currency' => $active,
                'rate_usd_to_pen' => $this->currencyService->getRateUsdToPen(),
            ]);
        }

        return redirect()->back();
    }
}
