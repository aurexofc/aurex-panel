<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;

/**
 * Aurex site display ads (Adsterra / Monetag).
 * Free users get the configured banner HTML; premium users get null (ad-free).
 */
class SiteAdController extends ClientApiController
{
    public function banner(Request $request): array
    {
        $user = $request->user();

        if ($user->is_premium) {
            return ['banner_html' => null];
        }

        if (!config('aurex.site_ads.enabled')) {
            return ['banner_html' => null];
        }

        $code = trim((string) config('aurex.site_ads.banner_code'));

        return ['banner_html' => $code !== '' ? $code : null];
    }
}
