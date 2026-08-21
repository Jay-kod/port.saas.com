<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OauthSetting extends Model
{
    protected $fillable = [
        'provider',
        'client_id',
        'client_secret',
        'is_enabled',
    ];

    protected $casts = [
        'client_secret' => 'encrypted',
        'is_enabled' => 'boolean',
    ];

    /**
     * Retrieve provider credentials from DB or fallback to config/services.php.
     */
    public static function getCredentials(string $provider): ?array
    {
        $setting = static::query()->where('provider', $provider)->first();

        if ($setting && ! empty($setting->client_id) && ! empty($setting->client_secret)) {
            return [
                'client_id' => $setting->client_id,
                'client_secret' => $setting->client_secret,
                'is_enabled' => (bool) $setting->is_enabled,
            ];
        }

        $configClientId = config("services.{$provider}.client_id");
        $configClientSecret = config("services.{$provider}.client_secret");

        if (! empty($configClientId) && ! empty($configClientSecret)) {
            return [
                'client_id' => $configClientId,
                'client_secret' => $configClientSecret,
                'is_enabled' => true,
            ];
        }

        return null;
    }
}
