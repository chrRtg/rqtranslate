<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelTranslate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'guild_id',
        'channel_id',
        'target_channel_id',
        'target_language',
        'autotranslate',
    ];

    public static function isAutomaticTranslationEnabled(string $guild_id, string $channel_id): bool
    {
        return static::query()
            ->where('guild_id', $guild_id)
            ->where('channel_id', $channel_id)
            ->where('autotranslate', true)
            ->exists();
    }
}
