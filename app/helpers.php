<?php

if (! function_exists('formatMoney')) {
    function formatMoney($value, string $currency = 'MZN'): string
    {
        $n = (float) $value;

        return number_format($n, 2, ',', ' ') . ' ' . $currency;
    }
}

if (! function_exists('statusBadge')) {
    function statusBadge($s): string
    {
        $colors = [
            'active' => 'bg-blue-600 text-white',
            'approved' => 'bg-blue-600 text-white',
            'paid' => 'bg-blue-600 text-white',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'open' => 'bg-yellow-100 text-yellow-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            'rejected' => 'bg-red-100 text-red-800',
            'banned' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'refunded' => 'bg-gray-100 text-gray-800',
            'blocked' => 'bg-red-100 text-red-800',
            'answered' => 'bg-blue-100 text-blue-800',
            'closed' => 'bg-gray-100 text-gray-800',
        ];
        $class = $colors[strtolower((string) $s)] ?? 'bg-gray-100 text-gray-800';

        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $class . '">' . e((string) $s) . '</span>';
    }
}

if (! function_exists('commissionValue')) {
    function commissionValue($price, $rate): float
    {
        return round(((float) $price * (float) $rate) / 100, 2);
    }
}

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        static $cache = [];

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        try {
            $row = \App\Models\Setting::where('key', $key)->first();

            return $cache[$key] = $row ? $row->value : $default;
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (! function_exists('flushSettingCache')) {
    function flushSettingCache(): void
    {
        // O helper usa static local; nada a limpar entre requests (cada request recomeça).
    }
}
