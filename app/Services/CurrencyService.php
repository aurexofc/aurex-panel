<?php

namespace Pterodactyl\Services;

/**
 * Currency helper for Aurex top-up packages.
 *
 * The admin picks a currency from Admin → Aurex → Top-ups → Settings.
 * All prices across the panel, notifications and emails use the selected
 * currency's symbol.
 */
class CurrencyService
{
    /**
     * Supported currencies: code => [symbol, name].
     */
    public const CURRENCIES = [
        'PKR' => ['symbol' => 'Rs', 'name' => 'Pakistani Rupee'],
        'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
        'EUR' => ['symbol' => '€', 'name' => 'Euro'],
        'GBP' => ['symbol' => '£', 'name' => 'British Pound'],
        'INR' => ['symbol' => '₹', 'name' => 'Indian Rupee'],
        'IDR' => ['symbol' => 'Rp', 'name' => 'Indonesian Rupiah'],
        'AED' => ['symbol' => 'AED', 'name' => 'UAE Dirham'],
        'SAR' => ['symbol' => 'SAR', 'name' => 'Saudi Riyal'],
        'TRY' => ['symbol' => '₺', 'name' => 'Turkish Lira'],
        'BDT' => ['symbol' => '৳', 'name' => 'Bangladeshi Taka'],
    ];

    /**
     * Country code (ISO 3166-1 alpha-2) => currency code.
     * Covers the most common visitor countries; unknown countries fall back to USD.
     */
    public const COUNTRY_CURRENCY = [
        'PK' => 'PKR', 'US' => 'USD', 'ES' => 'EUR', 'MX' => 'USD',
        'AR' => 'USD', 'CO' => 'USD', 'PE' => 'USD', 'CL' => 'USD',
        'GB' => 'GBP', 'DE' => 'EUR', 'FR' => 'EUR', 'IT' => 'EUR',
        'NL' => 'EUR', 'BE' => 'EUR', 'PT' => 'EUR', 'GR' => 'EUR',
        'IN' => 'INR', 'ID' => 'IDR', 'MY' => 'USD', 'PH' => 'USD',
        'BD' => 'BDT', 'LK' => 'USD', 'NP' => 'USD', 'AE' => 'AED',
        'SA' => 'SAR', 'QA' => 'USD', 'KW' => 'USD', 'BH' => 'USD',
        'OM' => 'USD', 'TR' => 'TRY', 'EG' => 'USD', 'NG' => 'USD',
        'ZA' => 'USD', 'KE' => 'USD', 'CA' => 'USD', 'AU' => 'USD',
        'NZ' => 'USD', 'JP' => 'USD', 'KR' => 'USD', 'CN' => 'USD',
        'SG' => 'USD', 'HK' => 'USD', 'TH' => 'USD', 'VN' => 'USD',
        'BR' => 'USD', 'RU' => 'USD', 'UA' => 'USD',
    ];

    public static function code(): string
    {
        $code = (string) config('aurex.topup.currency', 'PKR');

        return isset(self::CURRENCIES[$code]) ? $code : 'PKR';
    }

    public static function symbol(): string
    {
        return self::CURRENCIES[self::code()]['symbol'];
    }

    public static function name(): string
    {
        return self::CURRENCIES[self::code()]['name'];
    }

    /**
     * Format an amount with the current currency symbol.
     */
    public static function format(int|float $amount): string
    {
        return self::symbol() . ' ' . number_format($amount);
    }

    /**
     * Detect the visitor's currency from their IP address.
     * Uses ip-api.com (free, cached 24h). Falls back to the admin's
     * configured currency when detection fails or the IP is private.
     */
    public static function detectForIp(?string $ip): string
    {
        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return self::code();
        }

        try {
            $country = \Illuminate\Support\Facades\Cache::remember(
                "aurex:geo:currency:{$ip}",
                86400,
                function () use ($ip) {
                    try {
                        $response = \Illuminate\Support\Facades\Http::timeout(3)
                            ->get("http://ip-api.com/json/{$ip}", ['fields' => 'status,countryCode']);

                        if (!$response->successful()) {
                            return null;
                        }

                        $data = $response->json();

                        return ($data['status'] ?? '') === 'success'
                            ? ($data['countryCode'] ?? null)
                            : null;
                    } catch (\Throwable) {
                        return null;
                    }
                }
            );

            if ($country && isset(self::COUNTRY_CURRENCY[$country])) {
                $detected = self::COUNTRY_CURRENCY[$country];
                if (isset(self::CURRENCIES[$detected])) {
                    return $detected;
                }
            }
        } catch (\Throwable) {
            // Fail open — use admin currency.
        }

        return self::code();
    }

    /**
     * Convert an amount between currencies using live exchange rates.
     * Rates from open.er-api.com (free, no key), cached 24h.
     * Falls back to the unconverted amount on any failure.
     */
    public static function convert(int|float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return (float) $amount;
        }

        try {
            $rate = \Illuminate\Support\Facades\Cache::remember(
                "aurex:fx:{$from}:{$to}",
                86400,
                function () use ($from, $to) {
                    try {
                        $response = \Illuminate\Support\Facades\Http::timeout(5)
                            ->get("https://open.er-api.com/v6/latest/{$from}");

                        if (!$response->successful()) {
                            return null;
                        }

                        $data = $response->json();

                        return ($data['result'] ?? '') === 'success'
                            ? ($data['rates'][$to] ?? null)
                            : null;
                    } catch (\Throwable) {
                        return null;
                    }
                }
            );

            if ($rate && $rate > 0) {
                return round((float) $amount * (float) $rate, 2);
            }
        } catch (\Throwable) {
            // Fail open — return unconverted.
        }

        return (float) $amount;
    }

    /**
     * Format an amount in a specific currency (not the admin default).
     */
    public static function formatIn(int|float $amount, string $currency): string
    {
        $symbol = self::CURRENCIES[$currency]['symbol'] ?? $currency;

        return $symbol . ' ' . number_format($amount);
    }
}
