<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    /**
     * Tasa de cambio de contingencia / referencia cuando la API externa no está disponible.
     */
    public const FALLBACK_USD_TO_PEN = 3.75;

    /**
     * Obtiene la tasa de cambio actual (cuántos Soles peruanos equivale 1 Dólar estadounidense).
     * Se almacena en caché durante 1 hora para optimizar el rendimiento y evitar sobrecargar el servicio externo.
     */
    public function getRateUsdToPen(): float
    {
        return (float) Cache::remember('exchange_rate_usd_to_pen', 3600, function (): float {
            try {
                $response = Http::timeout(3)
                    ->get('https://open.er-api.com/v6/latest/USD');

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['rates']['PEN']) && is_numeric($data['rates']['PEN'])) {
                        return round((float) $data['rates']['PEN'], 4);
                    }
                }
            } catch (\Throwable $e) {
                // Registro silencioso del error y uso de tasa de contingencia
            }

            return self::FALLBACK_USD_TO_PEN;
        });
    }

    /**
     * Convierte un monto en Soles (PEN) a Dólares estadounidenses (USD).
     */
    public function convertPenToUsd(float $amountInPen): float
    {
        $rate = $this->getRateUsdToPen();
        if ($rate <= 0) {
            $rate = self::FALLBACK_USD_TO_PEN;
        }

        return round($amountInPen / $rate, 2);
    }

    /**
     * Convierte un monto en Dólares (USD) a Soles peruanos (PEN).
     */
    public function convertUsdToPen(float $amountInUsd): float
    {
        $rate = $this->getRateUsdToPen();

        return round($amountInUsd * $rate, 2);
    }

    /**
     * Obtiene la divisa actualmente seleccionada en la sesión del usuario ('PEN' o 'USD').
     */
    public function getActiveCurrency(): string
    {
        $currency = session()->get('moneda_activa', 'PEN');

        return in_array($currency, ['PEN', 'USD'], true) ? $currency : 'PEN';
    }

    /**
     * Establece la divisa activa en la sesión.
     */
    public function setActiveCurrency(string $currency): string
    {
        $currency = strtoupper(trim($currency));
        if (! in_array($currency, ['PEN', 'USD'], true)) {
            $currency = 'PEN';
        }

        session()->put('moneda_activa', $currency);

        return $currency;
    }

    /**
     * Retorna la información completa de precios formateados en ambas divisas.
     *
     * @return array{monto_pen: float, monto_usd: float, moneda_activa: string, tasa_cambio: float, principal_formateado: string, secundario_formateado: string}
     */
    public function formatPrices(float $amountInPen): array
    {
        $rate = $this->getRateUsdToPen();
        $amountInUsd = $this->convertPenToUsd($amountInPen);
        $active = $this->getActiveCurrency();

        if ($active === 'USD') {
            $principal = '$ '.number_format($amountInUsd, 2).' USD';
            $secundario = '≈ S/ '.number_format($amountInPen, 2).' PEN';
        } else {
            $principal = 'S/ '.number_format($amountInPen, 2).' PEN';
            $secundario = '≈ $ '.number_format($amountInUsd, 2).' USD';
        }

        return [
            'monto_pen' => $amountInPen,
            'monto_usd' => $amountInUsd,
            'moneda_activa' => $active,
            'tasa_cambio' => $rate,
            'principal_formateado' => $principal,
            'secundario_formateado' => $secundario,
        ];
    }
}
