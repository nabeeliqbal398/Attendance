<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpGeolocationService
{
    public function lookup(?string $ip): array
    {
        $default = [
            'ip' => $ip,
            'city' => null,
            'region' => null,
            'country' => null,
            'isp' => null,
            'label' => null,
        ];

        if (!$ip || $this->isPrivateIp($ip)) {
            $default['label'] = $ip ? 'Local network' : 'Unknown';
            return $default;
        }

        return Cache::remember("ipgeo:$ip", now()->addDay(), function () use ($ip, $default) {
            try {
                $response = Http::timeout(2)
                    ->retry(1, 200)
                    ->get("http://ip-api.com/json/{$ip}", [
                        'fields' => 'status,message,country,regionName,city,isp,query',
                    ]);

                if (!$response->successful()) {
                    return $default;
                }

                $data = $response->json();
                if (($data['status'] ?? null) !== 'success') {
                    return $default;
                }

                $city = $data['city'] ?? null;
                $region = $data['regionName'] ?? null;
                $country = $data['country'] ?? null;
                $label = trim(implode(', ', array_filter([$city, $region, $country]))) ?: $ip;

                return [
                    'ip' => $ip,
                    'city' => $city,
                    'region' => $region,
                    'country' => $country,
                    'isp' => $data['isp'] ?? null,
                    'label' => $label,
                ];
            } catch (\Throwable $e) {
                Log::warning('IpGeolocationService lookup failed', [
                    'ip' => $ip,
                    'error' => $e->getMessage(),
                ]);
                return $default;
            }
        });
    }

    public function summarizeUserAgent(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Unknown device';
        }

        $os = match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'iPhone'), str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'Mac OS X') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Other OS',
        };

        $browser = match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'OPR/'), str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => 'Other browser',
        };

        return "$browser on $os";
    }

    private function isPrivateIp(string $ip): bool
    {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
