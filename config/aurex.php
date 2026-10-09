<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Aurex Rewarded Ads
    |--------------------------------------------------------------------------
    |
    | Users watch an ad and earn coins. Rewards are verified server-side with
    | one-time tokens, cooldowns, and daily limits to prevent abuse.
    |
    | To plug in a real ad network (Monetag, Adsterra, ...), paste its
    | rewarded-ad embed code into AUREX_ADS_EMBED_CODE. Until then the
    | store shows a built-in demo ad so the full loop can be tested.
    |
    */

    'ads' => [
        'enabled' => env('AUREX_ADS_ENABLED', true),

        // Coins awarded per verified ad view.
        'reward_coins' => env('AUREX_ADS_REWARD', 100),

        // Max verified ad views per user per day.
        'daily_limit' => env('AUREX_ADS_DAILY_LIMIT', 10),

        // Seconds a user must wait between two verified ad views.
        'cooldown_seconds' => env('AUREX_ADS_COOLDOWN', 60),

        // Max verified ad views per IP address per day (across all users).
        'ip_daily_limit' => env('AUREX_ADS_IP_DAILY_LIMIT', 25),

        // How long a "start" token stays valid (minutes).
        'token_ttl_minutes' => env('AUREX_ADS_TOKEN_TTL', 10),

        // Demo ad length in seconds when no provider embed code is set.
        'demo_duration_seconds' => env('AUREX_ADS_DEMO_SECONDS', 20),

        // Paste your ad network's rewarded-ad HTML/JS here (or leave empty
        // to use the built-in demo ad).
        'embed_code' => env('AUREX_ADS_EMBED_CODE', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Aurex Site Ads (Adsterra / Monetag)
    |--------------------------------------------------------------------------
    |
    | Display ads shown to FREE users across the panel. Premium users never
    | see them. head_code runs on every page (popunder / social bar scripts),
    | banner_code renders as a banner slot on the dashboard.
    |
    */
    'site_ads' => [
        'enabled' => env('AUREX_SITE_ADS_ENABLED', false),
        'head_code' => env('AUREX_SITE_ADS_HEAD_CODE', ''),
        'banner_code' => env('AUREX_SITE_ADS_BANNER_CODE', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Aurex Referrals
    |--------------------------------------------------------------------------
    |
    | Users share their code; a friend enters it once in the panel ("claim").
    | Both sides earn coins. Each user can only ever claim one code.
    |
    */

    'referrals' => [
        'enabled' => env('AUREX_REFERRALS_ENABLED', true),

        // Coins for the referrer when their code is claimed.
        'referrer_bonus' => env('AUREX_REFERRAL_BONUS', 500),

        // Welcome coins for the user who claims a code.
        'referred_bonus' => env('AUREX_REFERRED_BONUS', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Aurex Registration — welcome bonus & anti-abuse
    |--------------------------------------------------------------------------
    |
    | New users can earn a one-time welcome bonus. To stop bonus farming,
    | registrations can be limited to one account per IP address and
    | VPN/proxy connections can be blocked (via a free IP reputation API).
    |
    */

    'registration' => [
        // Give every new user a one-time coin bonus on signup.
        'welcome_bonus_enabled' => env('AUREX_WELCOME_BONUS_ENABLED', true),

        // How many coins the welcome bonus is worth.
        'welcome_bonus_coins' => env('AUREX_WELCOME_BONUS', 500),

        // Only one account may ever be registered from the same IP address.
        'one_per_ip' => env('AUREX_REG_ONE_PER_IP', true),

        // Block registrations coming from known VPN/proxy IP addresses.
        'block_vpn' => env('AUREX_REG_BLOCK_VPN', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Aurex Manual Coin Top-Ups
    |--------------------------------------------------------------------------
    |
    | Users buy coin packages and pay manually (Easypaisa/JazzCash/USDT/
    | Binance). They submit the transaction reference; an admin verifies
    | the payment and approves, which credits the coins.
    |
    */

    'topup' => [
        // Master switch for the top-up system.
        'enabled' => env('AUREX_TOPUP_ENABLED', true),

        // Currency for package prices (PKR, USD, EUR, GBP, INR, IDR, AED, SAR, TRY, BDT).
        // Changeable from Admin → Aurex → Top-ups → Settings.
        'currency' => env('AUREX_TOPUP_CURRENCY', 'PKR'),

        // Payment methods: label shown to users, your account details,
        // and instructions shown at checkout. Empty account disables it.
        'methods' => [
            'easypaisa' => [
                'label' => 'Easypaisa',
                'account' => env('AUREX_TOPUP_EASYPAISA', ''),
                'instructions' => 'Send the exact amount to the Easypaisa number above, then enter the 11-digit Transaction ID (TID) here.',
            ],
            'jazzcash' => [
                'label' => 'JazzCash',
                'account' => env('AUREX_TOPUP_JAZZCASH', ''),
                'instructions' => 'Send the exact amount to the JazzCash number above, then enter the 11-digit Transaction ID (TID) here.',
            ],
            'usdt' => [
                'label' => 'USDT (TRC20)',
                'account' => env('AUREX_TOPUP_USDT', ''),
                'instructions' => 'Send the exact USDT amount to the wallet address above (TRC20 network only), then enter the transaction hash here.',
            ],
            'binance' => [
                'label' => 'Binance Pay',
                'account' => env('AUREX_TOPUP_BINANCE', ''),
                'instructions' => 'Send the payment via Binance Pay to the ID above, then enter the transaction reference/order ID here.',
            ],
        ],

        // WhatsApp notifications.
        // Provider 'wasphere' (recommended): self-hosted WaSphere on your VPS —
        // sends to ANY number (admin + users). Run wasphere-install.sh, scan the
        // QR code, create an API key.
        // Provider 'callmebot': free CallMeBot API — admin self-alerts ONLY
        // (free plan cannot message other numbers). Send
        // "I allow callmebot to send me messages" to +34 623 78 64 49
        // on WhatsApp to get an API key.
        'notifications' => [
            'enabled' => env('AUREX_TOPUP_NOTIFY_ENABLED', false),
            'provider' => env('AUREX_TOPUP_NOTIFY_PROVIDER', 'wasphere'),
            'admin_phone' => env('AUREX_TOPUP_NOTIFY_ADMIN_PHONE', ''),
            'apikey' => env('AUREX_TOPUP_NOTIFY_APIKEY', ''),
            'wasphere_url' => env('AUREX_TOPUP_NOTIFY_WASPHERE_URL', ''),
            'wasphere_key' => env('AUREX_TOPUP_NOTIFY_WASPHERE_KEY', ''),
            'wasphere_session' => env('AUREX_TOPUP_NOTIFY_WASPHERE_SESSION', ''),
        ],

        // Email notifications via Gmail SMTP (free, instant).
        // Setup: Google Account → 2-Step Verification → App Password at
        // myaccount.google.com/apppasswords → paste below in admin settings.
        'email_notifications' => [
            'enabled' => env('AUREX_TOPUP_EMAIL_ENABLED', false),
            'to' => env('AUREX_TOPUP_EMAIL_TO', ''),
            'smtp_user' => env('AUREX_TOPUP_EMAIL_SMTP_USER', ''),
            'smtp_pass' => env('AUREX_TOPUP_EMAIL_SMTP_PASS', ''),
        ],
    ],

];
