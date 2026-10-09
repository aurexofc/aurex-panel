<?php

namespace Pterodactyl\Providers;

use Psr\Log\LoggerInterface as Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * An array of configuration keys to override with database values
     * if they exist.
     */
    protected array $keys = [
        'app:name',
        'app:locale',
        'recaptcha:enabled',
        'recaptcha:secret_key',
        'recaptcha:website_key',
        'pterodactyl:guzzle:timeout',
        'pterodactyl:guzzle:connect_timeout',
        'pterodactyl:console:count',
        'pterodactyl:console:frequency',
        'pterodactyl:auth:2fa_required',
        'pterodactyl:client_features:allocations:enabled',
        'pterodactyl:client_features:allocations:range_start',
        'pterodactyl:client_features:allocations:range_end',
        // Aurex rewarded ads & referrals — editable from Admin → Aurex.
        'aurex:ads:enabled',
        'aurex:ads:reward_coins',
        'aurex:ads:daily_limit',
        'aurex:ads:cooldown_seconds',
        'aurex:ads:ip_daily_limit',
        'aurex:ads:demo_duration_seconds',
        'aurex:ads:embed_code',
        // Aurex site display ads (Adsterra / Monetag) — free users only.
        'aurex:site_ads:enabled',
        'aurex:site_ads:head_code',
        'aurex:site_ads:banner_code',
        'aurex:referrals:enabled',
        'aurex:referrals:referrer_bonus',
        'aurex:referrals:referred_bonus',
        // Aurex registration — welcome bonus & anti-abuse.
        'aurex:registration:welcome_bonus_enabled',
        'aurex:registration:welcome_bonus_coins',
        'aurex:registration:one_per_ip',
        'aurex:registration:block_vpn',
        // Aurex manual top-ups.
        'aurex:topup:enabled',
        'aurex:topup:currency',
        'aurex:topup:methods:easypaisa:account',
        'aurex:topup:methods:jazzcash:account',
        'aurex:topup:methods:usdt:account',
        'aurex:topup:methods:binance:account',
        // Aurex top-up WhatsApp notifications (WaSphere / CallMeBot).
        'aurex:topup:notifications:enabled',
        'aurex:topup:notifications:provider',
        'aurex:topup:notifications:admin_phone',
        'aurex:topup:notifications:apikey',
        'aurex:topup:notifications:wasphere_url',
        'aurex:topup:notifications:wasphere_key',
        'aurex:topup:notifications:wasphere_session',
        // Aurex top-up email notifications (Gmail SMTP).
        'aurex:topup:email_notifications:enabled',
        'aurex:topup:email_notifications:to',
        'aurex:topup:email_notifications:smtp_user',
        'aurex:topup:email_notifications:smtp_pass',
    ];

    /**
     * Keys specific to the mail driver that are only grabbed from the database
     * when using the SMTP driver.
     */
    protected array $emailKeys = [
        'mail:mailers:smtp:host',
        'mail:mailers:smtp:port',
        'mail:mailers:smtp:encryption',
        'mail:mailers:smtp:username',
        'mail:mailers:smtp:password',
        'mail:from:address',
        'mail:from:name',
    ];

    /**
     * Keys that are encrypted and should be decrypted when set in the
     * configuration array.
     */
    protected static array $encrypted = [
        'mail:mailers:smtp:password',
    ];

    /**
     * Boot the service provider.
     */
    public function boot(ConfigRepository $config, Encrypter $encrypter, Log $log, SettingsRepositoryInterface $settings): void
    {
        // Only set the email driver settings from the database if we
        // are configured using SMTP as the driver.
        if ($config->get('mail.default') === 'smtp') {
            $this->keys = array_merge($this->keys, $this->emailKeys);
        }

        try {
            $values = $settings->all()->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            })->toArray();
        } catch (QueryException $exception) {
            $log->notice('A query exception was encountered while trying to load settings from the database: ' . $exception->getMessage());

            return;
        }

        foreach ($this->keys as $key) {
            $value = array_get($values, 'settings::' . $key, $config->get(str_replace(':', '.', $key)));
            if (in_array($key, self::$encrypted)) {
                try {
                    $value = $encrypter->decrypt($value);
                } catch (DecryptException $exception) {
                }
            }

            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    $value = true;
                    break;
                case 'false':
                case '(false)':
                    $value = false;
                    break;
                case 'empty':
                case '(empty)':
                    $value = '';
                    break;
                case 'null':
                case '(null)':
                    $value = null;
            }

            $config->set(str_replace(':', '.', $key), $value);
        }
    }

    public static function getEncryptedKeys(): array
    {
        return self::$encrypted;
    }
}
